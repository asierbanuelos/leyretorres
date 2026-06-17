<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u351898102_ZK4lD' );

/** Database username */
define( 'DB_USER', 'u351898102_EAjOn' );

/** Database password */
define( 'DB_PASSWORD', '35#7WE.mG|' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '&^6uA+tL[^efNL]01fI{!.PN~U9jMYt{Y?o O1J:?.~ypaCN%,)Pm)Bp#FE@c2nw' );
define( 'SECURE_AUTH_KEY',   'ecEDCaqU[kZ>f_YiglB7]AZ$-*LR_+Kz:W>hx!;)I)`;C~u?n4C4CO-Tr{vcaRSJ' );
define( 'LOGGED_IN_KEY',     '|xO!OkP7/ZR*SX9-@1yuUdDmmK,knCA>t8c(sr73dW;,V/[nG-JDl^q%_,Jp#J}j' );
define( 'NONCE_KEY',         '{uyH6A&W4zp01Y(TJ]tg2;0jR{!wyS&^%Sj2]02q}FtvP@@=)t+J$ E=`*)tK`_U' );
define( 'AUTH_SALT',         '_scen37)ylypxMEvXM3/!-Q@>N#%W3LgF.aN,puB8<BWP`kf;*E3DJ)<dKD[Qesa' );
define( 'SECURE_AUTH_SALT',  '&E_!EW/i;u]uc-Ax!MKv|&)$I{mFzo)pDx9<Oef|bs DSLZR92*L9$PZK*;i3{k5' );
define( 'LOGGED_IN_SALT',    'D@g^G}Jdg{6hSy>/SI7_7`+(}C2xWl32{udY/eFoUodgQVaX0SmJg~zpS|K7)`:D' );
define( 'NONCE_SALT',        'j Vv>lT9WO1j)(,)-SJBUS16)!ZG*2Z~n0L3m&y[:|>:8|kgjDng0OC3-kp2~Zg*' );
define( 'WP_CACHE_KEY_SALT', 'FCKMwhE]wj!D{pV46HwZ5luM<aVKNBl?2!YFNsD+yR~!.ZC,HS~?H/zU3,0[3:Z?' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '9a4d82af22344bd30f5d944ed1a5fab0' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
