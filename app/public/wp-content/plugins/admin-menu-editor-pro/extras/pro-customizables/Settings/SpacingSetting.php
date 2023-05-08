<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable\Settings;

use YahnisElsts\AdminMenuEditor\ProCustomizable\CssPropertyGenerator;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\CssBoxSides;
use YahnisElsts\AdminMenuEditor\ProCustomizable\Settings\CssLengthSetting;

abstract class SpacingSetting extends CssBoxSides implements CssPropertyGenerator {
	public function getCssProperties() {
		$properties = [];
		if ( empty($this->cssPropertyPrefix) ) {
			return $properties;
		}

		foreach (self::SIDES as $side) {
			$setting = $this->settings[$side];
			if ( !($setting instanceof CssLengthSetting) ) {
				continue;
			}

			$value = $setting->getCssValue();
			if ( $value !== null ) {
				$properties[$this->cssPropertyPrefix . $side] = $value;
			}
		}
		return $properties;
	}

	public function getJsPreviewConfiguration() {
		$sideConfigs = [];
		foreach (self::SIDES as $side) {
			$setting = $this->settings[$side];
			if ( !($setting instanceof CssLengthSetting) ) {
				continue;
			}
			$sideConfigs = array_merge($sideConfigs, $setting->getJsPreviewConfiguration());
		}
		return $sideConfigs;
	}
}