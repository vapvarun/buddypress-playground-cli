<?php
/**
 * BuddyPress Playground Optimized Autoloader
 *
 * @package BuddyPress_Playground
 * @subpackage Core
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Memory-optimized autoloader for BuddyPress Playground classes
 *
 * @since 1.0.0
 */
class BP_Playground_Autoloader {

    /**
     * Lightweight class map - only paths, no heavy data
     *
     * @since 1.0.0
     * @var array
     */
    private static $class_map = [
        // Core classes (lightweight)
        'BP_Playground_Core' => 'includes/class-bp-playground-core.php',
        'BP_Playground_Utils' => 'includes/class-bp-playground-utils.php',
        'BP_Playground_Logger' => 'includes/class-bp-playground-logger.php',
        
        // Heavy classes (loaded on demand only)
        'BP_Playground_Batch_Processor' => 'includes/class-bp-playground-batch-processor.php',
        'BP_Playground_Data_Model' => 'includes/class-bp-playground-data-model.php',
        'BP_Playground_Validator' => 'includes/class-bp-playground-validator.php',
        'BP_Playground_Cleanup' => 'includes/class-bp-playground-cleanup.php',
        'BP_Playground_Sample_Data' => 'includes/class-bp-playground-sample-data.php',

        // Interfaces and abstracts
        'BP_Playground_Module_Interface' => 'includes/interfaces/interface-bp-playground-module.php',
        'BP_Playground_Abstract_Module' => 'includes/abstracts/abstract-bp-playground-module.php',

        // Module classes (heavy - only load when requested)
        'BP_Playground_Users_Module' => 'includes/modules/class-bp-playground-users-module.php',
        'BP_Playground_XProfile_Module' => 'includes/modules/class-bp-playground-xprofile-module.php',
        'BP_Playground_Groups_Module' => 'includes/modules/class-bp-playground-groups-module.php',
        'BP_Playground_Activities_Module' => 'includes/modules/class-bp-playground-activities-module.php',
        'BP_Playground_Messages_Module' => 'includes/modules/class-bp-playground-messages-module.php',
        'BP_Playground_Friends_Module' => 'includes/modules/class-bp-playground-friends-module.php',
        'BP_Playground_BBPress_Module' => 'includes/modules/class-bp-playground-bbpress-module.php',

        // CLI classes (only loaded during CLI execution)
        'BP_Playground_CLI_Main' => 'includes/cli/class-bp-playground-cli-main.php',
        'BP_Playground_CLI_Users' => 'includes/cli/class-bp-playground-cli-users.php',
        'BP_Playground_CLI_Groups' => 'includes/cli/class-bp-playground-cli-groups.php',
        'BP_Playground_CLI_Activities' => 'includes/cli/class-bp-playground-cli-activities.php',
        'BP_Playground_CLI_Scenario_Enhanced' => 'includes/cli/class-bp-playground-cli-scenario-enhanced.php',
        'BP_Playground_CLI_Names' => 'includes/cli/class-bp-playground-cli-names.php',
        
        // Helper classes
        'BP_Playground_Name_Handler' => 'includes/class-bp-playground-name-handler.php',
        'BP_Playground_XProfile_Generator' => 'includes/class-bp-playground-xprofile-generator.php',
        'BP_Playground_Sequence_Manager' => 'includes/class-bp-playground-sequence-manager.php',
    ];

    /**
     * Simple dependency map - minimal data
     *
     * @since 1.0.0
     * @var array
     */
    private static $dependencies = [
        'BP_Playground_Abstract_Module' => ['BP_Playground_Module_Interface'],
        'BP_Playground_Users_Module' => ['BP_Playground_Abstract_Module'],
        'BP_Playground_XProfile_Module' => ['BP_Playground_Abstract_Module'],
        'BP_Playground_Groups_Module' => ['BP_Playground_Abstract_Module'],
        'BP_Playground_Activities_Module' => ['BP_Playground_Abstract_Module'],
        'BP_Playground_Messages_Module' => ['BP_Playground_Abstract_Module'],
        'BP_Playground_Friends_Module' => ['BP_Playground_Abstract_Module'],
        'BP_Playground_BBPress_Module' => ['BP_Playground_Abstract_Module'],
    ];

    /**
     * Track loaded classes - simple array
     *
     * @since 1.0.0
     * @var array
     */
    private static $loaded = [];

    /**
     * Register the autoloader
     *
     * @since 1.0.0
     * @return bool Success status
     */
    public static function register() {
        return spl_autoload_register([__CLASS__, 'autoload'], true, true);
    }

    /**
     * Unregister the autoloader
     *
     * @since 1.0.0
     * @return bool Success status
     */
    public static function unregister() {
        return spl_autoload_unregister([__CLASS__, 'autoload']);
    }

    /**
     * Autoload a class - ultra-lightweight implementation
     *
     * @since 1.0.0
     * @param string $class_name Class name to load
     * @return bool Whether the class was loaded
     */
    public static function autoload($class_name) {
        // Quick exit for non-plugin classes
        if (strpos($class_name, 'BP_Playground_') !== 0) {
            return false;
        }

        // Quick exit if already loaded
        if (isset(self::$loaded[$class_name])) {
            return true;
        }

        // Load dependencies first (lightweight)
        if (isset(self::$dependencies[$class_name])) {
            foreach (self::$dependencies[$class_name] as $dependency) {
                if (!isset(self::$loaded[$dependency])) {
                    self::autoload($dependency);
                }
            }
        }

        // Load the class file
        if (isset(self::$class_map[$class_name])) {
            $file_path = BP_PLAYGROUND_PLUGIN_DIR . self::$class_map[$class_name];
            
            if (file_exists($file_path)) {
                require_once $file_path;
                self::$loaded[$class_name] = true;
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a class is loaded
     *
     * @since 1.0.0
     * @param string $class_name Class name to check
     * @return bool Whether the class is loaded
     */
    public static function is_loaded($class_name) {
        return isset(self::$loaded[$class_name]);
    }

    /**
     * Get minimal memory stats
     *
     * @since 1.0.0
     * @return array Simple memory info
     */
    public static function get_memory_stats() {
        return [
            'loaded_count' => count(self::$loaded),
            'loaded_classes' => array_keys(self::$loaded),
            'current_memory' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
        ];
    }

    /**
     * Force load essential classes (emergency method)
     *
     * @since 1.0.0
     * @return void
     */
    public static function emergency_load_essentials() {
        $essentials = [
            'BP_Playground_Core',
            'BP_Playground_Utils',
        ];

        foreach ($essentials as $class_name) {
            if (!class_exists($class_name)) {
                self::autoload($class_name);
            }
        }
    }
}