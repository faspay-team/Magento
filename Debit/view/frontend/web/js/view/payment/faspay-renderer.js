define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function ($,
              Component,
              rendererList
             ) 
    {
        'use strict';
        var component = 'Faspay_Debit/js/view/payment/method-renderer/custompayment';

        var methods = [
            {
                type: 'alfagroup',
                component: component
            },
            {
                type: 'bca_va',
                component: component
            },
            {
                type: 'bni_va',
                component: component
            },
            {
                type: 'bri_mocash',
                component: component
            },
            {
                type: 'bri_va',
                component: component
            },
            {
                type: 'danamon_va',
                component: component
            },
            {
                type: 'indomaret_point',
                component: component
            },
            {
                type: 'mandiri_bill',
                component: component
            },
            {
                type: 'mandiri_va',
                component: component
            },
            {
                type: 'maybank_va',
                component: component
            },
            {
                type: 'permata_va',
                component: component
            },
            {
                type: 'xl_tunai',
                component: component
            }

            ,
            {
                type: 'bca_sakuku',
                component: component
            },
            {
                type: 'bri_epay',
                component: component
            },
            {
                type: 'cimb_clicks',
                component: component
            },
            {
                type: 'danamon_online',
                component: component
            },
            {
                type: 'kredivo',
                component: component
            },
            {
                type: 'mandiri_clickpay',
                component: component
            },
            {
                type: 'mandiri_ecash',
                component: component
            },
            {
                type: 'permata_net',
                component: component
            },
            {
                type: 't_cash',
                component: component
            },
            {
                type: 'ovo',
                component: component
            },
            {
                type: 'm2u',
                component: component
            },
            {
                type: 'akulaku',
                component: component
            },
            {
                type: 'shopee_app',
                component: component
            },
            {
                type: 'shopee_qr',
                component: component
            },
            {
                type: 'dana',
                component: component
            }
        ];
        $.each( methods, function( k, method ){
            rendererList.push(method);
        });
        return Component.extend({});
    }
);