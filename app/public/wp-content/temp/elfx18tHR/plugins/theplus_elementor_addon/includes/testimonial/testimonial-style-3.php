<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$postid=get_the_ID();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="testimonial-list-content">
		<div class="testimonial-content-text">
			<?php include THEPLUS_INCLUDES_URL. 'testimonial/post-meta-title.php'; ?>
			<?php include THEPLUS_INCLUDES_URL. 'testimonial/get-excerpt.php'; ?>			
		</div>
		<div class="post-content-image">
			<?php include THEPLUS_INCLUDES_URL. 'testimonial/format-image.php'; ?>
			<div class="author-left-text">
				<?php include THEPLUS_INCLUDES_URL. 'testimonial/post-title.php'; ?>
				<?php include THEPLUS_INCLUDES_URL. 'testimonial/post-meta-designation.php'; ?>
			</div>
		</div>		
	</div>
</article>
