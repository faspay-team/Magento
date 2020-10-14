<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 23/03/2018
 * Time: 14:21
 */

namespace Faspay\Debit\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\View\Asset\Repository;
use Magento\Checkout\Model\Session;
use Faspay\Debit\Helper\BcaUtility;
use Faspay\Debit\Helper\Data;

class ConfigProvider implements ConfigProviderInterface
{

    protected $methodCodes = [
        'alfagroup',
        'bca_va',
        'bni_va',
        'bri_mocash',
        'bri_va',
        'danamon_va',
        'indomaret_point',
        'mandiri_bill',
        'mandiri_va',
        'maybank_va',
        'permata_va',
        'xl_tunai',

        'bca_klikpay',
        'bca_sakuku',
        'bri_epay',
        'cimb_clicks',
        'danamon_online',
        'kredivo',
        'mandiri_clickpay',
        'mandiri_ecash',
        'permata_net',
        't_cash',
        'ovo',
        'm2u',
        'akulaku',
        'shopee_app',
        'shopee_qr',
        'dana'
    ];

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];
    protected $filesystem;
    protected $assetRepo;
    protected $bcaUtility;
    protected $utility;
    protected $session;

    public function __construct(
        PaymentHelper $paymentHelper,
        Repository $assetRepo,
        BcaUtility $bcaUtility,
        Session $session,
        Data $data)
    {
        foreach ($this->methodCodes as $code){
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
        $this->assetRepo =$assetRepo;
        $this->bcaUtility = $bcaUtility;
        $this->session = $session;
        $this->utility =$data;
    }


    /**
     *  make config with name 'payment' and subname in array after that
     */
    public function getConfig()
    {

        //for image payment method
        $config = [];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['icon'][$code] = $this->getIcon($code);
            }
        }


        //-start- for bca klikpay
        $config['payment']['item']['name'] = $this->getItemName();
        $config['payment']['item']['price'] = $this->getItemPrice();

        $config['payment']['bca']['mixenable']= $this->getMixEnable();

        $config['payment']['bca']['enable']['3'] = $this->getEnable(3);
        $config['payment']['bca']['enable']['6'] = $this->getEnable(6);
        $config['payment']['bca']['enable']['12'] = $this->getEnable(12);
        $config['payment']['bca']['enable']['24'] = $this->getEnable(24);

        $config['payment']['bca']['minimum']['3'] = $this->getMinimum(3);
        $config['payment']['bca']['minimum']['6'] = $this->getMinimum(6);
        $config['payment']['bca']['minimum']['12'] = $this->getMinimum(12);
        $config['payment']['bca']['minimum']['24'] = $this->getMinimum(24);

        //-end-

        $config['payment']['fee']= $this->getFeePayment();


        return $config;
    }

    public function getIcon($code)
    {
        return $this->assetRepo->getUrl("Faspay_Debit::images/channel/".$code.".png");

    }


    //-start- for bca klikpay
    public function getItemData(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

        $items = $cart->getQuote()->getAllItems();

        return $items;
    }

    public function getItemName(){

        foreach ($this->getItemData() as $item){
            $itemName[] = $item->getName();
        }
        return $itemName;
    }

    public function getItemPrice(){

        foreach ($this->getItemData() as $item){
            $itemPrice[] = $item->getRowTotalInclTax();
        }
        return $itemPrice;
    }

    public function getEnable($num){
         $isEnable = $this->bcaUtility->isEnable($num);
         if($isEnable==1){
             return true;
         }
         else{
             return false;
         }
    }

    public function getMixEnable(){
        $isEnable = $this->bcaUtility->isMixEnable();
        if($isEnable==1){
            return true;
        }
        else{
            return false;
        }
    }

    public function getMinimum($num){

        $minimum = $this->bcaUtility->getMin($num);
        return $minimum;

    }
    //-end-


    public function getFeePayment(){
        $payment = $this->session->getQuote()->getPayment()->getMethod();

        if($payment) {
            $amount = $this->utility->getConfig("payment/" . $payment . "/fee");
            return $amount;
        }
        else{
            return 0;
        }
    }
}