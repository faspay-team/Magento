<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 14/03/2018
 * Time: 16:50
 */

namespace Faspay\Credit\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Faspay\Credit\Helper\Data;

Class UrlConfig extends AbstractHelper{

    protected $isProduction;
    protected $data;

    const SANDBOX_BASE_URL = 'https://fpgdev.faspay.co.id/payment';
    const PRODUCTION_BASE_URL = 'https://fpg.faspay.co.id/payment';

    const SANDBOX_VOID_URL = 'https://fpgdev.faspay.co.id/payment/api';
    const PRODUCTION_VOID_URL = 'https://fpg.faspay.co.id/payment/api';



    public function __construct(Context $context, Data $data)
    {
        parent::__construct($context);
        $this->data = $data;
        if($this->data->getProduction()==1){
            $this->isProduction = true;
        }
        else if($this->data->getProduction()==0){
            $this->isProduction = false;
        }
    }

    public function getProduction(){
        if($this->data->getProduction()==1){
            $this->isProduction = true;
        }
        else if($this->data->getProduction()==0){
            $this->isProduction = false;
        }

    }
    public function getPostUrl(){
        return $this->isProduction ? self::PRODUCTION_BASE_URL : self::SANDBOX_BASE_URL;
    }

    public function getVoidUrl(){
        return $this->isProduction ? self::PRODUCTION_VOID_URL : self::SANDBOX_VOID_URL;
    }

    public function getBaseUrl(){
        $currentPath = $_SERVER['PHP_SELF'];
        $pathInfo = pathinfo($currentPath);
        $hostName = $_SERVER['HTTP_HOST'];
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';

        return $protocol.$hostName.$pathInfo['dirname'];
    }
}