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
class Cushy_Boleto_StandardController extends Mage_Core_Controller_Front_Action {
	/**
	 * The name of the Boleto
	 *
	 * @see Cushy_Boleto_StandardController::_canViewOrder
	 * @var string
	 */
	protected $_method;

	/**
	 * Generate the bill
	 *
	 * @return void
	 */
	public function viewAction() {
		if (!$this->_loadValidOrder()) {
			return false;
		}

		$dadosboleto = Mage::getModel('boleto/' . $this->_method)->prepareValues();
		foreach ($dadosboleto as $key => $value) {
			$dadosboleto[$key] = utf8_decode($value);
		}

		$path = BP . DS . 'skin' . DS . 'boletophp' . DS . 'include' . DS;
		ob_start();
			include $path . 'funcoes_' . $this->_method . '.php';
			include $path . 'layout_' . $this->_method . '.php';
		$content = ob_get_clean();

		$url = preg_replace('/index\.php\/$/', '', Mage::getUrl('/')) . 'skin/boletophp/';
		$content = str_ireplace(array('src=imagens', 'src="imagens'), array('src=' . $url . 'imagens', 'src="' . $url . 'imagens'), $content);
		$content = str_ireplace('<body', '<body onload="window.print();"', $content);

		echo $content;
		exit;
	}

	/**
	 * Gets the order_id parameter passed by url and put order on Registry
	 *
	 * @param mixed $orderId
	 * @return boolean
	 */
	protected function _loadValidOrder($orderId = null) {
		if ($orderId == null) {
			$orderId = (int) $this->getRequest()->getParam('order_id');
		}
		if (!$orderId) {
			$this->_forward('noRoute');
			return false;
		}

		$order = Mage::getModel('sales/order')->load($orderId);
		if ($this->_canViewOrder($order)) {
			Mage::register('current_order', $order);
			return true;
		} else {
			$this->_redirect('sales/order/history');
			return false;
		}
	}

	/**
	 * Check if the current user made the given order
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @return boolean
	 */
	protected function _canViewOrder($order) {
		$customerId = Mage::getSingleton('customer/session')->getCustomerId();
		$availableStates = Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates();
		$method = $order->getPayment()->getMethod();
		if ($order->getCustomerId() == $customerId && in_array($order->getState(), $availableStates, true) && strpos($method, 'boleto_') !== false) {
			$this->_method = substr($method, 7);
			return true;
		}
		return false;
	}
}