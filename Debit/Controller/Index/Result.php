<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 26/03/2018
 * Time: 16:18
 */

namespace Faspay\Debit\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Result extends Action
{
    
    protected $pageFactory;

    public function __construct(Context $context,
                                PageFactory $pageFactory
                                )
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {

        return $this->pageFactory->create();
    }

}