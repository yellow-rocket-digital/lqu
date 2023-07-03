<?php
$furniture_categories = get_furniture_categories();
$show_reupholstery = current_user_can('edit_posts') ? true : get_field('show_reupholstery','option');


function the_social_links() {
  ?>
  <?=( (trim(get_field('facebook','option'))) ? '<a target="_blank" href="'.get_field('facebook','option').'"><i class="fab fa-facebook-f"></i></a>' : '') ?>
  <?=( (trim(get_field('instagram','option'))) ? '<a target="_blank" href="'.get_field('instagram','option').'"><i class="fab fa-instagram"></i></a>' : '') ?>
  <?=( (trim(get_field('pinterest','option'))) ? '<a target="_blank" href="'.get_field('pinterest','option').'"><i class="fab fa-pinterest"></i></a>' : '') ?>
  <?=( (trim(get_field('houzz','option'))) ? '<a target="_blank" href="'.get_field('houzz','option').'"><i class="fab fa-houzz"></i></a>' : '') ?>
  <?=( (trim(get_field('twitter','option'))) ? '<a target="_blank" href="'.get_field('twitter','option').'"><i class="fab fa-twitter"></i></a>' : '') ?>
  <?=( (trim(get_field('google_business','option'))) ? '<a target="_blank" href="'.get_field('google_business','option').'"><i class="fab fa-google"></i></a>' : '') ?>
  <?=( (trim(get_field('linkedin','option'))) ? '<a target="_blank" href="'.get_field('linkedin','option').'"><i class="fab fa-linkedin-in"></i></a>' : '') ?>

  <?php
}

?>
<section class="footer"><div>
  <div class="footer-q">
    <img
      width="600"
      height="290"
      src="<?=get_template_directory_uri() ?>/images/Q-footer.svg">
    </img>
  </div>
  <nav class="furniture">
    <ul>
      <li>
        <span class="toggle">Custom Order</span>
        <ul>
					  <li><a href="/product-category/custom-order/headboards-beds/">Beds</a></li>
                      <li><a href="/product-category/custom-order/chairs/">Chairs</a></li>
					  <li><a href="/product-category/custom-order/chaises/">Chaises</a></li>          
					  <li><a href="/product-category/custom-order/dining-chairs/">Dining Chairs</a></li>
			          <li><a href="/product-category/custom-order/draperies/">Draperies</a></li>
                      <li><a href="/product-category/custom-order/headboards-beds/">Headboards</a></li>
                      <li><a href="/product-category/custom-order/ottomans/">Ottomans</a></li>
			          <li><a href="/product-category/custom-order/pillows/">Pillows</a></li>
                      <li><a href="/product-category/custom-order/slipper-chairs/">Slipper Chairs</a></li>
                      <li><a href="/product-category/custom-order/sofas/">Sofas</a></li>
                    <li><a href="/product-category/custom-order/samples">Samples</a></li>

          <!--
          <?php if ($show_reupholstery) { ?>
          <li><br /><a href="/reupholstery">Reupholstery</a></li>
          <li><a href="/draperies">Draperies</a></li>
          <?php } ?>
          -->

        </ul>
      </li>
    </ul>
  </nav>
  <nav class="contact">
    <ul>
      <li>
        <span><a href="/contact">Contact</a></span>
        <ul>
          <li><a href="/contact">Request a Quote</a></li>
          <li><a href="/contact#visit">Visit our Showrooms</a></li>
          <li><a href="/faq">FAQ</a></li>

          <li class="social-icons">
            <?php the_social_links(); ?>
          </li>

        </ul>
      </li>
    </ul>
  </nav>
  <div class="text">
    <?=get_field('footer_text','option') ?>
  </div>
  <div class="copyright">
    &copy; <?=date('Y') ?> Luther Quintana Upholstery
  </div>
  <div class="privacy">
    <a href="/privacy-policy">Privacy Policy</a>
  </div>
</div></section>

<?php
if ($post and $post->post_name !== 'contact') {
  include('inquire-form.php');
}
?>

<nav class="main-mobile-nav">
  <ul>
    <li class="drop-down shown"><?php // start shown ?>
      <span>Custom Furniture</span>
      <ul>
        <?php
        $fc = get_furniture_categories();
        foreach($fc as $c) {
          ?>
          <li><a href="<?=get_tag_link($c) ?>"><?=$c->name ?></a></li>
          <?php
        }
        ?>
        <li><a href="/wood-finishes">Wood Finishes</a></li>
      </ul>
    </li>
    <?php if ($show_reupholstery) { ?>
    <li><a href="/reupholstery">Reupholstery</a></li>
    <?php } ?>
    <li><a href="/case-studies">Case Studies</a></li>
    <li><a href="/about">About &amp; Process</a></li>
    <li><a href="/contact">Visit Us</a></li>
    <li class="social-icons">
        <?php the_social_links(); ?>
    </li>
  </ul>
</nav>

<?php wp_footer(); ?>

</body>
</html>
