/** common */
//require('modules/ready');
//require('modules/click-handle');
//require('modules/get-ele-top');
//require('modules/get-ele-left');
//require('modules/auto-focus');
//require('modules/in-screen');
//require('modules/is-email');
//require('modules/replace-array');
//require('modules/window-scroll');
//require('modules/status-tip');
//require('modules/scroll-to');
//require('modules/ajax-loading-tip');
//require('modules/lazyload');
//require('modules/validate');
//require('modules/parse-html');
//require('modules/placeholder');

/** theme */
require('modules/back-to-top')();
require('modules/nav-main-scroller')();
require('modules/menu-toggle')();
require('modules/menu-mobile')();
require('modules/archive-pagination')();
require('modules/search')();
require('modules/toggle-on-js')();
require('modules/lazyload')();

/** addons common*/
require('addons/comment-ajax/assets/js/frontend')();
require('addons/comment-emotion/assets/js/frontend')();


/** addons custom */
require('addons/custom-slidebox/assets/js/frontend')();
require('addons/custom-homebox/assets/js/frontend')();
require('addons/custom-attachment-page/assets/js/frontend')();
require('addons/custom-post-point/assets/js/frontend')();
var pna = require('addons/page-nagination-ajax/assets/js/frontend');
	pna.init();
require('addons/full-width-mode/assets/js/frontend')();
require('addons/post-share/assets/js/frontend')();
require('addons/post-views/assets/js/frontend')();

/** vistor */
require('addons/custom-sign/assets/js/frontend')();
