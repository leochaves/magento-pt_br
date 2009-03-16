<?php
/**
 * Short description
 *
 * Long description
 *
 *
 * Copyright 2008, Renan Gonçalves <renan.saddam@gmail.com>
 * Licensed under The MIT License
 * Redistributions of files must retain the copyright notice.
 *
 * @copyright       Copyright 2008, Renan Gonçalves
 * @category        Cushy
 * @package         Cushy_Boleto
 * @license         http://www.opensource.org/licenses/mit-license.php The MIT License
 */
abstract class Cushy_Boleto_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	/**
	 * Prepare the values to show the bill
	 *
	 * @return array Values to display
	 */
	public function prepareValues() {
		$order = Mage::registry('current_order');
		$address = $order->getBillingAddress();

		// Default Values
		$default = array(
			'nosso_numero' => $order->getIncrementId(),
			'numero_documento' => $order->getIncrementId(),
			'data_vencimento' => date('d/m/Y', time() + (Mage::getStoreConfig('payment/' . $this->_code . '/due_date') * 86400)),
			'data_documento' => date('d/m/Y'),
			'data_processamento' => date('d/m/Y'),
			'valor_boleto' => number_format($order->getGrandTotal(), 2, ',', ''),
			'sacado' => $address->getFirstname() . ' ' . $address->getLastname(),
			'endereco1' => implode(' ', $address->getStreet()),
			'endereco2' => $address->getCity() . ' - ' . $address->getRegion() . ' - CEP: ' . $address->getPostcode(),
			'identificacao' => Mage::getStoreConfig('payment/' . $this->_code . '/identification'),
			'cpf_cnpj' => Mage::getStoreConfig('payment/' . $this->_code . '/cpf_cnpj'),
			'endereco' => Mage::getStoreConfig('payment/' . $this->_code . '/address'),
			'cidade_uf' => Mage::getStoreConfig('payment/' . $this->_code . '/city_region'),
			'cedente' => Mage::getStoreConfig('payment/' . $this->_code . '/transferor')
		);

		// Instructions sentences
		$instructions = explode("\n", Mage::getStoreConfig('payment/' . $this->_code . '/instructions'));
		for ($i = 0; $i < 4; $i++) {
			$instruction = isset($instructions[$i]) ? $instructions[$i] : '';
			$default['instrucoes' . ($i + 1)] = $instruction;
		}

		// Extra Informations
		$informations = explode("\n", Mage::getStoreConfig('payment/' . $this->_code . '/informations'));
		for ($i = 0; $i < 3; $i++) {
			$information = isset($informations[$i]) ? $informations[$i] : '';
			$default['demonstrativo' . ($i + 1)] = $information;
		}

		// Agency
		$agency = Mage::getStoreConfig('payment/' . $this->_code . '/agency');
		$default['agencia'] = substr($agency, 0, -1);
		$default['agencia_dv'] = substr($agency, -1);

		// Account
		$account = Mage::getStoreConfig('payment/' . $this->_code . '/account');
		$default['conta'] = substr($account, 0, -1);
		$default['conta_dv'] = substr($account, -1);

		return $this->_prepareValues($order, $default);
	}

	/**
	 * If the method needs specific data, that is the place
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @param array $values
	 * @return array Values to Display
	 */
	protected function _prepareValues(Mage_Sales_Model_Order $order, $values) {
		return $values;
	}
}