<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!isset($post_title_tag) && empty($post_title_tag)){
	$post_title_tag='h3';
}
$client_url = get_post_meta(get_the_id(), 'theplus_clients_url', true);

?>
<<?php echo theplus_validate_html_tag($post_title_tag); ?> class="post-title">
	<?php
	if(!empty($disable_link) && $disable_link=='yes'){ 
		echo '<div>'.esc_html(get_the_title()).'</div>';
	}else{ 
		?>
		<a href="<?php echo esc_url($client_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html(get_the_title()); ?></a>
		<?php				
	} ?>
	
</<?php echo theplus_validate_html_tag($post_title_tag); ?>>