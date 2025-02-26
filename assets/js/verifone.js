jQuery(function ($) {
	'use strict';

	var methodLogosVisible = false;
	if (woocommerceVerifone.usePaymentMethodLogos) {
		methodLogosVisible = true;
	}

	var selectors = {
		paymentMethodsList: 'select#verifone-payment-method',
		paymentMethodWrapper: 'option',
		paymentMethodActive: 'option:selected',
		paymentMethodApplePay: 'option[value="applepay"]'
	};

	if (methodLogosVisible) {
		selectors.paymentMethodsList = '.verifone-payment-method-logos';
		selectors.paymentMethodWrapper = '.verifone-payment-method-logo input';
		selectors.paymentMethodActive = '.verifone-payment-method-logo.active input';
		selectors.paymentMethodApplePay = '.verifone-payment-method-applepay';
	}

	var wc_verifone = {
		init: function () {
			// Legacy checkout
			$(document).on('updated_checkout', function (e) {
				wc_verifone.onPaymentMethodChange();
			});

			// Checkout block
			if ('undefined' !== typeof window.wp && 'undefined' !== typeof window.wp.hooks) {
				window.wp.hooks.addAction('wc.verifoneBlockInit', 'wc-verifone/handle-set-active-payment-method', function () {
					wc_verifone.onPaymentMethodChange();
				});
			}

			// Method change
			$(document).on('change', selectors.paymentMethodsList, function (e) {
				wc_verifone.changeCardBox(this);
			});

			// Method logo change
			$(document).on('click', '.verifone-payment-method-logo', function (e) {
				$('.verifone-payment-method-logo').removeClass('active');
				$(this).addClass('active');
			}
			);
		},
		onPaymentMethodChange: function () {
			wc_verifone.hideLoneAllInOne();
			wc_verifone.hideAllInOneLogo();
			wc_verifone.hideApplePay();
			wc_verifone.changeCardBox($(selectors.paymentMethodsList));
		},
		hideLoneAllInOne: function () {
			var $methods = $(selectors.paymentMethodsList);

			if ($methods.length === 0) {
				return true;
			}

			var $methods = $methods.find(selectors.paymentMethodWrapper);

			if ($methods.length > 1) {
				return true;
			}

			var $method = $methods[0];

			if ($method.value === 'all') {
				$methods.closest('.verifone-payment').hide();
			}
		},
		hideAllInOneLogo: function () {
			if (!methodLogosVisible) {
				return;
			}

			var $methods = $(selectors.paymentMethodWrapper);

			if ($methods.length === 0) {
				return true;
			}

			var $method = $methods[0];

			if ($method.value === 'all') {
				$('.verifone-payment-method-logo.' + $method.id).hide();
			}
		},
		hideApplePay: function () {
			var $method = $(selectors.paymentMethodApplePay);

			if ($method.length === 0) {
				return true;
			}

			if (!wc_verifone.applePaySupported()) {
				$method.hide();
			}
		},
		changeCardBox: function (elem) {
			var $option = $(elem).find(selectors.paymentMethodActive);

			if ($option.attr('data-type') !== 'card') {
				$(elem).closest('.verifone-payment').find('.verifone-save-payment-method-wrapper input').prop('checked', false);
				$(elem).closest('.verifone-payment').find('.verifone-save-payment-method-wrapper').hide();
			} else {
				$(elem).closest('.verifone-payment').find('.verifone-save-payment-method-wrapper').show();
			}
		},
		applePaySupported: function () {
			return window.ApplePaySession && ApplePaySession.canMakePayments() && ApplePaySession.supportsVersion(4);
		}
	};

	wc_verifone.init();

});
