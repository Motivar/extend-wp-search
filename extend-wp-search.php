<?php
/*
Plugin Name: Extend WP Search Interface
Plugin URI: https://motivar.io
Description: Simple stylish search powered with awm
Version: 0.1
Author: Giannopoulos Nikolaos
Author URI: https://motivar.io
Text Domain: extend-wp-search
 */

if (!defined('WPINC')) {
 die;
}

define('extend_wp_search_url', plugin_dir_url(__FILE__));
define('extend_wp_search_path', plugin_dir_path(__FILE__));
define('extend_wp_search_relative_path', dirname(plugin_basename(__FILE__)));

require_once(plugin_dir_path(__FILE__) . '/lib/autoload.php');

new \EWP_Search\Setup();