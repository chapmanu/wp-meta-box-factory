<?php
/*
Name: Meta Box Factory
Description: A Factory for WordPress MetaBoxes
Version: 0.1
Author: James Kerr
Author URI: http://jameskerr.info
*/

require_once(plugins_url( '/lib/meta-box-factory.php' , __FILE__ ));
require_once(plugins_url( '/boxes/sample-box-config.php' , __FILE__ ));

function call_meta_box_factory() {
	$sample_box_config = get_sample_box_config();
	new MetaBoxFactory($sample_box_config);
}

add_action('load-post.php',     'call_meta_box_factory');
add_action('load-post-new.php', 'call_meta_box_factory');
