jQuery(function ($) {
    'use strict';

    /**
     * Object to handle Verifone admin functions.
     */

    var wc_verifone_admin = {

        /**
         * Initialize.
         */
        init: function () {

            $(document.body).on('click', '#verifone-refresh-payment-methods-trigger', function (e) {
                e.stopPropagation();

                var data = {
                    'action': 'verifone_refresh_payment_methods'
                };

                jQuery.post(ajaxurl, data, function (response) {
                    response = JSON.parse(response);
                    if (response.code === 200) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                });

            });

            $('a[id^="verifone-generate-keys-trigger"]').on('click', function (e) {
                e.stopPropagation();

                var data = {
                    'action': 'verifone_generate_keys',
                    'type': $(this).attr('id').split('-').slice(-1)[0]
                };

                if (confirm($('#woocommerce_verifone_generate_keys').next().find('.confirm').text())) {
                    jQuery.post(ajaxurl, data, function (response) {
                        response = JSON.parse(response);
                        if (response.code === 200) {
                            console.log(response);
                            var message = response.messages.join('\\n');
                            alert(message);
                            // location.reload();
                        } else {
                            console.log(response);
                            var message = response.messages.join('\\n');
                            alert(message);
                        }
                    });
                }

            });

            $('#mainform').on('submit', function (e) {
                // e.preventDefault();
                wc_verifone_admin.setCookie('verifone_config_save', 1, 1);
                // e.submit();
            });

            $(function (e) {

                if (wc_verifone_admin.getCookie('verifone_config_save')) {
                    wc_verifone_admin.eraseCookie('verifone_config_save');
                    location.reload();
                }

                // Get the modal
                var modal = document.getElementById("verifone-summary-modal");

                // Get the button that opens the modal
                var btn = document.getElementById("verifone-summary-modal-trigger");

                // Get the <span> element that closes the modal
                var span = modal.getElementsByClassName("close")[0];

                // When the user clicks the button, open the modal
                btn.onclick = function () {
                    modal.style.display = "block";
                };

                // When the user clicks on <span> (x), close the modal
                span.onclick = function () {
                    modal.style.display = "none";
                };

                // When the user clicks anywhere outside of the modal, close it
                window.onclick = function (event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }

            });

            $(function (e) {
                wc_verifone_admin.changeVisibility();
            });

            $('#woocommerce_verifone_key_handling_mode').on('change', function () {
                wc_verifone_admin.changeVisibility();
            })

        },
        setCookie: function (name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        },
        getCookie: function (name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        },
        eraseCookie: function (name) {
            document.cookie = name + '=; Max-Age=-99999999; path=/';
        },
        changeVisibility: function () {
            var current = jQuery('#woocommerce_verifone_key_handling_mode').val();

            if (current === '0') {
                jQuery('.depends-key_handling_mode-0').closest('tr').show();
                jQuery('.depends-key_handling_mode-1').closest('tr').hide();
            } else {
                jQuery('.depends-key_handling_mode-0').closest('tr').hide();
                jQuery('.depends-key_handling_mode-1').closest('tr').show();
            }
        }

    };

    if (window.location.href.indexOf("verifone") > -1) {
        wc_verifone_admin.init();
    }

});
