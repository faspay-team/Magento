<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 02/04/2018
 * Time: 13:49
 */

namespace Faspay\Debit\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class BcaUtility extends AbstractHelper
{
    protected $_scopeConfig;
    
    public function __construct(Context $context,
                                ScopeConfigInterface $scopeInterface)
    {
        parent::__construct($context);
        $this->_scopeConfig = $scopeInterface;
    }

    public function getConfig($config){
        return $this->_scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    //BCA Clear Key
    public function getKeyId() {
        $clearKey = $this->getConfig('payment/bca_klikpay/key');
        return $this->genKeyId($clearKey);
    }

    //BCA Code
    public function getBcaCode(){
        return $this->getConfig('payment/bca_klikpay/code');
    }


    public function genSignature($klikPayCode, $transactionDate, $transactionNo, $amount, $currency, $keyId) {

        $tempKey1 = $klikPayCode . $transactionNo . $currency . $keyId;
        $hashKey1 = $this->getHash($tempKey1);
        $expDate = explode("/",substr($transactionDate,0,10));

        $strDate = $this->intval32bits($expDate[0] . $expDate[1] . $expDate[2]);
        $amt = $this->intval32bits($amount);
        $tempKey2 = $strDate + $amt;
        $hashKey2 = $this->getHash((string)$tempKey2);

        $signature = abs($hashKey1 + $hashKey2);

        return $signature;
    }

    public function genKeyId($clearKey) {
        return strtoupper(bin2hex($this->str2bin($clearKey)));
    }

    public function getBcaDate($date){
        $newDate = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        $bcaDate = $newDate->format('d/m/Y H:i:s');

        return $bcaDate;
    }

    public function genAuthKey($klikPayCode, $transactionNo, $currency, $transactionDate, $keyId) {

        $klikPayCode = str_pad($klikPayCode, 10, "0");
        $transactionNo = str_pad($transactionNo, 18, "A");
        $currency = str_pad($currency, 5, "1");

        $value_1 = $klikPayCode . $transactionNo . $currency . $transactionDate . $keyId;

        $hash_value_1 = strtoupper(md5($value_1));

        if (strlen($keyId) == 32)
            $key = $keyId . substr($keyId,0,16);
        else if (strlen($keyId) == 48)
            $key = $keyId;

        return strtoupper(bin2hex(mcrypt_encrypt(MCRYPT_3DES, hex2bin($key), hex2bin($hash_value_1), MCRYPT_MODE_ECB)));
    }

    private function intval32bits($value) {
        if ($value > 2147483647)
            $value = ($value - 4294967296);
        else if ($value < -2147483648)
            $value = ($value + 4294967296);
        return $value;
    }

    private function getHash($value) {
        $h = 0;
        for ($i = 0;$i < strlen($value);$i++) {
            $h = $this->intval32bits($this->add31T($h) + ord($value{$i}));
        }
        return $h;
    }

    private function add31T($value) {
        $result = 0;
        for($i=1;$i <= 31;$i++) {
            $result = $this->intval32bits($result + $value);
        }
        return $result;
    }

    private function str2bin($data) {
        $len = strlen($data);
        return pack("a" . $len, $data);
    }

    //Installment GETTER

    public function isEnable($num){
        return $this->getConfig('payment/bca_klikpay/active_'.$num);
    }

    public function isMixEnable(){
        return $this->getConfig('payment/bca_klikpay/active_mix');
    }

    public function getMid($num){
        return $this->getConfig('payment/bca_klikpay/mid_'.$num);
    }

    public function getMin($num){
        return $this->getConfig('payment/bca_klikpay/min_'.$num);
    }

}