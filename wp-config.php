<?php

/**

 * The base configurations of the WordPress.

 *

 * This file has the following configurations: MySQL settings, Table Prefix,

 * Secret Keys, and ABSPATH. You can find more information by visiting

 * {@link http://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}

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

define('DB_NAME', 'zzyzan');



/** MySQL database username */

define('DB_USER', 'root');



/** MySQL database password */

define('DB_PASSWORD', 'root');



/** MySQL hostname */

define('DB_HOST', '127.0.0.1');



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

define('AUTH_KEY',         'a0]&l1#c59L{k|d<x-WuW|<^ru[nX{SLKyZ5OfbKJ[z1uc;D#[)tKO29pbjR;)uz');

define('SECURE_AUTH_KEY',  'RE)xJ3TnZ,l[V+SW,%9n@--oh6oO@==}:*`X}?2-7ufygkdXj.%/-k6q3Z`+jypX');

define('LOGGED_IN_KEY',    'LxsS^<Xb3aA5+#++e-D|vR=F.RVKHs^ug(t)ULry.^W%{(/:Gd5<gHNp@7tmQ&%b');

define('NONCE_KEY',        '[Ps.|$3JgNkF=zu~,TWw=5;[9>GXn+3rF79L#;*New/Q3y+&>j3o^wv&>MJw?/I|');

define('AUTH_SALT',        'PIeeo05SSmksPSmv]feJHl~|ONZ:D+bOV|4zoJnBZvc7pYPwLvs}C*I$.AcQg0fR');

define('SECURE_AUTH_SALT', 'H(ph|WQC#J[LA=qdww!GLW fmi/5f@EM?|.XAih1.YxE|5i)W|gp}f.lvCf_9bTI');

define('LOGGED_IN_SALT',   '$I)*BGEp=r18?5/+I2~1+3}Nwtor+De!S%kKcCC[g6)F~e>Fg=hsO>R_p~lt-tw/');

define('NONCE_SALT',       'ZLj3kkK+bB>$UO{}Simq 32bM|R@/kUVHNHV%O|+$2YK[ji}+6M/`!D$MZf4Ph9n');



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

