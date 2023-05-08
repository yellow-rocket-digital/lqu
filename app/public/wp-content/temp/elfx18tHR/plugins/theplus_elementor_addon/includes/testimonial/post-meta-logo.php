<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	if($display_thumbnail=='yes' && !empty($thumbnail)){
		$testimonial_logo= tp_get_image_rander( get_the_ID(), $thumbnail,[], 'post' );
	}else{
		$testimonial_logo = get_post_meta(get_the_id(), 'theplus_testimonial_logo', true); 		
	}
	
if(!empty($testimonial_logo)){ 
?>
	<div class="testimonial-author-logo"><img src="<?php echo esc_url($testimonial_logo); ?>" /></div>
<?php } ?>