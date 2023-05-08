<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable\Settings;

use YahnisElsts\AdminMenuEditor\Customizable\Settings;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;

class CssBoxSides extends Settings\AbstractStructSetting {
	/**
	 * @var \YahnisElsts\AdminMenuEditor\Customizable\Settings\Setting[]
	 */
	protected $settings = array();

	/**
	 * @var \YahnisElsts\AdminMenuEditor\Customizable\Settings\StringEnumSetting
	 */
	protected $unitSetting;

	const SIDES = array(
		'left'   => 'left',
		'top'    => 'top',
		'right'  => 'right',
		'bottom' => 'bottom',
	);

	protected $cssPropertyPrefix = '';

	public function __construct($id, StorageInterface $store = null, $params = array()) {
		parent::__construct($id, $store, $params);

		$this->unitSetting = $this->createChild(
			'unit',
			Settings\StringEnumSetting::class,
			array('px', 'em', '%'),
			array('default' => 'px')
		);

		$this->unitSetting->describeChoice('px', 'px');
		$this->unitSetting->describeChoice('em', 'em');
		$this->unitSetting->describeChoice('%', '%');

		foreach (self::SIDES as $side) {
			$this->createChild(
				$side,
				CssLengthSetting::class,
				!empty($this->cssPropertyPrefix) ? ($this->cssPropertyPrefix . $side) : '',
				array(
					'minValue'    => -1000,
					'maxValue'    => 1000,
					'unitSetting' => $this->unitSetting,
					'default'     => \ameUtils::get($params, ['sideDefaults', $side]),
				)
			);
		}
	}

	/**
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Settings\StringEnumSetting
	 */
	public function getUnitSetting() {
		return $this->unitSetting;
	}
}