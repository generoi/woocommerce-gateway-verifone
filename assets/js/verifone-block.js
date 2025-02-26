const { getSetting } = window.wc.wcSettings;
const { decodeEntities } = window.wp.htmlEntities;
const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { createElement, useEffect } = window.wp.element;
const { __ } = window.wp.i18n;
const { doAction } = window.wp.hooks;

const wcVerifoneSettings = getSetting('verifone_data', {});
const wcVerifoneLabel = decodeEntities(wcVerifoneSettings.title) || __('Verifone', 'wc-verifone');
const wcVerifoneLogo = wcVerifoneSettings.logo ? decodeEntities(wcVerifoneSettings.logo) : '';

const wcVerifoneLabelElement = () => createElement('span', {
	dangerouslySetInnerHTML: { __html: `<span>${wcVerifoneLabel}</span> ${wcVerifoneLogo}` },
});

const wcVerifoneContentElement = ( props ) => {
	const { eventRegistration, emitResponse } = props;
	const { onPaymentProcessing } = eventRegistration;
	useEffect( () => {
		doAction( 'wc.verifoneBlockInit' );

		const wcVerifoneProcessing = onPaymentProcessing( async () => {
			const verifonePaymentMethodSelected = document.querySelector('input[name="verifone-payment-method"]:checked');
			const verifonePaymentMethod = verifonePaymentMethodSelected ? verifonePaymentMethodSelected.value : '';

			const verifonePaymentRememberMethodField = document.querySelector('input[name="verifone-save-payment-method"]:checked');
			const verifonePaymentRememberMethod = verifonePaymentRememberMethodField ? verifonePaymentRememberMethodField.value : '';

			return {
				type: emitResponse.responseTypes.SUCCESS,
				meta: {
					paymentMethodData: {
						verifonePaymentMethod,
						verifonePaymentRememberMethod,
					},
				},
			};
		} );

		return () => {
			wcVerifoneProcessing();
		};
	}, [
		emitResponse.responseTypes.ERROR,
		emitResponse.responseTypes.SUCCESS,
		onPaymentProcessing,
	] );

	return createElement('div', {
		dangerouslySetInnerHTML: { __html: wcVerifoneSettings.html },
	});
};

const wcVerifoneGatewayBlock = {
	name: 'verifone',
	label: createElement(wcVerifoneLabelElement),
	ariaLabel: wcVerifoneLabel,
	content: createElement(wcVerifoneContentElement),
	edit: createElement(wcVerifoneContentElement),
	canMakePayment: () => true,
};

registerPaymentMethod(wcVerifoneGatewayBlock);
