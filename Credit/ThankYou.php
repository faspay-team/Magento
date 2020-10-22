<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 27/03/2018
 * Time: 10:37
 */

namespace Faspay\Credit\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\OrderFactory;
use Faspay\Credit\Helper\Data;
use Faspay\Credit\Helper\UrlConfig;

class ThankYou extends Template
{

    protected $theData;
    protected $orderFactory;
    protected $urlConfig;

    public function __construct(Context $context,
                                Data $theData,
                                OrderFactory $orderFactory,
                                array $data = [],
                                UrlConfig $urlConfig)
    {

        parent::__construct($context, $data);
        $this->orderFactory = $orderFactory;
        $this->theData = $theData;
        $this->urlConfig = $urlConfig;
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function check($order_id,$get){
        if($get){
            
        }
        $order_id = $get['MERCHANT_TRANID'];
        $orderNow = $this->orderFactory->create()->loadByIncrementId($order_id);
        $resultInquiry = $this->inquiry($orderNow,$get);

        if($this->validateStatus($orderNow) == "complete"){
            return "Your Payment process with the following order id : ".$order_id." has been succeed";
        }
        else if($this->validateStatus($orderNow) == "payment_review"){
            return "Your Payment process with the following order id : ".$order_id." is still on process, please contact your merchant for further assistance";
        }
        else{
            if($this->setStatus($resultInquiry['TXN_STATUS'],$orderNow) == 'complete'){
                return "Your Payment process with the following order id : ".$order_id." has been succeed";
            }
            elseif($this->setStatus($resultInquiry['TXN_STATUS'],$orderNow) == 'payment_review'){
                return "Your Payment process with the following order id : ".$order_id." is still on process, please contact your merchant for further assistance"; 
            }
            elseif($this->setStatus($resultInquiry['TXN_STATUS'],$orderNow) == 'payment_review'){
                return "Your Payment process with the following order id : ".$order_id." is still on process, please contact your merchant for further assistance"; 
            }
            return "Your Payment process with the following order id : ".$order_id." has been failed, please try again or contact your merchant if still facing same difficulties";
        }

    }

    public function inquiry($orderNow,$response){
        $order_id = @$orderNow->getIncrementId();
        $amount = $this->theData->getNumFormat($orderNow->getTotalDue(),0);
        $signature = (sha1(strtoupper('##'.$this->theData->getMid($order_id).'##'.$this->theData->getPassword($order_id).'##'.$order_id.'##'.$amount.'.00##'.$response['TRANSACTIONID'].'##')));

        $post = array(
        "TRANSACTIONTYPE"      => '4',
        "RESPONSE_TYPE"        => '3',
        "MERCHANTID"           => $this->theData->getMid($order_id),
        "PAYMENT_METHOD"       => '1',
        "MERCHANT_TRANID"      => $order_id,
        "TRANSACTIONID"        => $response['TRANSACTIONID'],
        "AMOUNT"               => $this->theData->getNumFormat($orderNow->getGrandTotal(),0).'.00',
        "SIGNATURE"            => $signature
        );
    
        
         $post = http_build_query($post);
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
         curl_setopt($ch, CURLOPT_URL, $this->urlConfig->getVoidUrl());
         curl_setopt($ch, CURLOPT_HEADER, 0);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         
         $result = curl_exec($ch);
         curl_close($ch);

        $array = explode(";",$result);

        //assign the key
        $responseInquiry = array();
        foreach($array as $string){
            $body = explode("=",$string);
            $key = $body[0];
            if($body[1]){
                $value = $body[1];
            }
            else{
                $value = "NULL";
            }
            $responseInquiry[$key] = $value;
        }

        return $responseInquiry;
    }

    public function setStatus($result,$orderNow){
        if($result == 'S' || $result == 'C'){
            $orderNow->setState('complete')->setStatus('complete');
            $orderNow->save();

            return 'complete';
        }
        else if($result == 'A'){
            $orderNow->setState('payment_review')->setStatus('payment_review');
            $orderNow->save();

            return 'payment_review';
        }
        else if($result == 'V' || $result == 'F'){
            $orderNow->setState('canceled')->setStatus('canceled');
            $orderNow->save();

            return FALSE;
        }
        else{
           return FALSE; 
        }

        
    }

    public function validateStatus($thisData){
        if($thisData->getStatus()=='complete'){
            return "complete";
        }
        else if($thisData->getStatus()=='payment_review'){
            return "pending";
        }
        else{
            return "failed";
        }
    }


}