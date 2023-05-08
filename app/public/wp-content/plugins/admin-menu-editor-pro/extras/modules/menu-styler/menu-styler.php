<?php

namespace YahnisElsts\AdminMenuEditor\MenuStyler;

use YahnisElsts\AdminMenuEditor\Customizable\Builders\SettingFactory;
use YahnisElsts\AdminMenuEditor\Customizable\Controls\Section;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\TabbedPanelRenderer;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\AbstractSettingsDictionary;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\LazyArrayStorage;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;
use YahnisElsts\AdminMenuEditor\DynamicStylesheets\MenuScopedStylesheetHelper;
use YahnisElsts\AdminMenuEditor\StyleGenerator\CssRuleSet;
use YahnisElsts\AdminMenuEditor\StyleGenerator\StyleGenerator;
use YahnisElsts\WpDependencyWrapper\ScriptDependency;

class MenuStyler extends \ameModule {
	private $settings = null;

	const mainScriptHandle = 'ame-menu-styler-ui';
	const featureScriptHandle = 'ame-menu-styler-js-feats';

	/**
	 * @var \YahnisElsts\AdminMenuEditor\StyleGenerator\StyleGenerator
	 */
	private $styleGenerator = null;

	public function __construct($menuEditor) {
		parent::__construct($menuEditor);
		\ameMenu::add_custom_loader([$this, 'loadMenuStylerSettings']);

		if ( !is_admin() ) {
			return;
		}

		add_filter('admin_menu_editor-editor_script_dependencies', [$this, 'addEditorDependencies']);
		add_action('admin_menu_editor-enqueue_scripts-editor', [$this, 'addScriptData']);
		add_action('admin_menu_editor-enqueue_styles-editor', [$this, 'enqueueEditorStyles']);
		add_action('admin_menu_editor-footer-editor', [$this, 'outputDialog']);

		add_action('admin_enqueue_scripts', [$this, 'enqueueFeatureScript'], 9, 0);

		//Register and enqueue the custom admin menu stylesheet.
		//Let's use an earlier priority to register our stylesheet bundle before
		//other modules try to add their own stylesheets to the bundle.
		add_action('init', [$this, 'registerCustomStyle'], 9);

		//Integrate with the Admin Customizer.
		add_action('admin_menu_editor-register_ac_items', [$this, 'registerAdminCustomizerItems']);
		add_action('admin_menu_editor-register_ac_preview_deps', [$this, 'registerAdminCustomizerStylePreview']);
	}

	protected function isEnabledForRequest() {
		return parent::isEnabledForRequest() && is_admin();
	}

	protected function getSettings($menuConfigId = null) {
		if ( $this->settings !== null ) {
			return $this->settings;
		}

		if ( ($menuConfigId === null) ) {
			$helper = MenuScopedStylesheetHelper::getInstance($this->menuEditor);
			$menuConfigId = $helper->getConfigIdFromAjaxRequest();
		}

		$this->settings = new StyleSettings(
			new MenuStylerStorage($this->menuEditor, $menuConfigId)
		);
		return $this->settings;
	}

	protected function getInterfaceStructure() {
		$settings = $this->getSettings();
		$b = $settings->elementBuilder();
		$structure = $b->structure(
			$b->section(
				'Menu bar',
				$b->number('menuBar.menuWidth')->unitText('px')->params(['step' => 1]),
				$b->number('menuBar.submenuPopupWidth')->params(['step' => 1]),
				$b->number('menuBar.collapsedMenuWidth')->params(['step' => 1]),
				$b->auto('menuBar.layout'),
				$b->auto('menuBar.boxShadow')
			)->id('ame-ms-menuBar-section'),
			$b->section(
				'Menu items',
				$b->autoSection('topLevelItems.font'),
				$b->autoSection('topLevelItems.spacing')
					->add($b->html(sprintf(
						'<p class="ame-description">%s</p>',
						esc_html(
							'Tip: Usually, the left padding needs to include the menu icon width, which is 36px by default.'
						)
					)))
			),
			$b->section(
				'Submenus',
				$b->autoSection('submenu.font'),
				$b->autoSection('submenu.openSubmenuItemSpacing'),
				$b->autoSection('submenu.popupSubmenuItemSpacing'),
				$b->autoSection('submenu.boxShadow')
			)->id('ame-ms-Submenus-section'),
			$b->section(
				'Logo',
				$b->auto('logo.baseImage'),
				$b->auto('logo.baseHeight')->params(['step' => 1]),
				$b->auto('logo.collapsedImage'),
				$b->auto('logo.collapsedHeight')->params(['step' => 1]),
				$b->auto('logo.backgroundColor'),
				$b->auto('logo.linkUrl'),
				$b->auto('logo.spacing')
			),
			$b->section(
				'Collapse button',
				$b->checkBox('collapseButton.visible'),
				$b->auto('collapseButton.position'),
				$b->auto('collapseButton.label')
			)
		);

		//Let other modules add their own settings and UI elements.
		do_action('admin_menu_editor-ms_ui_structure', $structure);

		return $structure->build();
	}

	public function outputDialog() {
		$structure = $this->getInterfaceStructure();
		$renderer = new TabbedPanelRenderer(['ame-tp-height-100']);

		//The template will call $renderer->renderStructure($structure) for us.
		require __DIR__ . '/menu-styler-template.php';

		$renderer->enqueueDependencies();
	}

	public function addEditorDependencies($dependencies) {
		$this->enqueueFeatureScript(true);

		//This script needs to be loaded before menu-editor.js so that it can set
		//a "menuConfigurationLoaded" event handler before the editor loads the menu.
		//Both scripts use the jQuery(function() { ... }) shortcut and run their
		//initialization code when the DOM is ready.
		ScriptDependency::create(
			plugins_url('menu-styler-ui.js', __FILE__),
			self::mainScriptHandle
		)
			->addDependencies(
				'jquery',
				'ame-customizable-settings',
				'ame-style-generator',
				'ame-lodash',
				'ame-jquery-cookie',
				self::featureScriptHandle
			)
			->setTypeToModule()
			->register();

		$dependencies[] = self::mainScriptHandle;
		return $dependencies;
	}

	/**
	 * Add JS data for the menu styler UI to the menu styler script.
	 *
	 * This can't be done in addEditorDependencies() because wp_add_inline_script()
	 * only works for scripts that are already enqueued. The script is registered
	 * as an editor dependency, so it only gets enqueued when menu-editor.js is enqueued.
	 */
	public function addScriptData() {
		//Add "menu_styles" to the registered menu configuration child keys.
		add_filter('admin_menu_editor-aux_data_config', [$this, 'addAuxDataConfig']);

		$scriptData = [
			'defaults'            => apply_filters(
				'admin_menu_editor-ms_ui_setting_defaults',
				$this->getSettings()->getRecursiveDefaultsForJs()
			),
			'stylePreviewConfigs' => array_map(
				function (StyleGenerator $generator) {
					return $generator->getJsPreviewConfiguration();
				},
				$this->getAllStyleGenerators()
			),
		];

		wp_add_inline_script(
			self::mainScriptHandle,
			sprintf(
				'window.ameMenuStylerConfig = (%s);',
				wp_json_encode($scriptData)
			),
			'before'
		);
	}

	public function loadMenuStylerSettings($menuConfig, $storedConfig) {
		//Copy menu styler settings from the stored menu configuration to the validated menu configuration.
		if ( isset($storedConfig['menu_styles']) ) {
			$menuConfig['menu_styles'] = $storedConfig['menu_styles'];
		}
		return $menuConfig;
	}

	private function getStyleGenerator(StyleSettings $s) {
		//todo: This should create different instances if the setting argument is different.
		if ( $this->styleGenerator !== null ) {
			return $this->styleGenerator;
		}

		$g = $this->styleGenerator = new StyleGenerator();
		$g->setStylesheetsToDisableOnPreview(['link#ame-ms-custom-menu-styles-css']);

		//region Menu width
		$g->addRuleSet(
			['#adminmenuback', '#adminmenuwrap', '#adminmenu', '#adminmenu .wp-has-current-submenu > .wp-submenu'],
			[$s->getSetting('menuBar.menuWidth')]
		);
		$g->addRuleSet(
			['#wpcontent', '#wpfooter'],
			['margin-left' => $s->getSetting('menuBar.menuWidth')]
		);
		$g->addRuleSet(
			['#adminmenu .wp-submenu'],
			['left' => $s->getSetting('menuBar.menuWidth')]
		);
		$g->addRuleSet(
			[
				'.folded #adminmenuback',
				'.folded #adminmenuwrap',
				'.folded #adminmenu',
				'.folded #adminmenu li.menu-top',
			],
			[$s->getSetting('menuBar.collapsedMenuWidth')]
		);
		$g->addRuleSet(
			[
				'#adminmenu .wp-not-current-submenu .wp-submenu',
				'.folded #adminmenu .wp-has-current-submenu .wp-submenu',
				'#adminmenu .ame-has-deep-submenu:not(.ame-has-highlighted-item) > .wp-submenu',
			],
			[$s->getSetting('menuBar.submenuPopupWidth')]
		);
		//Let other components know the custom menu width.
		$g->addRuleSet(
			['body'],
			[
				'--ame-ms-menu-width'           => $s->getSetting('menuBar.menuWidth'),
				'--ame-ms-collapsed-menu-width' => $s->getSetting('menuBar.collapsedMenuWidth'),
			]
		);
		//endregion

		//region Menu bar: Full height
		$g->addSimpleCondition(
			$s->getSetting('menuBar.layout'),
			'==',
			'fullHeight',
			new CssRuleSet(
				[
					//Note: Selector specificity is intentionally increased to override
					//the margin-top rule added by the menu logo feature.
					'#adminmenu#adminmenu',
				],
				['margin-top' => 'calc(-1 * var(--wp-admin--admin-bar--height, 32px))']
			),
			//The "collapse button position: bottom" setting needs to know
			//how far the menu is from the top of the viewport.
			new CssRuleSet(
				['#adminmenuwrap'],
				['--ams-ms-menu-vp-top-offset' => '0px']
			),
			//Push the Toolbar/Admin Bar to the right to make room for the full-height menu.
			//The default menu width is 160px.
			new CssRuleSet(
				['#wpadminbar'],
				[
					'--ame-ms-fh-menu-width' => $s->getSetting('menuBar.menuWidth'),
					'margin-left'            => 'var(--ame-ms-fh-menu-width, 160px)',
				]
			),
			//Same for the collapsed menu. The default collapsed menu width is 32px.
			new CssRuleSet(
				['.folded #wpadminbar'],
				[
					'--ame-ms-fh-collapsed-menu-width' => $s->getSetting('menuBar.collapsedMenuWidth'),
					'margin-left'                      => 'var(--ame-ms-fh-collapsed-menu-width, 32px)',
				]
			)
		);
		//endregion

		//region Menu bar shadow
		$g->addRuleSet(
			['#adminmenuback'],
			[$s->getSetting('menuBar.boxShadow')]
		);
		//endregion

		//region Top level menu items
		$g->addRuleSet(
			['#adminmenu a.menu-top', '#adminmenu .wp-submenu-head'],
			[$s->getSetting('topLevelItems.font')]
		);
		//TODO: Icon alignment needs to be adjusted when the line height changes.

		$g->addRuleSet(
			['#adminmenu div.wp-menu-name'],
			[$s->getSetting('topLevelItems.spacing.padding')]
		);
		//Adjust menu icon alignment when the vertical padding changes so that the icon
		//stays in the same place relative to the text. The default top padding is 8px.
		$g->addSimpleCondition(
			$s->getSetting('topLevelItems.spacing.padding.top'),
			'>',
			0,
			new CssRuleSet(
				['#adminmenu .menu-top .wp-menu-image'],
				[
					'--ame-ms-item-top-padding' => $s->getSetting('topLevelItems.spacing.padding.top'),
					'padding-top'               => 'max(calc(var(--ame-ms-item-top-padding) - 8px), 0px)',
				]
			)
		);

		$g->addRuleSet(
			['#adminmenu > li.menu-top'],
			[$s->getSetting('topLevelItems.spacing.margin')]
		);
		//endregion

		//region Submenus
		$g->addRuleSet(
			['#adminmenu .wp-submenu a'],
			[$s->getSetting('submenu.font')]
		);
		$g->addRuleSet(
			['#adminmenu .wp-submenu'],
			[$s->getSetting('submenu.boxShadow')]
		);
		//TODO: Test with submenu icons. Custom CSS variables could help there.
		$g->addRuleSet(
			[
				'#adminmenu .wp-not-current-submenu li > a',
				//Third-level menu popup that's inside the current submenu,
				//but does not contain the current menu item.
				'#adminmenu .wp-has-current-submenu li.ame-has-deep-submenu.opensub li > a',
			],
			[$s->getSetting('submenu.popupSubmenuItemSpacing.padding')]
		);
		$g->addRuleSet(
			[
				'#adminmenu .wp-not-current-submenu li',
				'#adminmenu .wp-has-current-submenu li.ame-has-deep-submenu.opensub li',
			],
			[$s->getSetting('submenu.popupSubmenuItemSpacing.margin')]
		);
		$g->addRuleSet(
			['#adminmenu .wp-has-current-submenu ul > li > a'],
			[$s->getSetting('submenu.openSubmenuItemSpacing.padding')]
		);
		$g->addRuleSet(
			['#adminmenu .wp-has-current-submenu ul > li'],
			[$s->getSetting('submenu.openSubmenuItemSpacing.margin')]
		);
		//endregion

		//region Collapse button
		$g->addSimpleCondition(
			$s->getSetting('collapseButton.visible'),
			'==',
			false,
			new CssRuleSet(
				['#adminmenu #collapse-menu'],
				['display' => 'none']
			)
		);
		$g->addSimpleCondition(
			$s->getSetting('collapseButton.position'),
			'==',
			'bottom',
			new CssRuleSet(
				['#adminmenu'],
				[
					//Switch the admin menu to flexbox layout.
					'display'        => 'flex',
					'flex-direction' => 'column',
					//Menu height must be equal or greater than (viewport height - menu offset from
					//the top of the viewport) for the button to be positioned at the bottom of the screen.
					//The top offset is usually equal to the height of the Admin Bar, but it can be
					//zero if the menu is in "full height" mode.
					//We must also account for the menu bar's vertical margins to avoid overflowing the viewport.
					'box-sizing'     => 'border-box',
					'min-height'     => 'calc( 
						100vh 
						- var(--ams-ms-menu-vp-top-offset, var(--wp-admin--admin-bar--height, 32px)) 
						- var(--ame-ms-menu-margin-top, 12px) 
						- var(--ame-ms-menu-margin-bottom, 12px) 
					)',
				]
			),
			//In flexbox layout, "margin-top: auto" works to move the button to the bottom.
			new CssRuleSet(
				['#adminmenu #collapse-menu'],
				['margin-top' => 'auto']
			)
		);
		//endregion

		//region Logo
		//Most logo settings are handled elsewhere. This is just for the margins and padding.
		$g->addRuleSet(
			['#adminmenu #ame_ms_admin_menu_logo'],
			[$s->getSetting('logo.spacing')]
		);
		//endregion

		return $this->styleGenerator;
	}

	/**
	 * Get all style generators associated with the menu styler UI.
	 *
	 * This exists because other modules can add their own settings to that UI.
	 *
	 * @return StyleGenerator[]
	 */
	private function getAllStyleGenerators() {
		return apply_filters(
			'admin_menu_editor-ms_ui_style_generators',
			[$this->getStyleGenerator($this->getSettings())]
		);
	}

	public function enqueueFeatureScript($isRequired = false) {
		//Do this only once.
		static $isScriptEnqueued = false;
		if ( $isScriptEnqueued ) {
			return;
		}

		if ( !wp_script_is(self::featureScriptHandle, 'registered') ) {
			ScriptDependency::create(
				plugins_url('menu-styler-features.js', __FILE__),
				self::featureScriptHandle
			)
				->addDependencies('jquery')
				->setTypeToModule()
				//Adding the "async" attribute makes a module script execute sooner,
				//which is useful to prevent FOUC.
				//See https://gist.github.com/jakub-g/385ee6b41085303a53ad92c7c8afd7a6
				->setAsync()
				->register();
		}

		$settings = $this->getSettings();

		//Enqueue the script if one of the relevant settings is in use, or if this is
		//the AC preview frame, or if this is the settings page, This method handles
		//the first two and addEditorDependencies() handles the last one by setting
		//the $isRequired parameter to true.
		$isRequired = $isRequired
			|| $this->menuEditor->is_editor_page()
			|| apply_filters('admin_menu_editor-is_preview_frame', false);

		$buttonRequired = $isRequired;
		if ( !$buttonRequired ) {
			$collapseButtonLabel = $settings->get('collapseButton.label');
			$buttonRequired = !empty($collapseButtonLabel);
		}

		$logoRequired = $isRequired;
		if ( !$logoRequired ) {
			if ( $settings->get('logo.baseImage.attachmentId', 0) > 0 ) {
				$logoRequired = true;
			} else if ( (string)$settings->get('logo.baseImage.externalUrl', '') !== '' ) {
				$logoRequired = true;
			} else if ( (string)$settings->get('logo.collapsedImage.attachmentId', 0) > 0 ) {
				$logoRequired = true;
			} else if ( (string)$settings->get('logo.collapsedImage.externalUrl', '') !== '' ) {
				$logoRequired = true;
			}
		}

		if ( !($buttonRequired || $logoRequired) ) {
			return;
		}

		wp_enqueue_script(self::featureScriptHandle);
		$isScriptEnqueued = true;

		$scriptData = [];

		$labelSettings = [
			'collapseButton.label' => 'label',
		];
		$logoSettings = [
			//For attachments, the URL should already be cached in the setting.
			'logo.baseImage'       => 'baseImage',
			'logo.collapsedImage'  => 'collapsedImage',
			'logo.linkUrl'         => 'linkUrl',
			'logo.backgroundColor' => 'backgroundColor',
			'logo.baseHeight'      => 'baseHeight',
			'logo.collapsedHeight' => 'collapsedHeight',
		];

		$scriptDataKeys = [];
		if ( $buttonRequired ) {
			$scriptDataKeys['collapseButtonText'] = $labelSettings;
		}
		if ( $logoRequired ) {
			$scriptDataKeys['menuLogo'] = $logoSettings;
		}

		foreach ($scriptDataKeys as $key => $pathToKeyMap) {
			$settingValues = [];
			$settingMap = [];
			foreach ($pathToKeyMap as $path => $localKey) {
				$setting = $settings->getSetting($path);
				$settingValues[$localKey] = $setting->getValue();
				$settingMap[$setting->getId()] = $localKey;
			}
			$scriptData[$key] = [
				'settings'   => $settingValues,
				'settingMap' => $settingMap,
			];
		}

		wp_add_inline_script(
			self::featureScriptHandle,
			sprintf(
				'window.ameMenuStylerFeatureConfig = (%s);',
				wp_json_encode($scriptData)
			),
			'before'
		);
	}

	public function enqueueEditorStyles() {
		wp_enqueue_auto_versioned_style(
			'ame-menu-styler-editor-css',
			plugins_url('menu-styler.css', __FILE__)
		);
	}

	public function registerCustomStyle() {
		$bundleName = 'ame-menu-style-bundle';
		$helper = MenuScopedStylesheetHelper::getInstance($this->menuEditor);

		//Disable bundling in preview mode to make things easier for JS-based preview
		//updaters. This way each updater only needs to disable their own stylesheet,
		//instead of disabling the whole bundle and potentially breaking other features
		//that use the same bundle.
		$isAdminCustomizerPreview = apply_filters('admin_menu_editor-is_preview_frame', false);
		$queryParams = $this->menuEditor->get_query_params();
		$isPreview = (
			$isAdminCustomizerPreview
			//The menu editor page also does live preview when the "Style" dialog is open.
			//Note that we can't use $this->menuEditor->is_editor_page() here because
			//the current tab is not set yet during the "init" action.
			|| (
				isset($queryParams['page'])
				&& is_admin()
				&& (!wp_doing_ajax())
				&& ($queryParams['page'] === 'menu_editor')
				&& (
					empty($queryParams['sub_section'])
					|| ($queryParams['sub_section'] === 'editor')
					|| ($queryParams['sub_section'] === 'network-admin-menu')
				)
			)
		);
		if ( !$isPreview ) {
			$helper->addBundle($bundleName);
		}

		$helper->addStylesheet(
			'ame-ms-custom-menu-styles',
			function ($menuConfigId) {
				$settings = $this->getSettings($menuConfigId);

				$modTimeCallback = function () use ($settings) {
					$modificationTime = $settings->getLastModifiedTimestamp();
					return !empty($modificationTime) ? $modificationTime : 0;
				};

				$styleGenerationCallback = function () use ($settings) {
					$styleGenerator = $this->getStyleGenerator($settings);
					return $styleGenerator->generateCss();
				};

				return [$modTimeCallback, $styleGenerationCallback];
			},
			$bundleName
		);
	}

	public function addAuxDataConfig($config) {
		$config['keys'][StyleSettings::CONFIG_KEY] = StyleSettings::SETTING_ID_PREFIX;
		return $config;
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\AdminCustomizer\AmeAdminCustomizer $customizer
	 * @return void
	 */
	public function registerAdminCustomizerItems($customizer) {
		//Register settings.
		$settings = $this->getSettings();
		$customizer->addSettings($settings->getRegisteredSettings());

		//Add menu style controls to the "Admin Menu" section.
		$menuSectionOpt = $customizer->findSection('ame-admin-menu');
		if ( $menuSectionOpt->isEmpty() ) {
			//Add the section if it doesn't exist yet.
			$menuSection = new Section('Admin Menu', [], ['id' => 'ame-admin-menu']);
			$customizer->addSection($menuSection);
		} else {
			$menuSection = $menuSectionOpt->get();
		}

		$myStructure = $this->getInterfaceStructure();
		foreach ($myStructure->getAsSections() as $section) {
			$menuSection->add($section);
		}
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\AdminCustomizer\AmeAdminCustomizer $customizer
	 * @return void
	 */
	public function registerAdminCustomizerStylePreview($customizer) {
		foreach ($this->getAllStyleGenerators() as $generator) {
			$customizer->addPreviewStyleGenerator($generator);
		}
	}
}

class StyleSettings extends AbstractSettingsDictionary {
	const SETTING_ID_PREFIX = 'ws_menu_styler--';

	const CONFIG_KEY = 'menu_styles';

	public function __construct(StorageInterface $store) {
		parent::__construct($store, self::SETTING_ID_PREFIX, true);
	}

	protected function createDefaults() {
		return [];
	}

	protected function createSettings() {
		$f = $this->settingFactory();

		return [
			$f->boolean(
				'configProducesCss',
				'[This internal flag shows if the current configuration generates any CSS when applied]',
				[
					'default'    => null,
					'isEditable' => '__return_false', //Never directly editable.
				]
			),
			$f->customStruct(
				'menuBar',
				function (SettingFactory $cf) {
					$cf->enablePostMessageSupport();
					return [
						$cf->cssLength(
							'menuWidth',
							'Menu width',
							'width',
							['default' => null, 'minValue' => 30, 'maxValue' => 500]
						),
						$cf->cssLength(
							'collapsedMenuWidth',
							'Collapsed menu width',
							'width',
							['default' => null, 'minValue' => 10, 'maxValue' => 100]
						),
						$cf->cssLength(
							'submenuPopupWidth',
							'Submenu popup width',
							'width',
							['default' => null, 'minValue' => 30, 'maxValue' => 500]
						),
						$cf->enum('layout', ['default', 'fullHeight'], 'Layout')
							->describeChoice('default', 'Default')
							->describeChoice('fullHeight', 'Full height menu'),
						$cf->cssBoxShadow(
							'boxShadow',
							'Menu bar shadow'
						),
					];
				}
			),
			$f->customStruct(
				'topLevelItems',
				function (SettingFactory $cf) {
					$cf->enablePostMessageSupport();
					return [
						$cf->cssFont('font', 'Font'),
						$cf->cssSpacing('spacing', 'Spacing'),
					];
				}
			),
			$f->customStruct(
				'submenu',
				function (SettingFactory $cf) {
					$cf->enablePostMessageSupport();
					return [
						$cf->cssFont('font', 'Font'),
						$cf->cssSpacing('openSubmenuItemSpacing', 'Spacing: Open submenu items'),
						$cf->cssSpacing('popupSubmenuItemSpacing', 'Spacing: Popup submenu items'),
						$cf->cssBoxShadow('boxShadow', 'Submenu popup shadow'),
					];
				}
			),

			$f->customStruct(
				'logo',
				function (SettingFactory $cf) {
					$cf->enablePostMessageSupport();
					return [
						$cf->image('baseImage', 'Expanded menu logo'),
						$cf->image('collapsedImage', 'Collapsed menu logo'),
						$cf->cssLength(
							'baseHeight',
							'Logo height (expanded)',
							'height',
							['default' => 60, 'defaultUnit' => 'px', 'minValue' => 10, 'maxValue' => 200]
						),
						$cf->cssLength(
							'collapsedHeight',
							'Logo height (collapsed)',
							'height',
							['default' => 34, 'defaultUnit' => 'px', 'minValue' => 10, 'maxValue' => 200]
						),
						$cf->cssColor('backgroundColor', 'background-color', 'Background color'),
						$cf->url('linkUrl', 'Logo link URL'),
						$cf->cssSpacing('spacing', 'Logo Spacing'),
					];
				}
			),

			$f->customStruct(
				'collapseButton',
				function (SettingFactory $cf) {
					$cf->enablePostMessageSupport();
					return [
						$cf->boolean('visible', 'Show the "Collapse menu" button', [
							'groupTitle' => 'Visibility',
							'default'    => true,
						]),
						$cf->enum('position', ['default', 'bottom'], 'Position'),
						$cf->plainText('label', 'Text'),
					];
				}
			),
		];
	}
}

class MenuStylerStorage extends LazyArrayStorage implements StorageInterface {

	private $menuEditor;
	private $configId;

	public function __construct(\WPMenuEditor $menuEditor, $menuConfigId = null) {
		$this->menuEditor = $menuEditor;
		$this->configId = $menuConfigId;
		parent::__construct();
	}

	private function getMenuConfigId() {
		if ( $this->configId === null ) {
			$this->configId = $this->menuEditor->get_loaded_menu_config_id();
		}
		return $this->configId;
	}

	protected function loadData() {
		$configId = $this->getMenuConfigId();
		$customMenu = $this->menuEditor->load_custom_menu($configId);
		if ( ($customMenu !== null) && !empty($customMenu[StyleSettings::CONFIG_KEY]) ) {
			return $customMenu[StyleSettings::CONFIG_KEY];
		}
		return [];
	}

	protected function storeData($newData) {
		$configId = $this->getMenuConfigId();

		$customMenu = $this->menuEditor->load_custom_menu($configId);
		if ( $customMenu === null ) {
			//Design problem: Can't save menu styles without a custom menu.
			//Note that this will throw an exception if the menu has not been initialized yet.
			//For example, it might not work in an AJAX request.
			$customMenu = $this->menuEditor->get_active_admin_menu();
			$configId = null;
		}
		$customMenu[StyleSettings::CONFIG_KEY] = $newData;
		$this->menuEditor->set_custom_menu($customMenu, $configId);
	}

	protected function deleteStoredData() {
		$customMenu = $this->menuEditor->load_custom_menu($this->getMenuConfigId());
		if ( ($customMenu === null) || (empty($customMenu[StyleSettings::CONFIG_KEY])) ) {
			return;
		}
		unset($customMenu[StyleSettings::CONFIG_KEY]);
		$this->menuEditor->set_custom_menu($customMenu, $this->getMenuConfigId());
	}
}