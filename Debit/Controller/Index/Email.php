<?php

namespace Faspay\Debit\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Faspay\Debit\Helper\Data;
use Faspay\Debit\Model\ConfigProvider;

class Email extends \Magento\Framework\App\Action\Action
{

    protected $inlineTranslation;
    protected $transportBuilder;
    protected $storeManager;
    protected $data;
    protected $scopeConfig;
    protected $config;
    protected $orderId;

    public function __construct(
                        Context $context,
                        StateInterface $inlineTranslation,
                        TransportBuilder $transportBuilder,
                        StoreManagerInterface $storeManager,
                        ScopeConfigInterface $scopeConfig,
                        Data $data,
                        ConfigProvider $config)
    {
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->data = $data;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        parent::__construct($context);
    }


    public function execute()
    {
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
        $templateVars['data'] = array(
            'store' => $this->storeManager->getStore(),
            'faspay_order' => $this->getFaspayOrder(),
            'order' => $this->getOrder(),
            'helper' => $this->data,
            'total' => $this->data->getNumFormat(($this->getOrder()->getTotalDue()),2),
            'currency' => $this->getOrder()->getBaseCurrencyCode(),
            'trx_id' =>$this->getFaspayOrder()->getTrxId(),
            'bank_name' =>$this->getFaspayChannel()->getBankName(),
            'bank_code' =>$this->getFaspayChannel()->getBankCode(),
            'imageUrl' =>$this->getImage($this->getFaspayChannel()->getBankCode())
        );
        $from = array('email' => $this->getEmailStore(),
                    'name' => $this->data->getMerchantName());

        
        $this->orderId = $this->getFaspayOrder()->getOrderId();
        
        $this->inlineTranslation->suspend();
        $to = array($this->getOrder()->getCustomerEmail());
        // $transport = $this->transportBuilder->setTemplateIdentifier('faspay_email_template')
        //     ->setTemplateOptions($templateOptions)
        //     ->setTemplateVars($templateVars)
        //     ->setFrom($from)
        //     ->addTo($to)
        //     ->getTransport();
        // $transport->sendMessage();
        $this->inlineTranslation->resume();

        echo $this->orderId;
        $this->_redirect("debit/index/result?order_id=".$this->orderId);
    }

    public function getEmailStore(){
        return $this->scopeConfig->getValue(
            'trans_email/ident_sales/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getFaspayOrder(){

        return $this->data->getFaspayOrder();
    }

    public function getOrder(){

        return $this->data->getOrder();

    }

    public function getFaspayChannel(){

        return $this->data->getFaspayChannel();
        
    }

    public function getImage($code){
        return $this->config->getIcon($code);
    }

}