<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 26/03/2018
 * Time: 17:08
 */

namespace Faspay\Debit\Model;


use Magento\Framework\Model\AbstractModel;

class FaspayChannel extends AbstractModel
{
     protected function _construct()
     {
         $this->_init('Faspay\Debit\Model\Resource\FaspayChannel');
     }
}