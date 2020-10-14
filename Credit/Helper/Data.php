<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 13/03/2018
 * Time: 10:59
 */

namespace Faspay\Credit\Helper;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;


class Data extends AbstractHelper{

    protected $storeManager;
    protected $orderFactory;
    protected $session;
    protected $_scopeConfig;
    protected $bankNum;
    protected $connection;
    
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Session $session,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeInterface,
        ResourceConnection $connection
        )
    {
        $this->storeManager = $storeManager;
        parent::__construct($context);
        $this->session = $session;
        $this->orderFactory = $orderFactory;
        $this->_scopeConfig = $scopeInterface;
        $this->connection = $connection->getConnection();
    }

    public function getConfig($config){
        return $this->_scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //Store Config Getter

    public function getProduction(){
        return $this->getConfig('payment/creditpayment/is_production');
    }

    public function getTitle($orderId){
        $orderNow = $this->orderFactory->create()->load($orderId);
        $channel = $orderNow->getPayment()->getMethod();
        $path = 'payment/' . $channel . '/title';
        return $this->getConfig($path);
    }

    public function getMid($orderId){
        $orderNow = $this->orderFactory->create()->load($orderId);
        $channel = $orderNow->getPayment()->getMethod();
        $path = 'payment/' . $channel . '/mid';
        return $this->getConfig($path);
    }

    public function getPassword($orderId){
        $orderNow = $this->orderFactory->create()->load($orderId);
        $channel = $orderNow->getPayment()->getMethod();
        $path = 'payment/' . $channel . '/password';
        return $this->getConfig($path);
    }

    public function getMinimum($channel){
        $path = 'payment/' . $channel . '/minimum';
        return $this->getConfig($path);
    }

    public function getOrder(){

        $order =$this->session->getLastRealOrder();
        $orderId=$order->getEntityId();
        $orderNow = $this->orderFactory->create()->load($orderId);

        return $orderNow;
    }

    public function getNumFormat($num,$dec){

        $amount = number_format((float)$num, $dec, '.', '');

        return $amount;
    }

    public function getDate7($date){

        //set Jakarta TimeZone
        $date = new \DateTime( $date, new \DateTimeZone('UTC'));
        $tz = new \DateTimeZone('Asia/Jakarta');
        $date->setTimeZone($tz);

        return $date;
    }

}