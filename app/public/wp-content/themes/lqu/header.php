<!doctype html>
<html>
<head>
	<!--






	Luther Quintana Upholstery
	Copywrite <?=date('Y') ?>
	Luther Quintana Upholstery, Content
	David F. Choy, Development for King Cow Interactive LLC
	Marissa Rivera, Design






	-->
	<meta content="width=device-width, initial-scale=1" name="viewport">
	<!-- Old IE Support -->
	<meta content="IE=edge" http-equiv="X-UA-Compatible">
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	<script src='https://www.google.com/recaptcha/api.js'></script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

	<?php wp_head(); ?>
	<?php the_social_meta($post) ?>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-169312772-1"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'UA-169312772-1');
	</script>
	<title><?php bloginfo('name'); ?><?php wp_title('|'); ?></title>

	<link rel="apple-touch-icon" sizes="57x57" href="<?=get_template_directory_uri() ?>/images/favicon/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="<?=get_template_directory_uri() ?>/images/favicon/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="<?=get_template_directory_uri() ?>/images/favicon/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="<?=get_template_directory_uri() ?>/images/favicon/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="<?=get_template_directory_uri() ?>/images/favicon/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="<?=get_template_directory_uri() ?>/images/favicon/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="<?=get_template_directory_uri() ?>/images/favicon/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="<?=get_template_directory_uri() ?>/images/favicon/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="<?=get_template_directory_uri() ?>/images/favicon/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192"  href="<?=get_template_directory_uri() ?>/images/favicon/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="<?=get_template_directory_uri() ?>/images/favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="<?=get_template_directory_uri() ?>/images/favicon/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?=get_template_directory_uri() ?>/images/favicon/favicon-16x16.png">
	<link rel="manifest" href="<?=get_template_directory_uri() ?>/images/favicon/manifest.json">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="<?=get_template_directory_uri() ?>/images/favicon/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">
</head>
<body <?php body_class(); ?>>

<?php
$post_type = get_post_type( get_the_ID() );
if ($post_type == 'product') require('section-top-navigation.php');
?>
<?php
/*
if ( is_front_page() ) {
	include('section-intro-video.php');
}
include('section-top-navigation.php');
*/
?>

<!--
<div class="page"><div class="page-inner">
-->
