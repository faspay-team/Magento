<?php
namespace Faspay\Debit\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;

class Thankyou extends Action
{
    protected $pageFactory;



    public function __construct(Context $context,
                                PageFactory $pageFactory
                                )
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);

    }

    public function execute()
    {
        return $this->pageFactory->create();
    }
}