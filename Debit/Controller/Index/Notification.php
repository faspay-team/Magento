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
use Magento\Framework\App\ResponseInterface;
use Faspay\Debit\Helper\Data;
use Magento\Sales\Model\OrderFactory;
use Faspay\Debit\Model\FaspayOrderFactory;

class Notification extends Action
{
    
    protected $data;
    protected $faspayOrderFactory;
    protected $orderFactory;

    public function __construct(Context $context,
                                Data $data,
                                OrderFactory $orderFactory,
                                FaspayOrderFactory $faspayOrderFactory
                                )
    {
        parent::__construct($context);
        $this->data = $data;
        $this->orderFactory = $orderFactory;
        $this->faspayOrderFactory = $faspayOrderFactory;
        $this->execute();
    }

    public function execute()
    {
        $xml = simplexml_load_string(file_get_contents('php://input'));
        
        if($xml){
            $this->notif($xml);
        }
        else{
            echo "THIS PAGE FOR NOTIFICATION ONLY";
        }
        exit();
        
    }

    public function notif($xml){

        //data yang diambil dari faspay
        $trx_id = $xml->trx_id;
        $order_stat = $xml->payment_status_code;
        $order_id = $xml->bill_no;
        $signature = $xml->signature;

        //load data sesuai bill number yang di pass
        $orderNow = $this->orderFactory->create()->load($order_id);

        //load data sesuai DB Faspay
        $dataModel = $this->faspayOrderFactory->create();
        $thisData = $dataModel->load($order_id);

        //check Status First
        if($this->validateStatus($thisData)){

            //check signature
            if(!$this->validateSignature($order_id,$signature,$order_stat)){

                echo $this->response(99, 'Invalid Signature' ,$trx_id,$order_id);

            }

            elseif ($trx_id !=$thisData->getTrxId()){
                echo $this->response(99, 'Invalid TRX Id',$trx_id,$order_id);
            }

            elseif ($order_id == $thisData->getOrderId()){

                if($order_stat == 2){
                    $orderNow->setState('complete')->setStatus('complete');
                    $orderNow->save();

                    $thisData->setPaymentStatus('SUCCESS');
                    $thisData->save();
                }
                echo $this->response('00', "Done",$trx_id,$order_id);
            }

        }

        else {
            echo $this->response(99, "Already Processed Payment",$trx_id,$order_id);
        }


    }

    public function response($errno, $errmsg,$trx_id,$order_id){

        $xml ="<faspay>";
        $xml.="<response>Payment Notification</response>";
        $xml.="<trx_id>".$trx_id."</trx_id>";
        $xml.="<merchant_id>".$this->data->getMerchantId()."</merchant_id>";
        $xml.="<bill_no>".$order_id."</bill_no>";
        $xml.="<response_code>$errno</response_code>";
        $xml.="<response_desc>$errmsg</response_desc>";
        $xml.="<response_date>".Date("Y-m-d H:i:s")."</response_date>";
        $xml.="</faspay>";

        return $xml;
    }

    //signature for notif
    private function validateSignature($order_id,$signature,$order_stat){

        if($this->correct($order_id,$order_stat) == $signature){
            return TRUE;
        }

        return FALSE;
    }

    public function correct($order_id,$order_stat){
        $correctSignature = sha1(md5($this->data->getUser().
            $this->data->getPassword().
            $order_id.
            $order_stat));

        return $correctSignature;
    }

    //validate payment status
    public function validateStatus($thisData){
        if($thisData->getPaymentStatus()=='PENDING'){
            return true;
        }
        else{
            return false;
        }
    }
}