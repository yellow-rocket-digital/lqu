<?php

namespace YahnisElsts\AdminMenuEditor\AdminCustomizer;

use YahnisElsts\AdminMenuEditor\Customizable\Controls;
use YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting;
use YahnisElsts\AdminMenuEditor\Options\None;
use YahnisElsts\AdminMenuEditor\Options\Option;
use YahnisElsts\AdminMenuEditor\StyleGenerator\StyleGenerator;
use YahnisElsts\WpDependencyWrapper\ScriptDependency;

require_once AME_ROOT_DIR . '/includes/ame-option.php';

class AmeAdminCustomizer extends \ameModule {
	const CHANGESET_POST_TYPE = 'ame_ac_changeset';
	const SAVE_CHANGESET_ACTION = 'ws_ame_ac_save_changeset';
	const REFRESH_PREVIEW_ACTION = 'ws_ame_ac_refresh_preview_frame';

	/**
	 * @var AbstractSetting[]
	 */
	protected $registeredSettings = [];
	/**
	 * @var null|\YahnisElsts\AdminMenuEditor\Customizable\Controls\InterfaceStructure
	 */
	protected $structure = null;
	protected $isRegistrationDone = false;

	/**
	 * @var array<string,boolean> Settings that support preview via postMessage.
	 */
	protected $supportsPostMessage = [];

	protected $changesetName = null;
	/**
	 * @var AcChangeset|null
	 */
	protected $currentChangeset = null;

	/**
	 * Setting preview values submitted to the current page in a POST request.
	 *
	 * This is used to preview setting changes that have not yet been saved
	 * to the changeset. The submitted values can override matching values
	 * in the current changeset.
	 *
	 * @see REFRESH_PREVIEW_ACTION
	 * @var array<string,mixed>
	 */
	protected $submittedPreviewValues = [];

	protected $isPreviewFrameInitialized = false;

	/**
	 * @var \YahnisElsts\AdminMenuEditor\StyleGenerator\StyleGenerator[]
	 */
	protected $registeredStyleGenerators = [];

	public function __construct($menuEditor) {
		parent::__construct($menuEditor);

		if ( $this->isInPreviewFrame() ) {
			$this->initializePreviewFrame();
		}

		add_action('admin_menu', [$this, 'addAdminMenu']);
		add_action('init', [$this, 'registerChangesetPostType']);
		add_action('transition_post_status', [$this, 'applyChangesOnPublish'], 10, 3);

		add_action('wp_ajax_' . self::SAVE_CHANGESET_ACTION, [$this, 'ajaxSaveChangeset']);
	}

	public function addAdminMenu() {
		$hook = add_options_page(
			'Admin Customizer',
			'Admin Customizer',
			'manage_options',
			'ame-admin-customizer',
			[$this, 'outputAdminPage']
		);

		add_action('load-' . $hook, [$this, 'initCustomizerPage']);
		add_action('load-' . $hook, [$this, 'disableAdminHeader']);
	}

	public function outputAdminPage() {
		$this->outputMainTemplate();

		//Stop execution to prevent the admin footer from being displayed.
		exit;
	}

	protected function getTemplateVariables($templateName) {
		$variables = parent::getTemplateVariables($templateName);
		$variables['settings'] = $this->registeredSettings;
		$variables['structure'] = $this->structure;
		$variables['currentChangeset'] = $this->currentChangeset;
		return $variables;
	}

	public function initCustomizerPage() {
		add_filter('admin_menu_editor-is_admin_customizer', '__return_true');

		$this->registerCustomizableItems();
		$this->initializeCustomizerChangeset();
		$this->initializeSettingsPreview();

		$this->disableAdminHeader();
		$this->enqueueDependencies();
	}

	public function disableAdminHeader() {
		$_GET['noheader'] = 1;
	}

	protected function enqueueDependencies() {
		$this->menuEditor->register_base_dependencies();
		$this->registerBaseScripts();

		wp_enqueue_auto_versioned_style(
			'ame-admin-customizer',
			plugins_url('admin-customizer.css', __FILE__),
			['wp-admin', 'colors']
		);

		$settingInfo = AbstractSetting::serializeSettingsForJs(
			$this->registeredSettings,
			AbstractSetting::SERIALIZE_INCLUDE_VALUE | AbstractSetting::SERIALIZE_INCLUDE_POST_MESSAGE_SUPPORT,
			function ($data, AbstractSetting $setting) {
				//Backwards compatibility with older parts of the code that didn't
				//have access to the "supportsPostMessage" property on the setting.
				if ( !empty($this->supportsPostMessage[$setting->getId()]) ) {
					$data['supportsPostMessage'] = true;
				}
				return $data;
			}
		);

		$scriptData = [
			'ajaxUrl'             => admin_url('admin-ajax.php'),
			'changesetName'       => $this->currentChangeset->getName(),
			'changesetItemCount'  => count($this->currentChangeset),
			'changesetStatus'     => $this->currentChangeset->getStatus(),
			'saveChangesetNonce'  => wp_create_nonce(self::SAVE_CHANGESET_ACTION),
			'refreshPreviewNonce' => wp_create_nonce(self::REFRESH_PREVIEW_ACTION),
			'settings'            => $settingInfo,
			'interfaceStructure'  => $this->structure->serializeForJs(),
			'initialPreviewUrl'   => add_query_arg(
				[
					'ame-ac-preview'   => 1,
					'ame-ac-changeset' => $this->currentChangeset->getName(),
				],
				self_admin_url('options-general.php')
			),
			'allowedPreviewUrls'  => $this->getAllowedPreviewBaseUrls(),
			'allowedCommOrigins'  => $this->getAllowedCommunicationOrigins(),
		];

		ScriptDependency::create(
			plugins_url('admin-customizer.js', __FILE__),
			'ame-admin-customizer-js'
		)
			->addDependencies(
				'jquery',
				'ame-lodash',
				'ame-knockout',
				'ame-mini-functional-lib',
				'ame-customizable-settings',
				'ame-admin-customizer-communicator',
				'ame-admin-customizer-base'
			)
			->addJsVariable('wsAmeAdminCustomizerData', $scriptData)
			->setTypeToModule()
			->enqueue();

		$this->structure->enqueueKoComponentDependencies();

		do_action('admin_menu_editor-enqueue_ac_dependencies');
	}

	protected function registerCustomizableItems() {
		if ( $this->isRegistrationDone ) {
			return;
		}
		$this->isRegistrationDone = true;

		$this->structure = new Controls\InterfaceStructure('Admin Customizer');
		do_action('admin_menu_editor-register_ac_items', $this);

		//Register all settings used by controls. This way modules generally don't
		//have to do it explicitly.
		foreach ($this->structure->getAllDescendants() as $element) {
			if ( $element instanceof Controls\Control ) {
				$this->addSettings($element->getSettings());
			}
		}
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting[] $settings
	 * @return void
	 */
	public function addSettings($settings) {
		foreach (AbstractSetting::recursivelyIterateSettings($settings) as $instance) {
			$this->registeredSettings[$instance->getId()] = $instance;
		}
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\Customizable\Controls\Container $section
	 * @return void
	 */
	public function addSection($section) {
		if ( $section === null ) {
			return;
		}
		$this->structure->add($section);
	}

	/**
	 * @param \YahnisElsts\AdminMenuEditor\StyleGenerator\StyleGenerator $styleGenerator
	 * @return void
	 */
	public function addPreviewStyleGenerator(StyleGenerator $styleGenerator) {
		$this->registeredStyleGenerators[] = $styleGenerator;
	}

	/**
	 * Enable postMessage support for a setting.
	 *
	 * @param string|AbstractSetting|null $settingOrId
	 * @return void
	 */
	public function enablePostMessage($settingOrId) {
		if ( $settingOrId instanceof AbstractSetting ) {
			$setting = $settingOrId;
		} else if ( is_string($settingOrId) ) {
			$setting = $this->getSettingById($settingOrId);
		} else {
			return;
		}
		if ( $setting === null ) {
			return;
		}

		$this->supportsPostMessage[$setting->getId()] = true;
	}

	/**
	 * Find an existing section by ID.
	 *
	 * @param $sectionId
	 * @return Option<\YahnisElsts\AdminMenuEditor\Customizable\Controls\Section>
	 */
	public function findSection($sectionId) {
		if ( !$this->structure ) {
			//Structure only gets initialized when registering items.
			return Option::none();
		}

		$child = $this->structure->findChildById($sectionId);
		if ( $child instanceof Controls\Section ) {
			return Option::some($child);
		}
		return Option::none();
	}

	/**
	 * @param string $settingId
	 * @return \YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting|null
	 */
	private function getSettingById($settingId) {
		if ( isset($this->registeredSettings[$settingId]) ) {
			return $this->registeredSettings[$settingId];
		}
		/** @noinspection PhpRedundantOptionalArgumentInspection */
		return \ameMultiDictionary::get($this->registeredSettings, $settingId, null);
	}

	private function isInPreviewFrame() {
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, just checking if param is set.
		return !empty($_GET['ame-ac-preview'])
			&& function_exists('is_user_logged_in')
			&& is_user_logged_in();
	}

	private function initializePreviewFrame() {
		add_filter('admin_menu_editor-is_preview_frame', '__return_true');

		$this->requirePreviewChangeset();
		$this->parseSubmittedPreviewValues();

		add_action('wp_loaded', [$this, 'registerAndPreviewSettings']);

		add_action('admin_enqueue_scripts', [$this, 'enqueuePreviewDependencies']);
		add_action('wp_enqueue_scripts', [$this, 'enqueuePreviewDependencies']);

		add_filter('admin_menu_editor-ac_add_preview_params', [$this, 'addPreviewParamsToUrl']);

		$this->isPreviewFrameInitialized = true;
	}

	/**
	 * @return void
	 * @internal
	 */
	public function enqueuePreviewDependencies() {
		$this->menuEditor->register_safe_js_libraries();
		$this->registerBaseScripts();

		do_action('admin_menu_editor-register_ac_preview_deps', $this);

		wp_enqueue_auto_versioned_style(
			'ame-admin-customizer-preview-styles',
			plugins_url('preview.css', __FILE__)
		);

		ScriptDependency::create(
			plugins_url('preview-handler.js', __FILE__),
			'ame-admin-customizer-preview'
		)
			->setTypeToModule() //Uses other modules.
			->setAsync() //Load sooner.
			->addDependencies(
				'jquery',
				'ame-admin-customizer-communicator',
				'ame-customizable-settings',
				'ame-style-generator'
			)
			->addJsVariable('wsAmeAcPreviewData', [
				'changesetName'       => $this->currentChangeset->getName(),
				'allowedPreviewUrls'  => $this->getAllowedPreviewBaseUrls(),
				'allowedCommOrigins'  => $this->getAllowedCommunicationOrigins(),
				'settings'            => AbstractSetting::serializeSettingsForJs(
					$this->registeredSettings,
					AbstractSetting::SERIALIZE_INCLUDE_VALUE
				),
				'stylePreviewConfigs' => array_map(
					function (StyleGenerator $generator) {
						return $generator->getJsPreviewConfiguration();
					},
					$this->registeredStyleGenerators
				),
			])
			->enqueue();

		do_action('admin_menu_editor-enqueue_ac_preview');
	}

	private function registerBaseScripts() {
		if ( function_exists('ws_ame_register_customizable_js_lib') ) {
			ws_ame_register_customizable_js_lib();
		}

		$base = ScriptDependency::create(
			plugins_url('admin-customizer-base.js', __FILE__),
			'ame-admin-customizer-base'
		)
			->addDependencies('jquery', 'ame-customizable-settings')
			->setTypeToModule()
			->register();

		ScriptDependency::create(
			plugins_url('communicator.js', __FILE__),
			'ame-admin-customizer-communicator'
		)
			->addDependencies('jquery', $base)
			->register();
	}

	private function getAllowedPreviewBaseUrls() {
		$results = self::generateAllowedUrls();
		if ( is_ssl() ) {
			$results = array_merge($results, self::generateAllowedUrls('https'));
		}
		return array_unique($results);
	}

	private static function generateAllowedUrls($scheme = null) {
		$params = ['/'];
		if ( $scheme !== null ) {
			$params[] = $scheme;
		}
		$results = [
			home_url(...$params),
			admin_url(...$params),
		];
		if ( is_multisite() ) {
			$results[] = network_admin_url(...$params);
			$results[] = user_admin_url(...$params);
		}
		return $results;
	}

	private function getAllowedCommunicationOrigins() {
		$previewUrls = $this->getAllowedPreviewBaseUrls();
		$results = [];
		foreach ($previewUrls as $url) {
			$scheme = wp_parse_url($url, PHP_URL_SCHEME);
			$host = wp_parse_url($url, PHP_URL_HOST);
			//Skip invalid URLs.
			if ( ($scheme === null) || ($host === null) ) {
				continue;
			}
			$results[] = $scheme . '://' . $host;
		}
		return array_unique($results);
	}

	/**
	 * Register customizable settings and enable preview.
	 *
	 * This needs to run after other modules have been loaded so that modules can
	 * register their settings. However, it also needs to run early enough so that
	 * we can set up the preview before those settings are actually *used*.
	 *
	 * @access private It's only public because it's a hook callback.
	 */
	public function registerAndPreviewSettings() {
		$this->registerCustomizableItems();
		$this->initializeSettingsPreview();
	}

	/**
	 * Initialize the preview of registered settings using values from the current
	 * changeset and the current POST request, if any.
	 */
	private function initializeSettingsPreview() {
		$previewValues = [];
		$changeset = $this->getCurrentChangeset();
		if ( !empty($changeset) ) {
			foreach ($changeset as $settingId => $value) {
				$previewValues[$settingId] = $value;
			}
		}
		$previewValues = array_merge($previewValues, $this->submittedPreviewValues);

		foreach ($previewValues as $settingId => $value) {
			if ( isset($this->registeredSettings[$settingId]) ) {
				$this->registeredSettings[$settingId]->preview($value);
			}
		}
	}

	/**
	 * Load the current changeset while inside the preview frame.
	 *
	 * Will exit with an error if the changeset name is specified but invalid.
	 *
	 * @return void
	 */
	private function requirePreviewChangeset() {
		//Let other modules retrieve the changeset once it's ready.
		add_filter('admin_menu_editor-ac_preview_frame_changeset', function () {
			return $this->currentChangeset;
		});

		//The changeset name can be passed as a query parameter.
		if ( empty($this->changesetName) ) {
			$this->changesetName = $this->getChangesetNameFromRequest()->getOrElse(null);
		}
		if ( empty($this->changesetName) ) {
			//This can happen if the customizer has a created a new changeset,
			//but hasn't saved it yet. Let's use a temporary empty changeset.
			$this->currentChangeset = new AcChangeset();
			return;
		}

		//Changeset name must be a UUID.
		if ( !wp_is_uuid($this->changesetName) ) {
			wp_die('Invalid changeset name.', 400);
		}

		//Changeset must exist.
		$changeset = $this->loadChangeset($this->changesetName);
		if ( $changeset->isEmpty() ) {
			wp_die('The specified changeset does not exist.', 404);
		}
		$this->currentChangeset = $changeset->get();
	}

	/**
	 * @return Option<string>
	 */
	private function getChangesetNameFromRequest() {
		//phpcs:disable WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
		if ( !empty($_POST['ame-ac-changeset']) ) {
			return Option::fromValue(sanitize_key(strval($_POST['ame-ac-changeset'])));
		} else if ( isset($_GET['ame-ac-changeset']) ) {
			return Option::fromValue(sanitize_key(strval($_GET['ame-ac-changeset'])));
		}
		//phpcs:enable
		return None::getInstance();
	}

	private function parseSubmittedPreviewValues() {
		$this->submittedPreviewValues = [];
		if (
			empty($_POST['action'])
			|| ($_POST['action'] !== self::REFRESH_PREVIEW_ACTION)
			|| empty($_POST['nonce'])
			|| empty($_POST['modified'])
		) {
			return;
		}

		//Note: This method is expected to run before WordPress adds magic quotes
		//to $_POST. We don't need wp_unslash() here.

		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$verified = wp_verify_nonce(strval($_POST['nonce']), self::REFRESH_PREVIEW_ACTION);
		if ( $verified === false ) {
			return;
		}

		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- This is JSON.
		$modified = json_decode(strval($_POST['modified']), true);
		if ( !is_array($modified) ) {
			return;
		}
		$this->submittedPreviewValues = $modified;
	}

	/**
	 * Add query parameters that will enable preview mode to the specified URL.
	 *
	 * Only works while inside a fully initialized preview frame. In other contexts,
	 * this method returns the original URL unchanged.
	 *
	 * This is used as a filter callback that lets other modules add preview parameters
	 * to their own URLs without having to somehow get a reference to the admin customizer.
	 *
	 * @param string $url
	 * @return string
	 */
	public function addPreviewParamsToUrl($url) {
		if ( $this->isPreviewFrameInitialized && $this->currentChangeset ) {
			return add_query_arg(
				[
					'ame-ac-preview'   => '1',
					'ame-ac-changeset' => $this->currentChangeset->getName(),
				],
				$url
			);
		}
		return $url;
	}

	/**
	 * Load or create a changeset for the admin customizer.
	 *
	 * This is for the customizer UI, not for the preview frame.
	 *
	 * @return void
	 */
	private function initializeCustomizerChangeset() {
		if ( $this->isInPreviewFrame() ) {
			throw new \LogicException('This method should not be called in the preview frame.');
		}
		if ( !empty($this->currentChangeset) ) {
			return; //Already loaded.
		}

		//Load the changeset specified in a query parameter, load the most recent
		//draft changeset, or create a new changeset.
		$strategies = [
			[$this, 'loadRequestedChangeset'],
			[$this, 'getLatestUnfinishedChangeset'],
			[$this, 'createNewChangeset'],
		];
		foreach ($strategies as $strategy) {
			/** @var Option<AcChangeset> $changeset */
			$changeset = call_user_func($strategy);
			if ( $changeset->isDefined() ) {
				$this->currentChangeset = $changeset->get();
				$this->changesetName = $this->currentChangeset->getName();
				break;
			}
		}

		if ( $this->currentChangeset === null ) {
			wp_die('Could not load or create a changeset.', 500);
		}
	}

	/**
	 * @return Option<AcChangeset>
	 */
	private function loadRequestedChangeset() {
		return $this->getChangesetNameFromRequest()->flatMap(function ($name) {
			return $this->loadChangeset($name);
		});
	}

	/**
	 * @param $name
	 * @return Option<AcChangeset>
	 */
	private function loadChangeset($name) {
		$results = $this->findChangesets([
			'name'           => $name,
			'posts_per_page' => 1,
		]);
		if ( empty($results) ) {
			return Option::fromValue(null);
		}
		return Option::fromValue(reset($results));
	}

	/**
	 * @param array $args
	 * @return AcChangeset[]
	 */
	private function findChangesets($args) {
		$defaults = [
			'post_type'      => self::CHANGESET_POST_TYPE,
			'posts_per_page' => -1,
			//Allow all statuses. get_posts() would usually default to "publish" only.
			'post_status'    => get_post_stati(),
			'orderby'        => 'date',
			'order'          => 'DESC',

			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];

		$args = array_merge($defaults, $args);
		$posts = get_posts($args);
		if ( empty($posts) ) {
			return [];
		}

		$results = [];
		foreach ($posts as $post) {
			$changeset = AcChangeset::fromPost($post);
			if ( $changeset->isDefined() ) {
				$results[] = $changeset->get();
			}
		}

		return $results;
	}

	/**
	 * Get the most recent valid changeset that is not an auto-draft and is not yet published.
	 *
	 * @return Option<AcChangeset>
	 */
	private function getLatestUnfinishedChangeset() {
		$results = $this->findChangesets([
			'post_status'    => array_diff(
				get_post_stati(),
				['auto-draft', 'publish', 'trash', 'inherit',]
			),
			'posts_per_page' => 1,
			'author'         => get_current_user_id(),
		]);

		foreach ($results as $changeset) {
			return Option::fromValue($changeset);
		}
		return None::getInstance();
	}

	private function createNewChangeset() {
		$name = wp_generate_uuid4();
		$changeset = new AcChangeset($name);

		$postId = $this->saveChangeset($changeset);
		if ( is_wp_error($postId) || empty($postId) ) {
			return None::getInstance();
		}

		return Option::fromValue($changeset);
	}

	/**
	 * @param AcChangeset $changeset
	 * @param string|null $status
	 * @return int|\WP_Error Post ID on success, or an error object on failure.
	 */
	private function saveChangeset(AcChangeset $changeset, $status = null) {
		$postArray = [
			'post_content' => wp_json_encode($changeset->jsonSerialize(), JSON_PRETTY_PRINT),
		];

		$existingPostId = $changeset->getPostId();
		if ( $existingPostId !== null ) {
			$postArray['ID'] = $existingPostId;
		} else {
			//The new changeset must already have a name.
			$name = $changeset->getName();
			if ( empty($name) ) {
				return new \WP_Error('ame_changeset_missing_name', 'Changeset is missing a name.');
			}

			$postArray['post_type'] = self::CHANGESET_POST_TYPE;
			$postArray['post_status'] = 'auto-draft'; //Default.
			$postArray['post_name'] = $name;
			$postArray['post_title'] = $name;
			$postArray['post_author'] = get_current_user_id();
		}

		//Optionally, change the status.
		if ( $status !== null ) {
			$postArray['post_status'] = $status;
		}

		//Apparently, updating the post date extends the life of the auto-draft
		//and prevents it from being automatically deleted.
		if (
			//Is this post already an auto-draft?
			$existingPostId && (get_post_status($existingPostId) === 'auto-draft')
			//Will it remain an auto-draft?
			&& (empty($postArray['post_status']) || ($postArray['post_status'] === 'auto-draft'))
		) {
			$postArray['post_date'] = current_time('mysql');
			$postArray['post_date_gmt'] = '';
		}

		//Prevent WordPress from corrupting JSON data by "sanitizing" it.
		add_filter('wp_insert_post_data', [$this, 'preserveUnsanitizedPostContent'], 20, 3);
		if ( $existingPostId ) {
			$postId = wp_update_post(wp_slash($postArray), true);
		} else {
			$postId = wp_insert_post(wp_slash($postArray), true);
		}
		remove_filter('wp_insert_post_data', [$this, 'preserveUnsanitizedPostContent'], 20);

		if ( is_wp_error($postId) ) {
			return $postId;
		} else if ( $postId === 0 ) {
			return new \WP_Error('ame_changeset_save_failed', 'Could not save the changeset.');
		}

		if ( !$existingPostId ) {
			$changeset->setPostId($postId);
		}
		return $postId;
	}

	/**
	 *
	 * @param array $data
	 * @param array $postArr
	 * @param array $unsanitizedPostArr
	 * @return array
	 * @internal     This is a callback for the wp_insert_post_data filter.
	 *
	 * While the second argument is not used in this method, it is part of the filter
	 * signature and cannot be omitted because we need the third argument.
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function preserveUnsanitizedPostContent($data, $postArr = [], $unsanitizedPostArr = []) {
		//This is basically a reimplementation of what WP_Customize_Manager does.
		if (
			isset($unsanitizedPostArr['post_content'])
			&& isset($data['post_type'])
			&& (
				($data['post_type'] === self::CHANGESET_POST_TYPE)
				|| (
					($data['post_type'] === 'revision')
					&& !empty($data['post_parent'])
					&& (get_post_type($data['post_parent']) === self::CHANGESET_POST_TYPE)
				)
			)
		) {
			$data['post_content'] = $unsanitizedPostArr['post_content'];
		}
		return $data;
	}

	private function getCurrentChangeset() {
		return $this->currentChangeset;
	}

	/**
	 * @internal
	 */
	public function registerChangesetPostType() {
		$capability = $this->menuEditor->current_user_can_edit_menu()
			? 'manage_options'
			: 'do_not_allow';

		register_post_type(
			self::CHANGESET_POST_TYPE,
			[
				'public'           => false,
				'hierarchical'     => false,
				'rewrite'          => false,
				'query_var'        => false,
				'can_export'       => false,
				'delete_with_user' => false,
				'supports'         => ['title', 'author'],

				/* AFAICT, the "map_meta_cap" argument needs to be set to TRUE
				 * for WordPress to map meta capabilities like "edit_post" to
				 * context-specific capabilities like "edit_published_posts".
				 *
				 * Without it, checking for "edit_post" might just use the provided
				 * "edit_post" capability for all items regardless of their status.
				 */
				'map_meta_cap'     => true,
				'capability_type'  => self::CHANGESET_POST_TYPE,
				'capabilities'     => [
					'create_posts'           => $capability,
					'delete_others_posts'    => $capability,
					'delete_posts'           => $capability,
					'delete_private_posts'   => $capability,
					'delete_published_posts' => $capability,
					'edit_others_posts'      => $capability,
					'edit_posts'             => $capability,
					'edit_private_posts'     => $capability,
					'edit_published_posts'   => 'do_not_allow',
					'publish_posts'          => $capability,
					'read'                   => 'read',
					'read_private_posts'     => $capability,

					/*
					The "edit_post", "delete_post", and "read_post" keys are intentionally
					omitted. Using these keys makes WordPress treat the specified capability
					as a meta capability.

					For example, if we set "delete_post" to "manage_options", every time
					something checks the "manage_options" capability, WordPress will try
					to map it to "delete_post", which will fail without a post ID. This
					would effectively make the "manage_options" capability useless.
					*/
				],
			]
		);
	}

	public function ajaxSaveChangeset() {
		check_ajax_referer(self::SAVE_CHANGESET_ACTION);

		if ( !$this->menuEditor->current_user_can_edit_menu() ) {
			wp_send_json_error(
				['message' => 'You are not allowed to edit Admin Customizer changesets.'],
				403
			);
			//Technically, we don't need to call "exit" since wp_send_json_error()
			//will die(), but this lets my IDE know that execution stops here.
			exit;
		}

		$post = $this->menuEditor->get_post_params();

		/** Should we save the changeset even if some values are invalid? */
		$partialUpdatesAllowed = true;

		//The list of modified settings must be provided.
		if ( !isset($post['modified']) ) {
			wp_send_json_error(['message' => 'Missing "modified" parameter.'], 400);
			exit;
		}

		$modified = json_decode((string)($post['modified']), true);
		if ( !is_array($modified) ) {
			wp_send_json_error(
				['message' => 'Invalid "modified" parameter. It must be a JSON object.'],
				400
			);
			exit;
		}

		//We'll need the post type object to check user permissions.
		$postType = get_post_type_object(self::CHANGESET_POST_TYPE);
		if ( !$postType ) {
			//This should never happen unless there's a bug.
			wp_send_json_error(['message' => 'The changeset post type is missing.'], 500);
			exit;
		}

		$response = new AcSaveResponse();

		$createNewChangeset = empty($post['changeset']) && !empty($post['createNew']);
		if ( $createNewChangeset ) {
			if ( !current_user_can($postType->cap->create_posts) ) {
				wp_send_json_error(
					['message' => 'You are not allowed to create new changesets.'],
					403
				);
				exit;
			}

			$changeset = new AcChangeset(wp_generate_uuid4());
		} else {
			/**
			 * @var Option<AcChangeset> $csOption
			 */
			$csOption = $this->loadChangeset((string)($post['changeset']));
			if ( $csOption->isEmpty() ) {
				wp_send_json_error(['message' => 'Changeset not found.'], 400);
				exit;
			}
			$changeset = $csOption->get();

			if ( !current_user_can('edit_post', $changeset->getPostId()) ) {
				wp_send_json_error(
					$response->error('You do not have the required permissions to edit this changeset.'),
					403
				);
				exit;
			}
		}
		/** @var AcChangeset $changeset */

		//We need the setting objects to validate submitted values.
		$this->registerCustomizableItems();

		//Validate the submitted values.
		$validationResults = [];
		$successfulChanges = 0;
		foreach ($modified as $settingId => $value) {
			//Skip unmodified settings.
			if (
				$changeset->hasValueFor($settingId)
				&& ($changeset->getSettingValue($settingId) === $value)
			) {
				$response->addNote(sprintf('%s: %s', $settingId, 'New value is the same as old value.'));
				continue;
			}

			$validity = new AcValidationState();
			$validationResults[$settingId] = $validity;

			//The setting must exist.
			$setting = $this->getSettingById($settingId);
			if ( $setting === null ) {
				$validity->addError(new \WP_Error(
					'ame_invalid_setting_id',
					'Setting not found: ' . $settingId
				));
				$response->addNote(sprintf('%s: %s', $settingId, 'Setting not found.'));
				continue;
			}

			//Check user permissions.
			if ( !$setting->isEditableByUser() ) {
				$validity->addError(new \WP_Error(
					'ame_permission_denied',
					'You do not have permission to change this setting.'
				));
				$response->addNote(sprintf('%s: %s', $settingId, 'You do not have permission to change this setting.'));
				continue;
			}

			//Validate.
			$validationResult = $setting->validate(new \WP_Error(), $value);
			if ( is_wp_error($validationResult) ) {
				$validity->addError($validationResult);
				$response->addNote(sprintf('%s: Validation error: %s', $settingId, $validationResult->get_error_message()));
				continue;
			}
			$sanitizedValue = $validationResult;

			//Finally, update the value.
			$changeset->setSettingValue($settingId, $sanitizedValue);
			$successfulChanges++;

			$response->addNote(sprintf('%s: %s', $settingId, 'Value updated.'));
			$validity->setValid(true);
		}

		$response->setValidationResults($validationResults);
		$response->setChangeset($changeset);

		$hasValidationErrors = false;
		foreach ($validationResults as $validity) {
			if ( !$validity->isValid() ) {
				$hasValidationErrors = true;
				break;
			}
		}

		if ( $hasValidationErrors && !$partialUpdatesAllowed ) {
			wp_send_json_error(
				$response->error('There were one or more validation errors. Changes were not saved.'),
				400
			);
			exit;
		}

		//A published or trashed changeset cannot be modified.
		$currentStatus = $changeset->getStatus();
		if ( in_array($currentStatus, ['publish', 'future', 'trash']) ) {
			wp_send_json_error(
				$response->error('This changeset is already published or trashed, so it cannot be modified.'),
				400
			);
			exit;
		}

		//Will the status change?
		$newStatus = null;
		if (
			!empty($post['status'])
			&& AcChangeset::isSupportedStatus($post['status'])
			&& ($post['status'] !== $currentStatus)
		) {
			$newStatus = $post['status'];
		}

		//We don't support deleting changesets through this endpoint.
		if ( $newStatus === 'trash' ) {
			wp_send_json_error(
				$response->error('This endpoint does not support changeset deletion.'),
				400
			);
			exit;
		}

		//Is the user allowed to apply the new status?
		if ( $newStatus !== null ) {
			$postId = $changeset->getPostId();
			if ( ($newStatus === 'publish') || ($newStatus === 'future') ) {
				if ( $postId ) {
					$allowed = current_user_can('publish_post', $postId);
				} else {
					$allowed = current_user_can($postType->cap->publish_posts);
				}
				if ( !$allowed ) {
					wp_send_json_error(
						$response->error('You are not allowed to publish or schedule this changeset.'),
						403
					);
					exit;
				}
			}
		}

		$response->mergeWith(['updatedValues' => $successfulChanges]);

		if ( ($successfulChanges > 0) || $createNewChangeset || ($newStatus !== null) ) {
			$changeset->setLastModifiedToNow();
			$saved = $this->saveChangeset($changeset, $newStatus);
			if ( is_wp_error($saved) ) {
				wp_send_json_error(
					$response->error(
						'Error saving changeset: ' . $saved->get_error_message(),
						$saved->get_error_code()
					),
					500
				);
				exit;
			}

			//If the changeset was published or trashed, the customizer will need
			//a new changeset.
			if ( in_array($changeset->getStatus(), ['publish', 'future', 'trash']) ) {
				$this->createNewChangeset()->each(
					function (AcChangeset $newChangeset) use ($response) {
						$response->mergeWith(['nextChangeset' => $newChangeset->getName()]);
					}
				);
			}

			wp_send_json_success(
				$response->mergeWith([
					'message'               => 'Changeset saved.',
					'changesetWasPublished' => ($newStatus === 'publish'),
				]),
				200 //Requires WP 4.7+
			);
		} else {
			//We don't need to save the changeset if there are no actual changes,
			//but this is still technically a success.
			wp_send_json_success(
				$response->mergeWith(['message' => 'No changes were made.']),
				200 //Requires WP 4.7+
			);
		}
	}

	/**
	 * @param string $newStatus
	 * @param string $oldStatus
	 * @param \WP_Post $post
	 * @return void
	 */
	public function applyChangesOnPublish($newStatus, $oldStatus, $post) {
		if ( $oldStatus === $newStatus ) {
			return; //No change.
		}

		$isChangesetBeingPublished = ($newStatus === 'publish')
			&& ($post instanceof \WP_Post)
			&& ($post->post_type === self::CHANGESET_POST_TYPE);
		if ( !$isChangesetBeingPublished ) {
			return;
		}

		//Instantiate a changeset from the post.
		$option = AcChangeset::fromPost($post);
		if ( !$option->isDefined() ) {
			return; //Not a valid changeset.
		}
		/** @var AcChangeset */
		$changeset = $option->get();

		//Ensure settings are registered.
		$this->registerCustomizableItems();

		//Validate, update settings, then save all updated settings.
		$affectedSettings = [];
		foreach ($changeset as $settingId => $value) {
			$setting = $this->getSettingById($settingId);
			if ( $setting === null ) {
				continue;
			}

			$validationResult = $setting->validate(new \WP_Error(), $value, true);
			if ( is_wp_error($validationResult) ) {
				continue;
			}
			$sanitizedValue = $validationResult;

			$setting->update($sanitizedValue);
			$affectedSettings[] = $setting;
		}

		AbstractSetting::saveAll($affectedSettings);

		//Trash the changeset after it has been applied. We don't want to keep old
		//changesets in the database - they take up space and serve no useful purpose.
		wp_trash_post($post->ID);
	}
}

class AcChangeset implements \IteratorAggregate, \JsonSerializable, \Countable {
	/**
	 * @var array<string,mixed>
	 */
	private $settingValues = [];

	private $postId = null;
	private $name = null;
	private $lastModified;

	private $status = null;

	public function __construct($name = null) {
		if ( $name !== null ) {
			$this->name = $name;
		}
		$this->lastModified = time();
	}

	/** @noinspection PhpLanguageLevelInspection */
	#[\ReturnTypeWillChange]
	public function getIterator() {
		return new \ArrayIterator($this->settingValues);
	}

	/** @noinspection PhpLanguageLevelInspection */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'settings'     => $this->settingValues,
			'lastModified' => $this->lastModified,
		];
	}

	/**
	 * @param \WP_Post $post
	 * @return Option<AcChangeset>
	 */
	public static function fromPost(\WP_Post $post) {
		return static::fromJson($post->post_content)->each(
			function ($changeset) use ($post) {
				$changeset->postId = $post->ID;
				$changeset->name = $post->post_name;
			}
		);
	}

	/**
	 * @param string $jsonString
	 * @return Option<AcChangeset>
	 */
	public static function fromJson($jsonString) {
		$data = json_decode($jsonString, true);
		if ( !is_array($data) ) {
			return None::getInstance();
		}
		return Option::fromValue(static::fromArray($data));
	}

	/**
	 * @param $data
	 * @return static
	 */
	public static function fromArray($data) {
		$changeset = new static();
		$changeset->settingValues = $data['settings'];
		$changeset->lastModified = isset($data['lastModified']) ? intval($data['lastModified']) : null;
		return $changeset;
	}

	/**
	 * @param null|int $postId
	 * @return AcChangeset
	 */
	public function setPostId($postId) {
		if ( $this->postId !== null ) {
			throw new \RuntimeException('Cannot change the post ID of an existing changeset.');
		}
		$this->postId = $postId;
		return $this;
	}

	/**
	 * @return null|int
	 */
	public function getPostId() {
		return $this->postId;
	}

	/**
	 * @return string|null
	 */
	public function getName() {
		return $this->name;
	}

	public function setSettingValue($settingId, $value) {
		$this->settingValues[$settingId] = $value;
	}

	/**
	 * @param string $settingId
	 * @return mixed|null
	 */
	public function getSettingValue($settingId) {
		if ( array_key_exists($settingId, $this->settingValues) ) {
			return $this->settingValues[$settingId];
		}
		return null;
	}

	/**
	 * @param string $settingId
	 * @return bool
	 */
	public function hasValueFor($settingId) {
		return array_key_exists($settingId, $this->settingValues);
	}

	public static function isSupportedStatus($status) {
		return in_array($status, [
			'draft',
			'auto-draft',
			'publish',
			'future',
			'trash',
			'private',
			'pending',
		]);
	}

	/**
	 * @return string|null
	 */
	public function getStatus() {
		if ( !empty($this->postId) ) {
			$status = get_post_status($this->postId);
			if ( $status ) {
				return $status;
			}
		}
		return $this->status;
	}

	/** @noinspection PhpLanguageLevelInspection */
	#[\ReturnTypeWillChange]
	public function count() {
		return count($this->settingValues);
	}

	/**
	 * @return int|null
	 */
	public function getLastModified() {
		return $this->lastModified;
	}

	public function setLastModifiedToNow() {
		$this->lastModified = time();
	}
}

class AcValidationState implements \JsonSerializable {
	/**
	 * @var bool
	 */
	protected $isValid = true;
	/**
	 * @var \WP_Error[]
	 */
	protected $errors = [];

	public function addError(\WP_Error $error, $markAsInvalid = true) {
		$this->errors[] = $error;
		if ( $markAsInvalid ) {
			$this->isValid = false;
		}
	}

	public function setValid($valid) {
		$this->isValid = $valid;
	}

	/** @noinspection PhpLanguageLevelInspection */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		//Convert WP_Error instances to JSON-compatible associative arrays.
		$serializableErrors = [];
		foreach ($this->errors as $error) {
			foreach ($error->errors as $code => $messages) {
				foreach ($messages as $message) {
					$serializableErrors[] = [
						'code'    => $code,
						'message' => $message,
					];
				}
			}
		}

		return [
			'isValid' => $this->isValid,
			'errors'  => $serializableErrors,
		];
	}

	/**
	 * @return bool
	 */
	public function isValid() {
		return $this->isValid;
	}
}

class AcSaveResponse implements \JsonSerializable {
	private $fields = [];
	private $notes = [];
	/**
	 * @var AcChangeset|null
	 */
	private $changeset = null;

	/**
	 * @param array $additionalFields
	 * @return $this
	 */
	public function mergeWith($additionalFields) {
		$this->fields = array_merge($this->fields, $additionalFields);
		return $this;
	}

	public function error($message, $code = null) {
		$this->fields['message'] = $message;
		if ( $code !== null ) {
			$this->fields['code'] = $code;
		}
		return $this;
	}

	/**
	 * @param array $validationResults
	 * @return $this
	 */
	public function setValidationResults($validationResults) {
		$this->fields['validationResults'] = $validationResults;
		return $this;
	}

	public function setChangeset(AcChangeset $changeset) {
		$this->changeset = $changeset;
	}

	/**
	 * @param string $message
	 * @return $this
	 */
	public function addNote($message) {
		$this->notes[] = $message;
		return $this;
	}

	/** @noinspection PhpLanguageLevelInspection */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		$result = $this->fields;
		if ( $this->changeset !== null ) {
			$result = array_merge($result, [
				'changeset'          => $this->changeset->getName(),
				'changesetItemCount' => count($this->changeset),
				'changesetStatus'    => $this->changeset->getStatus(),
			]);
		}
		if ( !empty($this->notes) ) {
			$result['notes'] = $this->notes;
		}
		return $result;
	}
}