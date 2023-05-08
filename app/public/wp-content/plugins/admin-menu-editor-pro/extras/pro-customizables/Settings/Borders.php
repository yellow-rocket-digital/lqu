<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable\Settings;

use YahnisElsts\AdminMenuEditor\Customizable\Builders;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\CssEnumSetting;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\CssLengthSetting;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\CssSettingCollection;
use YahnisElsts\AdminMenuEditor\ProCustomizable\CssPropertyGenerator;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\CssColorSetting;

class Borders extends CssSettingCollection implements CssPropertyGenerator {
	protected $cssPropertyPrefix = 'border-';

	public function __construct($id, StorageInterface $store = null, $params = array()) {
		parent::__construct($id, $store, $params);

		$this->createChild(
			'color',
			CssColorSetting::class,
			'border-color'
		);

		$this->createChild(
			'width',
			CssLengthSetting::class,
			'border-width',
			array('minValue' => 0, 'maxValue' => 200, 'default' => 1)
		);

		$this->createChild(
			'style',
			CssEnumSetting::class,
			'border-style',
			array('none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset'),
			array('default' => 'none')
		);

		$this->createChild(
			'radius',
			CssLengthSetting::class,
			'border-radius',
			array('minValue' => 0, 'maxValue' => 100)
		);
	}

	public function createControls(Builders\ElementBuilderFactory $b) {
		// TODO: Implement createControls() method.
		return [];
	}

	public function getJsPreviewConfiguration() {
		return []; //TODO: Border preview.
	}
}