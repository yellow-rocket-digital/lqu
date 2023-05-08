<?php

namespace YahnisElsts\AdminMenuEditor\StyleGenerator;

use YahnisElsts\AdminMenuEditor\ProCustomizable\CssPropertyGenerator;
use YahnisElsts\AdminMenuEditor\ProCustomizable\CssValueGenerator;
use YahnisElsts\AdminMenuEditor\StyleGenerator\Dsl\Expression;
use YahnisElsts\AdminMenuEditor\StyleGenerator\Dsl\JsFunctionCall;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\Setting;
use YahnisElsts\AdminMenuEditor\StyleGenerator\Dsl\SerializableToJsExpression;

class CssRuleSet {
	private $selectors;
	private $declarations;

	/**
	 * @param string[] $selectors
	 * @param array<string|int|float|CssPropertyGenerator> $declarations
	 */
	public function __construct($selectors, $declarations) {
		$this->selectors = $selectors;
		$this->declarations = $declarations;
	}

	public function getRuleSetJsConfiguration() {
		return [
			'selectors'  => $this->selectors,
			'generators' => iterator_to_array($this->makeGeneratorConfigs(), false),
		];
	}

	private function makeGeneratorConfigs() {
		foreach ($this->declarations as $key => $value) {
			if ( is_string($key) ) {
				if ( $value instanceof CssPropertyGenerator ) {
					foreach ($value->getJsPreviewConfiguration() as $c) {
						$c->setCssProperty($key);
						yield $c;
					}
				} else {
					yield JsFunctionCall::prop($key, $value);
				}
			} else if ( $value instanceof CssPropertyGenerator ) {
				yield from $value->getJsPreviewConfiguration();
			} else if ( $value instanceof SerializableToJsExpression ) {
				//It's up to you to ensure that the serialized expression actually
				//produces a valid array of CSS declarations.
				yield $value;
			} else {
				throw new \LogicException(sprintf(
					"Error generating JS config: Unsupported declaration type '%s' for key '%s'",
					gettype($value),
					$key
				));
			}
		}
	}

	public function getCssText() {
		$hasSettings = false;
		$hasNonEmptySettings = false;

		$declarations = [];
		foreach ($this->declarations as $key => $value) {
			//Remember if this rule set uses settings.
			if ( !$hasNonEmptySettings ) {
				if ( $value instanceof AbstractSetting ) {
					$hasSettings = true;
					if ( $value->getValue('') !== '' ) {
						$hasNonEmptySettings = true;
					}
				}
			}

			if ( is_string($key) ) {
				$cssValue = null;

				if ( $value instanceof CssValueGenerator ) {
					$cssValue = $value->getCssValue();
				} else if ( $value instanceof Expression ) {
					$cssValue = $value->getValue();
				} else if ( $value instanceof Setting ) {
					$cssValue = $value->getValue();
				} else if ( is_scalar($value) ) {
					$cssValue = $value;
				} else {
					throw new \LogicException(sprintf(
						"Unsupported declaration type '%s' for key '%s'",
						gettype($value),
						$key
					));
				}

				if ( !StyleGenerator::isEmptyCssValue($cssValue) ) {
					$declarations[$key] = $cssValue;
				}

			} else if ( $value instanceof CssPropertyGenerator ) {
				$declarations = array_merge($declarations, $value->getCssProperties());
			} else {
				throw new \LogicException(sprintf(
					"Unsupported declaration type '%s'",
					gettype($value)
				));
			}
		}

		//Only generate CSS if:
		// a) The ruleset has at least one non-empty setting.
		// b) The ruleset only contains fixed declarations (no settings).
		if ( $hasSettings && !$hasNonEmptySettings ) {
			return '';
		}
		if ( empty($declarations) ) {
			return '';
		}

		$css = implode(', ', $this->selectors) . " {\n";
		foreach ($declarations as $key => $value) {
			$css .= "\t" . $key . ': ' . $value . ";\n";
		}
		$css .= "}\n";
		return $css;
	}
}