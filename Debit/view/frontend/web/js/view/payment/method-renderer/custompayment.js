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
                template: 'Faspay_Debit/payment/default'
            },
            getPaymentIcon: function(){
                return window.checkoutConfig.payment.icon[this.item.method];
            },

            redirectAfterPlaceOrder: true,
            placeOrder: function (data, event) {
                var self = this;

                if(confirm("After you click OK! you can't go back to this page. Continue?")){

                    
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
                                    redirectOnSuccessAction.execute();
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
        });
    }
);