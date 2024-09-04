<?php

$_SVIEWS_CONFIG = array();

$path = dirname(__FILE__) . DIRECTORY_SEPARATOR ;

//path
$_SVIEWS_CONFIG['template_dir'] = $path . 'templates';
$_SVIEWS_CONFIG['compile_dir'] = $path . 'compiled';
$_SVIEWS_CONFIG['javascript_base_dir'] = "includes/javascript";
$_SVIEWS_CONFIG['javascript_non_localized_dir'] = "common";
$_SVIEWS_CONFIG['css_base_dir'] = "includes/css";
$_SVIEWS_CONFIG['i18n_dir'] = "i18n";

//i18n
$_SVIEWS_CONFIG['use_i18n'] = false;
$_SVIEWS_CONFIG['i18n_default_language'] = "en";

//misc
$_SVIEWS_CONFIG['debug'] = false;
$_SVIEWS_CONFIG['cache_lifetime'] = 0;


return $_SVIEWS_CONFIG;


?>