define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Faspay_Debit/js/action/redirect-on-success',
        'Magento_Checkout/js/model/payment/additional-validators'

    ],
    function ($,Component,redirectOnSuccessAction,additionalValidators) {
        'use strict';


        return Component.extend({
            defaults: {
                template: 'Faspay_Debit/payment/klikpaydefault'
            },
            getPaymentIcon: function(){
                return window.checkoutConfig.payment.icon['bca_klikpay'];
            },
            getItemName: function(){
                return window.checkoutConfig.payment.item.name;
            },
            getEnable: function(num) {
                return window.checkoutConfig.payment.bca.enable[num];
            },
            getMinimum: function(num){
                return window.checkoutConfig.payment.bca.minimum[num];
            },
            getMixEnable: function(){
                return window.checkoutConfig.payment.bca.mixenable;
            },
            getShown: function(num,index){
                if(this.getMixEnable()){
                    if(this.getEnable(num)){
                        if(window.checkoutConfig.payment.item.price[index] >= this.getMinimum(num)){
                            return true;
                        }
                        else{
                            return false;
                        }

                    }
                    else {
                        return false
                    }
                }
                else{
                    return false;
                }


            },
            redirectAfterPlaceOrder: true,

            placeOrder: function (data, event) {

                //check installment

                var x=0;

                var table = document.getElementById("tabledatabca"),
                    row, i, j, cells, customerId,selected;

                for (i = 1; i < table.rows.length; ++i) {

                    row = table.rows[i];
                    selected =row.cells[1].childNodes[1].value;

                    if(selected!='00'){
                       x++;
                    }

                }

                if(x>5){
                    alert("You can't choose more than 5 items to be paid by installment procedure ")
                }
                else{

                    if(confirm("After you click OK you can't go back to this page. Continue?")){


                        var self = this;

                        if (event) {
                            event.preventDefault();
                        }

                        if (this.validate() && additionalValidators.validate()) {
                            this.isPlaceOrderActionAllowed(false);

                            this.getPlaceOrderDeferredObject()
                                .fail(
                                    function () {
                                        self.isPlaceOrderActionAllowed(true);
                                    }
                                ).done(
                                function () {
                                    self.afterPlaceOrder();

                                    if (self.redirectAfterPlaceOrder) {

                                        redirectOnSuccessAction.klikpay();
                                    }
                                }
                            );

                            return true;
                        }

                    }
                    else{

                        return false;

                    }


                    return false;

                }

            }
        });
    }
);