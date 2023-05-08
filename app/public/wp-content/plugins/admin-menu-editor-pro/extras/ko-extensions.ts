/// <reference path="../js/common.d.ts" />
/// <reference path="../js/knockout.d.ts" />
/// <reference path="../js/jquery.d.ts" />
/// <reference path="../js/lodash-3.10.d.ts" />

declare var wsAmeLodash: _.LoDashStatic;

interface AmeKnockoutDialog {
	isOpen: KnockoutObservable<boolean>;

	options?: Record<string, any>;
	autoCancelButton?: boolean;
	jQueryWidget: JQuery;
	title: KnockoutObservable<string>;

	onOpen?(event, ui);

	onClose?(event, ui);
}

/*
 * jQuery Dialog binding for Knockout.
 *
 * The main parameter of the binding is an instance of AmeKnockoutDialog. In addition to the standard
 * options provided by jQuery UI, the binding supports two additional properties:
 *
 *  isOpen - Required. A boolean observable that controls whether the dialog is open or closed.
 *  autoCancelButton - Set to true to add a WordPress-style "Cancel" button that automatically closes the dialog.
 *
 * Usage example:
 * <div id="my-dialog" data-bind="ameDialog: {isOpen: anObservable, autoCancelButton: true, options: {minWidth: 400}}">...</div>
 */
ko.bindingHandlers.ameDialog = {
	init: function (element, valueAccessor) {
		const dialog = ko.utils.unwrapObservable(valueAccessor()) as AmeKnockoutDialog;
		const _ = wsAmeLodash;

		let options = dialog.options ? dialog.options : {};
		if (!dialog.hasOwnProperty('isOpen')) {
			dialog.isOpen = ko.observable(false);
		}

		options = _.defaults(options, {
			autoCancelButton: _.get(dialog, 'autoCancelButton', true),
			autoOpen: dialog.isOpen(),
			modal: true,
			closeText: ' '
		});

		//Update isOpen when the dialog is opened or closed.
		options.open = function (event, ui) {
			dialog.isOpen(true);
			if (dialog.onOpen) {
				dialog.onOpen(event, ui);
			}
		};
		options.close = function (event, ui) {
			dialog.isOpen(false);
			if (dialog.onClose) {
				dialog.onClose(event, ui);
			}
		};

		let buttons = (typeof options['buttons'] !== 'undefined') ? ko.utils.unwrapObservable(options.buttons) : [];
		if (options.autoCancelButton) {
			//In WordPress, the "Cancel" option is usually on the left side of the form/dialog/pop-up.
			buttons.unshift({
				text: 'Cancel',
				'class': 'button button-secondary ame-dialog-cancel-button',
				click: function () {
					jQuery(this).closest('.ui-dialog-content').dialog('close');
				}
			});
		}
		options.buttons = buttons;

		if (!dialog.hasOwnProperty('title') || (dialog.title === null)) {
			dialog.title = ko.observable(_.get(options, 'title', null));
		} else if (dialog.title()) {
			options.title = dialog.title();
		}

		//Do in a setTimeout so that applyBindings doesn't bind twice from element being copied and moved to bottom.
		window.setTimeout(function () {
			jQuery(element).dialog(options);

			dialog.jQueryWidget = jQuery(element).dialog('widget');
			dialog.title(jQuery(element).dialog('option', 'title'));

			dialog.title.subscribe(function (newTitle) {
				jQuery(element).dialog('option', 'title', newTitle);
			});

			if (ko.utils.unwrapObservable(dialog.isOpen)) {
				jQuery(element).dialog('open');
			}
		}, 0);


		ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
			jQuery(element).dialog('destroy');
		});
	},

	update: function (element, valueAccessor) {
		const dialog = ko.utils.unwrapObservable(valueAccessor()) as AmeKnockoutDialog;
		const $element = jQuery(element);
		const shouldBeOpen = ko.utils.unwrapObservable(dialog.isOpen);

		//Do nothing if the dialog hasn't been initialized yet.
		const $widget = $element.dialog('instance');
		if (!$widget) {
			return;
		}

		if (shouldBeOpen !== $element.dialog('isOpen')) {
			$element.dialog(shouldBeOpen ? 'open' : 'close');
		}
	}
};

ko.bindingHandlers.ameOpenDialog = {
	init: function (element, valueAccessor) {
		const clickHandler = function (event) {
			const dialogSelector = ko.utils.unwrapObservable(valueAccessor());

			//Do nothing if the dialog hasn't been initialized yet.
			const $widget = jQuery(dialogSelector);
			if (!$widget.dialog('instance')) {
				return;
			}

			$widget.dialog('open');
			event.stopPropagation();
		};
		jQuery(element).on('click', clickHandler);

		ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
			jQuery(element).off('click', clickHandler);
		});
	}
};

/*
 * The ameEnableDialogButton binding enables the specified jQuery UI button only when the "enabled" parameter is true.
 *
 * It's tricky to bind directly to dialog buttons because they're created dynamically and jQuery UI places them
 * outside dialog content. This utility binding takes a jQuery selector, letting you bind to a button indirectly.
 * You can apply it to any element inside a dialog, or the dialog itself.
 *
 * Usage:
 * <div data-bind="ameDialogButtonEnabled: { selector: '.my-button', enabled: anObservable }">...</div>
 * <div data-bind="ameDialogButtonEnabled: justAnObservable">...</div>
 *
 * If you omit the selector, the binding will enable/disable the first button that has the "button-primary" CSS class.
 */
ko.bindingHandlers.ameEnableDialogButton = {
	init: function (element, valueAccessor, allBindings, viewModel, bindingContext) {
		//This binding could be applied before the dialog is initialised. In this case, the button won't exist yet.
		//Wait for the dialog to be created and then update the button.
		const dialogNode = jQuery(element).closest('.ui-dialog');
		if (dialogNode.length < 1) {
			const body = jQuery(element).closest('body');

			function setInitialButtonState() {
				//Is this our dialog?
				let dialogNode = jQuery(element).closest('.ui-dialog');
				if (dialogNode.length < 1) {
					return; //Nope.
				}

				//Yes. Remove the event handler and update the binding.
				body.off('dialogcreate', setInitialButtonState);
				ko.bindingHandlers.ameEnableDialogButton.update(element, valueAccessor, allBindings, viewModel, bindingContext);
			}

			body.on('dialogcreate', setInitialButtonState);
			//If our dialog never gets created, we still want to clean up the event handler eventually.
			ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
				body.off('dialogcreate', setInitialButtonState);
			});
		}
	},

	update: function (element, valueAccessor) {
		const _ = wsAmeLodash;
		let options = ko.unwrap(valueAccessor());
		if (!_.isPlainObject(options)) {
			options = {enabled: options};
		}

		options = _.defaults(
			options,
			{
				selector: '.button-primary:first',
				enabled: false
			}
		);

		jQuery(element)
			.closest('.ui-dialog')
			.find('.ui-dialog-buttonset')
			.find(options.selector)
			.button('option', 'disabled', !ko.utils.unwrapObservable(options.enabled));
	}
};

ko.bindingHandlers.ameColorPicker = {
	init: function (element, valueAccessor) {
		let valueUnwrapped = ko.unwrap(valueAccessor());

		const input = jQuery(element);
		input.val(valueUnwrapped);

		const guard = '__ameColorPickerIgnoreChange';
		let ignoredObservableValue: string|null = guard;

		function maybeUpdateObservable(newValue: string) {
			let observable = valueAccessor();
			if (!ko.isWriteableObservable(observable)) {
				return; //Can't update this thing.
			}

			//Don't update the observable if the value hasn't changed.
			//This helps prevent infinite loops.
			if (newValue === observable.peek()) {
				return;
			}

			//Avoid an unnecessary color picker update when changing the observable value.
			//This also helps prevent loops, and prevents a subtle bug where quickly
			//changing the color (e.g. by dragging the saturation slider) would cause
			//the picker to "drift" away from the actual color.
			ignoredObservableValue = newValue;
			observable(newValue);
			ignoredObservableValue = guard;
		}

		input.wpColorPicker({
			change: function (event, ui) {
				maybeUpdateObservable(ui.color.toString());
			},
			clear: function () {
				maybeUpdateObservable('');
			}
		});

		//Update the picker when the observable changes. We're using a computed observable
		//instead of the "update" callback because this lets us store state in the closure.
		ko.computed(function () {
			let newValue = ko.unwrap(valueAccessor());
			if (typeof newValue !== 'string') {
				newValue = '';
			}
			if (newValue === ignoredObservableValue) {
				return;
			}

			if (newValue === '') {
				//Programmatically click the "Clear" button. It's not elegant, but I haven't found
				//a way to do this using the Iris API.
				input.closest('.wp-picker-input-wrap').find('.wp-picker-clear').trigger('click');
			} else {
				input.iris('color', newValue);
			}
		}, null, {disposeWhenNodeIsRemoved: element});
	}
};

/**
 * This binding generates a custom JS event when the value of an observable changes.
 * It also listens for another custom event and updates the observable to the specified value.
 *
 * Usage:
 * <input type="text" data-bind="value: someObservable, ameObservableChangeEvents: someObservable" />
 *
 * Alternatively, you can set the parameter to "true". This binding will then look for
 * other bindings that are commonly used to set the value of an element (e.g. "value"),
 * and use the observable value of the first one it finds.
 *
 * Finally, you can pass an object with the following properties:
 * - observable: The observable to watch for changes, or "true" (see above).
 * - sendInitEvent: If true, the binding will send an event when it's initialized.
 */
ko.bindingHandlers.ameObservableChangeEvents = {
	init: function (element, valueAccessor, allBindings) {
		const defaults = {
			observable: null,
			sendInitEvent: false
		};

		let options = valueAccessor();

		if (ko.isObservable(options)) {
			//Just the observable.
			options = Object.assign({}, defaults, {observable: options});
		} else if (options === true) {
			//"true" means we'll try to find the observable automatically (see below).
			options = Object.assign({}, defaults, {observable: true});
		} else if (typeof options === 'object') {
			//Custom options.
			options = Object.assign({}, defaults, options);
		} else {
			throw new Error('Invalid options for the ameObservableChangeEvents binding.');
		}

		let targetObservable = options.observable;
		if (targetObservable === true) {
			let alternativeFound = false;
			const possibleValueBindings = ['value', 'checked', 'selectedOptions'];
			for (let i = 0; i < possibleValueBindings.length; i++) {
				const bindingValue = allBindings.get(possibleValueBindings[i]);
				if (ko.isWriteableObservable(bindingValue)) {
					targetObservable = bindingValue;
					alternativeFound = true;
					break;
				}
			}
			if (!alternativeFound) {
				throw new Error(
					'ameObservableChangeEvents did not find a suitable observable to watch. ' +
					'Supported bindings: ' + possibleValueBindings.join(', ')
				);
			}
		} else if (!ko.isWriteableObservable(targetObservable)) {
			throw new Error('The ameObservableChangeEvents binding accepts only an observable or the boolean "true".');
		}

		const inEvent = 'adminMenuEditor:controlValueChanged';
		const outEvent = 'adminMenuEditor:observableValueChanged';
		const initEvent = 'adminMenuEditor:observableBindingInit';

		const $element = jQuery(element);
		const uniqueMarker = {};
		let ignoredValue = uniqueMarker;

		const subscription = targetObservable.subscribe(function (newValue) {
			//Don't trigger the "out" event if the value was changed as a result
			//of the "in" event.
			if (newValue === ignoredValue) {
				return;
			}
			//console.log('Observable changed: ', newValue);
			$element.trigger(outEvent, [newValue]);
		});

		const incomingChangeHandler = function (event, newValue) {
			//Ignore events from child elements. For example, in a BoxSideSize control,
			//popup sliders associated with individual number inputs trigger their own
			//"controlValueChanged" events.
			if (event.target !== element) {
				return;
			}

			if ((typeof newValue !== 'undefined') && (newValue !== targetObservable.peek())) {
				//console.log('Control changed: ', newValue);
				ignoredValue = newValue;
				targetObservable(newValue);
				ignoredValue = uniqueMarker;
			}
		};

		$element.on(inEvent, incomingChangeHandler);

		ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
			// @ts-ignore - My jQuery typings are out of date.
			$element.off(inEvent, incomingChangeHandler);
			subscription.dispose();
		});

		//Optionally, send an initial event to synchronize the control with the observable.
		if (options.sendInitEvent) {
			$element.trigger(initEvent, [targetObservable.peek()]);
		}
	}
}

//A one-way binding for indeterminate checkbox states.
ko.bindingHandlers.indeterminate = {
	update: function (element, valueAccessor) {
		element.indeterminate = !!(ko.unwrap(valueAccessor()));
	}
};

//A "readonly" property binding for input and textarea elements.
ko.bindingHandlers.readonly = {
	update: function (element, valueAccessor) {
		const value = !!(ko.unwrap(valueAccessor()));
		if (value !== element.readOnly) {
			element.readOnly = value;
		}
	}
};

{
	interface ToggleCheckboxOptions {
		checked: KnockoutObservable<any>;
		onValue: any,
		offValue: any
	}

	function parseToggleCheckboxOptions(options: object): ToggleCheckboxOptions {
		let parsed: ToggleCheckboxOptions = wsAmeLodash.defaults(
			ko.unwrap(options),
			{
				onValue: true,
				offValue: false,
			}
		);
		parsed.onValue = ko.unwrap(parsed.onValue);
		parsed.offValue = ko.unwrap(parsed.offValue);
		return parsed;
	}

	ko.bindingHandlers.ameToggleCheckbox = {
		init: function (element, valueAccessor) {
			const $element = jQuery(element);

			const changeHandler = function () {
				const options = parseToggleCheckboxOptions(valueAccessor());
				if (ko.isWriteableObservable(options.checked)) {
					options.checked(
						$element.prop('checked') ? options.onValue : options.offValue
					);
				}
			}

			$element.on('change', changeHandler);
			ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
				$element.off('change', changeHandler);
			});
		},

		update: function (element, valueAccessor) {
			const options = parseToggleCheckboxOptions(valueAccessor());
			const checked = (ko.unwrap(options.checked) === options.onValue);
			jQuery(element).prop('checked', checked);
		}
	}
}