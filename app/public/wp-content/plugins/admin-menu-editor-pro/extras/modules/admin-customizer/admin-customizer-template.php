<?php
/**
 * These variables should be provided by the module.
 *
 * @var \YahnisElsts\AdminMenuEditor\Customizable\Settings\AbstractSetting[] $settings
 * @var \YahnisElsts\AdminMenuEditor\Customizable\Controls\InterfaceStructure $structure
 * @var AcChangeset $currentChangeset
 */

use YahnisElsts\AdminMenuEditor\AdminCustomizer\AcChangeset;

//Hide the Toolbar (a.k.a  Admin Bar). Using show_admin_bar(false) would not work
//because WordPress ignores it when is_admin() is true and shows the bar anyway.
if ( !defined('IFRAME_REQUEST') ) {
	define('IFRAME_REQUEST', true);
}

header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

//This template uses some internal WP core functions to generate a bare-bones
//admin page without most of the regular WP admin UI. It won't work if the functions
//are not available.
$requiredFunctions = [
	'_wp_admin_html_begin',
	'print_head_scripts',
	'print_admin_styles',
	'_wp_footer_scripts',
];
foreach ($requiredFunctions as $functionName) {
	if ( !function_exists($functionName) ) {
		wp_die(sprintf(
			'Error: This feature is not compatible with your WordPress version.' .
			' The required function <code>%s</code> is not defined.',
			esc_html($functionName)
		));
	}
}

wp_user_settings();
_wp_admin_html_begin();

$bodyClasses = ['wp-core-ui'];
if ( is_rtl() ) {
	$bodyClasses[] = ' rtl';
}
$bodyClasses[] = ' locale-' . sanitize_html_class(strtolower(str_replace('_', '-', get_user_locale())));

$pageTitle = 'Admin Customizer';
?>
	<title><?php echo esc_html($pageTitle); ?></title>
<?php

print_head_scripts();
print_admin_styles();

echo '</head>';

if ( empty($currentChangeset) ) {
	wp_die('Changeset not defined');
}
if ( empty($currentChangeset->getName()) ) {
	wp_die('Changeset does not have a name');
}
?>
	<body class="<?php echo esc_attr(implode(' ', $bodyClasses)); ?>">

	<div id="ame-ac-admin-customizer">
		<div id="ame-ac-sidebar">
			<div id="ame-ac-primary-actions">
				<a id="ame-ac-exit-admin-customizer"><span class="screen-reader-text">Close</span></a>
				<span class="spinner"></span>
				<?php
				submit_button(
					'Save Changes',
					'primary',
					'apply-changes',
					false,
					[
						'id'                  => 'ame-ac-apply-changes',
						'data-default-text'   => 'Save Changes',
						'data-published-text' => 'Saved',
						'disabled'            => 'disabled', //Disabled by default, enabled when changes are detected.
					]
				);
				?>
			</div>
			<div id="ame-ac-sidebar-info">
				<div id="ame-ac-global-notification-area"></div>
			</div>
			<div id="ame-ac-sidebar-content">
				<div id="ame-ac-container-collection"
				     data-bind="component: {
				        name: 'ame-ac-structure',
				        params: {structure: interfaceStructure, breadcrumbs: sectionNavigation.breadcrumbs}}">
				</div>
				<div id="ame-ac-sidebar-blocker-overlay"></div>
			</div>
		</div>
		<div id="ame-ac-preview-container">
			<iframe id="ame-ac-preview" name="ame-ac-preview-frame" src="about:blank">
				Preview
			</iframe>
			<div id="ame-ac-preview-refresh-indicator" title="Refreshing the preview...">
				<div id="ame-ac-refresh-spinner"></div>
			</div>
		</div>
	</div>

	<?php
	do_action('admin_menu_editor-admin_customizer_footer');

	//Some WordPress components like the visual editor want to load their scripts
	//and styles in the admin footer. Special-casing each dependency is too complex
	//and prone to bugs, so we'll just trigger the footer hooks instead.

	//In case some plugin developer tries to add content to all admin pages,
	//we'll also wrap all output in a hidden element.
	echo '<div id="ame-ac-hidden-footer-content" style="display:none;">';

	do_action('admin_footer', '');

	if ( !empty($GLOBALS['hook_suffix']) ) {
		do_action('admin_print_footer_scripts-' . $GLOBALS['hook_suffix']);
	}
	do_action('admin_print_footer_scripts');

	echo '</div>';
	?>

	<div id="ame-ac-templates" style="display:none">
		<div id="ame-ac-validation-error-list-template">
			<ul class="ame-ac-validation-errors" data-bind="foreach: $root">
				<li class="notice notice-error ame-ac-validation-error">
					<span data-bind="text: message, attr: {title: code}"></span>
				</li>
			</ul>
		</div>
	</div>
	</body>
<?php
echo '</html>';