<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 15/03/2018
 * Time: 9:44
 */

namespace Faspay\Debit\Controller\Index;

use Faspay\Debit\Helper\Data;
use Faspay\Debit\Helper\BcaUtility;
use Faspay\Debit\Helper\UrlConfig;
use Faspay\Debit\Model\FaspayChannelFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Asset\Repository;

class Redirect extends Action
{
    protected $data;
    protected $bcadata;
    protected $urlConfig;
    protected $faspayChannelFactory;
    protected $session;

    //tenor handler
    protected $tenor;
    protected $tenorFlag;
    protected $assetRepo;

    public function __construct(Context $context,
                                Data $data,
                                BcaUtility $bcadata,
                                UrlConfig $urlConfig,
                                FaspayChannelFactory $faspayChannelFactory,
                                Session $session,
                                Repository $assetRepo
    )
    {
        parent::__construct($context);
        $this->data = $data;
        $this->bcadata = $bcadata;
        $this->urlConfig = $urlConfig;
        $this->faspayChannelFactory = $faspayChannelFactory;
        $this->session = $session;
        $this->assetRepo = $assetRepo;
    }

    public function execute()
    {
        $this->checkTenor();

        $channel = $this->data->getFaspayChannel();
        
        if ($channel->getBankFlow()==2){
            $this->redirectToFaspay();
            $this->_redirect('debit/index/email');
        }
        elseif ($channel->getBankFlow()==1){
            $url = $this->resultRedirectFactory->create()->setUrl($this->redirectToFaspay());
            return $url;
        }

    }
    public function redirectToFaspay(){

        $order = $this->data->getOrder();
        $expiry = $this->data->getExpiry();
        $channel = $this->data->getFaspayChannel();

        //created at in JKT TimeZone
        $date = $this->data->getDate7($order->getCreatedAt());

        //timeleft
        $timestamp = strtotime($date->format('H:i:s')) + 60 * $expiry;
        $timeleft = date("Y-m-d H:i:s",$timestamp);

        //bankChannel
        $bankChannel = $this->data->getChannel();

        if($bankChannel == "403"){
            $bankChannel = "402";
        }

        $params = array(
            'payment_channel' => $bankChannel,
            'bill_no' => $order->getIncrementId(),
            'bill_total' => $this->data->getNumFormat($order->getTotalDue(),0).'00',
            'bill_gross' => $this->data->getNumFormat($order->getSubtotalIncltax(),0).'00',
            'bill_miscfee' => $this->data->getNumFormat(($order->getTotalDue() - $order->getSubtotalIncltax()),0).'00',
            'bill_date' => $date->format('Y-m-d H:i:s'),
            'bill_expired' => $timeleft,
            'bill_desc' => 'Pembayaran',
            'bill_currency' => $order->getBaseCurrencyCode(),
            'pay_type'  => $this->tenorFlag
        );
        $loads = $params;

        $signatureRequest = $this->generateSignature($params['bill_no']);

        $user = array(
            'merchant_id' => $this->data->getMerchantId(),
            'merchant' => $this->data->getMerchantName(),
            'signature'   => $signatureRequest
        );

        $buyer = array(
            'cust_name' => $order->getCustomerFirstName()." ".$order->getCustomerLastName(),
            'msisdn' => $order->getShippingAddress()->getTelephone(),
            'email' => $order->getCustomerEmail(),
            'shipping_address'  => $order->getShippingAddress()->getData('street'),
            'shipping_address_city' => $order->getShippingAddress()->getCity(),
            'shipping_address_region' => $order->getShippingAddress()->getRegion(),
            'shipping_address_poscode'  => $order->getShippingAddress()->getPostCode(),
            'shipping_msisdn'         => $order->getShippingAddress()->getTelephone(),
            'shipping_address_country_code'  => $order->getShippingAddress()->getCountryId(),
            'receiver_name_for_shipping' => $order->getShippingAddress()->getFirstname()." ". $order->getShippingAddress()->getLastname(),
            'billing_name' => $order->getBillingAddress()->getFirstname()." ".$order->getBillingAddress()->getLastname(),
            'billing_address'  => $order->getBillingAddress()->getData('street'),
            'billing_address_city' => $order->getBillingAddress()->getCity(),
            'billing_address_region' => $order->getBillingAddress()->getRegion(),
            'billing_address_poscode'  => $order->getBillingAddress()->getPostCode(),
            'billing_msisdn'         => $order->getBillingAddress()->getTelephone(),
            'billing_address_country_code'  => $order->getBillingAddress()->getCountryId()
        );


        //for handling customer name if not login
        if(!$this->session->isLoggedIn()){
            
            $noLogin = array(

                'cust_name' => $order->getShippingAddress()->getFirstname()." ". $order->getShippingAddress()->getLastname()

            );

            $buyer = array_merge($buyer,$noLogin);
        }

        $items = $order->getAllItems();

        $ext = array(
            'request'   => 'Post Data Transaksi',
            'terminal'  => 10,
            'signature' => $signatureRequest
        );

        $loads = array_merge($loads, $user, $buyer, $ext );

        
        $postUrl = $this->urlConfig->getPostUrl();
        $result = $this->post($postUrl,$loads, $items);
        $trx_id = $result->trx_id;
        if (isset($result->web_url)) {
            $web_url = $result->web_url;
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');

            $rootPath  =  $directory->getPath('app');
            $url = $rootPath.'/code/Faspay/Debit/view/frontend/web/images/qr/'.$trx_id.'.png';
            file_put_contents($url, file_get_contents($web_url));
        }else{
            $web_url = null;
        }

        $redirectUrl = $this->urlConfig->getRedirectUrl();
        $url = $redirectUrl.$signatureRequest."?trx_id=".$trx_id."&merchant_id=".$result->merchant_id."&bill_no=".$result->bill_no;

        //add to database TransData
        $this->data->setTransactionData($result->trx_id,$bankChannel,$order->getIncrementId(),$signatureRequest,$web_url);

        if ($channel->getBankFlow()==2){
            return $result;
        }
        elseif ($channel->getBankFlow()==1){
            if($this->data->getChannel()== 405){
                $this->redirectToBca($url);
            }
            if($this->data->getChannel()== 403){
                $this->redirectToPermataNet($order,$trx_id);
            }
            else{
                return $url;
            }

        }

    }

    public function post($url, $loads,$items){
        $request = $this->prepDataDebit($loads,$items);

        
        return $this->callServer($url, $request);
    }

    public function prepDataDebit($params,$items){

        $body   = '<faspay>';
        foreach ($params as $param => $value) {
            $body .= "<$param>".$value."</$param>";
        }
        $countTenor=0;

        foreach($items as $item) {


            $body .= '<item>';
            $body .= "<id>".$item->getProductId()."</id>";
            $body .= "<product>" . $item->getName() . "</product>";
            $body .= "<qty>" . number_format($item->getQtyOrdered()) . "</qty>";
            $body .= "<amount>" . $this->data->getNumFormat($item->getRowTotalInclTax(),0) ."00</amount>";


            //for BCA KlikPay
            if($this->data->getChannel()==405){
                $body .= "<tenor>".$this->tenor[$countTenor]."</tenor>";

                if($this->tenor[$countTenor]!=00){
                    $body .= "<payment_plan>2</payment_plan>";
                    $body .= "<merchant_id>" . $this->bcadata->getMid(number_format($this->tenor[$countTenor])) . "</merchant_id>";
                }
                else{
                    $body .= "<payment_plan>1</payment_plan>";
                    $body .= "<merchant_id>" . $this->data->getMerchantId() . "</merchant_id>";
                }

            }

            //for any else
            else{
                $body .= "<merchant_id>" . $this->data->getMerchantId() . "</merchant_id>";
                $body .= "<payment_plan>1</payment_plan>";
                $body .= "<tenor>00</tenor>";
            }
            
            $body .= '</item>';
            $countTenor++;
        }

        //for BCA VA
        if($this->data->getChannel()==702){
            $body .= "<reserve1>".$this->data->getTextBcaVa1()."</reserve1>";
            $body .= "<reserve2>".$this->data->getTextBcaVa2()."</reserve2>";
        }

        else if($this->data->getChannel()==709){
            $body .= "<reserve2>30_days</reserve2>";
        }


        $body  .= '</faspay>';

        return $body;
    }

    public function callServer($url , $data){

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

        if($data){
            curl_setopt ($c, CURLOPT_POSTFIELDS, $data);
        }else {
            curl_setopt ($c, CURLOPT_POSTFIELDS, '');
        }

        curl_setopt_array($c, $curl_options);

        $result = curl_exec($c);


        if($result === FALSE){
            echo curl_error($c);
        }else{
            $result_object = simplexml_load_string($result);
    
            if (isset($result_object->response_error)) {
                $error = $result_object->response_error;
                $message = 'Faspay Error (' . $error->response_code . '): ' . $error->response_desc;

                echo $message;
                exit;
            } else {
                return $result_object;
            }
        }

    }

    public function generateSignature($bill_no){

        $signature = sha1(md5($this->data->getUser().$this->data->getPassword().$bill_no));
        return $signature;

    }

    public function redirectToBca($url){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        echo $result;
        exit;
    }

    public function redirectToPermataNet($order,$trx_id){

        $amount = $this->data->getNumFormat($order->getSubtotalIncltax(),0);

        $post = array(
            "va_number"			=>$trx_id,
            "amount" 			=>$amount.'.00'
        );

        //redirect to Permata net
        $string = '<form method="post" name="form" action="'.$this->urlConfig->getPermataNetUrl().'">';
        if ($post != null) {
            foreach ($post as $name=>$value) {
                $string .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
            }
        }

        $string .= '</form>';
        $string .= '<script> document.form.submit();</script>';

        echo $string;
        exit;
    }

    public function checkTenor(){

        //for BCA KlikPay
        if(isset($_GET['tenor'])){

            $noslash = explode("/",$_GET['tenor']);
            $this->tenor = explode(",",$noslash[0]);
            
            if(in_array('00',$this->tenor)){
                $this->tenorFlag = 1 ;

                if(in_array('03',$this->tenor)){
                    $this->tenorFlag = 3 ;
                }

                elseif (in_array('06',$this->tenor)){
                    $this->tenorFlag = 3 ;
                }

                elseif (in_array('12',$this->tenor)){
                    $this->tenorFlag = 3 ;
                }

                elseif (in_array('24',$this->tenor)){
                    $this->tenorFlag = 3 ;
                }

            }

            else{
                $this->tenorFlag = 2 ;
            }

        }

        //for any else
        else{
            $this->tenorFlag = 1 ;
        }

    }
}