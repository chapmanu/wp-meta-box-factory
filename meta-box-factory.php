<?php
/*
Framework Name: Meta Box Factory
Plugin URI: https://github.com/chapmanuwp-meta-box-factory
Description: A Factory for WordPress MetaBoxes
Version: 0.1.1
Author: James Kerr, Chapman University
Author URI: http://jameskerr.info
License: GPLv2+
*/

require_once('lib/meta-box-factory.php');
require_once('boxes/sample-box-config.php');

function call_meta_box_factory() {
	$sample_box_config = get_sample_box_config();
	new MetaBoxFactory($sample_box_config);
}

add_action('load-post.php',     'call_meta_box_factory');
add_action('load-post-new.php', 'call_meta_box_factory');
