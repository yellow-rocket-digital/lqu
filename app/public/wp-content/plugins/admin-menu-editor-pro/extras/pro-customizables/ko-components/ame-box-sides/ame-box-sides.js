'use strict';
import { createControlComponentConfig, KoStandaloneControl } from '../control-base.js';
import { LazyPopupSliderAdapter } from '../lazy-popup-slider-adapter.js';
const allSideKeys = ['top', 'bottom', 'left', 'right'];
const SidesInOrder = [
    ['top', 'Top'],
    ['bottom', 'Bottom'],
    ['left', 'Left'],
    ['right', 'Right'],
];
let nextId = 0;
class AmeBoxSides extends KoStandaloneControl {
    constructor(params, $element) {
        super(params, $element);
        // noinspection JSUnusedGlobalSymbols -- Used in the template.
        this.sidesInOrder = SidesInOrder;
        this.inputIdPrefix = '_ame-box-sides-c-input-' + (nextId++);
        this.unitElementId = '_ame-box-sides-c-unit-' + (nextId++);
        //Make observable proxies for the individual side settings.
        const temp = {};
        for (const [sideKey, sideName] of SidesInOrder) {
            const setting = this.settings['value.' + sideKey];
            if (!setting || (typeof setting !== 'object')) {
                throw new Error(`Missing setting for the "${sideName}" side.`);
            }
            temp[sideKey] = ko.computed({
                read: () => {
                    return setting.value();
                },
                write: (newValue) => {
                    if (newValue === '') {
                        newValue = null;
                    }
                    setting.value(newValue);
                },
                deferEvaluation: true,
            });
        }
        this.sides = temp;
        //Similarly, make an observable for the unit setting.
        const unitSetting = this.settings['value.unit'];
        if (!unitSetting || (typeof unitSetting !== 'object')) {
            throw new Error('Missing setting for the unit.');
        }
        this.unitSetting = unitSetting;
        const defaultDropdownOptions = {
            options: [],
            optionsText: 'text',
            optionsValue: 'value'
        };
        if (params.unitDropdownOptions && (typeof params.unitDropdownOptions === 'object')) {
            this.unitDropdownOptions = {
                options: params.unitDropdownOptions['options'] || defaultDropdownOptions.options,
                optionsText: params.unitDropdownOptions['optionsText'] || defaultDropdownOptions.optionsText,
                optionsValue: params.unitDropdownOptions['optionsValue'] || defaultDropdownOptions.optionsValue,
            };
        }
        else {
            this.unitDropdownOptions = defaultDropdownOptions;
        }
        this.isLinkActive = ko.observable(false);
        //Enable the link button by default if all sides are equal. Exception: null values.
        //Sides can have different defaults, so null doesn't necessarily mean that the sides
        //are actually equal.
        const firstValue = this.sides.top();
        if ((firstValue !== null) && (firstValue !== '')) {
            let areAllSidesEqual = true;
            for (const sideKey of allSideKeys) {
                if (this.sides[sideKey]() !== firstValue) {
                    areAllSidesEqual = false;
                    break;
                }
            }
            this.isLinkActive(areAllSidesEqual);
        }
        //When "link" mode is enabled, keep all sides in sync.
        let isUpdatingAllSides = false; //Prevent infinite loops.
        const updateAllSides = (newValue) => {
            if (!isUpdatingAllSides && this.isLinkActive()) {
                isUpdatingAllSides = true;
                newValue = this.normalizeValue(newValue);
                for (const sideKey of allSideKeys) {
                    this.sides[sideKey](newValue);
                }
                isUpdatingAllSides = false;
            }
        };
        for (const sideKey of allSideKeys) {
            this.sides[sideKey].subscribe(updateAllSides);
        }
        let sliderOptions = {
            'positionParentSelector': '.ame-single-box-side',
            'verticalOffset': -2,
        };
        if (typeof params.popupSliderWithin === 'string') {
            sliderOptions.positionWithinClosest = params.popupSliderWithin;
        }
        this.sliderAdapter = new LazyPopupSliderAdapter(params.sliderRanges ? params.sliderRanges : null, '.ame-box-sides-control', 'input.ame-box-sides-input', sliderOptions);
    }
    get classes() {
        return ['ame-box-sides-control', ...super.classes];
    }
    //noinspection JSUnusedGlobalSymbols -- Used in the template.
    /**
     * Get an observable for a specific side.
     *
     * Unfortunately, Knockout doesn't seem to support nested indexed accessors
     * like "sides[$data[0]]", so we have to use a method instead.
     */
    getSideObservable(side) {
        return this.sides[side];
    }
    getInputIdFor(side) {
        return this.inputIdPrefix + '-' + side;
    }
    // noinspection JSUnusedGlobalSymbols
    getSideInputAttributes(side) {
        return {
            id: this.getInputIdFor(side),
            'data-unit-element-id': this.unitElementId,
            'data-ame-box-side': side,
        };
    }
    // noinspection JSUnusedGlobalSymbols -- Actually used in the template.
    toggleLink() {
        this.isLinkActive(!this.isLinkActive());
        //When enabling "link" mode, fill all inputs with the same value.
        //Use the first non-empty value.
        if (this.isLinkActive()) {
            let firstValue = null;
            for (const sideKey of allSideKeys) {
                const value = this.sides[sideKey]();
                if ((value !== null) && (value !== '')) {
                    firstValue = value;
                    break;
                }
            }
            if (firstValue !== null) {
                firstValue = this.normalizeValue(firstValue);
                for (const sideKey of allSideKeys) {
                    this.sides[sideKey](firstValue);
                }
            }
        }
    }
    normalizeValue(value) {
        if (value === null) {
            return null;
        }
        //Convert strings to numbers, and invalid strings to null.
        if (typeof value === 'string') {
            value = parseFloat(value);
            if (isNaN(value)) {
                return null;
            }
        }
        return value;
    }
}
export default createControlComponentConfig(AmeBoxSides, `
	<fieldset data-bind="class: classString, enable: isEnabled, style: styles" data-ame-is-component="1">
		<!-- ko foreach: sidesInOrder -->
			<div data-bind="class: ('ame-single-box-side ame-box-side-' + $data[0])">
				<input type="text" inputmode="numeric" maxlength="20" pattern="\\s*-?[0-9]+(?:[.,]\\d*)?\\s*" 
					data-bind="value: $parent.getSideObservable($data[0]), valueUpdate: 'input',
					attr: $component.getSideInputAttributes($data[0]),
					class: ('ame-small-number-input ame-box-sides-input ame-box-sides-input-' + $data[0]),
					enable: $component.isEnabled,
					click: $component.sliderAdapter.handleKoClickEvent" />				
				<label data-bind="text: $data[1], attr: {'for': $component.getInputIdFor($data[0])}" 
					class="ame-box-side-label"></label>
			</div>
		<!-- /ko -->
		<ame-unit-dropdown params="optionData: unitDropdownOptions, settings: {value: unitSetting},
			classes: ['ame-box-sides-unit-selector'],
			id: unitElementId"></ame-unit-dropdown>
		<button class="button button-secondary ame-box-sides-link-button hide-if-no-js"
			title="Link values" data-bind="enable: isEnabled, css: {'active': isLinkActive}, 
				click: $component.toggleLink.bind($component)"><span class="dashicons dashicons-admin-links"></span></button>
	</fieldset>
`);
//# sourceMappingURL=ame-box-sides.js.map