<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 26/03/2018
 * Time: 17:10
 */

namespace Faspay\Debit\Model\Resource;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FaspayChannel extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('faspay_channel','bank_num');
    }
}