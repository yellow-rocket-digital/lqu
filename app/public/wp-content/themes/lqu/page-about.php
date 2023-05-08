<?php
the_post();
get_header();
$top_navigation_color_theme = 'tan';
include('section-top-navigation.php');

include('section-about-introduction.php');
include('section-about-team.php');
include('section-about-services-overview.php');
include('section-about-process.php');

include('section-category-pathing.php');
include('section-location.php');
include('section-case-study-example.php');
get_footer();
?>
