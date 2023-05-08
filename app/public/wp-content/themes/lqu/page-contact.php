<?php
the_post();
get_header();
$top_navigation_color_theme = 'tan';
include('section-top-navigation.php');

include('section-contact-form.php'); //title and intro similar to overview
include('section-contact-visit.php'); //similar to locations, title and location similar to overview

include('section-category-pathing.php');
include('section-commitment.php');
include('section-case-study-example.php');
get_footer();
?>
