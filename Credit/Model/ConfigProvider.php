<?php
/**
 * Created by PhpStorm.
 * User: KevinHa
 * Date: 19/04/2018
 * Time: 16:51
 */

namespace Faspay\Credit\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\View\Asset\Repository;
use Faspay\Credit\Helper\Data;

class ConfigProvider implements ConfigProviderInterface
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

    protected $methods = [];
    protected $assetRepo;
    protected $thisData;

    public function __construct(
                    PaymentHelper $paymentHelper,
                    Repository $repository,
                    Data $data
                    )
    {
        foreach ($this->methodCodes as $code){
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
        $this->assetRepo =$repository;
        $this->thisData =$data;
    }

    public function getConfig()
    {
        $config = [];
        foreach ($this->methodCodes as $code) {
            if ($this->methods[$code]->isAvailable()) {
                $config['payment']['icon'][$code] = $this->getIcon($code);
            }
        }

        return $config;
    }

    public function getIcon($code)
    {
        return $this->assetRepo->getUrl("Faspay_Credit::images/type/".$code.".jpg");
    }

}