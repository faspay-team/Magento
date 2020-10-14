<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 27/03/2018
 * Time: 10:37
 */

namespace Faspay\Debit\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\OrderFactory;
use Faspay\Debit\Helper\Data;
use Faspay\Debit\Model\FaspayOrderFactory;

class ThankYou extends Template
{

    protected $theData;
    protected $faspayOrderFactory;
    protected $orderFactory;

    public function __construct(Context $context,
                                Data $theData,
                                OrderFactory $orderFactory,
                                FaspayOrderFactory $faspayOrderFactory,
                                array $data = [])
    {

        parent::__construct($context, $data);
        $this->orderFactory = $orderFactory;
        $this->theData = $theData;
        $this->faspayOrderFactory = $faspayOrderFactory;
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function check(){
        $orderNow = $this->theData->getOrder();
        $orderId = $orderNow->getIncrementId();
        $faspayOrderNow = $this->faspayOrderFactory->create()->load($orderId);
        $channel = $this->theData->getFaspayChannel();

        $result = $this->post($faspayOrderNow);
       
        //checkStatus in DB First
        if($this->validateStatus($faspayOrderNow)){
            
            if($this->setStatus($result,$faspayOrderNow,$orderNow))
            {
                echo "Your Payment process with the following order id : ".$result->bill_no." has been succeed";
            }

            else
                echo "Your Payment process with the following order id : ".$result->bill_no." has been failed, please try again later or contact your merchant if still facing same difficulties";

        }

        else{
            echo "Your Payment process with the following order id : ".$result->bill_no." has been succeed";
        }

    }

    public function post($faspayOrderNow){

        $url = 'https://dev.faspay.co.id/pws/100004/183xx00010100000';

        $xml  = "<faspay>";
        $xml .= "<request>Inquiry Status Payment</request>";
        $xml .= "<trx_id>".$faspayOrderNow->getTrxId()."</trx_id>";
        $xml .= "<merchant_id>".$this->theData->getMerchantId()."</merchant_id>";
        $xml .= "<bill_no>".$faspayOrderNow->getOrderId()."</bill_no>    ";
        $xml .= "<signature>".$faspayOrderNow->getSignature()."</signature>";
        $xml .= "</faspay>";
        
        $c = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array('Content-Type: text/xml'),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => TRUE,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1
        );

        if($xml){
            curl_setopt ($c, CURLOPT_POSTFIELDS, $xml);
        }else {
            curl_setopt ($c, CURLOPT_POSTFIELDS, '');
        }

        curl_setopt_array($c, $curl_options);

        $result = curl_exec($c);
        $result_object = simplexml_load_string($result);

        return $result_object;
    }

    public function setStatus($result,$faspayOrderNow,$orderNow){
        if($result->payment_status_code == 2){
            $orderNow->setState('complete')->setStatus('complete');
            $orderNow->save();

            $faspayOrderNow->setPaymentStatus('SUCCESS');
            $faspayOrderNow->save();
            return TRUE;
        }

        return FALSE;
    }

    public function validateStatus($thisData){
        if($thisData->getPaymentStatus()=='SUCCESS'){
            return false;
        }
        else{
            return true;
        }
    }


}