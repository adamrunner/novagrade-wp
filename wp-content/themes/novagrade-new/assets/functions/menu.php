<?php
// Register menus
register_nav_menus(
	array(
        'top-menu' => __( 'Top Menu', 'jointswp' ),
		'main-menu' => __( 'The Main Menu', 'jointswp' ),
		'mobile-menu' => __( 'The Mobile Menu', 'jointswp' )
	)
);

// The Top Menu
function joints_top_menu() {
	 wp_nav_menu(array(
    'container' => false,                           // Remove nav container
    'menu_class' => 'menu',                        // Adding custom nav class
    'theme_location' => 'top-menu',        			// Where it's located in the theme
    'depth' => 5,                                   // Limit the depth of the nav
    'fallback_cb' => false,                         // Fallback function (see below)
    'walker' => new Top_Menu_Walker()
    ));
}
/* End Top Menu */

/* The Main Menu */
function joints_main_menu() {
	 wp_nav_menu(array(
    'container' => false,                           // Remove nav container
    'items_wrap' => '<ul id="%1$s" class="%2$s" data-dropdown-menu>%3$s</ul>',
    'menu_class' => 'dropdown menu main-menu',           // Adding custom nav class
    'theme_location' => 'main-menu',                // Where it's located in the theme
    'depth' => 2,                                   // Limit the depth of the nav
    'fallback_cb' => false,                         // Fallback function (see below)
    'walker' => new Main_Menu_Walker()
    ));
}
/* End Main Menu */

// The Mobile Menu
function joints_mobile_menu() {
	 wp_nav_menu(array(
    'container' => false,                           // Remove nav container
    'container_class' => '',                        // Class of container
    'menu_class' => 'mobile-menu',                  // Adding custom nav class
    'theme_location' => 'mobile-menu',              // Where it's located in the theme
    'fallback_cb' => false,                         // Fallback function (see below)
    'walker' => new Mobile_Menu_Walker()
    ));
}
// The Mobile Menu