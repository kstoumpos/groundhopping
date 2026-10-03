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
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',          'Iz];Ag$Uz}cHNN&Di4eXihTQH*kuCh%q)6:%+&ITqKbJE2;+MG**He@ -lr0P5},' );
define( 'SECURE_AUTH_KEY',   'o3402k%fQ0H>F.?s)OmcPdXP^Hti-vU1*30c@``P5;f.~fauaAd<gcX~0f>vKEgj' );
define( 'LOGGED_IN_KEY',     'CG0YDbSNBuqt** To{@[Y>qfartO:.q;z=@i7F[?YGFyHnG/)OE5#4{Q:.]:+& L' );
define( 'NONCE_KEY',         'y|vHOK1.n^ppp&~DR Du)gXK.mZOyv7:Zzwlvq?#TqGoqhKmsD5H]KKb3s<cZ>:(' );
define( 'AUTH_SALT',         '~)b;%:_,TbUi4;Mtm2&&N*YqVQ2$wWfJ!hKE[x% ztu;;?9@),eSm_5Moa%upHb_' );
define( 'SECURE_AUTH_SALT',  '[_:,+ F4l}UWW#-*y#d/ A(qL&]a~9]Jly5Wyq=Acj26I4L5v)d[#Pub_#9E+WM+' );
define( 'LOGGED_IN_SALT',    ':fIChV2X[4?({T%>}}aoN3X.xEOKa.<J]9g~GU$Jc@Vl0G^F>g0fTSzoEO*CoF,z' );
define( 'NONCE_SALT',        'D_gxU,0OtZ}O?3yf*;FI`9)!MxFO-NNeD1O+W56F@b~;+nAG?KWs5dMX3*|fqw%p' );
define( 'WP_CACHE_KEY_SALT', 'z45X8XZC-oCxK92lR(`~W5}A_3/:F4MD;}-1V!R6v&,fsRIy>T~QsZ0`hQ%o07=E' );


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

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
