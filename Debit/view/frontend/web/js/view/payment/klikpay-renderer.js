define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component,
              rendererList
             )
    {
        'use strict';
        rendererList.push(
            {
                type: 'bca_klikpay',
                component: 'Faspay_Debit/js/view/payment/method-renderer/klikpaypayment'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);