<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	global $post;
	$featured_image_url = wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ) );
	$tsize='';
	if(! empty( $featured_image_url )){
		if($display_thumbnail=='yes' && !empty($thumbnail)){
			$tsize = $thumbnail;
		}else{
			$tsize = 'tp-image-grid';
		}
		$featured_image= tp_get_image_rander( get_the_ID(), $tsize,[], 'post' );
	}else{
		$featured_image=theplus_get_thumb_url();
		$featured_image=$featured_image='<img src="'.esc_url($featured_image).'" alt="'.esc_attr(get_the_title()).'">';
	}
	
?>
	<div class="testimonial-featured-image">
		<span class="thumb-wrap">
			<?php echo $featured_image; ?>
		</span>
	</div>