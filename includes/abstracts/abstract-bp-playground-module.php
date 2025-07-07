<?php
/**
 * BuddyPress Playground Abstract Module
 *
 * @package BuddyPress_Playground
 * @subpackage Abstracts
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract base class for all BuddyPress Playground modules
 *
 * @since 1.0.0
 */
abstract class BP_Playground_Abstract_Module implements BP_Playground_Module_Interface {

    /**
     * Module name
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_name = '';

    /**
     * Module description
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_description = '';

    /**
     * Module version
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_version = '1.0.0';

    /**
     * Generation start time
     *
     * @since 1.0.0
     * @var float
     */
    protected $generation_start_time;

    /**
     * Current operation context
     *
     * @since 1.0.0
     * @var array
     */
    protected $operation_context = [];

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize module
     *
     * @since 1.0.0
     * @return void
     */
    protected function init() {
        // Override in child classes if needed
    }

    /**
     * Get module name
     *
     * @since 1.0.0
     * @return string Module name
     */
    public function get_name() {
        return $this->module_name;
    }

    /**
     * Get module description
     *
     * @since 1.0.0
     * @return string Module description
     */
    public function get_description() {
        return $this->module_description;
    }

    /**
     * Get module version
     *
     * @since 1.0.0
     * @return string Module version
     */
    public function get_version() {
        return $this->module_version;
    }

    /**
     * Validate module dependencies
     *
     * @since 1.0.0
     * @return bool|WP_Error True if dependencies are met, WP_Error otherwise
     */
    public function check_dependencies() {
        // Check if BuddyPress is active
        if (!class_exists('BuddyPress')) {
            return new WP_Error(
                'buddypress_missing',
                __('BuddyPress is required for this module.', BP_PLAYGROUND_TEXT_DOMAIN)
            );
        }

        return true;
    }

    /**
     * Validate generation arguments
     *
     * @since 1.0.0
     * @param array $args Arguments to validate
     * @param array $rules Validation rules
     * @return array|WP_Error Validated arguments or error
     */
    protected function validate_args($args, $rules) {
        $core = bp_playground_get_module('core');
        if ($core) {
            return $core->validate_params($args, $rules);
        }

        // Fallback basic validation
        return $args;
    }

    /**
     * Start generation tracking
     *
     * @since 1.0.0
     * @param string $operation_name Operation name
     * @return void
     */
    protected function start_generation($operation_name = '') {
        $this->generation_start_time = microtime(true);
        $this->operation_context = [
            'operation' => $operation_name ?: $this->module_name,
            'start_time' => $this->generation_start_time,
            'memory_start' => memory_get_usage(true),
        ];

        $this->log("Starting {$this->module_name} generation: {$operation_name}");

        // Increment module stat
        $core = bp_playground_get_module('core');
        if ($core) {
            $core->increment_stat($this->module_name . '_operations_started');
        }
    }

    /**
     * End generation tracking
     *
     * @since 1.0.0
     * @return array Generation statistics
     */
    protected function end_generation() {
        $end_time = microtime(true);
        $duration = $end_time - $this->generation_start_time;
        $memory_end = memory_get_usage(true);
        $memory_used = $memory_end - $this->operation_context['memory_start'];

        $stats = [
            'operation' => $this->operation_context['operation'],
            'duration' => round($duration, 3),
            'memory_used' => $memory_used,
            'memory_formatted' => $this->format_bytes($memory_used),
            'start_time' => $this->operation_context['start_time'],
            'end_time' => $end_time,
        ];

        $this->log(sprintf(
            "Completed %s generation in %.3f seconds (Memory: %s)",
            $this->module_name,
            $duration,
            $this->format_bytes($memory_used)
        ));

        // Update core stats
        $core = bp_playground_get_module('core');
        if ($core) {
            $core->increment_stat($this->module_name . '_operations_completed');
        }

        $this->operation_context = [];
        return $stats;
    }

    /**
     * Log a message
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param string $level Log level (debug, info, warning, error)
     * @param array $context Additional context
     * @return void
     */
    protected function log($message, $level = 'info', $context = []) {
        $context['module'] = $this->module_name;
        
        $core = bp_playground_get_module('core');
        if ($core) {
            $core->log($message, $level, $context);
        }
    }

    /**
     * Log an error message
     *
     * @since 1.0.0
     * @param string $message Error message
     * @param array $context Additional context
     * @return void
     */
    protected function log_error($message, $context = []) {
        $this->log($message, 'error', $context);

        // Add error to core stats
        $core = bp_playground_get_module('core');
        if ($core) {
            $core->add_error($message, array_merge($context, ['module' => $this->module_name]));
        }
    }

    /**
     * Show progress information
     *
     * @since 1.0.0
     * @param int $current Current progress
     * @param int $total Total items
     * @param string $message Progress message
     * @return void
     */
    protected function show_progress($current, $total, $message = '') {
        if (defined('WP_CLI') && WP_CLI) {
            $percentage = $total > 0 ? round(($current / $total) * 100, 1) : 0;
            $progress_message = $message ? $message : "Processing {$this->module_name}";
            
            WP_CLI::log(sprintf(
                '%s: %d/%d (%s%%)',
                $progress_message,
                $current,
                $total,
                $percentage
            ));
        }
    }

    /**
     * Format bytes to human readable format
     *
     * @since 1.0.0
     * @param int $bytes Number of bytes
     * @return string Formatted string
     */
    protected function format_bytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Generate random string
     *
     * @since 1.0.0
     * @param int $length String length
     * @param string $charset Character set to use
     * @return string Random string
     */
    protected function generate_random_string($length = 10, $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
        $string = '';
        $charset_length = strlen($charset);
        
        for ($i = 0; $i < $length; $i++) {
            $string .= $charset[rand(0, $charset_length - 1)];
        }
        
        return $string;
    }

    /**
     * Generate random date within range
     *
     * @since 1.0.0
     * @param string $start_date Start date (Y-m-d format)
     * @param string $end_date End date (Y-m-d format)
     * @return string Random date in Y-m-d format
     */
    protected function generate_random_date($start_date = null, $end_date = null) {
        $start_date = $start_date ?: date('Y-m-d', strtotime('-1 year'));
        $end_date = $end_date ?: date('Y-m-d');

        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);

        $random_timestamp = rand($start_timestamp, $end_timestamp);
        return date('Y-m-d', $random_timestamp);
    }

    /**
     * Generate random time within day
     *
     * @since 1.0.0
     * @return string Random time in H:i:s format
     */
    protected function generate_random_time() {
        $hour = str_pad(rand(0, 23), 2, '0', STR_PAD_LEFT);
        $minute = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $second = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        
        return "{$hour}:{$minute}:{$second}";
    }

    /**
     * Generate random datetime within range
     *
     * @since 1.0.0
     * @param string $start_datetime Start datetime
     * @param string $end_datetime End datetime
     * @return string Random datetime in Y-m-d H:i:s format
     */
    protected function generate_random_datetime($start_datetime = null, $end_datetime = null) {
        $start_datetime = $start_datetime ?: date('Y-m-d H:i:s', strtotime('-1 year'));
        $end_datetime = $end_datetime ?: date('Y-m-d H:i:s');

        $start_timestamp = strtotime($start_datetime);
        $end_timestamp = strtotime($end_datetime);

        $random_timestamp = rand($start_timestamp, $end_timestamp);
        return date('Y-m-d H:i:s', $random_timestamp);
    }

    /**
     * Get random items from array
     *
     * @since 1.0.0
     * @param array $array Source array
     * @param int $count Number of items to get
     * @param bool $preserve_keys Whether to preserve array keys
     * @return array Random items
     */
    protected function get_random_items($array, $count = 1, $preserve_keys = false) {
        if (empty($array)) {
            return [];
        }

        $count = min($count, count($array));
        $keys = array_rand($array, $count);
        
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $result = [];
        foreach ($keys as $key) {
            if ($preserve_keys) {
                $result[$key] = $array[$key];
            } else {
                $result[] = $array[$key];
            }
        }

        return $result;
    }

    /**
     * Check if module is enabled
     *
     * @since 1.0.0
     * @return bool True if module is enabled
     */
    protected function is_module_enabled() {
        // Default implementation - override in child classes for specific checks
        return true;
    }

    /**
     * Get random user IDs
     *
     * @since 1.0.0
     * @param int $count Number of user IDs to get
     * @param array $exclude_ids User IDs to exclude
     * @return array Random user IDs
     */
    protected function get_random_user_ids($count = 10, $exclude_ids = []) {
        global $wpdb;

        $exclude_clause = '';
        if (!empty($exclude_ids)) {
            $exclude_ids = array_map('intval', $exclude_ids);
            $exclude_clause = 'WHERE ID NOT IN (' . implode(',', $exclude_ids) . ')';
        }

        $user_ids = $wpdb->get_col(
            "SELECT ID FROM {$wpdb->users} {$exclude_clause} ORDER BY RAND() LIMIT " . intval($count)
        );

        return array_map('intval', $user_ids);
    }

    /**
     * Check if user exists
     *
     * @since 1.0.0
     * @param int $user_id User ID to check
     * @return bool True if user exists
     */
    protected function user_exists($user_id) {
        return get_userdata($user_id) !== false;
    }

    /**
     * Sanitize and validate ID
     *
     * @since 1.0.0
     * @param mixed $id ID to validate
     * @return int|false Valid ID or false on failure
     */
    protected function validate_id($id) {
        $id = intval($id);
        return $id > 0 ? $id : false;
    }

    /**
     * Get default generation arguments
     *
     * @since 1.0.0
     * @return array Default arguments
     */
    protected function get_default_args() {
        return [
            'count' => 100,
            'batch_size' => 50,
            'dry_run' => false,
        ];
    }

    /**
     * Handle WP_Error or return success
     *
     * @since 1.0.0
     * @param mixed $result Result to check
     * @param string $error_message Default error message
     * @return mixed Original result or WP_Error
     */
    protected function handle_result($result, $error_message = 'Operation failed') {
        if (is_wp_error($result)) {
            $this->log_error($result->get_error_message());
            return $result;
        }

        if ($result === false) {
            $this->log_error($error_message);
            return new WP_Error('operation_failed', $error_message);
        }

        return $result;
    }

    /**
     * Get WordPress timezone
     *
     * @since 1.0.0
     * @return DateTimeZone WordPress timezone
     */
    protected function get_wp_timezone() {
        $timezone_string = get_option('timezone_string');
        
        if ($timezone_string) {
            return new DateTimeZone($timezone_string);
        }

        $offset = get_option('gmt_offset');
        $hours = (int) $offset;
        $minutes = abs(($offset - $hours) * 60);
        $offset_string = sprintf('%+03d:%02d', $hours, $minutes);
        
        return new DateTimeZone($offset_string);
    }

    /**
     * Get current time in WordPress timezone
     *
     * @since 1.0.0
     * @param string $format Date format
     * @return string Formatted current time
     */
    protected function get_wp_current_time($format = 'Y-m-d H:i:s') {
        $datetime = new DateTime('now', $this->get_wp_timezone());
        return $datetime->format($format);
    }
}