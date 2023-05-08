<?php
require_once __DIR__ . '/../includes/AmeAutoloader.php';

use YahnisElsts\AdminMenuEditor\AmeAutoloader;
use YahnisElsts\WpDependencyWrapper\ScriptDependency;

$wsAmeProAutoloader = new AmeAutoloader([
	'YahnisElsts\\AdminMenuEditor\\ProCustomizable\\'    => __DIR__ . '/pro-customizables',
	'YahnisElsts\\AdminMenuEditor\\StyleGenerator\\'     => __DIR__ . '/style-generator/',
	'YahnisElsts\\AdminMenuEditor\\DynamicStylesheets\\' => __DIR__ . '/dynamic-stylesheets/',
	'YahnisElsts\\WpDependencyWrapper\\'                 => __DIR__ . '/../wp-dependency-wrapper',
]);

$wsAmeProAutoloader->register();

//Additionally, "autoload" JS scripts by registering them before they're used.
//Other modules can then enqueue them or add them as dependencies.
//
//This file only registers scripts that are not part of a specific module. Specific
//modules can register their own scripts in their own hooks.
if ( function_exists('add_action') ) {
	//Register JS assets used on AME pages.
	function ws_ame_register_customizable_js_lib() {
		static $isDone = false;
		if ( $isDone ) {
			return;
		}
		$isDone = true;

		//Register client-side setting classes and view models.
		$customizableBase = ScriptDependency::create(
			plugins_url('pro-customizables/assets/customizable.js', __FILE__),
			'ame-customizable-settings'
		)
			->addDependencies('ame-mini-functional-lib', 'ame-knockout', 'ame-lodash')
			->setTypeToModule()
			->register();

		//Register style generator stuff.
		ScriptDependency::create(
			plugins_url('style-generator/style-generator.js', __FILE__),
			'ame-style-generator'
		)
			->addDependencies(
				$customizableBase,
				'ame-knockout',
				'ame-lodash',
				'ame-mini-functional-lib',
				'jquery-color'
			)
			->setTypeToModule()
			->register();
	}

	add_action('admin_menu_editor-register_scripts', 'ws_ame_register_customizable_js_lib', 9);
}