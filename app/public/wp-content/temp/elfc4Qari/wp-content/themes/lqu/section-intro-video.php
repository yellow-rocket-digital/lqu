<?php
	$video = get_field('video');
	$video_url = $video['url'];
	$video_width = $video['width']; //568
	$video_height = $video['height']; //320
	//print_r($video);
?>
<section class="intro-video">
	<div class="video">
		<video
			width="<?=$video_width ?>"
			height="<?=$video_height ?>"
			autoplay
			muted
			loop
			playsinline
			<?php // https://stackoverflow.com/questions/10797632/simulate-background-sizecover-on-video-or-img ?>
			style="

	    height: 100%;
	    width: <?=(100*$video_width/$video_height) // 100 * w / h ?>vh;
	    min-width: 100%;
	    min-height: <?=(100*$video_height/$video_width) // 100 * h / w ?>vw;

			"
		>
	  	<source src="<?=$video_url ?>" type="video/mp4">
		</video>
	</div>
	<div class="curtain">


		<div
			class="top"
			style="background-image:url('/wp-content/themes/lqu/images/Q-hole-3.svg');"
		>
			<div class="left"></div>
			<div class="right"></div>
			<div class="q"></div>
			<div class="below_q"></div>
		</div>

		<div class="bottom">
			<div class="logo">
				<img
					src="<?=get_template_directory_uri() ?>/images/logo_mobile_with_2px_padding_100x45.svg"
					width="250" height="112.5"
					alt="LQU"
				/>
			</div>
			<div class="lqu-name">Luther Quintana Upholstery, Inc</div>
			<div class="lqu-text">Upholstery and Fabrications Since&nbsp;1987</div>
		</div>

	</div>
</section>
