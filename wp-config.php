<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'gg');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '0w,/&Wi) c0P7j3#8rUs:RHD?o+wJKNd{~>8XX}+.J]NmC55 6fdd#x]d9,{8<*=');
define('SECURE_AUTH_KEY',  '_h^N,rrphvqOD#e{m$9>{t`OC.  iR1L{~$RQ(0V-HSGF8^5Ma|pN@x5XW#VorP+');
define('LOGGED_IN_KEY',    '!D,A;L -/$mW7A59s4v.:+I5Z<0GLam?L++OozcW br{j(yA]S/-JXP ;EYoRUUy');
define('NONCE_KEY',        '?~CD/0iHp}T$W{,<sC]9}_!^jp?LEW_6=+4o0 jpI;zHp<=ei_aI|9e0:[n5lfD9');
define('AUTH_SALT',        '!-h+YD=t0~k[YHJ5Tl&ZkIpJE%L4<Kc49VeZt>zZ1DAK|~Q5!^vyLjq|RH)ltaz)');
define('SECURE_AUTH_SALT', ']D:D;DE;h~-3rFMe+m:,2OZEkTM7u,KC$G7GB0D*3Eon%s*C*,0/ Xx%BHcX`8Nw');
define('LOGGED_IN_SALT',   'kQ&?5<HRHs~bv6zXlgd7z1fvTf  :Pgrt} m+JPe|#Lc^dcU}7M2)RIysv1?<@`A');
define('NONCE_SALT',       '`}xsl__e[*TsEEVw_2)6 0$?38DP(N]A,R3Y6)2zh+JT4U<S7kNgln-1v@Bf+pFg');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', 'en_GB');

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
