<?php

// Old website redirects
if ( rtrim($_SERVER['REQUEST_URI'],'/') == '/sofas' ) { header('location: https://lqupholstery.com/furniture_category/sofas/'); die(); }
if ( rtrim($_SERVER['REQUEST_URI'],'/') == '/sofas/nggallery/page/2' ) { header('location: https://lqupholstery.com/furniture_category/sofas/'); die(); }
if ( rtrim($_SERVER['REQUEST_URI'],'/') == '/sofas/the-process' ) { header('location: https://lqupholstery.com/about'); die(); }
if ( rtrim($_SERVER['REQUEST_URI'],'/') == '/case/amanda-nisbet-design-inc' ) { header('location: https://lqupholstery.com/about'); die(); }

get_header();
include('section-top-navigation.php');
?>
<section class="generic-page"><div>

  <div class="generic-content">
    <?php
    echo '<h1>Page not Found (404)</h1>';
    echo '<p><a href="/">Continue to our homepage.</a></p>';
    ?>
  </div>


</div></section>
<?php
get_footer();
?>
