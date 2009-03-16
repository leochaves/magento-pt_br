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
class Cushy_Boleto_Model_Itau extends Cushy_Boleto_Model_Standard {
	/**
	 * _code property
	 *
	 * @var string
	 */
	protected $_code = 'boleto_itau';

	/**
	 * Prepare the values to show in the bill
	 *
	 * @see Cushy_Boleto_Model_Standard::prepareValues
	 * @param Mage_Sales_Model_Order $order
	 * @param array $values
	 * @return array Values to Display
	 */
	protected function _prepareValues(Mage_Sales_Model_Order $order, $values) {
		$values = array_merge($values, array(
			'quantidade' => '001',
			'valor_unitario' => $values['valor_boleto'],
			'aceite' => '',
			'especie' => 'R$',
			'especie_doc' => '',
			'carteira' => Mage::getStoreConfig('payment/' . $this->_code . '/carteira')
		));

		return $values;
	}
}