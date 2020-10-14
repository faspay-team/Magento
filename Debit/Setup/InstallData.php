<?php


namespace Faspay\Debit\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $data = [
            [
                'bank_num' => 302,
                'bank_name' => 'LinkAja',
                'bank_flow' => 1,
                'bank_code' => 't_cash'
            ],
            [
                'bank_num' => 303,
                'bank_name' => 'XLTunai',
                'bank_flow' => 1,
                'bank_code' => 'xl_tunai'
            ],
            // [
            //     'bank_num' => 305,
            //     'bank_name' => 'Mandiri Ecash',
            //     'bank_flow' => 1,
            //     'bank_code' => 'mandiri_ecash'
            // ],
            [
                'bank_num' => 400,
                'bank_name' => 'BRIMoCash',
                'bank_flow' => 2,
                'bank_code' => 'bri_mocash'
            ],
            [
                'bank_num' => 401,
                'bank_name' => 'BRI ePay',
                'bank_flow' => 1,
                'bank_code' => 'bri_epay'
            ],
            [
                'bank_num' => 402,
                'bank_name' => 'Permata VA',
                'bank_flow' => 2,
                'bank_code' => 'permata_va'
            ],
            [
                'bank_num' => 405,
                'bank_name' => 'BCA KlikPay',
                'bank_flow' => 1,
                'bank_code' => 'bca_klikpay'
            ],
            [
                'bank_num' => 406,
                'bank_name' => 'Mandiri ClickPay',
                'bank_flow' => 1,
                'bank_code' => 'mandiri_clickpay'
            ],
            [
                'bank_num' => 408,
                'bank_name' => 'Maybank VA',
                'bank_flow' => 2,
                'bank_code' => 'maybank_va'
            ],
            [
                'bank_num' => 700,
                'bank_name' => 'CIMB Clicks',
                'bank_flow' => 1,
                'bank_code' => 'cimb_clicks'
            ],
            [
                'bank_num' => 701,
                'bank_name' => 'Danamon Online Banking',
                'bank_flow' => 1,
                'bank_code' => 'danamon_online'
            ],
            [
                'bank_num' => 702,
                'bank_name' => 'BCA VA',
                'bank_flow' => 2,
                'bank_code' => 'bca_va'
            ],
            [
                'bank_num' => 703,
                'bank_name' => 'Mandiri Bill Payment',
                'bank_flow' => 2,
                'bank_code' => 'mandiri_bill'
            ],
            [
                'bank_num' => 704,
                'bank_name' => 'BCA Sakuku',
                'bank_flow' => 1,
                'bank_code' => 'bca_sakuku'
            ],
            [
                'bank_num' => 706,
                'bank_name' => 'Indomaret Payment Point',
                'bank_flow' => 2,
                'bank_code' => 'indomaret_point'
            ],
            [
                'bank_num' => 707,
                'bank_name' => 'Alfagroup',
                'bank_flow' => 2,
                'bank_code' => 'alfagroup'
            ],
            [
                'bank_num' => 708,
                'bank_name' => 'Danamon VA',
                'bank_flow' => 2,
                'bank_code' => 'danamon_va'
            ],
            [
                'bank_num' => 709,
                'bank_name' => 'Kredivo',
                'bank_flow' => 1,
                'bank_code' => 'kredivo'
            ],
            [
                'bank_num' => 800,
                'bank_name' => 'BRI VA',
                'bank_flow' => 2,
                'bank_code' => 'bri_va'
            ],
            [
                'bank_num' => 801,
                'bank_name' => 'BNI VA',
                'bank_flow' => 2,
                'bank_code' => 'bni_va'
            ],
            [
                'bank_num' => 802,
                'bank_name' => 'Mandiri VA',
                'bank_flow' => 2,
                'bank_code' => 'mandiri_va'
            ],
            [
                'bank_num' => 403,// in 402
                'bank_name' => 'Permata Net',
                'bank_flow' => 1,
                'bank_code' => 'permata_net'
            ],
            [
                'bank_num' => 812,
                'bank_name' => 'OVO',
                'bank_flow' => 1,
                'bank_code' => 'ovo'
            ],
            [
                'bank_num' => 814,
                'bank_name' => 'Maybank M2U',
                'bank_flow' => 1,
                'bank_code' => 'm2u'
            ],
            [
                'bank_num' => 807,
                'bank_name' => 'Akulaku',
                'bank_flow' => 1,
                'bank_code' => 'akulaku'
            ],
            [
                'bank_num' => 711,
                'bank_name' => 'ShopeePay QRIS',
                'bank_flow' => 2,
                'bank_code' => 'shopee_qr'
            ],
            [
                'bank_num' => 713,
                'bank_name' => 'ShopeePay App',
                'bank_flow' => 1,
                'bank_code' => 'shopee_app'
            ],
            [
                'bank_num' => 819,
                'bank_name' => 'DANA',
                'bank_flow' => 1,
                'bank_code' => 'dana'
            ]
        ];
        foreach ($data as $bind) {
            $setup->getConnection()
                ->insertForce($setup->getTable('faspay_channel'), $bind);
        }

        $setup->endSetup();
    }
}