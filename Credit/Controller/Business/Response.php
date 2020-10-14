<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 18/04/2018
 * Time: 17:14
 */

namespace Faspay\Credit\Controller\Business;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Model\OrderFactory;
use Faspay\Credit\Helper\Data;
use Faspay\Credit\Helper\UrlConfig;

class Response extends Action
{

    protected $data;
    protected $urlConfig;
    protected $orderFactory;

    public function __construct(Context $context,
                                Data $data,
                                UrlConfig $urlConfig,
                                OrderFactory $orderFactory)
    {
        parent::__construct($context);
        $this->data = $data;
        $this->urlConfig = $urlConfig;
        $this->orderFactory = $orderFactory;
    }

    public function execute()
    {

        $response = $this->getFaspayResponse();

        if($this->validateSign($response['SIGNATURE'],$response['TXN_STATUS'],$response['MERCHANT_TRANID'])){

            echo $this->checkStatus($response);
        }
        else{
            echo "INVALID SIGNATURE";
        }

    }

    public function getFaspayResponse()
    {
        $get = file_get_contents('php://input');

        if($get){
            //make to array
            $array = explode("&",$get);

            //assign the key
            $response = array();
            foreach($array as $string){
                $body = explode("=",$string);
                $key = $body[0];
                $value = $body[1];
                $response[$key] = $value;
            }

            return $response;
        }
        else{
            echo "THIS PAGE IS FOR RESPONSE ONLY";
            exit;
        }
    }

    public function checkStatus($response){

        $status = NULL;

        if($response['TXN_STATUS']=="A"){
            $status = "payment_review";
        }
        else if($response['TXN_STATUS']=="B" || $response['TXN_STATUS']=="E" || $response['TXN_STATUS']=="F" || $response['TXN_STATUS']=="V"){
            $status = "canceled";
        }
        else if($response['TXN_STATUS']=="C" || $response['TXN_STATUS']=="S"){

            if($this->checkRisk($response['EXCEED_HIGH_RISK']) == "No"){
                $status = "complete";
            }
            elseif($this->checkRisk($response['EXCEED_HIGH_RISK']) == "Yes"){
                //do autoVoid
                $voidRes = $this->autoThings($response['TRANSACTIONID'],$response['MERCHANT_TRANID'],"V");

                if($voidRes['TXN_STATUS'] == "V"){
                    $status = "canceled";
                }
            }

        }
        else if($response['TXN_STATUS']=="CF" ){
            $status = "pending_payment";
        }
        else if($response['TXN_STATUS']=="P"){
            $status = "processing";
        }

        $this->updateDbStatus($status,$response['MERCHANT_TRANID']);

        if($response['TXN_STATUS']=="A"){

            //do auto capture if no high risk
            if($this->checkRisk($response['EXCEED_HIGH_RISK']) == "No"){

                $this->autoThings($response['TRANSACTIONID'],$response['MERCHANT_TRANID'],"A");

            }
        }
    }

    public function checkRisk($risk){

        //if any risk then false
        if($risk == "Yes"){
            return "Yes";
        }
        else{
            return "No";
        }

    }

    public function autoThings($transId,$orderId,$stat){

        $order = $this->orderFactory->create()->load($orderId);
        $signature = (sha1(strtoupper('##'.$this->data->getMid($orderId).'##'.$this->data->getPassword($orderId).'##'.$orderId.'##'.$this->data->getNumFormat($order->getGrandTotal(),0).'.00##'.$transId.'##')));

        //void 10
        //capture 2
        $trx_type = "";

        if($stat == "A"){
            $trx_type = '2';
        }
        elseif($stat == "V"){
            $trx_type = '10';
        }

        $post = array(
            "PAYMENT_METHOD"		=> '1',
            "TRANSACTIONTYPE"		=> $trx_type,
            "MERCHANTID"			=> $this->data->getMid($orderId),
            "MERCHANT_TRANID"		=> $orderId,
            "TRANSACTIONID"			=> $transId,
            "AMOUNT"				=> $this->data->getNumFormat($order->getGrandTotal(),0).'.00',
            "RESPONSE_TYPE"			=> '3',
            "SIGNATURE"				=> $signature
        );


        $data = http_build_query($post);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $this->urlConfig->getVoidUrl());
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result	= curl_exec($ch);
        curl_close($ch);

        $array = explode(";",$result);

        //assign the key
        $response = array();
        foreach($array as $string){
            $body = explode("=",$string);
            $key = $body[0];
            if($body[1]){
                $value = $body[1];
            }
            else{
                $value = "NULL";
            }
            $response[$key] = $value;
        }


        return $response;
    }

    public function validateSign($signGet,$status,$orderId){

        $order = $this->orderFactory->create()->load($orderId);

        $signature = strtoupper(sha1(strtoupper('##'.$this->data->getMid($orderId).'##'.$this->data->getPassword($orderId).'##'.$orderId.'##'.$this->data->getNumFormat($order->getGrandTotal(),0).'.00##'.$status.'##')));

        return $signature == $signGet;
    }

    public function updateDbStatus($var,$order_id){



        $orderNow = $this->orderFactory->create()->load($order_id);
        $orderNow->setState($var)->setStatus($var);
        $orderNow->save();

    }
}