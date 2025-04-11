<?php
/*
Plugin Name: Alert Message
Author: André Ranulfo
Author URI: https://www.linkedin.com/in/andre-ranulfo/
Version: 1.0
Description: Display a custom alert message on posts.
Text Domain: alert-message
Domain Path: /languages
Plugin URI: https://github.com/Zagaz/WP-Alert-Message-for-posts-plugin
*/

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-alert-message.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-alert-message-admin.php';

$alertMessage = new \AlertMessage\AlertMessage();
