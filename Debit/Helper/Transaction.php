<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 13/03/2018
 * Time: 10:59
 */

namespace Faspay\Debit\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Transaction extends AbstractHelper
{

    public function processDebitTransaction($loads){
        return PostData::post(UrlConfig::getPostUrl(), $loads);
    }

    public static function inquiry($id, $bill_no){
        $loads = array(
            'request'       => 'Inquiry Status Payment',
            'trx_id'        => $id,
            'merchant_id'   => Faspay_Config::$bussinessId,
            'bill_no'       => $bill_no,
            'signature'     => sha1(md5(Faspay_Config::$bussinessUser.Faspay_Config::$bussinessPassword.$bill_no))
        );

        return Api::post($loads);
    }

    public static function cancel($id, $bill_no, $reason){
        $loads = array(
            'request'       => 'Canceling Payment',
            'trx_id'        => $id,
            'merchant_id'   => Faspay_Config::$bussinessId,
            'merchant'      => Faspay_Config::$bussinessId,
            'bill_no'       => $bill_no,
            'payment cancel'=> $reason,
            'signature'     => sha1(md5(Faspay_Config::$bussinessUser.Faspay_Config::$bussinessPassword.$bill_no))
        );

        return Api::post($loads);
    }

    public static function void(){

    }

    public static function refund(){

    }
}