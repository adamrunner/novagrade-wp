<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link https://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'doug6875_test');

/** MySQL database username */
define('DB_USER', 'doug6875_user');

/** MySQL database password */
define('DB_PASSWORD', 'dp13store');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/** Increase PHP memory allocation **/
define( 'WP_MEMORY_LIMIT', '96M' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '7|hubrCz@]DO@mWzN&ydL3uIQ27(1lG0yCS@=]fvA;[]}k5h<1pC-O&}))rSS?j$');
define('SECURE_AUTH_KEY',  'rF+k-=ou[^~3mh|eMBjEr45{rj2HyjjWWY7W,B.xM|bl&3=IaSO|GmgZVSx&@g3Z');
define('LOGGED_IN_KEY',    'icELbLuw5Hbe=Rv:t-& &6BjD?SEPitYVVi:JD=},n/k2-@h3Q*zp?9O%-=M^yI)');
define('NONCE_KEY',        'xP7.W!]+@i 2V(cT+6$pdM-Q $^8j|L{mn0j~iArZX2ubj#J)e;Y3^;UfygyFvk#');
define('AUTH_SALT',        'bm^b8rX%I/7d Ry:+O1V?l;u@}f)=$uPh<|S`gCdA+<O6Ed**n!i8a+hipij3!w}');
define('SECURE_AUTH_SALT', '8J`xP+L=}L`0K*GIw  Dozp?IO8nt;q4 vs05wb3Af23e%deBv)`NXx:f$Pi<cC4');
define('LOGGED_IN_SALT',   'm NY{<J<cLk|j O+sYl2bV>ECctBQ-NJr*5|D3n|Aa-BqDAU>u]01t83cz+60EVx');
define('NONCE_SALT',       '$J>X;UhOFecMUI~ZN+8*K4s%[v,%moH<-8 qR>D%Tu2)iB:WD-XioULjh{YLH_ax');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
