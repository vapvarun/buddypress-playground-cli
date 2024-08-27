<?php
/**
 * Plugin Name: BuddyPress Playground CLI
 * Plugin URI: https://wbcomdesigns.com/
 * Description: A plugin to generate test data for BuddyPress, including activities, messages, connections, and more.
 * Version: 1.0.0
 * Author: vapvarun
 * Author URI: https://wbcomdesigns.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: buddypress-playground-cli
 * Domain Path: /languages
 */


// Define constants
define( 'BP_PLAYGROUND_CLI_PATH', plugin_dir_path( __FILE__ ) );
define( 'BP_PLAYGROUND_CLI_URL', plugin_dir_url( __FILE__ ) );

// Include module classes
require_once BP_PLAYGROUND_CLI_PATH . 'classes/class-bp-groups-module.php';
require_once BP_PLAYGROUND_CLI_PATH . 'classes/class-bp-activities-module.php';
require_once BP_PLAYGROUND_CLI_PATH . 'classes/class-bp-messages-module.php';
require_once BP_PLAYGROUND_CLI_PATH . 'classes/class-bp-connections-module.php';
require_once BP_PLAYGROUND_CLI_PATH . 'classes/class-bp-activity-comments-module.php';
require_once BP_PLAYGROUND_CLI_PATH . 'classes/class-bp-group-activities-module.php';
require_once BP_PLAYGROUND_CLI_PATH . 'classes/class-bp-group-members-module.php';
require_once BP_PLAYGROUND_CLI_PATH . 'classes/class-bp-update-last-activity-module.php';
require_once BP_PLAYGROUND_CLI_PATH . 'classes/class-bp-create-users-module.php';


// Include the WP-CLI command class
require_once BP_PLAYGROUND_CLI_PATH . 'class-bp-cli.php';
