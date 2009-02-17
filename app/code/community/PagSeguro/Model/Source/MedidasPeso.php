<?php
/**
 * Magento PagSeguro Payment Modulo
 *
 * @category   Shipping
 * @package    Pagseguro
 * @copyright  Author Guilherme Dutra (godutra@gmail.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PagSeguro Payment Action Dropdown source
 *
 */
class PagSeguro_Model_Source_MedidasPeso
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'gr', 'label' => 'gramas'),
            array('value' => 'kg', 'label' => 'kilogramas'),
        );
    }
}
