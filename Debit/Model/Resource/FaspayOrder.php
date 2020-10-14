<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 26/03/2018
 * Time: 17:10
 */

namespace Faspay\Debit\Model\Resource;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FaspayOrder extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected $_isPkAutoIncrement = false;

    protected function _construct()
    {
        $this->_init('faspay_order','order_id');
    }
}