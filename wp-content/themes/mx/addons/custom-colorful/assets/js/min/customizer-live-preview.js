
(function($){wp.customize('theme_options[theme_custom_colorful][scheme]',function(value){value.bind(function(newval){if(newval==='random')
return;var I=function(e,p){return p?p.getElementById(e):document.getElementById(e);},$css=I('theme_custom_colorful-frontend-css',I('customize-preview').querySelector('iframe').contentDocument);if($css)
$css.href=$css.href.replace(/\-\w+\.cs/,'-'+newval+'.cs');});});})(jQuery);