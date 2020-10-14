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
        var component = 'Faspay_Credit/js/view/payment/method-renderer/creditpayment';

        var methods = [
            {
                type: 'mid_1',
                component: component
            },
            {
                type: 'mid_2',
                component: component
            },
            {
                type: 'mid_3',
                component: component
            },
            {
                type: 'mid_4',
                component: component
            },
            {
                type: 'mid_5',
                component: component
            },
            {
                type: 'mid_6',
                component: component
            },
            {
                type: 'mid_7',
                component: component
            },
            {
                type: 'mid_8',
                component: component
            },
            {
                type: 'mid_9',
                component: component
            },
            {
                type: 'mid_10',
                component: component
            },
            {
                type: 'mid_11',
                component: component
            },
            {
                type: 'mid_12',
                component: component
            },
            {
                type: 'mid_13',
                component: component
            },
            {
                type: 'mid_14',
                component: component
            },
            {
                type: 'mid_15',
                component: component
            },
            {
                type: 'mid_16',
                component: component
            }
        ];
        $.each( methods, function( k, method ){
            rendererList.push(method);
        });
        return Component.extend({});
    }
);