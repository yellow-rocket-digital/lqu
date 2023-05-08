<?php

namespace YahnisElsts\AdminMenuEditor\StyleGenerator\Dsl;

use phpColor;
use YahnisElsts\AdminMenuEditor\StyleGenerator\StyleGenerator;

if ( !class_exists('phpColor', false) ) {
	require_once __DIR__ . '/../../phpColors/src/color.php';
}

class DslFunctions {
	public static function runEditHexAsHsl($args) {
		$color = $args['color'];
		if ( StyleGenerator::isEmptyCssValue($color) ) {
			return null;
		}

		try {
			$baseColor = new \phpColor($color);
			$hsl = $baseColor->getHsl();
			if ( isset($args['hue']) ) {
				$hsl['H'] = $args['hue'];
			}
			if ( isset($args['saturation']) ) {
				$hsl['S'] = $args['saturation'];
			}
			if ( isset($args['lightness']) ) {
				$hsl['L'] = $args['lightness'];
			}
			return '#' . phpColor::hslToHex($hsl);
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * As the "mix()" function in Sass.
	 *
	 * @param array $args
	 * @return string|null
	 */
	public static function runMixColors($args) {
		$color1 = $args['color1'];
		$color2 = $args['color2'];
		$weight = $args['weight'];

		if ( StyleGenerator::isEmptyCssValue($color1) || StyleGenerator::isEmptyCssValue($color2) ) {
			return null;
		}

		try {
			$color1 = new \phpColor($color1);
			//PhpColor expects the weight/amount to be between -100 and 100,
			//so we need to convert it from [0, 100] to [-100, 100].
			$amount = 2 * $weight - 100;
			return '#' . $color1->mix($color2, $amount);
		} catch (\Exception $e) {
			return null;
		}
	}

	public static function runDarken($args) {
		$color = $args['color'];
		$amount = $args['amount'];

		if ( StyleGenerator::isEmptyCssValue($color) ) {
			return null;
		}

		try {
			$color = new \phpColor($color);
			return '#' . $color->darken($amount);
		} catch (\Exception $e) {
			return null;
		}
	}

	public static function runFirstNonEmpty($args) {
		//Use the first non-empty value.
		foreach ($args as $value) {
			if ( $value instanceof Expression ) {
				$cssValue = $value->getValue();
			} else {
				throw new \InvalidArgumentException(sprintf(
					"Unboxed value found: %s",
					gettype($value)
				));
			}
			if ( !StyleGenerator::isEmptyCssValue($cssValue) ) {
				return $cssValue;
			}
		}
		return null;
	}

	public static function runFormatLength($args) {
		$value = $args['value'];
		$unit = isset($args['unit']) ? $args['unit'] : '';

		if ( StyleGenerator::isEmptyCssValue($value) ) {
			return null;
		}

		$numValue = floatval($value);
		if ( $numValue === 0.0 ) {
			return '0';
		}

		//We'll limit precision for readability. It shouldn't be visually noticeable.
		$numValue = round($numValue, 5);

		if ( $numValue === floor($numValue) ) {
			//Treat as an integer - no decimals.
			$formattedNumber = sprintf('%d', $numValue);
		} else {
			//Treat as a float. Note the use of the non-locale aware specifier %F
			//to ensure that the decimal point is always a period.
			$formattedNumber = sprintf('%F', $numValue);
		}

		return $formattedNumber . $unit;
	}

	public static function runCompare($args) {
		$value1 = $args['value1'];
		$value2 = $args['value2'];
		$operator = $args['op'];
		//Note that "thenResult" and "elseResult" could be NULL, so we can't use isset().
		$thenResult = array_key_exists('thenResult', $args) ? $args['thenResult'] : true;
		$elseResult = array_key_exists('elseResult', $args) ? $args['elseResult'] : null;

		switch ($operator) {
			case '==':
				$result = $value1 == $value2;
				break;
			case '!=':
				$result = $value1 != $value2;
				break;
			case '>':
				$result = $value1 > $value2;
				break;
			case '>=':
				$result = $value1 >= $value2;
				break;
			case '<':
				$result = $value1 < $value2;
				break;
			case '<=':
				$result = $value1 <= $value2;
				break;
			default:
				throw new \InvalidArgumentException(sprintf(
					'Unknown operator: %s',
					$operator
				));
		}

		return $result ? $thenResult : $elseResult;
	}

	public static function runIfSome($args) {
		$values = $args['values'];
		$thenResult = $args['thenResult'];
		$elseResult = array_key_exists('elseResult', $args) ? $args['elseResult'] : null;

		foreach ($values as $value) {
			if ( !empty($value) ) {
				return $thenResult;
			}
		}
		return $elseResult;
	}

	public static function runIfAll($args) {
		$values = $args['values'];
		$thenResult = $args['thenResult'];
		$elseResult = array_key_exists('elseResult', $args) ? $args['elseResult'] : null;

		if ( empty($values) ) {
			return $elseResult;
		}

		foreach ($values as $value) {
			if ( empty($value) ) {
				return $elseResult;
			}
		}
		return $thenResult;
	}
}