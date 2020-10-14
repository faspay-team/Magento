define(
    [
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'jquery'

    ],
    function (url, fullScreenLoader,$) {
        'use strict';

        return {

            execute: function () {

                fullScreenLoader.startLoader();
                window.location.replace(url.build('credit/business/redirect'));
                
            }


        };
    }
);