<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 26/03/2018
 * Time: 16:18
 */

namespace Faspay\Credit\Controller\Business;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Faspay\Credit\Helper\UrlConfig;
use Faspay\Credit\Helper\Data;

class Redirect extends Action
{
    protected $orderFactory;
    protected $urlConfig;
    protected $data;

    public function __construct(Context $context,
                                OrderFactory $orderFactory,
                                UrlConfig $urlConfig,
                                Data $data
                                )
    {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->urlConfig = $urlConfig;
        $this->data = $data;
    }

    public function execute()
    {
         $this->post();
    }

    public function post()
    {
        $order = $this->data->getOrder();

        $buyer = array(
            "CUSTNAME"					    => $order->getCustomerFirstName()." ".$order->getCustomerLastName(),
            "CUSTEMAIL"				        => $order->getCustomerEmail(),
            "PHONE_NO" 						=> $order->getBillingAddress()->getTelephone(),

            "BILLING_ADDRESS"				=> $order->getBillingAddress()->getData('street'),
            "BILLING_ADDRESS_CITY"			=> $order->getBillingAddress()->getCity(),
            "BILLING_ADDRESS_REGION"		=> $order->getBillingAddress()->getRegion(),
            "BILLING_ADDRESS_POSCODE"		=> $order->getBillingAddress()->getPostCode(),
            "BILLING_ADDRESS_COUNTRY_CODE"	=> $order->getBillingAddress()->getCountryId(),

            "RECEIVER_NAME_FOR_SHIPPING"	=> $order->getShippingAddress()->getFirstname()." ". $order->getShippingAddress()->getLastname(),

            "SHIPPING_ADDRESS" 				=> $order->getShippingAddress()->getData('street'),
            "SHIPPING_ADDRESS_CITY" 		=> $order->getShippingAddress()->getCity(),
            "SHIPPING_ADDRESS_REGION"		=> $order->getShippingAddress()->getRegion(),
            "SHIPPING_ADDRESS_POSCODE"		=> $order->getShippingAddress()->getPostCode(),
            "SHIPPING_ADDRESS_COUNTRY_CODE"	=> $order->getShippingAddress()->getCountryId(),
            "SHIPPINGCOST"					=> $this->data->getNumFormat($order->getBaseShippingInclTax(),0).'.00'
        );

        $merchant = array(

            "MERCHANTID"              	=> $this->data->getMid($order->getIncrementId()),
            "TXN_PASSWORD" 			    => $this->data->getPassword($order->getIncrementId()), //Transaction password  ajgbi
            "SIGNATURE" 			 	=> $this->generateSignature($order->getIncrementId(),$this->data->getNumFormat($order->getTotalDue(),0))

        );

        $param = array(
            "MERCHANT_TRANID"			=> $order->getIncrementId(),
            "AMOUNT"					=> $this->data->getNumFormat($order->getTotalDue(),0).'.00',
            "CURRENCYCODE"				=> $order->getBaseCurrencyCode(),
            "PAYMENT_METHOD"			=> '1',
            "RESPONSE_TYPE"			    => '1', //via post method
            "RETURN_URL"              	=> $this->urlConfig->getBaseUrl() . '/credit/business/thankyou?order_id='.$order->getIncrementId(), //*
            "DESCRIPTION"             	=> $this->data->getTitle($order->getIncrementId()),

        );

        $items = $order->getAllItems();
        $itemcount=1;
        $itemOrdered = array();
        
        foreach($items as $item) {
            $merger= array(
                "MREF".$itemcount => $item->getName()." : ".$this->data->getNumFormat($item->getRowTotalInclTax(),0),
            );

            $itemOrdered = array_merge($itemOrdered, $merger);
            $itemcount++;
        }

        $post = array(
            "TRANSACTIONTYPE"			=> '1',
            //"SHOPPER_IP"				=> '192.168.130.130',
            "LANG" 					    => '',
            "MPARAM1" 						=> '',// direct, isi dengan direct
            "MPARAM2" 						=> '',
            "CUSTOMER_REF"	 				=> '',
            "PYMT_IND"    					=> '', //selalu diisi 'tokenization',
            "PYMT_CRITERIA"   				=> '', //'registration' = pendaftaran  atau 'payment' = setelah bayar
            "PYMT_TOKEN"					=> '', //diisi dengan token
            "paymentoption"                 => '0', // 0 kartu yang disimpan, 1 pakai kartu baru
            "FRISK1"						=> '',
            "FRISK2"						=> '',
            
            "DOMICILE_ADDRESS"				=> '',
            "DOMICILE_ADDRESS_CITY"			=> '',
            "DOMICILE_ADDRESS_REGION"		=> '',
            "DOMICILE_ADDRESS_STATE"		=> '',
            "DOMICILE_ADDRESS_POSCODE" 		=> '',
            "DOMICILE_ADDRESS_COUNTRY_CODE"	=> '',
            "DOMICILE_PHONE_NO"	 			=> '',
            
            //"handshake_url"				=> '',
            //"handshake_param"				=> '',
            // "style_merchant_name"           => 'black',
            // "style_order_summary"           => 'black',
            // "style_order_no"                => 'black',
            // "style_order_desc"              => 'black',
            // "style_amount"                  => 'black',
            // "style_background_left"         => '#fff',
            // "style_button_cancel"           => 'grey',
            // "style_font_cancel"             => 'white',

            // "style_image_url"               => 'http://www.pikiran-rakyat.com/sites/files/public/styles/medium/public/image/2017/06/Logo%20HUT%20RI%20ke-72%20yang%20paling%20bener.jpg?itok=RsQpqpqD',
        );

        $loads =  array_merge($buyer,$merchant,$param,$itemOrdered,$post);

        $string = '<form method="post" name="form" action="'.$this->urlConfig->getPostUrl().'">';
        if ($loads != null) {
            foreach ($loads as $name=>$value) {
                $string .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
            }
        }
        $string .= '</form>';
        $string .= '<script> document.form.submit();</script>';

        echo $string;
    }

    public function generateSignature($order_id,$amount){

        $signature = strtoupper(sha1(strtoupper('##'.$this->data->getMid($order_id).'##'.$this->data->getPassword($order_id).'##'.$order_id.'##'.$amount.'.00##0##')));
        return $signature;

    }

}