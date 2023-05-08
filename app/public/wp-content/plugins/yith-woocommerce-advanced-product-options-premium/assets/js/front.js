/**
 * Front JS
 */

jQuery( document ).ready(
	function ( $ ) {

		/**
		 * Init the colorpicker input
		 */
		initColorpicker = function() {
			  // Customizable args for wpColorPicker function.
			  var colorPicker_opt = {
					color: false, // If Iris is attached to an input element, it will first try to pick up its value attribute. Otherwise, you can supply a color of any type that Color.js supports. (Hex, rgb, and hsl are good bets.).
					mode: 'hsl', // Iris can sport a variety of looks. It supports hsl and ‘hsv’ modes depending on your needs.
					controls: {
						horiz: 's', // horizontal defaults to saturation.
						vert: 'l', // vertical defaults to lightness.
						strip: 'h' // right strip defaults to hue.
					},
					hide: true, // Hide colorpickers by default.
					target: false, // a DOM element / jQuery selector that the element will be appended within. Only used when called on an input.
					width: 180, // the width of the collection of UI elements.
					palettes: false, // show a palette of basic colors beneath the square.
					change: function(event, ui) {
						let pickerContainer    = $( this ).closest( '.wp-picker-container' );
						let pickerInputWrap    = pickerContainer.find( '.wp-picker-input-wrap' );
						let placeholderElement = pickerContainer.find( '.wp-picker-custom-placeholder' );
						let clearElement       = pickerContainer.find( '.wp-picker-default-custom' );
						let colorPickerShow    = pickerContainer.find( '.wp-color-picker' ).data( 'addon-colorpicker-show' );

						// Placeholder option to hide or not the necessary elements.
						if ( 'placeholder' === colorPickerShow ) {
							if ( '' !== ui.color.toString() || 'undefined' !== ui.color.toString() ) {
								pickerInputWrap.find( '.wp-color-picker' ).show();
								placeholderElement.hide();
								clearElement.show();
								placeholderElement.css( 'line-height', '3.0' );
							}
						}

						$( document ).trigger( 'wapo-colorpicker-change' );

					},
					clear: function(event, ui) {
						let pickerContainer    = $( this ).closest( '.wp-picker-container' );
						let pickerInputWrap    = pickerContainer.find( '.wp-picker-input-wrap' );
						let placeholderElement = pickerContainer.find( '.wp-picker-custom-placeholder' );
						let clearElement       = pickerContainer.find( '.wp-picker-default-custom' );
						let colorPickerShow    = pickerContainer.find( '.wp-color-picker' ).data( 'addon-colorpicker-show' );

						// Placeholder option to hide or not the necessary elements.
						if ( 'placeholder' === colorPickerShow ) {
							pickerInputWrap.find( '.wp-color-picker' ).hide();
							placeholderElement.show();
							clearElement.hide();
							placeholderElement.css( 'line-height', '0' );
						}
						$( document ).trigger( 'wapo-colorpicker-change' );

					}
			};

			function inicializeAddonColorpickers() {

				// Initialize each colorpicker with wpColorPicker function.
				$( '.yith-wapo-block .yith-wapo-addon-type-colorpicker .wp-color-picker' ).each(
					function() {
						$( this ).wpColorPicker( colorPicker_opt );

						let pickerContainer = $( this ).closest( '.wp-picker-container' );
						let pickerText      = pickerContainer.find( 'button .wp-color-result-text' );
						let clearButton     = pickerContainer.find( '.wp-picker-default' );
						let pickerInputWrap = pickerContainer.find( '.wp-picker-input-wrap' );
						let colorPickerShow = $( this ).data( 'addon-colorpicker-show' );
						let placeholder     = $( this ).data( 'addon-placeholder' );

						// Hide always the picker text
						pickerText.html( '' );

						// Create an custom element to show the custom Clear button.
						let wrap_main1 = $( this ).parents( '.wp-picker-container' ),
						wrap1          = wrap_main1.find( '.wp-picker-input-wrap' );

						if ( ! wrap_main1.hasClass( 'yith-wapo-colorpicker-initialized' ) ) {
							wrap_main1.addClass( 'yith-wapo-colorpicker-initialized' );
						}

						if ( ! wrap1.find( '.wp-picker-default-custom' ).length ) {
							var button = $( '<span/>' ).attr(
								{
									class: 'wp-picker-default-custom'
								}
							);
							wrap1.find( '.wp-picker-default, .wp-picker-clear' ).wrap( button );
						}

						// If it's placeholder option, create a custom element to show the placeholder label.
						if ( 'placeholder' === colorPickerShow ) {
							pickerInputWrap.find( '.wp-color-picker' ).hide();
							if ( ! pickerInputWrap.find( '.wp-picker-custom-placeholder' ).length ) {
								var placeholder_el = $( '<span/>' ).attr(
									{
										class: 'wp-picker-custom-placeholder',
									}
								);
								placeholder_el.html( placeholder );
								pickerInputWrap.find( '.screen-reader-text' ).before( placeholder_el );
							}
							let clearElement       = pickerContainer.find( '.wp-picker-default-custom' );
							let placeholderElement = pickerContainer.find( '.wp-picker-custom-placeholder' );

							clearElement.hide();
							placeholderElement.css( 'line-height', '0' );
						}

						clearButton.trigger( 'click' );

					}
				);
			}

			$( document ).on( 'yith-wapo-after-reload-addons', inicializeAddonColorpickers );

			checkColorPickerOnInput = function() {
				$( document ).on(
					'click',
					function (e) {
						if ( ! $( e.target ).is( '.yith-wapo-colorpicker-container .iris-picker, .yith-wapo-colorpicker-container .iris-picker-inner' ) ) {
							let initializedColorPickers = $( '.yith-wapo-colorpicker-container .yith-wapo-colorpicker-initialized .wp-color-picker' );
							if ( initializedColorPickers.length > 0 ) {
								initializedColorPickers.iris( 'hide' );
							}
							return;
						}
					}
				);
				$( '.yith-wapo-colorpicker-container .yith-wapo-colorpicker-initialized .wp-color-picker' ).click(
					function ( event ) {
						$( this ).iris( 'show' );
						return;
					}
				);
			};

			inicializeAddonColorpickers();
			checkColorPickerOnInput();

		};

		initColorpicker();

	}
);

jQuery(
	function ($) {
		var firstVariationLoading = false;

    calculateAddonsPrice = function() {
			var firstFreeOptions = 0,
			  currentAddonID     = 0,
			  totalPrice         = 0,
			  quantity           = $( yith_wapo.productQuantitySelector ).val(); // Quantity of the Add to cart form.

			if ( ! quantity > 0) {
				quantity = 1;
			}

			$( 'form.cart .yith-wapo-addon:not(.hidden):visible input, form.cart .yith-wapo-addon:not(.hidden):visible select, form.cart .yith-wapo-addon:not(.hidden):visible textarea' ).each(
				function () {

					let option          = $( this ),
					defaultProductPrice = parseFloat( $( '#yith-wapo-container' ).attr( 'data-product-price' ) ),
					optionID            = option.data( 'addon-id' );

					if ( optionID ) {
						  let optionType = option.attr( 'type' ),
						  priceMethod    = option.data( 'price-method' ),
						  price          = 0,
						  priceType      = '',
						  addon          = option.parents( '.yith-wapo-addon' ),
						  addonType      = addon.data( 'addon-type' ),
						  addonQty       = 1;

						if ( 'number' === optionType && 0 == option.val() ) {
							return totalPrice;
						}

            if ( option.is( 'textarea' ) ) {
              optionType = 'textarea';
            }

						if (option.is( ':checked' ) || option.find( ':selected' ).is( 'option' )
						|| (option.is( 'input:not([type=checkbox])' ) && option.is( 'input:not([type=radio])' ) && option.val() != '')
						|| (option.is( 'textarea' ) && option.val() != '')
						  ) {

							if ( option.is( 'select' ) ) {
								  option = option.find( ':selected' );
							}

							if ('number' === optionType) {
								yith_wapo_check_multiplied_price( option );
							}

							if ('text' === optionType || 'textarea' === optionType) {
								yith_wapo_check_multiplied_length( option );
							}

							if ( currentAddonID != optionID ) {
								currentAddonID   = option.data( 'addon-id' );
								firstFreeOptions = option.data( 'first-free-options' );
							}

							if ( option.data( 'first-free-enabled' ) == 'yes' && firstFreeOptions > 0) {
								firstFreeOptions--;
							} else {
								if ( typeof option.data( 'price-type' ) != 'undefined' && '' !== option.data( 'price-type' ) ) {
									priceType = option.data( 'price-type' ); // Percentage or fixed.
								}

								let dataPriceSale = option.data( 'price-sale' ),
								dataPrice         = option.data( 'price' );

								if (typeof dataPriceSale != 'undefined' && '' !== dataPriceSale && dataPriceSale >= 0 && 'multiplied' !== priceType ) {
									price = parseFloat( dataPriceSale );
								} else if (typeof dataPrice != 'undefined' && '' !== dataPrice ) {
									price = parseFloat( dataPrice );
								}

								if ( 'percentage' === priceType && 'discount' !== priceMethod ) {
									price = ( price * defaultProductPrice ) / 100;
								}

								if ( 'product' === addonType ) {
									if ( ! option.hasClass( '.yith-wapo-option' ) ) {
										option   = option.parents( '.yith-wapo-option' );
										addonQty = option.find( '.wapo-product-qty' );
										if ( addonQty ) {
											addonQty = addonQty.val();
											if ( addonQty > 1 ) {
												price = price * addonQty;
											}
										}
									}
								}

								// Multiply price by quantity. Not multiplied for Sell individually add-ons ( it will be 1 on cart ).
								if ( quantity > 1 && ! addon.hasClass( 'sell_individually' ) ) {
									price = price * quantity;
								}

								totalPrice += price;
							}
						}
					}
				}
			);

			return totalPrice;
		};

		setTotalBoxPrices = function( defaultProductPrice, totalPrice, replacePrice = true ) {
			var totalCurrency  = yith_wapo.woocommerce_currency_symbol,
			  totalCurrencyPos = yith_wapo.woocommerce_currency_pos,
			  totalThousandSep = yith_wapo.total_thousand_sep,
			  totalDecimalSep  = yith_wapo.decimal_sep,
			  totalPriceNumDec = yith_wapo.num_decimal,
			  quantity         = $( yith_wapo.productQuantitySelector ).val();

			if ( ! quantity > 0) {
				quantity = 1;
			}

			var totalProductPrice = defaultProductPrice * quantity,
			totalOptionsPrice     = parseFloat( totalPrice ),
			totalOrderPrice       = parseFloat( totalPrice + totalProductPrice ),
			totalOrderPriceHtml   = totalOrderPrice;

			// Price without formatting.
			var total_ProductPrice = totalProductPrice,
			total_OptionsPrice     = totalOptionsPrice;

			// Price formatting
			totalProductPrice = totalProductPrice.toFixed( totalPriceNumDec ).replace( '.', totalDecimalSep ).replace( /(\d)(?=(\d{3})+(?!\d))/g, '$1' + totalThousandSep );
			totalOptionsPrice = totalOptionsPrice.toFixed( totalPriceNumDec ).replace( '.', totalDecimalSep ).replace( /(\d)(?=(\d{3})+(?!\d))/g, '$1' + totalThousandSep );
			totalOrderPrice   = totalOrderPrice.toFixed( totalPriceNumDec ).replace( '.', totalDecimalSep ).replace( /(\d)(?=(\d{3})+(?!\d))/g, '$1' + totalThousandSep );

			if (totalCurrencyPos == 'right') {
				totalProductPrice   = totalProductPrice + totalCurrency;
				totalOptionsPrice   = totalOptionsPrice + totalCurrency;
				totalOrderPriceHtml = totalOrderPrice + totalCurrency;
			} else if (totalCurrencyPos == 'right_space') {
				totalProductPrice   = totalProductPrice + ' ' + totalCurrency;
				totalOptionsPrice   = totalOptionsPrice + ' ' + totalCurrency;
				totalOrderPriceHtml = totalOrderPrice + ' ' + totalCurrency;
			} else if (totalCurrencyPos == 'left_space') {
				  totalProductPrice   = totalCurrency + ' ' + totalProductPrice;
				  totalOptionsPrice   = totalCurrency + ' ' + totalOptionsPrice;
				  totalOrderPriceHtml = totalCurrency + ' ' + totalOrderPrice;
			} else {
				totalProductPrice   = totalCurrency + totalProductPrice;
				totalOptionsPrice   = totalCurrency + totalOptionsPrice;
				totalOrderPriceHtml = totalCurrency + totalOrderPrice;
			}

			if ( yith_wapo.price_suffix ) {
				calculateProductPrice();
			} else {
				$( '#wapo-total-product-price' ).html( totalProductPrice );
			}

      replaceProductPrice( replacePrice, totalOrderPrice, totalOrderPriceHtml );

			$( '#wapo-total-options-price' ).html( totalOptionsPrice );
			$( '#wapo-total-order-price' ).html( totalOrderPriceHtml );

			$( '#wapo-total-price-table' ).css( 'opacity', '1' );

			$( document ).trigger( 'yith_wapo_product_price_updated', [total_ProductPrice + total_OptionsPrice] );
		},

    replaceProductPrice = function ( replacePrice, totalOrderPrice, totalOrderPriceHtml ) {
      if ( replacePrice && 'yes' === yith_wapo.replace_product_price && ! isNaN( parseFloat( totalOrderPrice ) ) && $( yith_wapo.replace_product_price_class ).length > 0 ) {
        let priceSuffix   = yith_wapo.priceSuffix,
          priceSuffixHtml = '';

        if ( priceSuffix ) {
          priceSuffixHtml = ' <small class="woocommerce-price-suffix">' + priceSuffix + '</small>';
        }
        $( yith_wapo.replace_product_price_class ).html( '<span class="woocommerce-Price-amount amount"><bdi>' + totalOrderPriceHtml + '</bdi></span>' + priceSuffixHtml );
        let productPrice    = $( yith_wapo.replace_product_price_class + ' bdi' ).text();
		if ( wcPriceToFloat( productPrice ) === 0 ) {
          $( yith_wapo.replace_product_price_class ).find( 'bdi' ).remove();
        }
      }
    },

		calculateProductPrice = function () {
			  var data_price_suffix = {
					'action'	: 'update_totals_with_suffix',
					'product_id'	: parseInt( $( '#yith-wapo-container' ).attr( 'data-product-id' ) ),
					'security'  : yith_wapo.addons_nonce,
			};
			jQuery.ajax(
				{
					url : yith_wapo.ajaxurl,
					type : 'post',
					data : data_price_suffix,
					success : function( response ) {
						if ( response ) {
							let totalProductPrice = response['price_html'];
							$( '#wapo-total-product-price' ).html( totalProductPrice );

						}
					}
				}
			);
		},

		calculateTotalAddonsPrice = function (replacePrice = true) {

      //Check logical conditions before calculate prices.
      yith_wapo_conditional_logic_check();

      if ( 'yes' === yith_wapo.hide_button_required ) {
				yith_wapo_check_required_fields( 'hide' );
			}

			$( '#wapo-total-price-table' ).css( 'opacity', '0.5' );
					var totalPrice          = 0;
					var defaultProductPrice = parseFloat( $( '#yith-wapo-container' ).attr( 'data-product-price' ) );
					var totalPriceBoxOption = yith_wapo.total_price_box_option;

					let selectedGifCardAmountButton = $( 'button.ywgc-amount-buttons.selected_button' );

					if ( selectedGifCardAmountButton.length > 0 ) {
						  defaultProductPrice = selectedGifCardAmountButton.data( 'price' );
					}

					totalPrice = calculateAddonsPrice();

					// Plugin option "Total price box".
					if ( 'hide_options' === totalPriceBoxOption ) {
						if ( 0 !== totalPrice ) {
							$( '#wapo-total-price-table .hide_options tr.wapo-total-options' ).fadeIn();
						} else {
							$( '#wapo-total-price-table .hide_options tr.wapo-total-options' ).hide();
						}
					}

					setTotalBoxPrices( defaultProductPrice, totalPrice, replacePrice );

		};

		productQuantityChange = function () {
			let inputNumber   = $( this ),
			  inputVal        = inputNumber.val(),
			  productId       = inputNumber.closest( '.yith-wapo-option' ).data( 'product-id' ),
			  addToCartLink   = inputNumber.closest( '.option-add-to-cart' ).find( '.add_to_cart_button' ),
			  productQuantity = 1,
			  hrefCreated     = '';

			if ( addToCartLink.length && productId ) {
				if ( inputVal > 1 ) {
					 productQuantity = inputVal;
				}

				hrefCreated = '?add-to-cart=' + productId + '&quantity=' + productQuantity;

				addToCartLink.attr( 'href', hrefCreated );
			}

		};

		wcPriceToFloat = function (wc_price){
			let price = wc_price.replace( /(?![\.\,])\D/g, '' )
			  .replace( yith_wapo.total_thousand_sep, '' )
			  .replace( yith_wapo.decimal_sep, '.' );

			return parseFloat( price );
		},
      getDefaultProductPrice = function () {
        let product_id = $( '.variations_form.cart' ).data( 'product_id' );
        let data = {
          'action' : 'get_default_variation_price',
          'product_id' : parseInt( product_id ),
          'security'  : yith_wapo.addons_nonce,
        };
        jQuery.ajax(
          {
            url : yith_wapo.ajaxurl,
            type : 'post',
            data : data,
            success : function( response ) {

              if ( response ) {
                let defaultProductPrice = response['price_html'];
				let container = jQuery( '#yith-wapo-container' );
				container.attr( 'data-product-price', response['current_price'] );
				container.attr( 'data-product-id', product_id );

                if ( 'yes' === yith_wapo.replace_product_price && container.find('.yith-wapo-block').length ) {
					$( yith_wapo.replace_product_price_class ).html( defaultProductPrice );
                }

              }

            },
            complete: function (){
			      }
          }
        );
      },

      /**
       * Check the default options selected on load page to replace the image.
       */
      checkDefaultOptionsOnLoad = function() {
        let optionsSelected =  $( '.yith-wapo-addon:not(.conditional_logic) .yith-wapo-option.selected' );
        $( optionsSelected ).each(
          function() {
            let option = $( this );
            yith_wapo_replace_image( option );
          }
        );
      },

      resetAddons = function ( event, params ) {

        if ( 'yith_wccl' === params ) {
          return;
        }

        if ( ! firstVariationLoading ) {
          firstVariationLoading = true;
          return;
        }

        getDefaultProductPrice();
        //let container = jQuery( '#yith-wapo-container' );
        //container.attr( 'data-product-price', 0 );

        $( document ).trigger( 'yith-wapo-reset-addons' );

      },
      foundVariation = function( event, variation ) {
        updateContainerProductPrice( variation );
        $( document ).trigger( 'yith-wapo-reload-addons' );
      },

      reloadAddons = function ( event, productPrice = '' ) {
        var addons = $( 'form.cart' ).serializeArray(),
        container = $( 'form.cart:not(.ywcp) #yith-wapo-container' ),
        data   = {
          	'action'	: 'live_print_blocks',
          	'addons'	: addons,
			'currency'	: yith_wapo.woocommerce_currency,

        };

        if ( productPrice != '' ) {
          data.price = productPrice;
        }

        $( '#yith-wapo-container' ).css( 'opacity', '0.5' );

        $.ajax(
          {
            url : yith_wapo.ajaxurl,
            type : 'post',
            data : data,
            success : function( response ) {
              container.html( response );
              container.css( 'opacity', '1' );

              $( 'form.cart' ).trigger( 'yith-wapo-after-reload-addons' );

              calculateTotalAddonsPrice();

            },
          }
        );
      };

	  // Calculate Add-ons price triggers
		$( document ).on(
			'ywgc-amount-changed',
			function( e, button_amount ) {
				let price     = button_amount.data( 'price' );
				let container = jQuery( '#yith-wapo-container' );

				container.attr( 'data-product-price', price );
				calculateTotalAddonsPrice();
			}
		);

		$( document ).on( 'change', '.gift-cards-list .ywgc-manual-amount-container input.ywgc-manual-amount', function( e ) {
			let t     = $( this ),
				price = t.val();

			let container = jQuery( '#yith-wapo-container' );

			container.attr( 'data-product-price', price );
			calculateTotalAddonsPrice();
		} );

		$( document ).on(
			'ywdpd_price_html_updated',
			function( e, html_price ) {
				let price = jQuery( html_price ).children( '.amount bdi' ).text();
				price     = wcPriceToFloat( price );

				if ( ! isNaN( price ) ) {
					let container = jQuery( '#yith-wapo-container' );

					container.attr( 'data-product-price', price );
					calculateTotalAddonsPrice();
				}
			}
		);

		$( document ).on(
			'yith_wcpb_ajax_update_price_request',
			function( e, response ) {
				let price = jQuery( response.price_html ).children( '.amount bdi' ).text();
				price     = wcPriceToFloat( price );

				if ( ! isNaN( price ) ) {
					let container = jQuery( '#yith-wapo-container' );

					container.attr( 'data-product-price', price );
					calculateTotalAddonsPrice();
				}
			}
		);

		$( document ).on(
			'change',
			'form.cart div.yith-wapo-addon, form.cart .quantity input[type=number]',
			function () {
				calculateTotalAddonsPrice();
			}
		);
		$( document ).on(
			'keyup',
			'form.cart .yith-wapo-addon-type-number input[type="number"], form.cart .yith-wapo-addon-type-text input[type="text"], form.cart .yith-wapo-addon-type-textarea textarea',
			function () {
				calculateTotalAddonsPrice();
			}
		);
		$( document ).on(
			'click',
			'form.cart .yith-wapo-addon-type-colorpicker .yith-wapo-colorpicker-initialized input.wp-color-picker',
			function () {
				calculateTotalAddonsPrice();
			}
		);
		$( document ).on(
			'wapo-colorpicker-change',
			function() {
				calculateTotalAddonsPrice();
			}
		);

    calculateTotalAddonsPrice();
    checkDefaultOptionsOnLoad();

    /** Product quantity change */
    $( document ).on( 'change keyup', '.yith-wapo-option .wapo-product-qty', productQuantityChange );
    $( document ).on( 'reset_data', resetAddons );
    $( document ).on( 'found_variation', foundVariation );

    /** ajax reload addons **/
    $( document ).on( 'yith-wapo-reload-addons', reloadAddons );

	}
);

// addon type (checkbox)

jQuery( document ).on(
	'change',
	'.yith-wapo-addon-type-checkbox input',
	function() {
		let checkboxInput   = jQuery( this );
		let checkboxButton  = checkboxInput.parents( '.checkboxbutton' );
		let checkboxOption  = checkboxInput.parents( '.yith-wapo-option' );
		let checkboxOptions = checkboxOption.parent();

		let isChecked = checkboxInput.attr( 'checked' );

		if ( 'checked' !== isChecked ) {

			// Single selection
			if ( checkboxOption.hasClass( 'selection-single' ) ) {
				// Disable all.
				checkboxOptions.find( 'input' ).attr( 'checked', false );
				checkboxOptions.find( 'input' ).prop( 'checked', false );
				checkboxOptions.find( '.selected, .checked' ).removeClass( 'selected checked' );
			}

			// Enable only the current option.
			checkboxInput.attr( 'checked', true );
			checkboxInput.prop( 'checked', true );
			checkboxOption.addClass( 'selected' );
			checkboxButton.addClass( 'checked' );

			// Replace image
			yith_wapo_replace_image( checkboxOption );

		} else {
			checkboxInput.attr( 'checked', false );
			checkboxInput.prop( 'checked', false );
			checkboxOption.removeClass( 'selected' );
			checkboxButton.removeClass( 'checked' );

			yith_wapo_replace_image( checkboxOption, true );
		}
	}
);

// addon type (color)

jQuery( document ).on(
	'change',
	'.yith-wapo-addon-type-color input',
	function() {
		var optionWrapper = jQuery( this ).parent();
		// Proteo check
		if ( ! optionWrapper.hasClass( 'yith-wapo-option' ) ) {
			optionWrapper = optionWrapper.parent(); }
		if ( jQuery( this ).is( ':checked' ) ) {
			optionWrapper.addClass( 'selected' );

			// Single selection
			if ( optionWrapper.hasClass( 'selection-single' ) ) {
				// Disable all
				optionWrapper.parent().find( 'input' ).prop( 'checked', false );
				optionWrapper.parent().find( '.selected' ).removeClass( 'selected' );
				// Enable only the current option
				optionWrapper.find( 'input' ).prop( 'checked', true );
				optionWrapper.addClass( 'selected' );
			}

			// Replace image
			yith_wapo_replace_image( optionWrapper );

		} else {
			optionWrapper.removeClass( 'selected' );
			yith_wapo_replace_image( optionWrapper, true );
		}
	}
);

// addon type (label)

jQuery( 'body' ).on(
	'change',
	'.yith-wapo-addon-type-label input',
	function() {
		var optionWrapper = jQuery( this ).parent();
		// Proteo check
		if ( ! optionWrapper.hasClass( 'yith-wapo-option' ) ) {
			optionWrapper = optionWrapper.parent(); }
		if ( jQuery( this ).is( ':checked' ) ) {
			optionWrapper.addClass( 'selected' );

			// Single selection
			if ( optionWrapper.hasClass( 'selection-single' ) ) {
				// Disable all
				optionWrapper.parent().find( 'input' ).prop( 'checked', false );
				optionWrapper.parent().find( '.selected' ).removeClass( 'selected' );
				// Enable only the current option
				optionWrapper.find( 'input' ).prop( 'checked', true );
				optionWrapper.addClass( 'selected' );
			}

			// Replace image
			yith_wapo_replace_image( optionWrapper );

		} else {
			optionWrapper.removeClass( 'selected' );
			yith_wapo_replace_image( optionWrapper, true );
		}
	}
);

// addon type (product)
jQuery( document ).on( 'click change', '.yith-wapo-addon-type-product .quantity input',
	function (e) {
		e.stopPropagation();
	}
);

jQuery( document ).on(
  'click',
  '.yith-wapo-addon-type-product .yith-wapo-option .product-container',
  function() {
    jQuery( this ).closest( '.yith-wapo-option' ).find( '.yith-proteo-standard-checkbox' ).click();
  }
);

jQuery( document ).on(
	'change',
	'.yith-wapo-addon-type-product .yith-wapo-option input.yith-proteo-standard-checkbox',
	function() {

		var optionWrapper = jQuery( this ).parent();// Proteo check
		// Proteo check
		if ( ! optionWrapper.hasClass( 'yith-wapo-option' ) ) {
			optionWrapper = optionWrapper.parent(); }
		if ( jQuery( this ).is( ':checked' ) ) {
			optionWrapper.addClass( 'selected' );

			// Single selection
			if ( optionWrapper.hasClass( 'selection-single' ) ) {
				// Disable all
				optionWrapper.parent().find( 'input' ).prop( 'checked', false );
				optionWrapper.parent().find( '.selected' ).removeClass( 'selected' );
				// Enable only the current option
				optionWrapper.find( 'input' ).prop( 'checked', true );
				optionWrapper.addClass( 'selected' );
			}

			// Replace image
			yith_wapo_replace_image( optionWrapper );

		} else {
			optionWrapper.removeClass( 'selected' );
			yith_wapo_replace_image( optionWrapper, true );
		}
	}
);

// addon type (radio)

jQuery( 'body' ).on(
	'change',
	'.yith-wapo-addon-type-radio input',
	function() {
		var optionWrapper = jQuery( this ).parent();
		// Proteo check
		if ( ! optionWrapper.hasClass( 'yith-wapo-option' ) ) {
			optionWrapper = optionWrapper.closest( '.yith-wapo-option' );
    }
		if ( jQuery( this ).is( ':checked' ) ) {
			optionWrapper.addClass( 'selected' );

			// Replace image
			yith_wapo_replace_image( optionWrapper );

			   // Remove selected siblings
			   optionWrapper.siblings().removeClass( 'selected' );

		} else {
			optionWrapper.removeClass( 'selected' ); }
	}
);

// addon type (select)

jQuery( 'body' ).on(
	'change',
	'.yith-wapo-addon-type-select select',
	function() {
		let optionWrapper    = jQuery( this ).parent();
		let selectedOption   = jQuery( this ).find( 'option:selected' );
		let optionImageBlock = optionWrapper.find( 'div.option-image' );
		// Proteo check
		if ( ! optionWrapper.hasClass( 'yith-wapo-option' ) ) {
			optionWrapper = optionWrapper.parent();
		}

		// Description & Image.
		var optionImage       = selectedOption.data( 'image' );
		var optionDescription = selectedOption.data( 'description' );
		var option_desc       = optionWrapper.find( 'p.option-description' );

		if ( typeof optionImage !== 'undefined' && optionImage ) {
			optionImage = '<img src="' + optionImage + '" style="max-width: 100%">';
			optionImageBlock.html( optionImage );
		}

		if ( 'default' === selectedOption.val() || '' === optionImage ) {
			optionImageBlock.hide();
		} else {
			optionImageBlock.fadeIn();
		}

		if ( 'undefined' === typeof optionDescription ) {
			option_desc.empty();
		} else {
			option_desc.html( optionDescription );
		}

		// Replace image
		if ( selectedOption.data( 'replace-image' ) ){
			yith_wapo_replace_image( selectedOption );
		} else {
			yith_wapo_replace_image( selectedOption, true );
		}

	}
);
jQuery( '.yith-wapo-addon-type-select select' ).trigger( 'change' );



// toggle feature

jQuery( document ).on(
	'click',
	'.yith-wapo-addon.wapo-toggle .wapo-addon-title',
	function() {

		let addon_title = jQuery( this );
		let addon_el    = addon_title.parents( '.yith-wapo-addon' );

		if ( addon_el.hasClass( 'toggle-open' ) ) {
			addon_el.removeClass( 'toggle-open' ).addClass( 'toggle-closed' );
		} else {
			addon_el.removeClass( 'toggle-closed' ).addClass( 'toggle-open' );
		}
		if ( addon_title.hasClass( 'toggle-open' ) ) {
			addon_title.removeClass( 'toggle-open' ).addClass( 'toggle-closed' );
		} else {
			addon_title.removeClass( 'toggle-closed' ).addClass( 'toggle-open' );
		}

		addon_title.parents( '.yith-wapo-addon' ).find( '.options, .title-image' ).toggle( 'fast' );

		jQuery( document ).trigger( 'yith_proteo_inizialize_html_elements' );
	}
);






// function: replace image

function yith_wapo_replace_image( optionWrapper, reset = false ) {

	var defaultPath   = yith_wapo.replace_image_path;
	var zoomMagnifier = '.yith_magnifier_zoom_magnifier, .zoomWindowContainer .zoomWindow';

	if ( typeof optionWrapper.data( 'replace-image' ) !== 'undefined' && optionWrapper.data( 'replace-image' ) != '' ) {
		var replaceImageURL = optionWrapper.data( 'replace-image' );

		// save original image for the reset
		if ( typeof( jQuery( defaultPath ).attr( 'wapo-original-img' ) ) == 'undefined' ) {
			jQuery( defaultPath ).attr( 'wapo-original-img', jQuery( defaultPath ).attr( 'src' ) );
			if ( jQuery( zoomMagnifier ).length ) {
				jQuery( zoomMagnifier ).attr( 'wapo-original-img', jQuery( zoomMagnifier ).css( 'background-image' ).slice( 4, -1 ).replace( /"/g, "" ) );
			}
		}
		jQuery( defaultPath ).attr( 'src', replaceImageURL );
		jQuery( defaultPath ).attr( 'srcset', replaceImageURL );
		jQuery( defaultPath ).attr( 'data-src', replaceImageURL );
		jQuery( zoomMagnifier ).css( 'background-image', 'url(' + replaceImageURL + ')' );
		jQuery( '#yith_wapo_product_img' ).val( replaceImageURL );
		jQuery( defaultPath ).attr( 'data-large_image', replaceImageURL );

		// Reset gallery position when add-on image change
		if ( jQuery( '.woocommerce-product-gallery .woocommerce-product-gallery__image' ).length > 0 ) {
			jQuery( '.woocommerce-product-gallery' ).trigger( 'woocommerce_gallery_reset_slide_position' );
		}
		jQuery( '.woocommerce-product-gallery' ).trigger( 'woocommerce_gallery_init_zoom' );
		jQuery( document ).trigger( 'yith-wapo-after-replace-image' );
	}

	if ( reset && typeof( jQuery( defaultPath ).attr( 'wapo-original-img' ) ) != 'undefined' ) {
		let checkReset = true;
		jQuery( ".yith-wapo-option" ).each(
			function( index, element ) {
				let option = jQuery( element );
				// Check if one option is still selected and has a image to replace, then do not change to default image.
				if ( option.data( 'replace-image' ) && option.hasClass( 'selected' ) ) {
					  checkReset = false;
				}
			}
		);
		if ( checkReset ) {
			var originalImage = jQuery( defaultPath ).attr( 'wapo-original-img' );
			var originalZoom  = jQuery( zoomMagnifier ).attr( 'wapo-original-img' );

			jQuery( defaultPath ).attr( 'src', originalImage );
			jQuery( defaultPath ).attr( 'srcset', originalImage );
			jQuery( defaultPath ).attr( 'data-src', originalImage );
			jQuery( defaultPath ).attr( 'data-large_image', originalImage );
			jQuery( zoomMagnifier ).css( 'background-image', 'url(' + originalZoom + ')' );

			// Reset gallery position when add-on image change
			if ( jQuery( '.woocommerce-product-gallery .woocommerce-product-gallery__image' ).length > 0 ) {
				jQuery( '.woocommerce-product-gallery' ).trigger( 'woocommerce_gallery_reset_slide_position' );
			}
			jQuery( '.woocommerce-product-gallery' ).trigger( 'woocommerce_gallery_init_zoom' );
			jQuery( document ).trigger( 'yith-wapo-after-replace-image' );
		}
	}
}

// function: check_required_fields

function yith_wapo_check_required_fields( action ) {

	var isRequired    = false;
	var hideButton    = false;
	var buttonClasses = yith_wapo.dom.single_add_to_cart_button;
	jQuery( 'form.cart .yith-wapo-addon:not(.hidden):visible input, form.cart .yith-wapo-addon:not(.hidden):visible select, form.cart .yith-wapo-addon:not(.hidden):visible textarea' ).each(
		function() {
			let element            = jQuery( this );
			let parent             = element.parents( '.yith-wapo-option' );
			let toggle_addon       = element.parents( 'div.yith-wapo-addon.wapo-toggle' );
			let toggle_addon_title = toggle_addon.find( 'h3.wapo-addon-title.toggle-closed' );
			let upload_el          = parent.find( '.yith-wapo-ajax-uploader' );

			if (
			element.attr( 'required' ) && ( 'checkbox' === element.attr( 'type' ) || 'radio' === element.attr( 'type' ) ) && ! element.parents( '.yith-wapo-option' ).hasClass( 'selected' )
			||
			element.attr( 'required' ) && ( element.val() == '' || element.val() == 'Required' || ( element.val() == 'default' && element.is('select') )  )
			) {
				if ( action === 'highlight' ) {
					if ( upload_el ) {
						upload_el.css( 'border', '1px dashed #f00' );
						upload_el.css( 'background-color', '#fee' );
					} else {
						element.css( 'border', '1px solid #f00' );
					}

					parent.find( '.required-error' ).css( 'display', 'block' );

					if ( toggle_addon_title ) {
						toggle_addon_title.click();
					}
				}

				  hideButton = true;
				  isRequired = true;
			}
		}
	);
	if ( action == 'hide' ) {
		if ( hideButton ) {
			jQuery( buttonClasses ).hide(); } else {
			jQuery( buttonClasses ).fadeIn(); }
	}
	return ! isRequired;
}


// Conditional logic.
/**
 * Conditional Logic
 */
function yith_wapo_conditional_logic_check( lastFinalConditions = {} ) {
	var finalConditions = {};

  jQuery( 'form.cart .yith-wapo-addon.conditional_logic' ).each(
		function() {

			var AddonConditionSection = false,
        AddonVariationSection = false;

			var logicDisplay = jQuery( this ).data( 'conditional_logic_display' ); // show / hide

			// Applied to conditions.
			var logicDisplayIf = jQuery( this ).data( 'conditional_logic_display_if' ); // all / any

			var ruleAddon     = String( jQuery( this ).data( 'conditional_rule_addon' ) ),
        ruleAddonIs   = String( jQuery( this ).data( 'conditional_rule_addon_is' ) ),
        ruleVariation = String( jQuery( this ).data( 'conditional_rule_variations' ) );

			ruleAddon     = ( typeof ruleAddon !== 'undefined' && ruleAddon !== "0" && ruleAddon !== '' ) ? ruleAddon.split( '|' ) : false; // addon number
			ruleAddonIs   = ( typeof ruleAddonIs !== 'undefined' && ruleAddonIs !== '' ) ? ruleAddonIs.split( '|' ) : false; // selected / not-selected / empty / not-empty
			ruleVariation = ( typeof ruleVariation !== 'undefined' && ruleVariation !== '' ) ? ruleVariation.split( '|' ) : false; // variations number

			if ( ! ruleVariation && ( ! ruleAddon || ! ruleAddonIs ) ) {  // Show addon if no variations conditions or addons conditions.

				AddonConditionSection = true;
				AddonVariationSection = true;
				logicDisplay          = 'show';

			} else {

				// ConditionLogic.
				if ( ruleAddon && ruleAddonIs ) {

					switch ( logicDisplayIf ) {

						case 'all':
							AddonConditionSection = conditionalLogicAllRules( ruleAddon, ruleAddonIs );
					break;

						case 'any':
							AddonConditionSection = conditionalLogicAnyRules( ruleAddon, ruleAddonIs );
					break;

					}

				} else {
					AddonConditionSection = true;
				}

				if ( AddonConditionSection && ruleVariation ) { // Prevent check variations if addons condition fails.
					var variationProduct = jQuery( '.variation_id' ).val();
					if (  -1 !== jQuery.inArray( String( variationProduct ), ruleVariation ) ) {
						AddonVariationSection = true;
					}

				} else {
					AddonVariationSection = true;
				}

			}

			switch ( logicDisplay ) {

				case 'show' :

					if ( AddonVariationSection && AddonConditionSection ) { // Both conditions true --> apply logic Display
						finalConditions[jQuery( this ).attr( 'id' )] = 'not-hidden';
					} else {
						finalConditions[jQuery( this ).attr( 'id' )] = 'hidden';
					}
				break;

				case 'hide' :
					if ( AddonVariationSection && AddonConditionSection ) {  // Both conditions true --> apply logic Display
						finalConditions[jQuery( this ).attr( 'id' )] = 'hidden';
					} else {
						finalConditions[jQuery( this ).attr( 'id' )] = 'not-hidden';
					}
			}
		}
	);

  jQuery.each(
    finalConditions,
    function( id, mode ) {
      let element = jQuery( '#' + id );

      if ( 'not-hidden' === mode ) {

        // Todo: We avoid out of stock to change disabled value.
        element.fadeIn().removeClass( 'hidden' ).find( '.yith-wapo-option:not(.out-of-stock) .yith-wapo-option-value' ).attr( 'disabled', false );
        let selectedOption = element.find( '.yith-wapo-option.selected' );
        yith_wapo_replace_image( selectedOption );


        // Re-enable select add-ons if it was hidden
		if ( element.hasClass( 'yith-wapo-addon-type-select' ) ){
			element.find( '.yith-wapo-option-value' ).attr( 'disabled', false );
		}

        // Check the min_max after disable value.
        yith_wapo_check_min_max( element );
      } else {
        element.hide().addClass( 'hidden' ).find( '.yith-wapo-option-value' ).attr( 'disabled', true );
      }

    }
  );

  if ( JSON.stringify( finalConditions ) !== JSON.stringify( lastFinalConditions ) ) {
    yith_wapo_conditional_logic_check( finalConditions );
  }

}

/**
 * Conditional Rule AND
 *
 * @param ruleAddon
 * @param ruleAddonIs
 * @returns {boolean}
 */
function conditionalLogicAllRules( ruleAddon, ruleAddonIs ) {
	var conditional = true;

	for ( var x = 0; x < ruleAddon.length; x++ ) {

		if ( ruleAddon[x] == 0 || ! ruleAddon[x] ) {
			continue;
		}

		var ruleAddonSplit = ruleAddon[x].split( '-' );
    var AddonSelected  = false;
    var AddonNotEmpty  = false;

		// variation check
		if ( typeof ruleAddonSplit[1] != 'undefined' ) {

			AddonSelected = ( // Selector or checkbox
			jQuery( '#yith-wapo-' + ruleAddonSplit[0] + '-' + ruleAddonSplit[1] ).is( ':checked' )
			|| jQuery( 'select#yith-wapo-' + ruleAddonSplit[0] ).val() == ruleAddonSplit[1]
			) && ! jQuery( '#yith-wapo-addon-' + ruleAddonSplit[0] ).hasClass( 'hidden' );

			var typeText     = jQuery( 'input#yith-wapo-' + ruleAddonSplit[0] + '-' + ruleAddonSplit[1] ).val();			// text
			var typeTextarea = jQuery( 'textarea#yith-wapo-' + ruleAddonSplit[0] + '-' + ruleAddonSplit[1] ).val();		// textarea
			AddonNotEmpty    = (
			( typeof typeText != 'undefined' && typeText !== '' )
			|| ( typeof typeTextarea != 'undefined' && typeTextarea !== '' )
			) && ! jQuery( '#yith-wapo-addon-' + ruleAddonSplit[0] ).hasClass( 'hidden' );

			// addon check
		} else {
			AddonSelected = (
			jQuery( '#yith-wapo-addon-' + ruleAddon[x] + ' input:checkbox:checked' ).length > 0
			|| jQuery( '#yith-wapo-addon-' + ruleAddon[x] + ' input:radio:checked' ).length > 0
			|| jQuery( '#yith-wapo-addon-' + ruleAddon[x] + ' option:selected' ).length > 0
			&& 'default' != jQuery( '#yith-wapo-addon-' + ruleAddon[x] + ' option:selected' ).val() // Check if not default value of Select Add-on
			);
			AddonSelected = AddonSelected && ! jQuery( '#yith-wapo-addon-' + ruleAddon[x] ).hasClass( 'hidden' );

			var typeText = 'undefined';
			jQuery( '#yith-wapo-addon-' + ruleAddonSplit[0] + ' input, #yith-wapo-addon-' + ruleAddonSplit[0] + ' textarea' ).each(
				function( index ){
					if ( jQuery( this ).val() !== '' ) {
						typeText = true;
						return;
					}
				}
			);
			AddonNotEmpty = (
			( typeText != 'undefined' && typeText !== '')
			// || (typeof typeTextarea != 'undefined' && typeTextarea !== '')
			) && ! jQuery( '#yith-wapo-addon-' + ruleAddonSplit[0] ).hasClass( 'hidden' );
		}

    switch ( ruleAddonIs[x]  ) {
			case 'selected' :
				if ( ! AddonSelected ) {
					conditional = false;
				}
			break;
			case 'not-selected':
				if ( AddonSelected ) {
					conditional = false;
				}
			break;

			case 'empty' :
				if ( AddonNotEmpty ) {
					conditional = false;
				}
			break;

			case 'not-empty' :
				if ( ! AddonNotEmpty ) {
					conditional = false;
				}

			break;
		}

		if ( ! conditional ) {
			break;
		}

	}

	return conditional;

}

/**
 * Conditional Rule OR
 *
 * @param ruleAddon
 * @param ruleAddonIs
 * @returns {boolean}
 */
function conditionalLogicAnyRules( ruleAddon, ruleAddonIs ) {

	var conditional = false;

	for ( var x = 0; x < ruleAddon.length; x++ ) {

		if ( ruleAddon[x] == 0 || ! ruleAddon[x] ) {
			continue;
		}
		var ruleAddonSplit = ruleAddon[x].split( '-' );

		// variation check
		if (typeof ruleAddonSplit[1] != 'undefined') {

			AddonSelected = ( // Selector or checkbox
			jQuery( '#yith-wapo-' + ruleAddonSplit[0] + '-' + ruleAddonSplit[1] ).is( ':checked' )
			|| jQuery( 'select#yith-wapo-' + ruleAddonSplit[0] ).val() == ruleAddonSplit[1]
			) && ! jQuery( '#yith-wapo-addon-' + ruleAddonSplit[0] ).hasClass( 'hidden' );

			var typeText     = jQuery( 'input#yith-wapo-' + ruleAddonSplit[0] + '-' + ruleAddonSplit[1] ).val();			// text
			var typeTextarea = jQuery( 'textarea#yith-wapo-' + ruleAddonSplit[0] + '-' + ruleAddonSplit[1] ).val();		// textarea
			AddonNotEmpty    = (
			(typeof typeText != 'undefined' && typeText !== '')
			|| (typeof typeTextarea != 'undefined' && typeTextarea !== '')
			) && ! jQuery( '#yith-wapo-addon-' + ruleAddonSplit[0] ).hasClass( 'hidden' );

			// addon check
		} else {
			AddonSelected = (
			jQuery( '#yith-wapo-addon-' + ruleAddon[x] + ' input:checkbox:checked' ).length > 0
			|| jQuery( '#yith-wapo-addon-' + ruleAddon[x] + ' input:radio:checked' ).length > 0
			|| jQuery( '#yith-wapo-addon-' + ruleAddon[x] + ' option:selected' ).length > 0
			&& 'default' != jQuery( '#yith-wapo-addon-' + ruleAddon[x] + ' option:selected' ).val() // Check if not default value of Select Add-on
			);
			AddonSelected = AddonSelected && ! jQuery( '#yith-wapo-addon-' + ruleAddon[x] ).hasClass( 'hidden' );

			var typeText = 'undefined';
			jQuery( '#yith-wapo-addon-' + ruleAddonSplit[0] + ' input, #yith-wapo-addon-' + ruleAddonSplit[0] + ' textarea' ).each(
				function( index ){
					if ( jQuery( this ).val() !== '' ) {
						typeText = true;
						return;
					}
				}
			);
			AddonNotEmpty = (
			( typeText != 'undefined' && typeText !== '')
			// || (typeof typeTextarea != 'undefined' && typeTextarea !== '')
			) && ! jQuery( '#yith-wapo-addon-' + ruleAddonSplit[0] ).hasClass( 'hidden' );
		}

		switch ( ruleAddonIs[x] ) {
			case 'selected' :
				if ( AddonSelected ) {
					conditional = true;
				}
			break;
			case 'not-selected':
				if ( ! AddonSelected ) {
					conditional = true;
				}
			break;

			case 'empty' :
				if ( ! AddonNotEmpty ) {
					conditional = true;
				}
			break;

			case 'not-empty' :
				if ( AddonNotEmpty ) {
					conditional = true;
				}

			break;
		}
		if ( conditional ) {
			break;
		}
	}

	return conditional;
}

function updateContainerProductPrice( variation ) {

  // Do not allow updating the price if product bundle form exists.
  if ( jQuery( '.cart.yith-wcpb-bundle-form' ).length ) {
    return;
  }

	let container         = jQuery( '#yith-wapo-container' ),
  new_product_price = 0;
	if ( typeof( variation.display_price ) !== 'undefined' ) {
		new_product_price = variation.display_price;
		// Check if variation price and price_html are different, use the last one
		if ( 'yes' === yith_wapo.use_price_html_on_variations && typeof( variation.price_html ) !== 'undefined' ) {
			let new_product_price_html = jQuery( variation.price_html ).find( '> .amount bdi' ).text();
			new_product_price_html     = wcPriceToFloat( new_product_price_html );
			if ( ! isNaN( new_product_price_html ) && new_product_price !== new_product_price_html ) {
				new_product_price = new_product_price_html;
			}
		}
	}
	container.attr( 'data-product-price', new_product_price );
	container.attr( 'data-product-id', variation.variation_id );

}

// WooCommerce Measurement Price Calculator (compatibility)
jQuery( 'form.cart' ).on(
	'change',
	'#price_calculator.wc-measurement-price-calculator-price-table',
	function() {
		var price = jQuery( '#price_calculator.wc-measurement-price-calculator-price-table .product_price .amount' ).text();
		price     = wcPriceToFloat( price );

		if ( ! isNaN( price ) ) {
			let container = jQuery( '#yith-wapo-container' );

			container.attr( 'data-product-price', price );
			jQuery( document ).trigger( 'yith-wapo-reload-addons', [ price ] );
		}
	}
);

/*
 *	ajax upload file
 */

// preventing page from redirecting
jQuery( 'html' ).on(
	'dragover',
	function(e) {
		e.preventDefault();
		e.stopPropagation();
	}
);
jQuery( 'html' ).on( 'drop', function(e) { e.preventDefault(); e.stopPropagation(); } );

// drag enter
jQuery( '.yith-wapo-ajax-uploader' ).on(
	'dragenter',
	function (e) {
		e.stopPropagation();
		e.preventDefault();
		jQuery( this ).css( 'opacity', '0.5' );
	}
);

// drag over
jQuery( '.yith-wapo-ajax-uploader' ).on(
	'dragover',
	function (e) {
		e.stopPropagation();
		e.preventDefault();
	}
);

// drag leave
jQuery( '.yith-wapo-ajax-uploader' ).on(
	'dragleave',
	function (e) {
		e.stopPropagation();
		e.preventDefault();
		jQuery( this ).css( 'opacity', '1' );
	}
);

// drop
jQuery( '.yith-wapo-ajax-uploader' ).on(
	'drop',
	function (e) {
		e.stopPropagation();
		e.preventDefault();

		var input = jQuery( this ).parent().find( 'input.file' );
		var file  = e.originalEvent.dataTransfer.files[0];
		var data  = new FormData();
		data.append( 'action', 'upload_file' );
		data.append( 'file', file );

		if ( yith_wapo.upload_allowed_file_types.includes( file.name.split( '.' ).pop().toLowerCase() ) ) {
			if ( file.size <= yith_wapo.upload_max_file_size * 1024 * 1024 ) {
				yith_wapo_ajax_upload_file( data, file, input );
			} else {
				alert( 'Error: max file size ' + yith_wapo.upload_max_file_size + ' MB!' ) }
		} else {
			alert( 'Error: not supported extension!' ) }
	}
);

// click
jQuery( document ).on(
	'click',
	'.yith-wapo-ajax-uploader .button, .yith-wapo-ajax-uploader .link',
	function() {
		jQuery( this ).parent().parent().find( 'input.file' ).click();
	}
);

// upload on click
jQuery( document ).on(
	'change',
	'.yith-wapo-addon-type-file input.file',
	function() {
		var input = jQuery( this );
		var file  = jQuery( this )[0].files[0];
		var data  = new FormData();
		data.append( 'action', 'upload_file' );
		data.append( 'file', file );

		if ( yith_wapo.upload_allowed_file_types.includes( file.name.split( '.' ).pop().toLowerCase() ) ) {
			if ( file.size <= yith_wapo.upload_max_file_size * 1024 * 1024 ) {
				yith_wapo_ajax_upload_file( data, file, input );
			} else {
				alert( 'Error: max file size ' + yith_wapo.upload_max_file_size + ' MB!' ) }
		} else {
			alert( 'Error: not supported extension!' ) }

	}
);

// remove
jQuery( document ).on(
	'click',
	'.yith-wapo-uploaded-file .remove',
	function() {
		jQuery( this ).parent().hide();
		jQuery( this ).parent().parent().find( '.yith-wapo-ajax-uploader' ).fadeIn();
		jQuery( this ).parent().parent().find( 'input' ).val( '' );
		jQuery( this ).parent().parent().find( 'input.option' ).change();
	}
);

function yith_wapo_ajax_upload_file( data, file, input ) {

	let exactSize = calculate_exact_file_size( file );
	input.parent().find( '.yith-wapo-ajax-uploader' ).append( '<div class="loader"></div>' );

	jQuery.ajax(
		{
			url			: yith_wapo.ajaxurl,
			type		: 'POST',
			contentType	: false,
			processData	: false,
			dataType: 'json',
			data		: data,
			success : function( response ) {
				var wapo_option = input.parent();
				wapo_option.find( '.yith-wapo-ajax-uploader .loader' ).remove();
				wapo_option.find( '.yith-wapo-ajax-uploader' ).hide();
				// jQuery('.yith-wapo-ajax-uploader').html( 'Drop file to upload or <a href="' + response + '" target="_blank">browse</a>' );

				var file_name = response.file_name.replace( /^.*[\\\/] / , '' );

				wapo_option.find( '.yith-wapo-uploaded-file .info' ).html( file_name + '<br />' + exactSize );
				wapo_option.find( '.yith-wapo-uploaded-file' ).fadeIn();
				wapo_option.find( 'input.option' ).val( response.url ).change();
			},
			error : function ( response ) {
				// jQuery('.yith-wapo-ajax-uploader').html( 'Error!<br /><br />' + response );
			}
		}
	);
	return false;
}

function calculate_exact_file_size( file ) {
	let exactSize  = 0;
	let file_size  = file.size;
	let file_types = ['Bytes', 'KB', 'MB', 'GB'],
	i              = 0;
	while ( file_size > 900 ) {
		file_size /= 1024;
		i++;
	}
	exactSize = ( Math.round( file_size * 100 ) / 100 ) + ' ' + file_types[i];

	return exactSize;
}

jQuery( 'form.cart' ).on(
	'click',
	'span.checkboxbutton',
	function() {
		if ( jQuery( this ).find( 'input' ).is( ':checked' ) ) {
			jQuery( this ).addClass( 'checked' );
		} else {
			jQuery( this ).removeClass( 'checked' );
		}
	}
);

jQuery( 'form.cart' ).on(
	'click',
	'span.radiobutton',
	function() {
		if ( jQuery( this ).find( 'input' ).is( ':checked' ) ) {
			jQuery( this ).parent().parent().parent().find( 'span.radiobutton.checked' ).removeClass( 'checked' );
			jQuery( this ).addClass( 'checked' );
		}
	}
);


// min max rules

jQuery( '.yith-wapo-addon-type-checkbox, .yith-wapo-addon-type-color, .yith-wapo-addon-type-label, .yith-wapo-addon-type-product' ).each(
	function() {
		yith_wapo_check_min_max( jQuery( this ) );
	}
);
jQuery( document ).on(
	'change',
	'.yith-wapo-addon-type-checkbox, .yith-wapo-addon-type-color, .yith-wapo-addon-type-label, .yith-wapo-addon-type-product',
	function() {
		yith_wapo_check_min_max( jQuery( this ) );
	}
);

// Check required fields before adding to cart( Required select and min/max values ).
jQuery( document ).on(
	'click',
	'form.cart button',
	function() {
    let minMaxResult = yith_wapo_check_required_min_max();
		if ( minMaxResult ) {
			jQuery( 'form.cart .yith-wapo-addon.conditional_logic.hidden' ).remove();
		} else {
			if ( ! yith_wapo.disable_scroll_on_required_mix_max ) {
				  jQuery( 'html, body' ).animate( { scrollTop: jQuery( '#yith-wapo-container' ).offset().top }, 500 );
			}
		}

		return minMaxResult;
	}
);

jQuery( document ).on(
	'click',
	'.add-request-quote-button',
	function(e) {
		e.preventDefault();
		if ( typeof yith_wapo_general === 'undefined' ){
			yith_wapo_general = { do_submit: true };
		}
		if ( ! yith_wapo_check_required_min_max() ) {
			yith_wapo_general.do_submit = false;
		} else {
			yith_wapo_general.do_submit = true;
		}
	}
);

function yith_wapo_check_required_min_max() {

	if ( ! checkRequiredSelect() ) {
		return false;
	}
	if ( ! checkTextInputLimit() ){
		return false;
	}

	if ( ! yith_wapo_check_required_fields( 'highlight' ) ) {
		return false;
	}
	var requiredOptions = 0;
	var checkMinMax     = '';
	jQuery( 'form.cart .yith-wapo-addon:not(.hidden)' ).each(
		function() {
			checkMinMax = yith_wapo_check_min_max( jQuery( this ), true );
			if ( checkMinMax > 0 ) {
				  requiredOptions += checkMinMax;
			}
		}
	);
  if ( requiredOptions > 0 ) {
		return false;
	}
	return true;
}

function yith_wapo_check_min_max( addon, submit = false ) {

	var addonType       = addon.data( 'addon-type' );
	var minValue        = addon.data( 'min' );
	var maxValue        = addon.data( 'max' );
	var exaValue        = addon.data( 'exa' );
	var numberOfChecked = 0;

	let toggle_addon_title = addon.find( 'h3.wapo-addon-title.toggle-closed' );

	if ( 'number' === addonType || 'text' === addonType || 'textarea' === addonType ) {
		jQuery( addon ).find( '.yith-wapo-option-value' ).each(
			function( index ) {
				let numberValue = jQuery( this ).val();
				if ( numberValue.length ) {
					numberOfChecked++;
				}
			}
		);

    if ( '' !== maxValue && numberOfChecked > maxValue ) {
      let optionsElement = jQuery( addon ).find( '.options' );
      if ( ! optionsElement.find( '.max-selected-error' ).length ) {
        optionsElement.append( '<p class="max-selected-error">' + yith_wapo.maxOptionsSelectedMessage + '</p>' );
      }
      return 1;
    }

  } else {
		numberOfChecked = addon.find( 'input:checkbox:checked, input:radio:checked, option:not([value=""]):not([value="default"]):selected' ).length;
	}

	if ( exaValue > 0 ) {

		var optionsToSelect = 0;
		if ( exaValue == numberOfChecked ) {
			addon.removeClass( 'required-min' );
			addon.find( '.min-error, .min-error span' ).hide();
			// addon.find('input:checkbox').attr( 'required', false );
			addon.find( 'input:checkbox' ).not( ':checked' ).attr( 'disabled', true );
		} else {
			if ( submit ) {
				optionsToSelect = exaValue - numberOfChecked;
				addon.addClass( 'required-min' );
				addon.find( '.min-error' ).show();
				if ( optionsToSelect == 1 ) {
					addon.find( '.min-error-select, .min-error-an, .min-error-option' ).show();
					addon.find( '.min-error-qty' ).hide();
				} else {
					addon.find( '.min-error-an, .min-error-option' ).hide();
					addon.find( '.min-error-select, .min-error-qty, .min-error-options' ).show();
					addon.find( '.min-error-qty' ).text( optionsToSelect );
				}
				if ( toggle_addon_title ) {
					toggle_addon_title.click();
				}
			}
			// addon.find('input:checkbox').attr( 'required', true );
			addon.find( '.yith-wapo-option:not(.out-of-stock) input:checkbox' ).not( ':checked' ).attr( 'disabled', false );
		}
		return optionsToSelect;

	} else {
		if ( maxValue > 0 ) {
			if ( maxValue >= numberOfChecked ) {
				addon.removeClass( 'required-min' );
				addon.find( '.max-selected-error' ).hide();
			} else {
				if ( submit ) {
					addon.addClass( 'required-min' );
					let optionsElement = jQuery( addon ).find( '.options' );
					if ( ! optionsElement.find( '.max-selected-error' ).length ) {
						optionsElement.append( '<small class="max-selected-error">' + yith_wapo.maxOptionsSelectedMessage + '</small>' );
					}
				}
				return 1;
			}
		}

		if ( minValue > 0 ) {
			var optionsToSelect = 0;
			if ( minValue <= numberOfChecked ) {
				addon.removeClass( 'required-min' );
				addon.find( '.min-error, .min-error span' ).hide();
				// addon.find('input:checkbox').attr( 'required', false );
			} else {
				optionsToSelect = minValue - numberOfChecked;
				if ( submit ) {
					addon.addClass( 'required-min' );
					addon.find( '.min-error' ).show();
					if ( optionsToSelect == 1 ) {
						addon.find( '.min-error-select, .min-error-an, .min-error-option' ).show();
						addon.find( '.min-error-qty, .min-error-options' ).hide();
					} else {
						addon.find( '.min-error-an, .min-error-option' ).hide();
						addon.find( '.min-error-select, .min-error-qty, .min-error-options' ).show();
						addon.find( '.min-error-qty' ).text( optionsToSelect );
					}
					if ( toggle_addon_title ) {
						toggle_addon_title.click();
					}
				}
				// addon.find('input:checkbox').attr( 'required', true );
			}
			return optionsToSelect;
		}

	}
}

// Check if the Select add-ons are required.
function checkRequiredSelect() {

	let value = true;

	jQuery( '.yith-wapo-addon.yith-wapo-addon-type-select select' ).each(
		function () {
			let currentSelect = jQuery( this );
			if ( currentSelect.is( ':required' ) ) {
				let addon       = currentSelect.parents( '.yith-wapo-addon' );
				let selectValue = currentSelect.val();
				if ( 'default' === selectValue && ! addon.hasClass( 'hidden' ) ) {
					value = false;
					if ( ! value ) {
						 let error_el           = addon.find( '.min-error, .min-error-an, .min-error-option' );
						 let toggle_addon       = currentSelect.parents( 'div.yith-wapo-addon.wapo-toggle' );
						 let toggle_addon_title = toggle_addon.find( 'h3.wapo-addon-title.toggle-closed' );
						 addon.addClass( 'required-min' );

						if ( toggle_addon_title ) {
							toggle_addon_title.click();
						}

						addon.css( 'border', '1px solid #f00' );

						error_el.show();
					}
				}
			}
		}
	);

	return value;
}

function checkTextInputLimit(){
	let valid = true;
	jQuery( 'form.cart .yith-wapo-addon.yith-wapo-addon-type-text:not(.hidden) input' ).each( ( index, input ) => {
		let currentInput = jQuery( input ),
		currentValue = currentInput.val(),
		minLength = currentInput.attr( 'minlength' ),
		maxLength = currentInput.attr( 'maxlength' );
		if ( ( minLength !== '' && currentValue.length < minLength ) || ( maxLength !== '' && currentValue.length > maxLength ) ){
			currentInput.addClass( 'length-error' );
			valid = false;
		} else {
			currentInput.removeClass( 'length-error' );
		}
	} );
	
	return valid;
}

// multiplied by value price

jQuery( 'body' ).on(
	'change keyup',
	'.yith-wapo-addon-type-number input',
	function() {
		yith_wapo_check_multiplied_price( jQuery( this ) );
	}
);

function yith_wapo_check_multiplied_price( addon ) {
	let price        = addon.data( 'price' );
	let sale_price   = addon.data( 'price-sale' );
	let defaultPrice = addon.data( 'default-price' );
	let priceType    = addon.data( 'price-type' );
	let priceMethod  = addon.data( 'price-method' );
	let default_attr = 'price';
	let final_price  = 0;
	let addon_value  = addon.val();

	if ( ! defaultPrice > 0 ) {
		if ( sale_price > 0 && ( 'number' !== addon.attr( 'type' ) && 'multiplied' === priceType ) ) {
			price        = sale_price;
			default_attr = 'price-sale';
		}
		defaultPrice = price;
		addon.data( 'default-price', defaultPrice );
	}
	if ( priceMethod == 'value_x_product' ) {
		var productPrice = parseFloat( jQuery( '#yith-wapo-container' ).attr( 'data-product-price' ) );
		final_price      = addon_value * productPrice;
	} else if ( priceType == 'multiplied' ) {
		final_price = addon_value * defaultPrice;
	}

	if ( final_price > 0 || priceMethod == 'decrease' ) {
		addon.data( default_attr, final_price );
	}
}

// Multiply add-on price by length

function yith_wapo_check_multiplied_length( addon ) {

	let price        = addon.data( 'price' );
	let defaultPrice = addon.data( 'default-price' );
	let priceType    = addon.data( 'price-type' );

	if ( ! defaultPrice > 0 ) {
		defaultPrice = price;
		addon.data( 'default-price', defaultPrice );
	}
	if ( 'characters' === priceType ) {
		let remove_spaces = addon.data( 'remove-spaces' );
		let addonLength   = addon.val().length;
		if ( remove_spaces ) {
			addonLength = addon.val().replace( /\s+/g, '' ).length;
		}
		addon.data( 'price', addonLength * defaultPrice );
	}
}


// product qty

jQuery( '.wapo-product-qty' ).keyup(
	function() {
		var productID  = jQuery( this ).data( 'product-id' );
		var productQTY = jQuery( this ).val();
		var productURL = '?add-to-cart=' + productID + '&quantity=' + productQTY;
		jQuery( this ).parent().find( 'a' ).attr( 'href', productURL );
	}
);

// calendar time selection

jQuery( '.single-product' ).on(
	'change',
	'#wapo-datepicker-time select',
	function() {
		var time = jQuery( this ).val();
		jQuery( '#temp-time' ).text( time );
		jQuery( '.ui-state-active' ).click();
		// jQuery('#wapo-datepicker-time select').val( time );
		// var input = jQuery('.yith_wapo_date:focus');
		// var selectedDate = input.val();
		// input.val( selectedDate + ' ' + time );
	}
);

// calendar time save

jQuery( '.single-product' ).on(
	'click',
	'#wapo-datepicker-save button',
	function() {
		jQuery( '.hasDatepicker' ).datepicker( 'hide' );
		// jQuery('#temp-time').text('');
	}
);
