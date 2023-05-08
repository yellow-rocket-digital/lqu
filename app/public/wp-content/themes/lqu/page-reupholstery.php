<?php

$show_reupholstery = current_user_can('edit_posts') ? true : get_field('show_reupholstery','option');
if (!$show_reupholstery) die();

the_post();
get_header();
$top_navigation_color_theme = 'white';
include('section-top-navigation.php');
include('section-re-upholsteries.php');
include('section-category-pathing.php');
include('section-commitment.php');
include('section-location.php');
include('section-case-study-example.php');
get_footer();
?>
