<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 13/03/2018
 * Time: 10:59
 */

namespace Faspay\Debit\Block;



use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;


class Base extends Template{

   
	public function __construct(
        Context $context,
        array $data = [])
	{
    	parent::__construct($context,$data);
	}

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

}