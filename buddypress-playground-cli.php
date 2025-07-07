<?php
/**
 * Plugin Name: BuddyPress Playground CLI
 * Plugin URI: https://github.com/vapvarun/buddypress-playground-cli
 * Description: Generate comprehensive BuddyPress and bbPress test data for addon development and testing
 * Version: 1.0.0
 * Author: vapvarun
 * Author URI: https://wbcomdesigns.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bp-playground
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: true
 * 
 * @package BuddyPress_Playground
 * @version 1.0.0
 * @author vapvarun <varun@wbcomdesigns.com>
 * @copyright 2025 Wbcom Designs
 * @license GPL-2.0+
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BP_PLAYGROUND_VERSION', '1.0.0');
define('BP_PLAYGROUND_PLUGIN_FILE', __FILE__);
define('BP_PLAYGROUND_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BP_PLAYGROUND_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BP_PLAYGROUND_TEXT_DOMAIN', 'bp-playground');

/**
 * Main BuddyPress Playground Plugin Class
 * 
 * @since 1.0.0
 * @author vapvarun
 */
final class BuddyPress_Playground {
    
    private static $instance = null;
    private $modules = [];
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
        $this->init_modules();
    }
    
    private function init_hooks() {
        add_action('init', [$this, 'load_textdomain']);
        add_action('admin_notices', [$this, 'check_dependencies']);
        
        if (defined('WP_CLI') && WP_CLI) {
            add_action('cli_init', [$this, 'register_cli_commands']);
        }
        
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    public function load_textdomain() {
        load_plugin_textdomain(BP_PLAYGROUND_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function check_dependencies() {
        if (!class_exists('BuddyPress')) {
            echo '<div class="notice notice-error"><p>BuddyPress Playground requires BuddyPress to be installed and active.</p></div>';
        }
    }

    private function load_dependencies() {
        // Core classes
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/class-bp-playground-core.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/class-bp-playground-batch-processor.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/class-bp-playground-data-model.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/class-bp-playground-logger.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/class-bp-playground-validator.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/class-bp-playground-cleanup.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/class-bp-playground-utils.php';

        // Interfaces and abstracts
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/interfaces/interface-bp-playground-module.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/abstracts/abstract-bp-playground-module.php';

        // Modules
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/modules/class-bp-playground-users-module.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/modules/class-bp-playground-xprofile-module.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/modules/class-bp-playground-groups-module.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/modules/class-bp-playground-activities-module.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/modules/class-bp-playground-messages-module.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/modules/class-bp-playground-friends-module.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/modules/class-bp-playground-bbpress-module.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/modules/class-bp-playground-social-network-module.php';
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/modules/class-bp-playground-settings-module.php';

        // CLI commands
        if (defined('WP_CLI') && WP_CLI) {
            require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/cli/class-bp-playground-cli-main.php';
            require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/cli/class-bp-playground-cli-users.php';
            require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/cli/class-bp-playground-cli-groups.php';
            require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/cli/class-bp-playground-cli-activities.php';
            require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/cli/class-bp-playground-cli-scenario.php';
        }
    }

    private function init_modules() {
        $this->modules = [
            'core' => new BP_Playground_Core(),
            'batch_processor' => new BP_Playground_Batch_Processor(),
            'data_model' => new BP_Playground_Data_Model(),
            'logger' => new BP_Playground_Logger(),
            'validator' => new BP_Playground_Validator(),
            'cleanup' => new BP_Playground_Cleanup(),
            'utils' => new BP_Playground_Utils(),
            'users' => new BP_Playground_Users_Module(),
            'xprofile' => new BP_Playground_XProfile_Module(),
            'groups' => new BP_Playground_Groups_Module(),
            'activities' => new BP_Playground_Activities_Module(),
            'messages' => new BP_Playground_Messages_Module(),
            'friends' => new BP_Playground_Friends_Module(),
            'bbpress' => new BP_Playground_BBPress_Module(),
            'social_network' => new BP_Playground_Social_Network_Module(),
            'settings' => new BP_Playground_Settings_Module(),
        ];
    }

    public function register_cli_commands() {
        if (!class_exists('WP_CLI')) {
            return;
        }

        WP_CLI::add_command('bp playground', 'BP_Playground_CLI_Main');
        WP_CLI::add_command('bp playground users', 'BP_Playground_CLI_Users');
        WP_CLI::add_command('bp playground groups', 'BP_Playground_CLI_Groups');
        WP_CLI::add_command('bp playground activities', 'BP_Playground_CLI_Activities');
        WP_CLI::add_command('bp playground scenario', 'BP_Playground_CLI_Scenario');
    }

    public function get_module($module_name) {
        return isset($this->modules[$module_name]) ? $this->modules[$module_name] : null;
    }

    public function activate() {
        // Create necessary tables, set default options
        $this->create_plugin_tables();
        add_option('bp_playground_version', BP_PLAYGROUND_VERSION);
    }

    public function deactivate() {
        // Cleanup scheduled events
        wp_clear_scheduled_hook('bp_playground_cleanup_cron');
    }

    private function create_plugin_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}bp_playground_logs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            level varchar(20) NOT NULL DEFAULT 'info',
            message text NOT NULL,
            context longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY level (level),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

/**
 * Initialize the plugin
 */
function bp_playground() {
    return BuddyPress_Playground::get_instance();
}

/**
 * Helper function to get module
 */
function bp_playground_get_module($module_name) {
    return bp_playground()->get_module($module_name);
}

// Initialize
bp_playground();