<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "container" div.
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 1.0
 */

?>
<!doctype html>
<html class="no-js" <?php language_attributes(); ?> >
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<link rel="icon" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/icons/favicon.ico" type="image/x-icon">
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/icons/apple-touch-icon-144x144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/icons/apple-touch-icon-114x114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/icons/apple-touch-icon-72x72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/icons/apple-touch-icon-precomposed.png">
		
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
	<?php do_action( 'foundationpress_after_body' ); ?>

<div id="large-wrapper">

    <div id="header">
        <div id="logo">
            <h1><a href="<?php bloginfo( 'url' ); ?>" title="<?php bloginfo( 'name' ); ?> | "><?php bloginfo( 'name' ); ?></a></h1>
        </div>
        <nav id="menu" class="top-bar" data-topbar role="navigation">
            <section>
                <?php foundationpress_top_bar_r(); ?>
            </section>
            <section class="top-bar-section hide-for-small-only">
                <?php foundationpress_top_bar_l(); ?>
            </section>
            <section class="top-bar-section show-for-small-only">


              <section class="top-bar-section show-for-small-only">
                <ul class="mobile-menu show-for-small-only">
                  <li class="has-dropdown">
                    <a href="#">Menu</a>
                    <ul class="dropdown">
                        <li class="close"><a href="#" title="#">&#215;</a></li>
                        <?php wp_nav_menu( array('menu' => 'Main menu' )); ?>
                    </ul>
                  </li>
                </ul>
              </section>


            </section>
        </nav>
    </div>
	
	<?php do_action( 'foundationpress_layout_start' ); ?>

	<?php do_action( 'foundationpress_after_header' ); ?>
