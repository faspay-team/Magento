<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 13/03/2018
 * Time: 10:59
 */

namespace Faspay\Debit\Helper;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Faspay\Debit\Model\FaspayChannelFactory;
use Faspay\Debit\Model\FaspayOrderFactory;


class Data extends AbstractHelper{

    protected $storeManager;
    protected $orderFactory;
    protected $session;
    protected $_scopeConfig;
    protected $bankNum;
    protected $connection;
    protected $faspayChannelFactory;
    protected $faspayOrderFactory;
    
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Session $session,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeInterface,
        ResourceConnection $connection,
        FaspayChannelFactory $faspayChannelFactory,
        FaspayOrderFactory $faspayOrderFactory
        )
    {
        $this->storeManager = $storeManager;
        parent::__construct($context);
        $this->session = $session;
        $this->orderFactory = $orderFactory;
        $this->_scopeConfig = $scopeInterface;
        $this->connection = $connection->getConnection();
        $this->faspayChannelFactory = $faspayChannelFactory;
        $this->faspayOrderFactory = $faspayOrderFactory;
    }

    public function getConfig($config){
        return $this->_scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //Store Config Getter

    public function getMerchantId(){
    	return $this->getConfig('payment/custompayment/merchant_id');
    }

    public function getMerchantName(){
        return $this->getConfig('payment/custompayment/merchant_name');
    }

    public function getUser(){
    	return $this->getConfig('payment/custompayment/user_id');
    }

    public function getPassword(){
    	return $this->getConfig('payment/custompayment/password');
    }

    public function getExpiry(){
        return $this->getConfig('payment/custompayment/custom_expiry');
    }

    public function getProduction(){
        return $this->getConfig('payment/custompayment/is_production');
    }

    public function getChannel(){
        $channel = $this->getOrder()->getPayment()->getMethod();
        $path = 'payment/' . $channel . '/channel';
        return $this->getConfig($path);
    }

    public function getOrder(){

        $order =$this->session->getLastRealOrder();
        $orderId=$order->getEntityId();
        $orderNow = $this->orderFactory->create()->load($orderId);

        return $orderNow;
    }

    public function getFaspayOrder(){

        $orderNow = $this->getOrder();
        $orderId = $orderNow->getIncrementId();

        $dataModel = $this->faspayOrderFactory->create();
        $faspayOrder = $dataModel->load($orderId);

        return $faspayOrder;
    }

    public function setTransactionData($trx_id,$bank_num,$order_id,$signature,$reserve1=null,$reserve2=null){

        $dataModel = $this->faspayOrderFactory->create();

        $data = [
            'trx_id' => $trx_id,
            'order_id' => $order_id,
            'bank_num' => $bank_num,
            'payment_status' => "PENDING",
            'signature' => $signature,
            'reserve1' => $reserve1,
            'reserve2' => $reserve2
        ];

        $dataModel->setData('order_id',$order_id);
        $dataModel->setData('bank_num',$bank_num);
        $dataModel->setData('trx_id',$trx_id);
        $dataModel->setData('signature',$signature);
        $dataModel->setData('reserve1',$reserve1);
        $dataModel->setData('reserve2',$reserve2);

        $dataModel->save();

    }

    public function getFaspayChannel(){
        $bank_num = $this->getChannel();

        $dataModel = $this->faspayChannelFactory->create();
        $faspayChannel = $dataModel->load($bank_num);

        return $faspayChannel;
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

    public function getTextBcaVa1(){
        return $this->getConfig('payment/bca_va/free_text1');
    }

    public function getTextBcaVa2(){
        return $this->getConfig('payment/bca_va/free_text2');
    }

    public function getFeePayment(){


        $payment = $this->getOrder()->getPayment()->getMethod();
        $type = $this->getConfig("payment/" . $payment . "/pricetype");

        
        if($payment) {

            $amount = $this->getConfig("payment/" . $payment . "/fee");

            //fixed
            if( $type == "0"){

                return $amount;

            }

            //percentage
            elseif ($type == "1"){

                 $add = ($amount/100) * $this->getOrder()->getSubtotal();
                 return $add;

            }
        }
        else{
            return 0;
        }

    }
}