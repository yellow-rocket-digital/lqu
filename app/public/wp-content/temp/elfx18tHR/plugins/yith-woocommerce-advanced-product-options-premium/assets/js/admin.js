
/**
 * Admin JS
 */

( function ( $ ) {

  initAdmin = function () {
    /**
     * Dependencies Handler for ColorPicker fields
     *
     * @type {{init: ywapoDependenciesHandler.init,
     * dom: {
     * colorpickerShow: string
     * },
     * handle: ywapoDependenciesHandler.handle,
     * conditions: {
     * defaultColorpicker: string,
     * colorpickerPlaceholder: string}
     * }}
     */
    var ywapoDependenciesHandler = {
      dom               : {
        colorpickerShow   : '.option-colorpicker-show',
      },
      conditions          : {
        defaultColorpicker      : '.default-colorpicker',
        colorpickerPlaceholder  : '.colorpicker-placeholder',
      },

      init              : function () {
        var self = ywapoDependenciesHandler;

        $( document ).on( 'change', self.dom.colorpickerShow, function( event ) {
          self.handle( $( event.target ).closest('.fields').find( self.conditions.defaultColorpicker ), 'default_color' === $( event.target ).val()  );
          self.handle( $( event.target ).closest('.fields').find( self.conditions.colorpickerPlaceholder ), 'placeholder' === $( event.target ).val() );
        } );

      },
      handle            : function ( target, condition ) {
        var targetHide    = $(target);
        if ( condition ) {
          targetHide.show();
        } else {
          targetHide.hide();
        }
      }

    };

    /**
     * Enable/Disable Add-ons tabs.
     */
    checkAdminTabs      = function() {

      let currentTab = $( this ),
        tabs         = $( '#addon-tabs a' ),
        tab_id = currentTab.attr('id'),
        divs_container =  $( '#addon-container > div' );

      tabs.removeClass('selected');
      currentTab.addClass('selected');
      divs_container.hide();
      $( '#addon-container #tab-' + tab_id ).show();
    },

    /**
     * Avoid browser Save Popup with existing changes.
     */
    avoidBrowserSave      = function() {
      window.onbeforeunload = null;
    },

    /**
     * Avoid browser Save Popup with existing changes.
     */
    blockRulesShowTo      = function() {
        let option         = $( this ),
          optionVal        = option.val(),
          showToUserRoles  = $( '.yith-wapo-block-rule-show-to-user-roles' ),
          showToMembership = $( '.yith-wapo-block-rule-show-to-membership' );

        if ( 'user_roles' === optionVal ) {
          showToUserRoles.fadeIn();
          showToMembership.hide();
        } else if ( 'membership' === optionVal ) {
          showToUserRoles.hide();
          showToMembership.fadeIn();
        } else {
          showToUserRoles.fadeOut();
          showToMembership.fadeOut();
        }
    },
      /**
       * Check Min/Max Rules
       */
    initMinMaxRules = function () {
        let firstRule           = $('#min-max-rules .field.rule:first-child');
        let firstRuleValue      = firstRule.find( 'select' ).val();
        let extraRulesSelectors = $( '#min-max-rules div.rule.min-max-rule:not(:first)' );
        let addRuleElement      = $( 'div.enabled-by-addon-enable-min-max #add-min-max-rule' );
        if ( 'min' !== firstRuleValue && 'max' !== firstRuleValue || extraRulesSelectors.length ) {
          addRuleElement.hide();
        }
    };

    /**
     * Delete Min/Max rule action
     */
    deleteMinMaxrule = function () {
        let removeButton      = $( this );
        let firstRuleSelector = $( '#min-max-rules div.rule.min-max-rule:first-child select' );
        removeButton.parent().remove();
        firstRuleSelector.change();
    },

      /**
       * First Mix/Max rule action, remove all rules, show Add rule ( hide on 'exa' value )
       */
      firstMixMaxRule = function () {
        let selectorEl        = $( this ),
          selectValue         = selectorEl.val(),
          addRuleElement      = selectorEl.parents( 'div.enabled-by-addon-enable-min-max' ).find( '#add-min-max-rule' ),
          extraRulesSelectors = $( '#min-max-rules div.rule.min-max-rule:not(:first)' );

        extraRulesSelectors.remove();
        addRuleElement.show();

        if ( 'exa' === selectValue ) {
          addRuleElement.hide();
        }
    },
      /**
       * Add New Min/Max rule
       */
      addNewMinMaxRule = function () {
        let addButton               = $( this ),
          min_max_rule              = $( '#min-max-rules' ),
          firstRule                 = $('#min-max-rules .field.rule:first-child'),
          firstRuleValue            = firstRule.find( 'select' ).val(),
          addRuleElement            = addButton.parents( 'div.enabled-by-addon-enable-min-max' ).find( '#add-min-max-rule' );

        let clonedOption            = firstRule.clone(),
          clonedOptionSelect        = clonedOption.find( 'select' ),
          clonedOptionSelectOptions = clonedOptionSelect.find( 'option' );

        clonedOption.find( 'span.select2.select2-container' ).remove();
        clonedOptionSelect.select2(
          {
            minimumResultsForSearch: -1
          }
        );

        clonedOption.find('input[type=number]').val('');
        if ( 'min' === firstRuleValue || 'max' === firstRuleValue ) {
          clonedOptionSelectOptions.each( function () {
              let optionValue  = $( this ).val();
              let removeOption = false;
              if ( firstRuleValue === optionValue ) {
                removeOption = true;
              }
              if ( 'exa' === optionValue ) {
                removeOption = true;
              }
              if ( removeOption ) {
                $( this ).remove();
              }
            }
          );
        }
        min_max_rule.append( clonedOption );
        addRuleElement.hide();

        return false;
      },

      /**
       * Add conditional logic
       */
      addConditionalLogic = function () {
        let ruleTemplate = $( '#conditional-rules .field.rule:first-child'),
        clonedOption     = ruleTemplate.clone( false );

        clonedOption.find('input[type=number]').val('');
        clonedOption.insertBefore('#add-conditional-rule');
        clonedOption.find('.select2').remove();
        clonedOption.find('.addon-conditional-rule-addon').select2({
          width: '200px',
        });
        clonedOption.find('.addon-conditional-rule-addon-is').select2({
          width: '150px',
        });

      },

      /**
       * Remove conditional logic
       */
      removeConditionalLogic = function () {
        let removeButton = $( this );
        removeButton.parent().remove();
      },

      /**
       * Update Product Name on hidden input when select a new Product
       */
      updateProductNameOnSelect = function () {
        let selector = $( this ),
          optionLabelSelected = selector.closest( '.field' ).find( '.select2-selection__rendered' ).attr( 'title' ),
          productLabelOption  = selector.closest( '.fields' ).find( '.yith-wapo-product-addon-label' );

        productLabelOption.val( optionLabelSelected );

      },

      /** Close Add-on popup when click outside panel. */
      closeAddonPopup = function ( e ) {

        if ( e.target !== this ) {
          return;
        }
        closeAddonPopupAction();

      },

      /**
       * Close Addon Popup
       */
      closeAddonPopupAction = function ( ) {
        let popup    = $( '#yith-wapo-addon-overlay' ),
          currentURL = window.location.href;
        popup.fadeOut();
        currentURL = currentURL.split('&addon_id');
        window.history.pushState( '', '', currentURL[0] );
      },
      /**
       * Adjust Addons Index for each option
       */
      adjustAddonsIndex = function ( ) {
        const options_array = [ 'addon_enabled', 'default', 'show_image' ];

        $.each( options_array, function( index, value ){
          let inputsSelected = $( 'input[name^="options[' + value + ']" ]' );
          inputsSelected.each( function( index ) {
            $( this ).attr('name', 'options[' + value + '][' + index + ']' );
          });
        } );

      },
      /**
       * Show/Hide Addon price condition ( fixed, percentage, multiplied )
       */
      checkAddonPriceConditions = function( ) {
        let selectedElement = $( this );
        let parentElement   = selectedElement.parents( '.option-cost' );
        let saleElement     = parentElement.find( 'div.option-price-sale' );

        if ( 'multiplied' === selectedElement.val() ) {
          saleElement.fadeOut();
        } else {
          saleElement.fadeIn();
        }
      },

      /**
       * Add a new time slot for Calendar add-on
       **/
      addTimeSlot = function( ) {
        let addDataRuleContainer = $(this).parent();
        let dateRulesContainer = $(this).parent().parent();
        let ruleTemplate = dateRulesContainer.find('.slot:first-child');
        let clonedOption = ruleTemplate.clone();
        let randomID = Math.floor( Math.random() * 100000 );
        clonedOption.find('.delete-slot').show();
        addDataRuleContainer.before( clonedOption );
        return false;
      },
      /**
       * Delete a time slot for Calendar add-on
       **/
      deleteTimeSlot = function( ) {
        $(this).parent().remove();
      },

      changeDateRuleSelector = function() {
        var rule = $( this ).closest( '.rule' );
        rule.find('.field:not(.what)').hide();
        if ( $(this).val() == 'years' ) {
          rule.find('.field.years').fadeIn();
        } else if ( $(this).val() == 'months' ) {
          rule.find('.field.months').fadeIn();
        } else if ( $(this).val() == 'daysweek' ) {
          rule.find('.field.daysweek').fadeIn();
        } else {
          rule.find('.field.days').fadeIn();
        }
      },

      /**
       * Delete date rule
       */
      deleteDateRule = function() {
        $(this).parent().remove();
      },

      /** Add a new rule for Datepickers */
      addNewDateRule = function() {
        let ruleId     = $( this ).parents( '.option' ).data( 'index' ),
          ruleOptionId = $( this ).parents( '.date-rules' ).find( '.date-rules-container .rule' ).length,
          template     = wp.template( 'yith-wapo-date-rule-template' ),
          lastRule     = $( this ).parents( '.date-rules' ).find( '.date-rules-container .rule' ).last();

        lastRule.after( template( {
          addon_id: ruleId,
          option_id: ruleOptionId,
        } ) );

        $( document ).trigger( 'yith_fields_init' );

        return false;
      },

        /** Add-ons tabs change */
    $( document ).on( 'click', '#yith-wapo-addon-overlay #addon-tabs a', checkAdminTabs );

    /** Avoid Browser popup when redirect to another page */
    $( document ).on( 'click', '.yith-wapo a, .yith-wapo button, .yith-wapo input', avoidBrowserSave );

    /**
     * Mix/Max triggers
     */
    $( document ).on( 'click', '#add-min-max-rule a', addNewMinMaxRule );
    $( document ).on( 'click', '#min-max-rules .delete-min-max-rule', deleteMinMaxrule );
    $( document ).on( 'change', '#min-max-rules div.rule.min-max-rule:first-child select', firstMixMaxRule );
    initMinMaxRules();

    /** Block dependencies - Show to **/
    $( document ).on( 'change', '#yith-wapo-block-rule-show-to', blockRulesShowTo );

    /** Conditional logic **/
    $( document ).on( 'click', '#add-conditional-rule a', addConditionalLogic );
    $( document ).on( 'click', '#conditional-rules .delete-rule', removeConditionalLogic );

    /** Populate options tab */
    $( document ).on( 'change', 'select[name="options[product][]"]', updateProductNameOnSelect );

    /** Addon Popup */
    $( document ).on( 'click', '#yith-wapo-addon-overlay', closeAddonPopup );
    $( document ).on( 'click', '#yith-wapo-addon-overlay #close-popup, #yith-wapo-addon-overlay button.cancel', closeAddonPopupAction );

    /** Add-ons index adjust */
    $( document ).on( 'click', '#add-new-option', adjustAddonsIndex );

    /** Colorpicker dependencies ( Show color / Placeholder ) */
    ywapoDependenciesHandler.init();

    /** Change price option ( fixed, percentage, multiplied ) */
    $( document ).on( 'change', '#option-price-type', checkAddonPriceConditions );

    /** Calendar - Time Slots **/
    $( document ).on( 'click', '.add-time-slot a', addTimeSlot );
    $( document ).on( 'click', '.delete-slot', deleteTimeSlot );

    /** Calendar - Date rules */
    $( document ).on( 'change', '.date-rules .select_what', changeDateRuleSelector );
    $( document ).on( 'click', '.add-date-rule a', addNewDateRule );
    $( document ).on( 'click' , '.date-rules .delete-rule', deleteDateRule );



    };

  /** Init Admin JS */
  initAdmin();




	/*
	 *
	 *	enable/disable
	 *	blocks
	 *
	 * * * * * * * * * * * * * * * * * * * */

	$('#sortable-blocks').on( 'change', '.active .yith-plugin-fw-onoff-container input', function() {

		var blockID = $(this).attr('id').replace( 'yith-wapo-active-block-', '' );
		var blockVisibility = 0;
		if ( $(this).is(':checked') ) { blockVisibility = 1; }
		else { blockVisibility = 0; }

		// Ajax method
		var data = {
			'action'		: 'enable_disable_block',
			'block_id'		: blockID,
			'block_vis'		: blockVisibility,
		};
		$.post( ajaxurl, data, function(response) {
			console.log( '[YITH.LOG] - Block visibility updated' );
		});

	});

	/*
	 *
	 *	enable/disable
	 *	addons
	 *
	 * * * * * * * * * * * * * * * * * * * */

	$( '#sortable-addons' ).on( 'change', '.addon-onoff input', function() {

		var addonID         = $( this ).attr( 'id' ).replace( 'yith-wapo-active-addon-', '' );
		var addonVisibility = 0;
		if ( $( this ).is( ':checked' ) ) { addonVisibility = 1; }
		else { addonVisibility = 0; }

		// Ajax method
		var data = {
			'action'		: 'enable_disable_addon',
			'addon_id'		: addonID,
			'addon_vis'		: addonVisibility,
		};
		$.post( ajaxurl, data, function(response) {
			console.log( '[YITH.LOG] - Addon visibility updated' );
		});

	});

	/*
	 *
	 *	sortable admin feature
	 *	blocks + addons
	 *
	 * * * * * * * * * * * * * * * * * * * */

	$( '#sortable-blocks' ).sortable( {
    containment: '#yith_wapo_panel_blocks .yith-plugin-fw-panel-custom-tab-container',
		helper: fixWidthHelper,
		revert: true,
		axis: 'y',
		update: function ( event, ui ) {
      var order = $('#sortable-blocks').sortable('serialize');
      var movedItem = $( ui.item ).data('id'),
        prevItem  = parseFloat( $( ui.item ).prev().data('priority') );
        nextItem  = parseFloat( $( ui.item ).next().data('priority') );

			var data = {
				'action'		: 'sortable_blocks',
				'moved_item'	: movedItem,
				'prev_item'		: prevItem,
				'next_item'		: nextItem,
			};

      $.post( ajaxurl, data, function(response) {
				var res    = response.split('-');
				var itemID = res[0];
				var itemPR = parseFloat( res[1] );
        var blockSelected = $( '#sortable-blocks #block-' + itemID );
        blockSelected.attr( 'data-priority', itemPR );
        blockSelected.find( 'td.priority' ).html( Math.round(itemPR) );
      } );
		}
	} );
	$( '#sortable-addons' ).sortable( {
    containment: '#block-addons-container',
		revert: true,
		axis: 'y',
		update: function ( event, ui ) {
			var movedItem = ui.item.data('id');
			var prevItem  = parseFloat( ui.item.prev().data('priority') );
			var nextItem  = parseFloat( ui.item.next().data('priority') );
			// Ajax method
			var data = {
				'action'		: 'sortable_addons',
				'moved_item'	: movedItem,
				'prev_item'		: prevItem,
				'next_item'		: nextItem,
			};
			$.post( ajaxurl, data, function(response) {
				var res = response.split('-');
				var itemID = res[0];
				var itemPR = parseFloat( res[1] );
				$( '#sortable-addons #addon-' + itemID ).attr( 'data-priority', itemPR );
			} );
		}
	});

  $( '#addon_options' ).sortable({
    helper: fixWidthHelper,
    revert: true,
    axis: 'y',
    delay: 150,
    update: function( event, ui ) {
      adjustAddonsIndex();
    }

  });

	$( 'ul, li, tbody, tr, td' ).disableSelection();
	function fixWidthHelper( e, ui ) {
		ui.children().each(function() { $(this).width( $(this).width() ); });
		return ui;
	}

	/*
	 *
	 *	block rules dependencies
	 *
	 * * * * * * * * * * * * * * * * * * * */

	var showInInput = $('#block-rules #yith-wapo-block-rule-show-in');
	var showInProducts = $('.field-wrap.yith-wapo-block-rule-show-in-products');
	var showInCategories = $('.field-wrap.yith-wapo-block-rule-show-in-categories');
	var showInVal = showInInput.val();
  var excludeProductsInput = $('#block-rules #yith-wapo-block-rule-exclude-products');
	var excludeProductsProducts = $('.field-wrap.yith-wapo-block-rule-exclude-products-products');
	var excludeProductsCategories = $('.field-wrap.yith-wapo-block-rule-exclude-products-categories');

  showInInput.change(function() {
		showInVal = $(this).val();
		if ( 'products' === showInVal ) {
      showInProducts.fadeIn();
			showInCategories.hide();
    } else {
			showInProducts.fadeOut();
			showInCategories.fadeOut();
		}
	});

  excludeProductsInput.change(function() {
		if ( $(this).val() == 'yes' ) {
			excludeProductsProducts.fadeIn();
			if ( showInVal == 'all' ) {
				excludeProductsCategories.fadeIn();
			}
		} else {
			excludeProductsProducts.fadeOut();
			excludeProductsCategories.fadeOut();
		}
	});

	/*
	 *
	 *	options dependencies (enablers)
	 *	only for simple onoff options
	 *	function: check enablers
	 *
	 * * * * * * * * * * * * * * * * * * * */

	$('.yith-wapo').on('change', '.enabler input', function() { yith_wapo_check_enablers( $(this) ); });
	$('.yith-wapo .enabler input').each( function() { yith_wapo_check_enablers( $(this) ); });
	function yith_wapo_check_enablers( enabler ) {
		if ( enabler.is(':checked') ) { $( '.enabled-by-' + enabler.attr('id') ).fadeIn(); }
		else { $( '.enabled-by-' + enabler.attr('id') ).fadeOut(); }
	}
	// HTML Separator
	$('.yith-wapo').on('change', '#option-separator-style', function() {
		if ( $(this).val() == 'empty_space' ) {
			$('.field-wrap.option-separator-color').fadeOut();
		} else {
			$('.field-wrap.option-separator-color').fadeIn();
		}
	});
	// Label Style
	$('.yith-wapo').on('change', '#addon-custom-style', function() {
		if ( ! $(this).is(':checked') && $('#addon-image-equal-height').is(':checked') ) {
			$('.addon-image-equal-height .yith-plugin-fw-onoff').click();
		}
	});

	/*
	 *
	 *	option toggle
	 *
	 * * * * * * * * * * * * * * * * * * * */

	$('#tab-options-list').on( 'click', '.option .title', function( e ) {
    let itemClicked = jQuery( e.target );
    if ( itemClicked.hasClass( 'selected-by-default-chbx' ) ) {
        return;
    }
    var fieldsContainer = $(this).parent().find('.fields');
    fieldsContainer.toggle();
    if ( fieldsContainer.is(':visible') ) {
        $(this).parent().removeClass('close').addClass('open');
    } else {
        $(this).parent().removeClass('open').addClass('close');
    }

  });

	/*
	 *
	 *	new option
	 *
	 * * * * * * * * * * * * * * * * * * * */

	$('#add-new-option').click( function() {
		var newOptionID = $(this).parent().find('.option').length;
		$('.yith-plugin-fw-upload-container button').off();
		var template = wp.template( 'new-option-' + newOptionID );
		$('#add-new-option').before( template() );
		$('body').trigger( 'yith_fields_init' );
		// $('body').trigger( 'yith-framework-enhanced-select-init' );
	});

	$('#tab-options-list').on( 'change', '.option-price-method', function() {
		var parent = $(this).parent().parent().parent().parent();
		if ( $(this).val() != 'free' && $(this).val() != 'product' && $(this).val() != 'value_x_product' ) {
			parent.find('.option-cost').fadeIn();
			if ( $(this).val() == 'increase' ) {
				parent.find('.option-cost .option-price-method-increase').fadeIn();
				parent.find('.option-cost .option-price-method-decrease').fadeOut();
			} else {
				parent.find('.option-cost .option-price-method-increase').fadeOut();
				parent.find('.option-cost .option-price-method-decrease').fadeIn();
			}
		} else {
			parent.find('.option-cost').fadeOut();
		}
	});

	$('#tab-options-list').on( 'change', '.option-selectable-dates', function() {
		var parent = $(this).parent().parent().parent().parent();
		if ( $(this).val() == 'days' ) {
			parent.find('.option-selectable-days-ranges').fadeIn();
			parent.find('.option-selectable-date-ranges').hide();
		} else if ( $(this).val() == 'date' ) {
			parent.find('.option-selectable-days-ranges').hide();
			parent.find('.option-selectable-date-ranges').fadeIn();
		} else {
			parent.find('.option-selectable-days-ranges').hide();
			parent.find('.option-selectable-date-ranges').hide();
		}
	});

	/*
	 *
	 *	remove option
	 *
	 * * * * * * * * * * * * * * * * * * * */

	$( document ).on( 'click', '#addon-container .yith-plugin-fw__action-button--delete-action', function() {
		$( this ).parents( '.option' ).remove();
    adjustAddonsIndex();
    return false;
	});

	/*
	 *
	 *	color swatch
	 *
	 * * * * * * * * * * * * * * * * * * * */

	$('#tab-options-list').on( 'change', '.color-show-as select', function() {
		var parent = $( this ).parent().parent().parent().parent().parent();
		if ( $( this ).val() == 'double' ) {
			parent.find('.color').fadeIn();
			parent.find('.color_b').fadeIn();
			parent.find('.color_image').hide();
		} else if ( $( this ).val() == 'image' ) {
			parent.find('.color').hide();
			parent.find('.color_b').hide();
			parent.find('.color_image').fadeIn();
		} else {
			parent.find('.color').fadeIn();
			parent.find('.color_b').hide();
			parent.find('.color_image').hide();
		}
	});
	$('#tab-options-list').on( 'click', '.option .title', function() {
		$(this).parent().find('.color-show-as select').change();
	});
	$('#tab-options-list').find('.color-show-as select').change();

	/*
	 *
	 *	calendar (date picker)
	 *
	 * * * * * * * * * * * * * * * * * * * */

	$('.yith-wapo').on('change', '.option-date-default', function() {
		var parent = $(this).parent().parent().parent().parent();
		if ( $(this).val() == 'specific' ) {
			parent.find('.option-date-default-day').fadeIn();
			parent.find('.option-date-default-interval').hide();
		} else if ( $(this).val() == 'interval') {
			parent.find('.option-date-default-day').hide();
			parent.find('.option-date-default-interval').fadeIn();
		} else {
			parent.find('.option-date-default-day').fadeOut();
			parent.find('.option-date-default-interval').fadeOut();
		}
	});

	/*
	 *
	 *	Conditional logic
	 *
	 * * * * * * * * * * * * * * * * * * * */
	$( document ).on( 'select2:open', function ( e ) {
		$( '.select2-results' ).closest( '.select2-container' ).addClass( 'yith-addons-select2-container' );
	} );
} )( jQuery );
