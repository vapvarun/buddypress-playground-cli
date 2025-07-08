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

// Emergency memory increase - do this FIRST
if (function_exists('ini_set')) {
    ini_set('memory_limit', '1024M');
}

// Define plugin constants
define('BP_PLAYGROUND_VERSION', '1.0.0');
define('BP_PLAYGROUND_PLUGIN_FILE', __FILE__);
define('BP_PLAYGROUND_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BP_PLAYGROUND_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BP_PLAYGROUND_TEXT_DOMAIN', 'bp-playground');

/**
 * Ultra-minimal plugin class to prevent memory issues
 * 
 * @since 1.0.0
 */
final class BuddyPress_Playground {
    
    private static $instance = null;
    private $autoloader_registered = false;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Only register hooks - don't load anything else yet
        $this->register_hooks();
    }
    
    /**
     * Register minimal hooks only
     */
    private function register_hooks() {
        // Only register essential hooks
        add_action('plugins_loaded', [$this, 'init_plugin'], 10);
        
        // CLI hooks only when needed
        if (defined('WP_CLI') && WP_CLI) {
            add_action('cli_init', [$this, 'register_cli_commands'], 20);
        }
        
        // Activation/deactivation
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    /**
     * Initialize plugin after WordPress is fully loaded
     */
    public function init_plugin() {
        // Load autoloader only when WordPress is ready
        $this->load_autoloader();
        
        // Load textdomain
        load_plugin_textdomain(BP_PLAYGROUND_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Check dependencies
        add_action('admin_notices', [$this, 'check_dependencies']);
    }
    
    /**
     * Load autoloader safely
     */
    private function load_autoloader() {
        if ($this->autoloader_registered) {
            return;
        }
        
        $autoloader_file = BP_PLAYGROUND_PLUGIN_DIR . 'includes/class-bp-playground-autoloader.php';
        if (file_exists($autoloader_file)) {
            require_once $autoloader_file;
            BP_Playground_Autoloader::register();
            $this->autoloader_registered = true;
        }
    }
    
    /**
     * Get module (lazy loaded)
     */
    public function get_module($module_name) {
        // Ensure autoloader is loaded
        $this->load_autoloader();
        
        // Map module names to class names
        $module_classes = [
            'core' => 'BP_Playground_Core',
            'utils' => 'BP_Playground_Utils',
            'logger' => 'BP_Playground_Logger',
            'batch_processor' => 'BP_Playground_Batch_Processor',
            'data_model' => 'BP_Playground_Data_Model',
            'validator' => 'BP_Playground_Validator',
            'cleanup' => 'BP_Playground_Cleanup',
            'users' => 'BP_Playground_Users_Module',
            'xprofile' => 'BP_Playground_XProfile_Module',
            'groups' => 'BP_Playground_Groups_Module',
            'activities' => 'BP_Playground_Activities_Module',
            'messages' => 'BP_Playground_Messages_Module',
            'friends' => 'BP_Playground_Friends_Module',
            'bbpress' => 'BP_Playground_BBPress_Module',
        ];

        if (!isset($module_classes[$module_name])) {
            return null;
        }

        $class_name = $module_classes[$module_name];
        
        // Create instance if class exists
        if (class_exists($class_name)) {
            return new $class_name();
        }

        return null;
    }
    
    /**
     * Register CLI commands
     */
    public function register_cli_commands() {
        if (!class_exists('WP_CLI')) {
            return;
        }

        // Ensure autoloader is ready
        $this->load_autoloader();

        // Register commands - classes will be autoloaded when WP_CLI instantiates them
        WP_CLI::add_command('bp playground', 'BP_Playground_CLI_Main');
        WP_CLI::add_command('bp playground users', 'BP_Playground_CLI_Users');
        WP_CLI::add_command('bp playground groups', 'BP_Playground_CLI_Groups');
        WP_CLI::add_command('bp playground activities', 'BP_Playground_CLI_Activities');
        WP_CLI::add_command('bp playground scenario', 'BP_Playground_CLI_Scenario');
    }
    
    /**
     * Check dependencies
     */
    public function check_dependencies() {
        if (!class_exists('BuddyPress')) {
            echo '<div class="notice notice-error"><p>BuddyPress Playground requires BuddyPress to be installed and active.</p></div>';
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Increase memory for activation
        ini_set('memory_limit', '1024M');
        
        // Create tables
        $this->create_plugin_tables();
        add_option('bp_playground_version', BP_PLAYGROUND_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup
        wp_clear_scheduled_hook('bp_playground_cleanup_cron');
        
        // Unregister autoloader if registered
        if ($this->autoloader_registered && class_exists('BP_Playground_Autoloader')) {
            BP_Playground_Autoloader::unregister();
        }
        
        flush_rewrite_rules();
    }
    
    /**
     * Create plugin tables with complete schema
     */
    private function create_plugin_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}bp_playground_logs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            level varchar(20) NOT NULL DEFAULT 'info',
            message text NOT NULL,
            context longtext,
            memory_usage bigint(20) unsigned DEFAULT 0,
            peak_memory bigint(20) unsigned DEFAULT 0,
            user_id bigint(20) unsigned DEFAULT 0,
            ip_address varchar(45) DEFAULT '',
            user_agent text DEFAULT '',
            request_uri text DEFAULT '',
            module varchar(50) DEFAULT 'core',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY level (level),
            KEY module (module),
            KEY created_at (created_at),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

/**
 * Initialize the plugin - but only create the instance
 */
function bp_playground() {
    return BuddyPress_Playground::get_instance();
}

/**
 * Helper function to get module (with safety check)
 */
function bp_playground_get_module($module_name) {
    $plugin = bp_playground();
    if ($plugin) {
        return $plugin->get_module($module_name);
    }
    return null;
}

// Initialize plugin instance
bp_playground();