<?php
if ( !isset($top_navigation_color_theme) ) {
	//tan, white
	$top_navigation_color_theme = 'tan';
}
$show_reupholstery = current_user_can('edit_posts') ? true : get_field('show_reupholstery','option');
?>
<section class="top-navigation color-theme-<?=$top_navigation_color_theme ?>">
	<nav>
		<ul class="left">
			<li class="text-link mobile-link menu">
				<a href="#menu" class="mobile-nav-menu-button">
					Menu
				</a>
			</li>

			<li class="text-link desktop-link">
				<span>
					<span>Custom</span> <span>Order</span>
				</span>
				<ul>
					<li><a href="/product-category/custom-order/beds/">Beds</a></li>
					<li><a href="/product-category/custom-order/chairs/">Chairs</a></li>
					<li><a href="/product-category/custom-order/chaises/">Chaises</a></li>
					<li><a href="/product-category/custom-order/dining-chairs/">Dining Chairs</a></li>
					<li><a href="/product-category/custom-order/draperies/">Draperies</a></li>
					<li><a href="/product-category/custom-order/headboards/">Headboards</a></li>
					<li><a href="/product-category/custom-order/ottomans/">Ottomans</a></li>
					<li><a href="/product-category/custom-order/pillows/">Pillows</a></li>
					<li><a href="/product-category/custom-order/slipper-chairs/">Slipper Chairs</a></li>
					<li><a href="/product-category/custom-order/sofas/">Sofas</a></li>
					<li><a href="/product-category/custom-order/samples/">Samples</a></li>
				</ul>
			</li>
		
			<li class="text-link desktop-link">
				<a href="/reupholstery"><span>Reupholstery</span></a>
			</li>
			
			<li class="text-link desktop-link">
				<a href="/mercado"><span>Mercado</span></a>
			</li>
			
		</ul>

		<div class="logo">
			<a href="/">
				<?php
				/*
				<img
					src="<?=get_template_directory_uri() ?>/images/logo_mobile_with_2px_padding_100x45.svg"
					width="100" height="45"
					alt="LQU"
				/>
				*/
				?>
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 45" width="100" height="45">
					<!--
				  <defs>
				    <style>
				      .logo-fill { fill:#232960; }
				    </style>
				  </defs>
					-->
				  <path
				    class="logo-fill logo-letter-l"
				    d="M9.7,28l0,.82q0,2.81.54,3.33t3.51.51a24.65,24.65,0,0,0,7.61-1q3.23-1,4.11-2.75.33-.63.66-.63c.25,0,.36.16.35.47A6.39,6.39,0,0,1,25,32.07q-2,2.53-6.53,2.54c-.5,0-1.08,0-1.73-.07l-2.53-.19c-1.49-.11-3-.16-4.71-.16a34.08,34.08,0,0,0-4.82.28c-.55.08-1,.12-1.32.14s-.58-.12-.58-.41.21-.43.63-.55a3.59,3.59,0,0,0,2.18-1.44A6.71,6.71,0,0,0,6.12,29l0-1.08V7q0-3.12-2.53-3.63l-1-.21C2.19,3.06,2,2.91,2,2.68s.26-.52.79-.52q.54,0,1.47.09c1.06.09,2.25.14,3.58.14s2.73,0,3.9-.12l1,0c.37,0,.56.11.56.34s-.23.56-.68.66l-.82.19q-2.1.51-2.1,4.19Z"/>
				  <path
				    class="logo-fill logo-letter-q"
				    d="M43.29,3.09A10.42,10.42,0,0,0,35,6.8a14.3,14.3,0,0,0-3.19,9.66q0,8.63,5.41,13.56a13.2,13.2,0,0,0,9.07,3.68,10.55,10.55,0,0,0,8.34-3.63,14,14,0,0,0,3.17-9.51A18.74,18.74,0,0,0,53.57,8.18,12.92,12.92,0,0,0,43.29,3.09M35,32.22a16.56,16.56,0,0,1-6.95-13.69A17.28,17.28,0,0,1,31.9,7.23q4.55-5.52,12.78-5.52A15.79,15.79,0,0,1,61.17,18.29,17.78,17.78,0,0,1,59,26.93a15.07,15.07,0,0,1-5.82,6,19,19,0,0,1-5.54,1.94c1.57.8,2.67,1.39,3.3,1.76l2.17,1.43,1.78,1.16a6.81,6.81,0,0,0,3.34,1.31,7,7,0,0,0,3.42-.79,1.75,1.75,0,0,1,.65-.26c.27,0,.41.1.42.35,0,.57-.7,1.17-2.21,1.8a11.36,11.36,0,0,1-4.33.94,13.72,13.72,0,0,1-6.62-2.23C47.33,39,46,38.25,45.58,38L41.65,36q-3.51-1.86-5.14-1.86a3.73,3.73,0,0,0-2.62,1.15A4.74,4.74,0,0,0,32.44,38c-.1.57-.29.85-.57.85s-.61-.25-.58-.75a7.52,7.52,0,0,1,1.2-3.56A5.62,5.62,0,0,1,35,32.22"/>
				  <path
				    class="logo-fill logo-letter-u"
				    d="M93.6,20.79V8.68a8.15,8.15,0,0,0-.84-4.24,3.52,3.52,0,0,0-2.85-1.51c-.46-.05-.68-.22-.68-.51s.15-.49.46-.48a14.77,14.77,0,0,1,1.5.19,13,13,0,0,0,2.12.14c1.74,0,2.82,0,3.22-.07l1-.07c.29,0,.44.14.44.41s-.16.41-.49.55a3.12,3.12,0,0,0-1.85,1.64,12.44,12.44,0,0,0-.42,4V20.29q0,6.39-2.55,9.83a11.11,11.11,0,0,1-4.62,3.47,16,16,0,0,1-6.42,1.3,16.15,16.15,0,0,1-7.16-1.55,10.19,10.19,0,0,1-4.66-4.18,16.51,16.51,0,0,1-1.66-7.94V9.34l0-1.34a6.23,6.23,0,0,0-.69-3.45A4,4,0,0,0,64.66,3.3Q64,3.21,64,2.82c0-.36.29-.55.89-.55.39,0,.92,0,1.61,0s1.24.07,1.57.07l1.8,0h1.78c.19,0,.62,0,1.31-.1s1-.07,1.19-.07.54.12.54.37-.25.48-.75.57a2.48,2.48,0,0,0-1.81,1.18,7.37,7.37,0,0,0-.46,3.14l0,1.55V20.79q0,6,1.87,8.6Q76.28,33,82.5,33q5.58,0,8.34-3.05t2.76-9.2"/>
				</svg>
			</a>
		</div>

		<ul class="right">
			<li class="text-link desktop-link">
				<a href="/case-studies"><span>Case</span> <span>Studies</span></a>
			</li>
			<li class="text-link desktop-link">
				<a href="/about"><span>About &amp;</span> <span>Process</span></a>
			</li>
			<li class="text-link desktop-link">
				<a href="/contact">
					<span class="visit">Visit <span class="us">Us</span> <span class="and">&amp;</span></span>
					<span class="contact">Contact</span>
				</a>
			</li>
			<li class="text-link mobile-link contact">
				<a href="/contact">
					Contact
				</a>
			</li>
			<li class="text-link desktop-link">
				<a href="/my-account">
					<?php if (is_user_logged_in()): ?>
						<span>My Account</span>
					<?php else: ?>
						<span>Login</span>
					<?php endif; ?>
				</a>
			</li>
		</ul>

	</nav>
</section>
