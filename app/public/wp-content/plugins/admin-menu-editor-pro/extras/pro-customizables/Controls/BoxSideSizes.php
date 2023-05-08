<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\Controls\AbstractNumericControl;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\ChoiceControlOption;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\PopupSlider;
use YahnisElsts\AdminMenuEditor\Customizable\HtmlHelper;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\CssBoxSides;

class BoxSideSizes extends AbstractNumericControl {
	protected $type = 'boxSides';
	protected $koComponentName = 'ame-box-sides';

	const INPUT_ID_PREFIX = '_ame-box-sides-input-';
	const UNIT_SELECTED_PREFIX = '_ame-box-sides-unit-';
	protected static $generatedIdCounter = 0;

	public function renderContent(Renderer $renderer) {
		if ( !($this->mainSetting instanceof CssBoxSides) ) {
			throw new \InvalidArgumentException('The main setting for this control must be a CssBoxSides.');
		}

		echo HtmlHelper::tag('fieldset', [
			'class'              => array_merge(
				[
					'ame-container-with-popup-slider',
					'ame-box-sides-control',
				],
				$this->classes
			),
			'style'              => $this->styles,
			'data-ac-setting-id' => $this->mainSetting->getId(),
			'data-bind'          => $this->makeKoDataBind($this->getKoEnableBinding()),
		]);

		$unitElementId = self::UNIT_SELECTED_PREFIX . (self::$generatedIdCounter++);

		$order = ['top' => 'Top', 'bottom' => 'Bottom', 'left' => 'Left', 'right' => 'Right',];
		foreach ($order as $side => $label) {
			$setting = $this->mainSetting->getChild($side);
			if ( !$setting ) {
				continue;
			}

			$inputId = self::INPUT_ID_PREFIX . (self::$generatedIdCounter++);

			echo HtmlHelper::tag('div', [
				'class' => ['ame-single-box-side', 'ame-box-side-' . $side],
			]);

			echo HtmlHelper::tag(
				'input',
				array_merge(
					$this->getBasicInputAttributes(),
					[
						'name'     => $this->getFieldName($side),
						'value'    => $setting->getValue(),
						'id'       => $inputId,
						'class'    => [
							'ame-small-number-input',
							'ame-input-with-popup-slider',
							'ame-box-sides-input',
							'ame-box-sides-input-' . $side,
						],
						'disabled' => !$this->isEnabled(),

						'data-unit-element-id' => $unitElementId,
						'data-ame-box-side'    => $side,
						'data-bind'            => $this->makeKoDataBind(array_merge([
							'value'                     =>
								$this->getKoObservableExpression($setting->getValue(), $setting),
							'ameObservableChangeEvents' => 'true',
						], $this->getKoEnableBinding())),
					]
				)
			);

			echo HtmlHelper::tag(
				'label',
				[
					'for'   => $inputId,
					'class' => 'ame-box-side-label',
				],
				esc_html($label)
			);
			echo '</div>';
		}

		//Unit selector.
		$unitSetting = $this->mainSetting->getUnitSetting();
		$this->renderUnitDropdown($unitSetting, [
			'name'               => $this->getFieldName('unit'),
			'id'                 => $unitElementId,
			'class'              => 'ame-box-sides-unit-selector',
			'data-slider-ranges' => wp_json_encode($this->getSliderRanges()),
			'disabled'           => !$this->isEnabled(),
		]);

		//"Link" button.
		//Enable it by default if all sides are the same. Do not enable if the value is an empty
		//string or null: overwriting all defaults with equal values leads to undesirable results
		//for elements that have different defaults for different sides, like admin menu items.
		$linkButtonEnabled = true;
		$firstSetting = $this->mainSetting->getChild(\ameUtils::getFirstKey($order));
		if ( $firstSetting ) {
			$firstValue = $firstSetting->getValue();
			if ( ($firstValue === '') || ($firstValue === null) ) {
				$linkButtonEnabled = false;
			} else {
				foreach ($order as $side => $label) {
					$setting = $this->mainSetting->getChild($side);
					if ( $setting && ($setting->getValue() !== $firstValue) ) {
						$linkButtonEnabled = false;
						break;
					}
				}
			}
		}

		$buttonClasses = ['button', 'button-secondary', 'ame-box-sides-link-button', 'hide-if-no-js'];
		if ( $linkButtonEnabled ) {
			$buttonClasses[] = 'active';
		}
		echo HtmlHelper::tag(
			'button',
			[
				'class'     => $buttonClasses,
				'title'     => 'Link values',
				'disabled'  => !$this->isEnabled(),
				'data-bind' => $this->makeKoDataBind($this->getKoEnableBinding()),
			],
			'<span class="dashicons dashicons-admin-links"></span>'
		);

		$slider = new PopupSlider([
			'positionParentSelector' => '.ame-single-box-side',
			'verticalOffset'         => -2,
		]);
		$slider->render();
		echo '</fieldset>';

		static::enqueueDependencies();
	}

	protected function getKoComponentParams() {
		$params = parent::getKoComponentParams();

		if ( ($this->mainSetting instanceof CssBoxSides) ) {
			$unitSetting = $this->mainSetting->getUnitSetting();
			$params['unitDropdownOptions'] = ChoiceControlOption::generateKoOptions(
				$unitSetting->generateChoiceOptions()
			);
		}

		return $params;
	}

	public function enqueueKoComponentDependencies() {
		parent::enqueueKoComponentDependencies();

		//Enqueue the Popup Slider. Unlike regular rendering, this doesn't happen
		//automatically with KO components.
		PopupSlider::enqueueDependencies();
	}
}