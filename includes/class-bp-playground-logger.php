<?php
/**
 * BuddyPress Playground Logger
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
 * Comprehensive logging system for BuddyPress Playground
 *
 * @since 1.0.0
 */
class BP_Playground_Logger {

    /**
     * Log levels and their numeric values
     *
     * @since 1.0.0
     * @var array
     */
    private $log_levels = [
        'debug' => 100,
        'info' => 200,
        'warning' => 300,
        'error' => 400,
        'critical' => 500,
    ];

    /**
     * Current log level threshold
     *
     * @since 1.0.0
     * @var string
     */
    private $log_level = 'info';

    /**
     * Whether logging is enabled
     *
     * @since 1.0.0
     * @var bool
     */
    private $logging_enabled = true;

    /**
     * Log to database
     *
     * @since 1.0.0
     * @var bool
     */
    private $log_to_database = true;

    /**
     * Log to file
     *
     * @since 1.0.0
     * @var bool
     */
    private $log_to_file = false;

    /**
     * Log file path
     *
     * @since 1.0.0
     * @var string
     */
    private $log_file_path;

    /**
     * Maximum database log entries
     *
     * @since 1.0.0
     * @var int
     */
    private $max_db_entries = 10000;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->load_settings();
        $this->setup_log_file();
        $this->setup_cleanup_hooks();
    }

    /**
     * Load logger settings
     *
     * @since 1.0.0
     * @return void
     */
    private function load_settings() {
        $core = bp_playground_get_module('core');
        $settings = $core ? $core->get_settings() : [];

        $this->logging_enabled = isset($settings['enable_logging']) ? $settings['enable_logging'] : true;
        $this->log_level = isset($settings['log_level']) ? $settings['log_level'] : 'info';
        $this->log_to_database = isset($settings['log_to_database']) ? $settings['log_to_database'] : true;
        $this->log_to_file = isset($settings['log_to_file']) ? $settings['log_to_file'] : false;
        $this->max_db_entries = isset($settings['max_log_entries']) ? $settings['max_log_entries'] : 10000;
    }

    /**
     * Setup log file path
     *
     * @since 1.0.0
     * @return void
     */
    private function setup_log_file() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/bp-playground-logs';

        // Create log directory if it doesn't exist
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
            
            // Add index.php for security
            file_put_contents($log_dir . '/index.php', '<?php // Silence is golden');
            
            // Add .htaccess to prevent direct access
            $htaccess_content = "Order deny,allow\nDeny from all";
            file_put_contents($log_dir . '/.htaccess', $htaccess_content);
        }

        $this->log_file_path = $log_dir . '/bp-playground-' . date('Y-m-d') . '.log';
    }

    /**
     * Setup cleanup hooks
     *
     * @since 1.0.0
     * @return void
     */
    private function setup_cleanup_hooks() {
        // Schedule daily cleanup if not already scheduled
        if (!wp_next_scheduled('bp_playground_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'bp_playground_cleanup_logs');
        }

        add_action('bp_playground_cleanup_logs', [$this, 'cleanup_old_logs']);
    }

    /**
     * Log a message
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param string $level Log level (debug, info, warning, error, critical)
     * @param array $context Additional context data
     * @return bool Success status
     */
    public function log($message, $level = 'info', $context = []) {
        if (!$this->logging_enabled) {
            return false;
        }

        // Check if this level should be logged
        if (!$this->should_log($level)) {
            return false;
        }

        // Prepare log entry
        $log_entry = $this->prepare_log_entry($message, $level, $context);

        $success = true;

        // Log to database
        if ($this->log_to_database) {
            $success = $this->log_to_db($log_entry) && $success;
        }

        // Log to file
        if ($this->log_to_file) {
            $success = $this->log_to_file_system($log_entry) && $success;
        }

        // Log to WP-CLI if available and appropriate level
        if (defined('WP_CLI') && WP_CLI && in_array($level, ['error', 'critical'])) {
            WP_CLI::warning("BP Playground {$level}: {$message}");
        }

        return $success;
    }

    /**
     * Log debug message
     *
     * @since 1.0.0
     * @param string $message Debug message
     * @param array $context Additional context
     * @return bool Success status
     */
    public function debug($message, $context = []) {
        return $this->log($message, 'debug', $context);
    }

    /**
     * Log info message
     *
     * @since 1.0.0
     * @param string $message Info message
     * @param array $context Additional context
     * @return bool Success status
     */
    public function info($message, $context = []) {
        return $this->log($message, 'info', $context);
    }

    /**
     * Log warning message
     *
     * @since 1.0.0
     * @param string $message Warning message
     * @param array $context Additional context
     * @return bool Success status
     */
    public function warning($message, $context = []) {
        return $this->log($message, 'warning', $context);
    }

    /**
     * Log error message
     *
     * @since 1.0.0
     * @param string $message Error message
     * @param array $context Additional context
     * @return bool Success status
     */
    public function error($message, $context = []) {
        return $this->log($message, 'error', $context);
    }

    /**
     * Log critical message
     *
     * @since 1.0.0
     * @param string $message Critical message
     * @param array $context Additional context
     * @return bool Success status
     */
    public function critical($message, $context = []) {
        return $this->log($message, 'critical', $context);
    }

    /**
     * Check if level should be logged
     *
     * @since 1.0.0
     * @param string $level Log level to check
     * @return bool Whether to log this level
     */
    private function should_log($level) {
        if (!isset($this->log_levels[$level])) {
            return false;
        }

        $level_value = $this->log_levels[$level];
        $threshold_value = isset($this->log_levels[$this->log_level]) 
            ? $this->log_levels[$this->log_level] 
            : $this->log_levels['info'];

        return $level_value >= $threshold_value;
    }

    /**
     * Prepare log entry
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param string $level Log level
     * @param array $context Additional context
     * @return array Prepared log entry
     */
    private function prepare_log_entry($message, $level, $context) {
        return [
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'user_id' => get_current_user_id(),
            'ip_address' => $this->get_user_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'request_uri' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
            'module' => isset($context['module']) ? $context['module'] : 'core',
        ];
    }

    /**
     * Log to database
     *
     * @since 1.0.0
     * @param array $log_entry Log entry data
     * @return bool Success status
     */
    private function log_to_db($log_entry) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'bp_playground_logs';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
            $this->create_log_table();
        }

        $result = $wpdb->insert(
            $table_name,
            [
                'level' => $log_entry['level'],
                'message' => $log_entry['message'],
                'context' => maybe_serialize($log_entry['context']),
                'memory_usage' => $log_entry['memory_usage'],
                'peak_memory' => $log_entry['peak_memory'],
                'user_id' => $log_entry['user_id'],
                'ip_address' => $log_entry['ip_address'],
                'user_agent' => $log_entry['user_agent'],
                'request_uri' => $log_entry['request_uri'],
                'module' => $log_entry['module'],
                'created_at' => $log_entry['timestamp'],
            ],
            [
                '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s'
            ]
        );

        return $result !== false;
    }

    /**
     * Log to file system
     *
     * @since 1.0.0
     * @param array $log_entry Log entry data
     * @return bool Success status
     */
    private function log_to_file_system($log_entry) {
        $formatted_message = $this->format_log_message($log_entry);
        
        $result = file_put_contents(
            $this->log_file_path,
            $formatted_message . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        return $result !== false;
    }

    /**
     * Format log message for file output
     *
     * @since 1.0.0
     * @param array $log_entry Log entry data
     * @return string Formatted log message
     */
    private function format_log_message($log_entry) {
        $timestamp = $log_entry['timestamp'];
        $level = strtoupper($log_entry['level']);
        $message = $log_entry['message'];
        $module = $log_entry['module'];

        $formatted = "[{$timestamp}] {$level} [{$module}] {$message}";

        // Add context if present
        if (!empty($log_entry['context'])) {
            $context_json = json_encode($log_entry['context'], JSON_UNESCAPED_SLASHES);
            $formatted .= " Context: {$context_json}";
        }

        // Add memory info for debug level
        if ($log_entry['level'] === 'debug') {
            $memory = $this->format_bytes($log_entry['memory_usage']);
            $peak = $this->format_bytes($log_entry['peak_memory']);
            $formatted .= " Memory: {$memory} (Peak: {$peak})";
        }

        return $formatted;
    }

    /**
     * Create log table
     *
     * @since 1.0.0
     * @return void
     */
    private function create_log_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'bp_playground_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
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
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Get recent logs
     *
     * @since 1.0.0
     * @param array $args Query arguments
     * @return array Log entries
     */
    public function get_logs($args = []) {
        global $wpdb;

        $defaults = [
            'limit' => 100,
            'offset' => 0,
            'level' => '',
            'module' => '',
            'since' => '',
            'order' => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);
        $table_name = $wpdb->prefix . 'bp_playground_logs';

        // Build WHERE clause
        $where_clauses = [];
        $where_values = [];

        if (!empty($args['level'])) {
            $where_clauses[] = 'level = %s';
            $where_values[] = $args['level'];
        }

        if (!empty($args['module'])) {
            $where_clauses[] = 'module = %s';
            $where_values[] = $args['module'];
        }

        if (!empty($args['since'])) {
            $where_clauses[] = 'created_at >= %s';
            $where_values[] = $args['since'];
        }

        $where_clause = '';
        if (!empty($where_clauses)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        // Build ORDER BY clause
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        // Build query
        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY created_at {$order} LIMIT %d OFFSET %d";
        $where_values[] = intval($args['limit']);
        $where_values[] = intval($args['offset']);

        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }

        $results = $wpdb->get_results($query, ARRAY_A);

        // Unserialize context data
        foreach ($results as &$result) {
            $result['context'] = maybe_unserialize($result['context']);
        }

        return $results;
    }

    /**
     * Get log statistics
     *
     * @since 1.0.0
     * @param string $since Since date (optional)
     * @return array Log statistics
     */
    public function get_log_stats($since = '') {
        global $wpdb;

        $table_name = $wpdb->prefix . 'bp_playground_logs';
        $where_clause = '';
        $where_values = [];

        if (!empty($since)) {
            $where_clause = 'WHERE created_at >= %s';
            $where_values[] = $since;
        }

        // Total count
        $total_query = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
        if (!empty($where_values)) {
            $total_query = $wpdb->prepare($total_query, $where_values);
        }
        $total_logs = $wpdb->get_var($total_query);

        // Count by level
        $level_query = "SELECT level, COUNT(*) as count FROM {$table_name} {$where_clause} GROUP BY level";
        if (!empty($where_values)) {
            $level_query = $wpdb->prepare($level_query, $where_values);
        }
        $level_counts = $wpdb->get_results($level_query, ARRAY_A);

        // Count by module
        $module_query = "SELECT module, COUNT(*) as count FROM {$table_name} {$where_clause} GROUP BY module ORDER BY count DESC LIMIT 10";
        if (!empty($where_values)) {
            $module_query = $wpdb->prepare($module_query, $where_values);
        }
        $module_counts = $wpdb->get_results($module_query, ARRAY_A);

        // Recent errors
        $error_query = "SELECT message, created_at FROM {$table_name} WHERE level IN ('error', 'critical') {$where_clause} ORDER BY created_at DESC LIMIT 5";
        $error_where_clause = !empty($where_clause) ? 'AND ' . str_replace('WHERE ', '', $where_clause) : '';
        $error_query = str_replace($where_clause, $error_where_clause, $error_query);
        
        if (!empty($where_values)) {
            $error_query = $wpdb->prepare($error_query, $where_values);
        }
        $recent_errors = $wpdb->get_results($error_query, ARRAY_A);

        return [
            'total_logs' => intval($total_logs),
            'level_counts' => $level_counts,
            'module_counts' => $module_counts,
            'recent_errors' => $recent_errors,
        ];
    }

    /**
     * Clear logs
     *
     * @since 1.0.0
     * @param array $args Clear arguments
     * @return int Number of deleted logs
     */
    public function clear_logs($args = []) {
        global $wpdb;

        $defaults = [
            'older_than_days' => 30,
            'level' => '',
            'module' => '',
        ];

        $args = wp_parse_args($args, $defaults);
        $table_name = $wpdb->prefix . 'bp_playground_logs';

        // Build WHERE clause
        $where_clauses = [];
        $where_values = [];

        if ($args['older_than_days'] > 0) {
            $where_clauses[] = 'created_at < %s';
            $where_values[] = date('Y-m-d H:i:s', time() - ($args['older_than_days'] * 24 * 3600));
        }

        if (!empty($args['level'])) {
            $where_clauses[] = 'level = %s';
            $where_values[] = $args['level'];
        }

        if (!empty($args['module'])) {
            $where_clauses[] = 'module = %s';
            $where_values[] = $args['module'];
        }

        if (empty($where_clauses)) {
            // If no conditions, don't delete everything - require at least older_than_days
            $where_clauses[] = 'created_at < %s';
            $where_values[] = date('Y-m-d H:i:s', time() - (7 * 24 * 3600)); // Default to 7 days
        }

        $where_clause = 'WHERE ' . implode(' AND ', $where_clauses);
        $query = "DELETE FROM {$table_name} {$where_clause}";

        if (!empty($where_values)) {
            $query = $wpdb->prepare($query, $where_values);
        }

        return $wpdb->query($query);
    }

    /**
     * Cleanup old logs (scheduled task)
     *
     * @since 1.0.0
     * @return void
     */
    public function cleanup_old_logs() {
        $core = bp_playground_get_module('core');
        $settings = $core ? $core->get_settings() : [];
        
        $retention_days = isset($settings['cleanup_retention_days']) ? $settings['cleanup_retention_days'] : 30;

        // Clean database logs
        $deleted = $this->clear_logs(['older_than_days' => $retention_days]);
        
        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} old log entries", ['module' => 'logger']);
        }

        // Clean up old log files
        $this->cleanup_old_log_files($retention_days);

        // Enforce maximum database entries
        $this->enforce_max_db_entries();
    }

    /**
     * Cleanup old log files
     *
     * @since 1.0.0
     * @param int $retention_days Number of days to retain
     * @return void
     */
    private function cleanup_old_log_files($retention_days) {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/bp-playground-logs';

        if (!is_dir($log_dir)) {
            return;
        }

        $cutoff_time = time() - ($retention_days * 24 * 3600);
        $files = glob($log_dir . '/bp-playground-*.log');

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff_time) {
                unlink($file);
            }
        }
    }

    /**
     * Enforce maximum database entries
     *
     * @since 1.0.0
     * @return void
     */
    private function enforce_max_db_entries() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'bp_playground_logs';
        $current_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");

        if ($current_count > $this->max_db_entries) {
            $excess = $current_count - $this->max_db_entries;
            
            // Delete oldest entries
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$table_name} ORDER BY created_at ASC LIMIT %d",
                $excess
            ));
        }
    }

    /**
     * Export logs to file
     *
     * @since 1.0.0
     * @param array $args Export arguments
     * @return string|WP_Error File path on success, WP_Error on failure
     */
    public function export_logs($args = []) {
        $defaults = [
            'format' => 'csv',
            'since' => date('Y-m-d', strtotime('-7 days')),
            'level' => '',
            'module' => '',
            'filename' => '',
        ];

        $args = wp_parse_args($args, $defaults);

        // Get logs
        $logs = $this->get_logs([
            'limit' => 10000, // Large limit for export
            'since' => $args['since'],
            'level' => $args['level'],
            'module' => $args['module'],
            'order' => 'ASC',
        ]);

        if (empty($logs)) {
            return new WP_Error('no_logs', 'No logs found for export.');
        }

        // Generate filename
        if (empty($args['filename'])) {
            $args['filename'] = 'bp-playground-logs-' . date('Y-m-d-H-i-s') . '.' . $args['format'];
        }

        $upload_dir = wp_upload_dir();
        $export_path = $upload_dir['basedir'] . '/bp-playground-logs/' . $args['filename'];

        // Export based on format
        switch ($args['format']) {
            case 'csv':
                $result = $this->export_logs_csv($logs, $export_path);
                break;
            case 'json':
                $result = $this->export_logs_json($logs, $export_path);
                break;
            default:
                return new WP_Error('invalid_format', 'Invalid export format.');
        }

        return $result ? $export_path : new WP_Error('export_failed', 'Failed to export logs.');
    }

    /**
     * Export logs as CSV
     *
     * @since 1.0.0
     * @param array $logs Log entries
     * @param string $file_path File path
     * @return bool Success status
     */
    private function export_logs_csv($logs, $file_path) {
        $handle = fopen($file_path, 'w');
        if (!$handle) {
            return false;
        }

        // Write header
        fputcsv($handle, ['ID', 'Timestamp', 'Level', 'Module', 'Message', 'User ID', 'IP Address', 'Memory Usage']);

        // Write data
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log['id'],
                $log['created_at'],
                $log['level'],
                $log['module'],
                $log['message'],
                $log['user_id'],
                $log['ip_address'],
                $this->format_bytes($log['memory_usage']),
            ]);
        }

        fclose($handle);
        return true;
    }

    /**
     * Export logs as JSON
     *
     * @since 1.0.0
     * @param array $logs Log entries
     * @param string $file_path File path
     * @return bool Success status
     */
    private function export_logs_json($logs, $file_path) {
        $json_data = json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return file_put_contents($file_path, $json_data) !== false;
    }

    /**
     * Get user IP address
     *
     * @since 1.0.0
     * @return string User IP address
     */
    private function get_user_ip() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                
                // Handle comma-separated IPs (from proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }

    /**
     * Format bytes to human readable format
     *
     * @since 1.0.0
     * @param int $bytes Number of bytes
     * @return string Formatted string
     */
    private function format_bytes($bytes) {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Set log level
     *
     * @since 1.0.0
     * @param string $level New log level
     * @return void
     */
    public function set_log_level($level) {
        if (isset($this->log_levels[$level])) {
            $this->log_level = $level;
        }
    }

    /**
     * Get current log level
     *
     * @since 1.0.0
     * @return string Current log level
     */
    public function get_log_level() {
        return $this->log_level;
    }

    /**
     * Enable or disable logging
     *
     * @since 1.0.0
     * @param bool $enabled Whether to enable logging
     * @return void
     */
    public function set_logging_enabled($enabled) {
        $this->logging_enabled = (bool) $enabled;
    }

    /**
     * Check if logging is enabled
     *
     * @since 1.0.0
     * @return bool Whether logging is enabled
     */
    public function is_logging_enabled() {
        return $this->logging_enabled;
    }

    /**
     * Get available log levels
     *
     * @since 1.0.0
     * @return array Available log levels
     */
    public function get_available_levels() {
        return array_keys($this->log_levels);
    }
}