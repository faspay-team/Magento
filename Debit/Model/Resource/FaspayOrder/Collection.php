<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 26/03/2018
 * Time: 17:11
 */

namespace Faspay\Debit\Model\Resource\FaspayOrder;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
      protected function _construct()
      {
          $this->_init(
                'Faspay\Debit\Model\FaspayOrder',
                'Faspay\Debit\Model\Resource\FaspayOrder'
          );
      }
}