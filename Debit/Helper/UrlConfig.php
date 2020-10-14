<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 14/03/2018
 * Time: 16:50
 */

namespace Faspay\Debit\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Faspay\Debit\Helper\Data;
use Magento\Framework\App\Helper\Context;

Class UrlConfig extends AbstractHelper{


    protected $data;

    const SANDBOX_BASE_URL = 'https://dev.faspay.co.id/pws/300011/183xx00010100000';
    const PRODUCTION_BASE_URL = 'https://web.faspay.co.id/pws/300011/383xx00010100000';
//    const PRODUCTION_BASE_URL = 'https://web.faspay.co.id/pws/300002/383xx00010100000';

    const SANDBOX_REDIRECT_URL = 'https://dev.faspay.co.id/pws/100003/0830000010100000/';
    const PRODUCTION_REDIRECT_URL = 'https://web.faspay.co.id/pws/100003/2830000010100000/';
//    const PRODUCTION_BASE_URL = 'https://web.faspay.co.id/pws/100003/2830000010100000';

//    const SANDBOX_BCA_URL       = 'http://faspaydev.mediaindonusa.com/pws/redirectBCA';
    const SANDBOX_BCA_URL       = 'https://dev.faspay.co.id/bcaklikpay/purchasing';
    const PRODUCTION_BCA_URL    = 'https://klikpay.klikbca.com/purchasing/purchase.do?action=loginRequest';

    const SANDBOX_PERMATANET_URL       = 'https://dev.faspay.co.id/permatanet/payment';
    const PRODUCTION_PERMATANET_URL    = 'https://web.faspay.co.id/permatanet/payment';



    public function __construct(Context $context, Data $data)
    {
        parent::__construct($context);

        $this->data = $data;
    }

    public function getProduction(){

        if($this->data->getProduction()==1){
            return true;
        }
        else if($this->data->getProduction()==0){
            return false;
        }


    }
    public function getPostUrl(){
        return $this->getProduction() ? self::PRODUCTION_BASE_URL : self::SANDBOX_BASE_URL;
    }

    public function getRedirectUrl(){
        return $this->getProduction() ? self::PRODUCTION_REDIRECT_URL : self::SANDBOX_REDIRECT_URL;
    }

    public function getBCAUrl(){
        return $this->getProduction() ? self::PRODUCTION_BCA_URL : self::SANDBOX_BCA_URL;
    }

    public function getPermataNetUrl(){
        return $this->getProduction() ? self::PRODUCTION_PERMATANET_URL : self::SANDBOX_PERMATANET_URL;
    }

    public function getBaseUrl(){
        $currentPath = $_SERVER['PHP_SELF'];
        $pathInfo = pathinfo($currentPath);
        $hostName = $_SERVER['HTTP_HOST'];
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';

        return $protocol.$hostName.$pathInfo['dirname'];
    }
}