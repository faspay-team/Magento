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
                window.location.replace(url.build('debit/index/redirect'));
                
            },

            klikpay: function(){

                var x=  new Array();

                var table = document.getElementById("tabledatabca"),
                    row, i, j, cells, customerId,selected;

                for (i = 1; i < table.rows.length; ++i) {

                    row = table.rows[i];

                    selected =row.cells[1].childNodes[1].value;

                    x[i-1] = selected;
                }

                fullScreenLoader.startLoader();
                window.location.replace(url.build('debit/index/redirect?tenor=') + x);
            }


        };
    }
);