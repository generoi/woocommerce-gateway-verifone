jQuery(function ($) {
    'use strict';

    var wc_verifone = {

        /**
         * Initialize.
         */
        init: function () {

            $(document).on('updated_checkout', function (e) {
                wc_verifone.hideAllInOne();
                wc_verifone.changeCardBox($('select#verifone-payment-method'));
            });

            $(document).on('change', 'select#verifone-payment-method', function(e){
                wc_verifone.changeCardBox(this);
            });
        },
        hideAllInOne: function () {
            var $select = $('select#verifone-payment-method');

            if($select.length === 0) {
                return true;
            }

            var $methods = $select.find('option');

            if($methods.length > 1) {
                return true;
            }

            var $method = $methods[0];

            if($method.value === 'all') {
                $select.closest('.verifone-payment').hide();
            }
        },
        changeCardBox: function (elem) {
            var $option = $(elem).find('option:selected');

            if($option.attr('data-type') !== 'card') {
                $(elem).closest('.verifone-payment').find('.verifone-save-payment-method-wrapper').hide();
            } else {
                $(elem).closest('.verifone-payment').find('.verifone-save-payment-method-wrapper').show();
            }
        }
    };

    wc_verifone.init();

});