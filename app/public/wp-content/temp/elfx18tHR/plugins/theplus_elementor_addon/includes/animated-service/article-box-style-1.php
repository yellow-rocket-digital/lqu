<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="article-box-inner-content">
<div class="article-box-img"><img src="<?php echo $featured_image;?>"></div>
<div class="article-overlay">
	<div class="article-box-content">
		<?php echo $list_title.$description.$loop_button; ?>
	</div>
</div>
</div>
