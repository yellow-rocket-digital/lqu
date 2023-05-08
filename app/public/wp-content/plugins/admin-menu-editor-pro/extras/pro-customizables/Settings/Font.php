<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable\Settings;

use YahnisElsts\AdminMenuEditor\Customizable\Builders;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\ChoiceControlOption;
use YahnisElsts\AdminMenuEditor\ProCustomizable\CssPropertyGenerator;
use YahnisElsts\AdminMenuEditor\Customizable\Settings;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\StringEnumSetting;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;

class Font extends CssSettingCollection implements CssPropertyGenerator {
	public function __construct($id, StorageInterface $store = null, $params = array()) {
		parent::__construct($id, $store, $params);

		$this->createChild(
			'family',
			Settings\StringSetting::class,
			array('default' => null)
		);

		/** @var StringEnumSetting $fontSizeUnit */
		$fontSizeUnit = $this->createChild(
			'sizeUnit',
			StringEnumSetting::class,
			array('px', 'em', 'rem', 'vw'),
			array('default' => 'px')
		);
		//Override the default label generation algorithm and just use the unit name.
		$fontSizeUnit->describeChoice('px', 'px');
		$fontSizeUnit->describeChoice('em', 'em');
		$fontSizeUnit->describeChoice('rem', 'rem');
		$fontSizeUnit->describeChoice('vw', 'vw');

		$this->createChild(
			'size',
			CssLengthSetting::class,
			'font-size',
			array(
				'minValue'    => 0,
				'maxValue'    => 200,
				'default'     => null,
				'unitSetting' => $fontSizeUnit,
				'label'       => 'Font size',
			)
		);

		$this->createChild(
			'weight',
			CssEnumSetting::class,
			'font-weight',
			[
				null,
				'normal',
				'bold',
				'bolder',
				'lighter',
				'100',
				'200',
				'300',
				'400',
				'500',
				'600',
				'700',
				'800',
				'900',
			],
			['default' => null, 'label' => 'Font weight']
		);

		/** @var StringEnumSetting $lineHeightUnit */
		$lineHeightUnit = $this->createChild(
			'lineHeightUnit',
			StringEnumSetting::class,
			['', 'px', 'em'],
			['default' => '']
		);
		$lineHeightUnit->describeChoice('', '—'); //Unit-less.
		$lineHeightUnit->describeChoice('px', 'px');
		$lineHeightUnit->describeChoice('em', 'em');

		$this->createChild(
			'line-height',
			CssLengthSetting::class,
			'line-height',
			[
				'minValue'    => 0,
				'maxValue'    => 200,
				'default'     => null,
				'unitSetting' => $lineHeightUnit,
				'defaultUnit' => '',
				'label'       => 'Line height',
			]
		);

		$this->createChild(
			'style',
			CssEnumSetting::class,
			'font-style',
			array(null, 'normal', 'italic', 'oblique'),
			array('default' => null)
		);

		$this->createChild(
			'variant',
			CssEnumSetting::class,
			'font-variant',
			array(null, 'normal', 'small-caps'),
			array('default' => null)
		);

		$this->createChild(
			'text-transform',
			CssEnumSetting::class,
			'text-transform',
			array(null, 'none', 'uppercase', 'lowercase', 'capitalize', 'full-width'),
			array('default' => null)
		);

		$this->createChild(
			'text-decoration',
			CssEnumSetting::class,
			'text-decoration',
			array(null, 'none', 'underline', 'overline', 'line-through'),
			array('default' => null)
		);
	}

	public function createControls(Builders\ElementBuilderFactory $b) {
		/** @var StringEnumSetting $sizeUnit */
		$sizeUnit = $this->settings['sizeUnit'];

		return [
			$b->number($this->settings['size'])
				->unitSetting($sizeUnit)
				->inputClasses('ame-font-size-input')
				->params([
					'rangeByUnit' => [
						'px'  => ['min' => 1, 'max' => 72, 'step' => 1],
						'em'  => ['min' => 0.2, 'max' => 10, 'step' => 0.1],
						'rem' => ['min' => 0.2, 'max' => 10, 'step' => 0.1],
						'vw'  => ['min' => 0.1, 'max' => 10, 'step' => 0.1],
					],
				])
				->asGroup(),
			$b->select($this->settings['weight'])
				->params([
					'choices' => [
						//The default value is NULL = no change.
						new ChoiceControlOption(null, 'Default'),
						'100' => 'Thin',
						'200' => 'Extra Light',
						'300' => 'Light',
						'400' => 'Normal',
						'500' => 'Medium',
						'600' => 'Semi Bold',
						'700' => 'Bold',
						'800' => 'Extra Bold',
						'900' => 'Heavy',
					],
				]),
			$b->number($this->settings['line-height'])
				->inputClasses('ame-small-number-input', 'ame-line-height-input')
				->params([
					'rangeByUnit' => [
						''   => ['min' => 0.1, 'max' => 5, 'step' => 0.1],
						'px' => ['min' => 1, 'max' => 100, 'step' => 1],
						'em' => ['min' => 0.2, 'max' => 10, 'step' => 0.1],
					],
				]),
			$b->fontStyle([
				'font-style'      => $this->settings['style'],
				'font-variant'    => $this->settings['variant'],
				'text-transform'  => $this->settings['text-transform'],
				'text-decoration' => $this->settings['text-decoration'],
			])->label('Style'),
		];
	}
}