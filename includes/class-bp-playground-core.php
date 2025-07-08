<?php
/**
 * BuddyPress Playground Core Class
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
 * Core functionality for BuddyPress Playground
 *
 * @since 1.0.0
 */
class BP_Playground_Core {

    /**
     * Plugin settings
     *
     * @since 1.0.0
     * @var array
     */
    private $settings;

    /**
     * Current operation statistics
     *
     * @since 1.0.0
     * @var array
     */
    private $stats = [];

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->load_settings();
        $this->init_stats();
    }

    /**
     * Load plugin settings
     *
     * @since 1.0.0
     * @return void
     */
    private function load_settings() {
        $defaults = [
            'batch_size' => 100,
            'memory_limit' => '1024M',
            'time_limit' => 0,
            'enable_logging' => true,
            'log_level' => 'info',
            'cleanup_retention_days' => 30,
            'enable_progress_tracking' => true,
        ];

        $this->settings = wp_parse_args(
            get_option('bp_playground_settings', []),
            $defaults
        );
    }

    /**
     * Initialize statistics tracking
     *
     * @since 1.0.0
     * @return void
     */
    private function init_stats() {
        $this->stats = [
            'users_created' => 0,
            'groups_created' => 0,
            'activities_created' => 0,
            'messages_created' => 0,
            'friends_created' => 0,
            'forums_created' => 0,
            'topics_created' => 0,
            'replies_created' => 0,
            'xprofile_fields_created' => 0,
            'start_time' => null,
            'end_time' => null,
            'memory_peak' => 0,
            'errors' => [],
        ];
    }

    /**
     * Get plugin settings
     *
     * @since 1.0.0
     * @param string $key Optional. Setting key to retrieve
     * @return mixed Settings array or specific setting value
     */
    public function get_settings($key = null) {
        if ($key) {
            return isset($this->settings[$key]) ? $this->settings[$key] : null;
        }
        return $this->settings;
    }

    /**
     * Update plugin settings
     *
     * @since 1.0.0
     * @param array $new_settings Settings to update
     * @return bool True on success, false on failure
     */
    public function update_settings($new_settings) {
        $this->settings = wp_parse_args($new_settings, $this->settings);
        return update_option('bp_playground_settings', $this->settings);
    }

    /**
     * Start operation tracking
     *
     * @since 1.0.0
     * @param string $operation Operation name
     * @return void
     */
    public function start_operation($operation = 'Unknown') {
        $this->stats['start_time'] = microtime(true);
        $this->stats['operation'] = $operation;
        
        // Set memory and time limits
        if ($this->settings['memory_limit']) {
            ini_set('memory_limit', $this->settings['memory_limit']);
        }
        
        if ($this->settings['time_limit'] !== null) {
            set_time_limit($this->settings['time_limit']);
        }

        $this->log("Starting operation: {$operation}", 'info');
    }

    /**
     * End operation tracking
     *
     * @since 1.0.0
     * @return array Operation statistics
     */
    public function end_operation() {
        $this->stats['end_time'] = microtime(true);
        $this->stats['duration'] = $this->stats['end_time'] - $this->stats['start_time'];
        $this->stats['memory_peak'] = memory_get_peak_usage(true);

        $operation = isset($this->stats['operation']) ? $this->stats['operation'] : 'Unknown';
        $this->log("Completed operation: {$operation} in {$this->stats['duration']} seconds", 'info');

        return $this->get_stats();
    }

    /**
     * Get current statistics
     *
     * @since 1.0.0
     * @return array Current statistics
     */
    public function get_stats() {
        return $this->stats;
    }

    /**
     * Update statistic counter
     *
     * @since 1.0.0
     * @param string $key Statistic key
     * @param int $increment Increment value (default: 1)
     * @return void
     */
    public function increment_stat($key, $increment = 1) {
        if (isset($this->stats[$key])) {
            $this->stats[$key] += $increment;
        } else {
            $this->stats[$key] = $increment;
        }
    }

    /**
     * Add error to statistics
     *
     * @since 1.0.0
     * @param string $error Error message
     * @param array $context Optional error context
     * @return void
     */
    public function add_error($error, $context = []) {
        $this->stats['errors'][] = [
            'message' => $error,
            'context' => $context,
            'time' => current_time('mysql'),
        ];

        $this->log($error, 'error', $context);
    }

    /**
     * Check if BuddyPress components are active
     *
     * @since 1.0.0
     * @param array $components Components to check
     * @return array Array of component status
     */
    public function check_bp_components($components = []) {
        if (empty($components)) {
            $components = ['activity', 'groups', 'members', 'messages', 'xprofile', 'friends'];
        }

        $status = [];
        $active_components = bp_get_option('bp-active-components', []);

        foreach ($components as $component) {
            $status[$component] = isset($active_components[$component]);
        }

        return $status;
    }

    /**
     * Check if bbPress is active and properly configured
     *
     * @since 1.0.0
     * @return bool True if bbPress is ready, false otherwise
     */
    public function check_bbpress_status() {
        if (!class_exists('bbPress')) {
            return false;
        }

        // Check if bbPress pages are properly set up
        $pages = [
            '_bbp_root_slug_custom_slug',
            '_bbp_forum_slug',
            '_bbp_topic_slug',
            '_bbp_reply_slug',
        ];

        foreach ($pages as $page_option) {
            if (!get_option($page_option)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get system information for debugging
     *
     * @since 1.0.0
     * @return array System information
     */
    public function get_system_info() {
        global $wpdb;

        $bp_components = $this->check_bp_components();
        $active_components = array_filter($bp_components);

        return [
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'mysql_version' => $wpdb->db_version(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'bp_version' => class_exists('BuddyPress') ? bp_get_version() : 'Not installed',
            'bp_active_components' => $active_components,
            'bbpress_version' => class_exists('bbPress') ? bbp_get_version() : 'Not installed',
            'bbpress_status' => $this->check_bbpress_status(),
            'multisite' => is_multisite(),
            'wp_cli' => defined('WP_CLI') && WP_CLI,
            'plugin_version' => BP_PLAYGROUND_VERSION,
        ];
    }

    /**
     * Validate operation parameters
     *
     * @since 1.0.0
     * @param array $params Parameters to validate
     * @param array $rules Validation rules
     * @return array|WP_Error Validated parameters or WP_Error on failure
     */
    public function validate_params($params, $rules) {
        $validated = [];
        $errors = [];

        foreach ($rules as $param_name => $rule) {
            $value = isset($params[$param_name]) ? $params[$param_name] : null;

            // Required parameter check
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[] = sprintf(__('Parameter %s is required.', BP_PLAYGROUND_TEXT_DOMAIN), $param_name);
                continue;
            }

            // Type validation
            if (!empty($value) && isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'int':
                        $value = intval($value);
                        break;
                    case 'float':
                        $value = floatval($value);
                        break;
                    case 'bool':
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        break;
                    case 'array':
                        if (!is_array($value)) {
                            $errors[] = sprintf(__('Parameter %s must be an array.', BP_PLAYGROUND_TEXT_DOMAIN), $param_name);
                            continue 2;
                        }
                        break;
                    case 'string':
                        $value = strval($value);
                        break;
                }
            }

            // Range validation
            if (isset($rule['min']) && $value < $rule['min']) {
                $errors[] = sprintf(
                    __('Parameter %s must be at least %d.', BP_PLAYGROUND_TEXT_DOMAIN),
                    $param_name,
                    $rule['min']
                );
                continue;
            }

            if (isset($rule['max']) && $value > $rule['max']) {
                $errors[] = sprintf(
                    __('Parameter %s must be at most %d.', BP_PLAYGROUND_TEXT_DOMAIN),
                    $param_name,
                    $rule['max']
                );
                continue;
            }

            // Default value
            if (empty($value) && isset($rule['default'])) {
                $value = $rule['default'];
            }

            $validated[$param_name] = $value;
        }

        if (!empty($errors)) {
            return new WP_Error('validation_failed', implode(' ', $errors));
        }

        return $validated;
    }

    /**
     * Log message
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param string $level Log level (debug, info, warning, error)
     * @param array $context Additional context
     * @return void
     */
    public function log($message, $level = 'info', $context = []) {
        if (!$this->settings['enable_logging']) {
            return;
        }

        $logger = bp_playground_get_module('logger');
        if ($logger) {
            $logger->log($message, $level, $context);
        }
    }

    /**
     * Clean up orphaned data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function cleanup_orphaned_data($options = []) {
        global $wpdb;

        $defaults = [
            'dry_run' => false,
            'components' => ['all'],
            'older_than_days' => 30,
        ];

        $options = wp_parse_args($options, $defaults);
        $results = [];

        // Start cleanup operation
        $this->start_operation('cleanup_orphaned_data');

        try {
            $cleanup = bp_playground_get_module('cleanup');
            if ($cleanup) {
                $results = $cleanup->cleanup_orphaned_data($options);
            }
        } catch (Exception $e) {
            $this->add_error('Cleanup failed: ' . $e->getMessage());
            $results['error'] = $e->getMessage();
        }

        $this->end_operation();
        return $results;
    }

    /**
     * Generate comprehensive test scenario
     *
     * @since 1.0.0
     * @param string $scenario_name Scenario name
     * @param array $params Scenario parameters
     * @return array Generation results
     */
    public function generate_scenario($scenario_name, $params = []) {
        $this->start_operation("generate_scenario_{$scenario_name}");

        $results = [
            'scenario' => $scenario_name,
            'success' => false,
            'components' => [],
            'errors' => [],
        ];

        try {
            $scenario_config = $this->get_scenario_config($scenario_name);
            if (is_wp_error($scenario_config)) {
                throw new Exception($scenario_config->get_error_message());
            }

            // Merge scenario config with provided params
            $config = wp_parse_args($params, $scenario_config);

            // Generate components in dependency order
            $generation_order = [
                'users',
                'xprofile',
                'groups',
                'friends',
                'activities',
                'messages',
                'bbpress',
            ];

            foreach ($generation_order as $component) {
                if (isset($config[$component]) && !empty($config[$component])) {
                    $module = bp_playground_get_module($component);
                    if ($module) {
                        $component_result = $module->generate($config[$component]);
                        $results['components'][$component] = $component_result;
                        
                        if (is_wp_error($component_result)) {
                            $results['errors'][] = $component_result->get_error_message();
                        }
                    }
                }
            }

            $results['success'] = empty($results['errors']);

        } catch (Exception $e) {
            $this->add_error('Scenario generation failed: ' . $e->getMessage());
            $results['errors'][] = $e->getMessage();
        }

        $results['stats'] = $this->end_operation();
        return $results;
    }

    /**
     * Get predefined scenario configuration
     *
     * @since 1.0.0
     * @param string $scenario_name Scenario name
     * @return array|WP_Error Scenario configuration or error
     */
    private function get_scenario_config($scenario_name) {
        $scenarios = [
            'small-community' => [
                'users' => ['count' => 500, 'with_xprofile' => true],
                'groups' => [
                    'count' => 25, 
                    'types' => 'mixed',
                    'enable_forums' => 'auto',
                    'membership_patterns' => true
                ],
                'activities' => ['count' => 10000, 'with_mentions' => true],
                'messages' => ['count' => 2000],
                'bbpress' => ['forums' => 15, 'topics_per_forum' => 33],
            ],
            'medium-community' => [
                'users' => ['count' => 2000, 'with_xprofile' => true],
                'groups' => [
                    'count' => 100, 
                    'types' => 'mixed',
                    'enable_forums' => 'auto',
                    'membership_patterns' => true
                ],
                'activities' => ['count' => 50000, 'with_mentions' => true],
                'messages' => ['count' => 8000],
                'bbpress' => ['forums' => 40, 'topics_per_forum' => 50],
            ],
            'large-community' => [
                'users' => ['count' => 10000, 'with_xprofile' => true],
                'groups' => [
                    'count' => 500, 
                    'types' => 'mixed',
                    'enable_forums' => 'auto',
                    'membership_patterns' => true
                ],
                'activities' => ['count' => 200000, 'with_mentions' => true],
                'messages' => ['count' => 25000],
                'bbpress' => ['forums' => 100, 'topics_per_forum' => 100],
            ],
            'addon-testing' => [
                'users' => ['count' => 500, 'with_xprofile' => true, 'member_types' => true],
                'xprofile' => ['field_groups' => 8, 'fields_per_group' => 10],
                'groups' => [
                    'count' => 50, 
                    'types' => 'all', 
                    'with_hierarchy' => true,
                    'enable_forums' => true,
                    'membership_patterns' => true
                ],
                'activities' => ['count' => 15000, 'with_mentions' => true, 'favorite_rate' => 0.15],
                'messages' => ['count' => 3000, 'thread_variations' => true],
                'bbpress' => ['forums' => 20, 'hierarchy_depth' => 3, 'with_tags' => true],
            ],
        ];
    
        if (!isset($scenarios[$scenario_name])) {
            return new WP_Error(
                'invalid_scenario',
                sprintf(__('Unknown scenario: %s', BP_PLAYGROUND_TEXT_DOMAIN), $scenario_name)
            );
        }
    
        return $scenarios[$scenario_name];
    }

    /**
     * Get available scenarios
     *
     * @since 1.0.0
     * @return array Available scenario names and descriptions
     */
    public function get_available_scenarios() {
        return [
            'small-community' => [
                'name' => __('Small Community', BP_PLAYGROUND_TEXT_DOMAIN),
                'description' => __('500 users, 25 groups, 10K activities - Perfect for basic testing', BP_PLAYGROUND_TEXT_DOMAIN),
                'users' => 500,
                'estimated_time' => '2-3 minutes',
            ],
            'medium-community' => [
                'name' => __('Medium Community', BP_PLAYGROUND_TEXT_DOMAIN),
                'description' => __('2K users, 100 groups, 50K activities - Ideal for integration testing', BP_PLAYGROUND_TEXT_DOMAIN),
                'users' => 2000,
                'estimated_time' => '5-8 minutes',
            ],
            'large-community' => [
                'name' => __('Large Community', BP_PLAYGROUND_TEXT_DOMAIN),
                'description' => __('10K users, 500 groups, 200K activities - Great for performance testing', BP_PLAYGROUND_TEXT_DOMAIN),
                'users' => 10000,
                'estimated_time' => '15-25 minutes',
            ],
            'addon-testing' => [
                'name' => __('Addon Testing', BP_PLAYGROUND_TEXT_DOMAIN),
                'description' => __('Optimized dataset for comprehensive addon development and testing', BP_PLAYGROUND_TEXT_DOMAIN),
                'users' => 500,
                'estimated_time' => '3-5 minutes',
            ],
        ];
    }
}