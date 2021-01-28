<?php

/**
 * Poka Media Login
 *
 * @package     Poka Filter Product
 * @author      thientran
 * @copyright   2020 poka-media
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Poka Filter Product
 * Plugin URI:  https://pokamedia.com/poka-filter-proudct
 * Description: A simple Filter product for your Store
 * Version:     0.0.1
 * Author:      thientran
 * Author URI:  http://fb.com/makiosp1
 * Text Domain: poka-related-post
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */
// Block direct access to file
defined('ABSPATH') or die('Not Authorized!');

// Plugin Defines
define("PFP_FILE", __FILE__);
define("PFP_DIRECTORY", dirname(__FILE__));
define("PFP_TEXT_DOMAIN", dirname(__FILE__));
define("PFP_DIRECTORY_BASENAME", plugin_basename( PFP_FILE ));
define("PFP_DIRECTORY_PATH", plugin_dir_path( PFP_FILE ));
define("PFP_DIRECTORY_URL", plugins_url( null, PFP_FILE ));

// Require the main class file
require_once( PFP_DIRECTORY . '/include/main-class.php' );
