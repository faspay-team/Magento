<?php

namespace Faspay\Debit\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PriceType implements ArrayInterface
{
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Percentage Price')],
            ['value' => 0, 'label' => __('Fixed Price')]];
    }

    
    public function toArray()
    {
        return [1 => __('Percentage Price'),
            0 => __('Fixed Price')];
    }
}