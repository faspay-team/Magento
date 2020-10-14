<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 02/05/2018
 * Time: 9:19
 */

namespace Faspay\Credit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Faspay\Credit\Helper\Data;

class PaymentMethodAvailable implements ObserverInterface
{

    protected $methodCodes = [
        'mid_1',
        'mid_2',
        'mid_3',
        'mid_4',
        'mid_5',
        'mid_6',
        'mid_7',
        'mid_8',
        'mid_9',
        'mid_10',
        'mid_11',
        'mid_12',
        'mid_13',
        'mid_14',
        'mid_15',
        'mid_16',
    ];
    protected $data;

    public function __construct(Data $data)
    {
        $this->data = $data;
    }

    public function execute(Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

        $totalNow = $cart->getQuote()->getGrandTotal();
        foreach ($this->methodCodes as $code){
            if($this->data->getMinimum($code)> $totalNow){
                if($observer->getEvent()->getMethodInstance()->getCode()==$code){
                    $checkResult = $observer->getEvent()->getResult();
                    $checkResult->setData('is_available', false); //this is disabling the payment method at checkout page
                }
            }
        }

    }
}