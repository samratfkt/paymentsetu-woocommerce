( function () {
	'use strict';

	var registerPaymentMethod = window.wc.wcBlocksRegistry.registerPaymentMethod;
	var getSetting            = window.wc.wcSettings.getSetting;
	var createElement         = window.wp.element.createElement;
	var __                    = window.wp.i18n.__;
	var decodeEntities        = window.wp.htmlEntities.decodeEntities;

	var settings    = getSetting( 'paymentsetu_data', {} );
	var title       = decodeEntities( settings.title       || __( 'UPI / QR Code', 'paymentsetu-gateway' ) );
	var description = decodeEntities( settings.description || '' );

	/**
	 * Label shown next to the radio button.
	 */
	var PaymentSetupLabel = function ( props ) {
		var PaymentMethodLabel = props.components.PaymentMethodLabel;
		return createElement( PaymentMethodLabel, { text: title } );
	};

	/**
	 * Content shown below the selected payment method.
	 */
	var PaymentSetupContent = function () {
		if ( ! description ) {
			return null;
		}
		return createElement( 'p', { style: { margin: '8px 0 0', fontSize: '0.9em', color: '#555' } }, description );
	};

	registerPaymentMethod( {
		name:           'paymentsetu',
		label:          createElement( PaymentSetupLabel, null ),
		content:        createElement( PaymentSetupContent, null ),
		edit:           createElement( PaymentSetupContent, null ),
		canMakePayment: function () { return true; },
		ariaLabel:      title,
		supports: {
			features: settings.supports || [ 'products' ],
		},
	} );
} )();
