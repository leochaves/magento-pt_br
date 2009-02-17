<?php
/**
* PagSeguro Module Adapter
*/

class PagSeguro_Model_PagSeguro extends Mage_Payment_Model_Method_Abstract {
  //changing the payment to different from cc payment type and PagSeguro payment type
  const PAYMENT_TYPE_AUTH = 'AUTHORIZATION';
  const PAYMENT_TYPE_SALE = 'SALE';
  //const ACTION_AUTHORIZE          = 'authorize';
  //const ACTION_AUTHORIZE_CAPTURE  = 'authorize_capture';
    
  /**
  * unique internal payment method identifier
  *
  * @var string [a-z0-9_]
  */
  protected $_code = 'PagSeguro';

  protected $_allowCurrencyCode = array('BRL');

  protected $_formBlockType = 'PagSeguro_Block_Form';

  /**
   * Here are examples of flags that will determine functionality availability
   * of this module to be used by frontend and backend.
   *
   * @see all flags and their defaults in Mage_Payment_Model_Method_Abstract
   *
   * It is possible to have a custom dynamic logic by overloading
   * public function can* for each flag respectively
   */

  /**
   * Is this payment method a gateway (online auth/charge) ?
   */
  protected $_isGateway               = true;

  /**
   * Can authorize online?
   */
  protected $_canAuthorize            = true;

  /**
   * Can capture funds online?
   */
  protected $_canCapture              = true;

  /**
   * Can capture partial amounts online?
   */
  protected $_canCapturePartial       = false;

  /**
   * Can refund online?
   */
  protected $_canRefund               = false;

  /**
   * Can void transactions online?
   */
  protected $_canVoid                 = true;

  /**
   * Can use this payment method in administration panel?
   */
  protected $_canUseInternal          = true;

  /**
   * Can show this payment method as an option on checkout payment page?
   */
  protected $_canUseCheckout          = true;

  /**
   * Is this payment method suitable for multi-shipping checkout?
   */
  protected $_canUseForMultishipping  = true;

  /**
   * Can save credit card information for future processing?
   */
  protected $_canSaveCc = false;

  /**
   * Here you will need to implement authorize, capture and void public methods
   *
   * @see examples of transaction specific public methods such as
   * authorize, capture and void in Mage_Paygate_Model_Authorizenet
   */

     /**
     * Get PagSeguro session namespace
     *
     * @return Mage_PagSeguro_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('PagSeguro/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Using for multiple shipping address
     *
     * @return bool
     */
    public function canUseForMultishipping()
    {
        return true;
    }

    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('PagSeguro/form', $name)
            ->setMethod('PagSeguro')
            ->setPayment($this->getPayment())
            ->setTemplate('PagSeguro/form.phtml');

        return $block;
    }
    
    //Add New
    public function getTransactionId()
    {
        return $this->getSessionData('transaction_id');
    }

    public function setTransactionId($data)
    {
        return $this->setSessionData('transaction_id', $data);
    }

    public function validate() {
   }

   public function getOrderPlaceRedirectUrl() {
	return Mage::getUrl('pagseguro/redirect');
   }

   public function getCheckoutFormFields() {
        $a = $this->getQuote()->getShippingAddress();

        $currency_code = $this->getQuote()->getBaseCurrencyCode();

       	$sArr = array(
            'email_cobranca'    =>  $this->getConfigData('emailID'),
            'Tipo'              => "CP",
            'Moeda'             => "BRL",
       	    'ref_transacao'     => $this->getCheckout()->getLastRealOrderId(),
            'cliente_nome'      => $a->getFirstname() . ' ' . $a->getLastname(),
            'cliente_cep'       => $a->getPostcode(),
            'cliente_end'       => $a->getStreet(1),
            'cliente_num'       => "?",
            'cliente_compl'     => $a->getStreet(2),
            'cliente_bairro'    => "?",
            'cliente_cidade'    => $a->getCity(),
            'cliente_uf'        => $a->getState(),
            'cliente_pais'      => $a->getCountry(),
            'cliente_ddd'       =>substr($a->getTelephone(),0,-8),
            'cliente_tel'       =>substr($a->getTelephone(),-8),
            'cliente_email'     =>$a->getEmail(),
        );
	
	$items = $this->getQuote()->getAllItems();

        if ($items) {
            $i = 1;
            foreach($items as $item){
                    $sArr = array_merge($sArr, array(
        	            'item_descr_'.$i   => $item->getName(),
        	            'item_id_'.$i      => $item->getSku(),
                	    'item_quant_'.$i   => $item->getQty(),
			    'item_peso_'.$i    => 0,
				//'item_peso_'.$i    => round($item->getWeight()),   //  para o PagSeguro calcular o frete, tem que passar via Post o peso arredondado, senão dá erro  //  desligado para quando o modulo de correio no Magento estiver funcionando
			    'item_frete_'.$i   => 0,
                            'item_valor_'.$i   => ($item->getBaseCalculationPrice() - $item->getBaseDiscountAmount())*100,
		    ));

                    if($item->getBaseTaxAmount()>0) {
	                    $sArr = array_merge($sArr, array(
		                       'tax_'.$i => sprintf('%.2f',$item->getBaseTaxAmount()),
                            ));
                    }
                    $i++;
 	    }
        }

        $transaciton_type = $this->getConfigData('transaction_type');
        $totalArr = $a->getTotals();
        $shipping = sprintf('%.2f', $this->getQuote()->getShippingAddress()->getBaseShippingAmount());

	//passa o valor do frete total em uma única variavel para o pagseguro, utilizado junto com o modulo de correio
        $sArr = array_merge($sArr, array('item_frete_1' => str_replace(".", ",", $shipping * 100) ));
        $sReq = '';
        $rArr = array();

        foreach ($sArr as $k=>$v) {
	    /*
            replacing & char with and. otherwise it will break the post
            */
            $value =  str_replace("&","and",$v);
            $rArr[$k] =  $value;
            $sReq .= '&'.$k.'='.$value;
        }

        if ($this->getDebug() && $sReq) {
            $sReq = substr($sReq, 1);
            $debug = Mage::getModel('PagSeguro/api_debug')
                    ->setApiEndpoint($this->getPagSeguroUrl())
                    ->setRequestBody($sReq)
                    ->save();
        }

        return $rArr;
    }

    //define a url do pagseguro
    public function getPagSeguroUrl() {
         $url='https://pagseguro.uol.com.br/security/webpagamentos/webpagto.aspx';
         return $url;
    }

    public function getDebug() {
	    return Mage::getStoreConfig('PagSeguro/wps/debug_flag');
    }

    public function updateOrder($FromData) {
    }

    public function ipnPostSubmit() {
    }

    public function getTitle() {
  	return $this->getData('title');
    }
}
?>
