<?php
/**
 * BuddyPress Playground Utility Functions
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
 * Utility functions and helpers for BuddyPress Playground
 *
 * @since 1.0.0
 */
class BP_Playground_Utils {

    /**
     * Generate Lorem Ipsum text
     *
     * @since 1.0.0
     * @param int $word_count Number of words to generate
     * @param bool $start_with_lorem Whether to start with "Lorem ipsum"
     * @return string Generated text
     */
    public static function generate_lorem_ipsum($word_count = 50, $start_with_lorem = false) {
        $lorem_words = [
            'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit',
            'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'ut', 'labore', 'et', 'dolore',
            'magna', 'aliqua', 'enim', 'ad', 'minim', 'veniam', 'quis', 'nostrud',
            'exercitation', 'ullamco', 'laboris', 'nisi', 'aliquip', 'ex', 'ea', 'commodo',
            'consequat', 'duis', 'aute', 'irure', 'in', 'reprehenderit', 'voluptate',
            'velit', 'esse', 'cillum', 'fugiat', 'nulla', 'pariatur', 'excepteur', 'sint',
            'occaecat', 'cupidatat', 'non', 'proident', 'sunt', 'culpa', 'qui', 'officia',
            'deserunt', 'mollit', 'anim', 'id', 'est', 'laborum'
        ];

        $words = [];
        
        if ($start_with_lorem) {
            $words = ['Lorem', 'ipsum', 'dolor', 'sit', 'amet'];
            $word_count -= 5;
        }

        for ($i = 0; $i < $word_count; $i++) {
            $words[] = $lorem_words[array_rand($lorem_words)];
        }

        $text = implode(' ', $words);
        
        // Capitalize first letter and add period
        $text = ucfirst($text) . '.';
        
        return $text;
    }

    /**
     * Generate realistic company names
     *
     * @since 1.0.0
     * @return string Generated company name
     */
    public static function generate_company_name() {
        $prefixes = ['Tech', 'Digital', 'Smart', 'Global', 'Creative', 'Dynamic', 'Innovative', 'Future'];
        $suffixes = ['Solutions', 'Systems', 'Technologies', 'Innovations', 'Labs', 'Studios', 'Group', 'Corp'];
        
        $prefix = $prefixes[array_rand($prefixes)];
        $suffix = $suffixes[array_rand($suffixes)];
        
        return $prefix . ' ' . $suffix;
    }

    /**
     * Generate realistic email addresses
     *
     * @since 1.0.0
     * @param string $name Base name for email
     * @param string $domain Optional domain
     * @return string Generated email address
     */
    public static function generate_email($name = '', $domain = '') {
        if (empty($name)) {
            $names = ['john', 'jane', 'alex', 'sarah', 'mike', 'lisa', 'david', 'emma'];
            $name = $names[array_rand($names)];
        }

        if (empty($domain)) {
            $domains = ['example.com', 'test.org', 'demo.net', 'sample.co', 'playground.dev'];
            $domain = $domains[array_rand($domains)];
        }

        // Clean name for email
        $name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        
        // Add random number to ensure uniqueness
        $name .= rand(100, 999);
        
        return $name . '@' . $domain;
    }

    /**
     * Generate realistic phone numbers
     *
     * @since 1.0.0
     * @param string $format Phone number format
     * @return string Generated phone number
     */
    public static function generate_phone_number($format = '(###) ###-####') {
        $phone = '';
        for ($i = 0; $i < strlen($format); $i++) {
            $char = $format[$i];
            if ($char === '#') {
                $phone .= rand(0, 9);
            } else {
                $phone .= $char;
            }
        }
        return $phone;
    }

    /**
     * Generate realistic addresses
     *
     * @since 1.0.0
     * @return array Generated address components
     */
    public static function generate_address() {
        $street_numbers = range(100, 9999);
        $street_names = [
            'Main St', 'Oak Ave', 'Pine St', 'Maple Dr', 'Cedar Ln', 'Elm St',
            'Park Ave', 'First St', 'Second St', 'Broadway', 'Market St', 'Church St'
        ];
        $cities = [
            'Springfield', 'Franklin', 'Georgetown', 'Madison', 'Arlington', 'Riverside',
            'Oakland', 'Fairview', 'Midway', 'Salem', 'Dover', 'Hudson'
        ];
        $states = [
            'CA', 'NY', 'TX', 'FL', 'PA', 'IL', 'OH', 'GA', 'NC', 'MI', 'NJ', 'VA'
        ];

        return [
            'street' => $street_numbers[array_rand($street_numbers)] . ' ' . $street_names[array_rand($street_names)],
            'city' => $cities[array_rand($cities)],
            'state' => $states[array_rand($states)],
            'zip' => str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
        ];
    }

    /**
     * Generate random dates within a range
     *
     * @since 1.0.0
     * @param string $start_date Start date (Y-m-d format)
     * @param string $end_date End date (Y-m-d format)
     * @param string $format Output format
     * @return string Generated date
     */
    public static function generate_random_date($start_date = null, $end_date = null, $format = 'Y-m-d') {
        $start_date = $start_date ?: date('Y-m-d', strtotime('-1 year'));
        $end_date = $end_date ?: date('Y-m-d');

        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);

        $random_timestamp = rand($start_timestamp, $end_timestamp);
        return date($format, $random_timestamp);
    }

    /**
     * Generate random time
     *
     * @since 1.0.0
     * @param string $format Time format
     * @return string Generated time
     */
    public static function generate_random_time($format = 'H:i:s') {
        $hour = str_pad(rand(0, 23), 2, '0', STR_PAD_LEFT);
        $minute = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $second = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        
        $time_string = "{$hour}:{$minute}:{$second}";
        return date($format, strtotime($time_string));
    }

    /**
     * Generate random color hex codes
     *
     * @since 1.0.0
     * @return string Generated hex color
     */
    public static function generate_random_color() {
        return '#' . str_pad(dechex(rand(0, 16777215)), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate random URLs
     *
     * @since 1.0.0
     * @param string $type URL type (website, social, etc.)
     * @return string Generated URL
     */
    public static function generate_random_url($type = 'website') {
        switch ($type) {
            case 'social':
                $platforms = ['twitter.com', 'facebook.com', 'linkedin.com', 'instagram.com'];
                $platform = $platforms[array_rand($platforms)];
                $username = 'user' . rand(100, 999);
                return "https://{$platform}/{$username}";
                
            case 'website':
            default:
                $domains = ['example.com', 'demo.org', 'test.net', 'sample.co'];
                $domain = $domains[array_rand($domains)];
                return "https://www.{$domain}";
        }
    }

    /**
     * Format file sizes
     *
     * @since 1.0.0
     * @param int $bytes Number of bytes
     * @param int $precision Decimal precision
     * @return string Formatted file size
     */
    public static function format_file_size($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Parse memory limit strings
     *
     * @since 1.0.0
     * @param string $memory_limit Memory limit string (e.g., "128M")
     * @return int Memory limit in bytes
     */
    public static function parse_memory_limit($memory_limit) {
        $unit = strtoupper(substr($memory_limit, -1));
        $value = (int) $memory_limit;
        
        switch ($unit) {
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Generate secure random strings
     *
     * @since 1.0.0
     * @param int $length String length
     * @param string $charset Character set to use
     * @return string Generated random string
     */
    public static function generate_secure_random_string($length = 32, $charset = null) {
        if ($charset === null) {
            $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        }
        
        $string = '';
        $charset_length = strlen($charset);
        
        for ($i = 0; $i < $length; $i++) {
            $string .= $charset[wp_rand(0, $charset_length - 1)];
        }
        
        return $string;
    }

    /**
     * Sanitize and validate user input
     *
     * @since 1.0.0
     * @param mixed $input Input to sanitize
     * @param string $type Input type (text, email, url, int, float, bool)
     * @return mixed Sanitized input
     */
    public static function sanitize_input($input, $type = 'text') {
        switch ($type) {
            case 'text':
                return sanitize_text_field($input);
            case 'email':
                return sanitize_email($input);
            case 'url':
                return esc_url_raw($input);
            case 'int':
                return intval($input);
            case 'float':
                return floatval($input);
            case 'bool':
                return (bool) $input;
            case 'array':
                return is_array($input) ? array_map(['self', 'sanitize_text_field'], $input) : [];
            default:
                return sanitize_text_field($input);
        }
    }

    /**
     * Check if a string is JSON
     *
     * @since 1.0.0
     * @param string $string String to check
     * @return bool Whether string is valid JSON
     */
    public static function is_json($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Get WordPress timezone
     *
     * @since 1.0.0
     * @return DateTimeZone WordPress timezone object
     */
    public static function get_wp_timezone() {
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
     * Convert array to CSV string
     *
     * @since 1.0.0
     * @param array $data Array data
     * @param array $headers Optional headers
     * @return string CSV string
     */
    public static function array_to_csv($data, $headers = []) {
        if (empty($data)) {
            return '';
        }
        
        $csv = '';
        
        // Add headers if provided
        if (!empty($headers)) {
            $csv .= implode(',', array_map(function($header) {
                return '"' . str_replace('"', '""', $header) . '"';
            }, $headers)) . "\n";
        }
        
        // Add data rows
        foreach ($data as $row) {
            $csv_row = [];
            foreach ($row as $value) {
                $csv_row[] = '"' . str_replace('"', '""', $value) . '"';
            }
            $csv .= implode(',', $csv_row) . "\n";
        }
        
        return $csv;
    }

    /**
     * Generate weighted random selection
     *
     * @since 1.0.0
     * @param array $weights Array of weights [item => weight]
     * @return mixed Selected item
     */
    public static function weighted_random($weights) {
        $total_weight = array_sum($weights);
        $random = wp_rand(1, $total_weight);
        
        $cumulative = 0;
        foreach ($weights as $item => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $item;
            }
        }
        
        // Fallback to first item
        return array_keys($weights)[0];
    }

    /**
     * Generate realistic user agent strings
     *
     * @since 1.0.0
     * @return string User agent string
     */
    public static function generate_user_agent() {
        $user_agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ];
        
        return $user_agents[array_rand($user_agents)];
    }

    /**
     * Generate realistic IP addresses
     *
     * @since 1.0.0
     * @param string $type IP type (public, private, local)
     * @return string Generated IP address
     */
    public static function generate_ip_address($type = 'public') {
        switch ($type) {
            case 'private':
                $ranges = [
                    ['10.0.0.0', '10.255.255.255'],
                    ['172.16.0.0', '172.31.255.255'],
                    ['192.168.0.0', '192.168.255.255'],
                ];
                $range = $ranges[array_rand($ranges)];
                $start = ip2long($range[0]);
                $end = ip2long($range[1]);
                return long2ip(wp_rand($start, $end));
                
            case 'local':
                return '127.0.0.1';
                
            case 'public':
            default:
                // Generate random public IP (avoiding private ranges)
                $ip = '';
                for ($i = 0; $i < 4; $i++) {
                    if ($i > 0) $ip .= '.';
                    $ip .= wp_rand(1, 254);
                }
                return $ip;
        }
    }

    /**
     * Create directory with proper permissions
     *
     * @since 1.0.0
     * @param string $directory Directory path
     * @param int $permissions Directory permissions
     * @return bool Success status
     */
    public static function create_directory($directory, $permissions = 0755) {
        if (is_dir($directory)) {
            return true;
        }
        
        return wp_mkdir_p($directory) && chmod($directory, $permissions);
    }

    /**
     * Write data to file safely
     *
     * @since 1.0.0
     * @param string $file_path File path
     * @param string $data Data to write
     * @param bool $append Whether to append to file
     * @return bool Success status
     */
    public static function write_file($file_path, $data, $append = false) {
        // Ensure directory exists
        $directory = dirname($file_path);
        if (!self::create_directory($directory)) {
            return false;
        }
        
        $flags = LOCK_EX;
        if ($append) {
            $flags |= FILE_APPEND;
        }
        
        return file_put_contents($file_path, $data, $flags) !== false;
    }

    /**
     * Get system information
     *
     * @since 1.0.0
     * @return array System information
     */
    public static function get_system_info() {
        global $wpdb;
        
        return [
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'mysql_version' => $wpdb->db_version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'wp_memory_limit' => WP_MEMORY_LIMIT,
            'wp_max_memory_limit' => WP_MAX_MEMORY_LIMIT,
            'multisite' => is_multisite(),
            'ssl' => is_ssl(),
            'timezone' => get_option('timezone_string') ?: 'UTC',
            'language' => get_bloginfo('language'),
            'debug' => WP_DEBUG,
        ];
    }

    /**
     * Check plugin dependencies
     *
     * @since 1.0.0
     * @return array Dependency status
     */
    public static function check_dependencies() {
        $dependencies = [
            'buddypress' => [
                'name' => 'BuddyPress',
                'required' => true,
                'active' => class_exists('BuddyPress'),
                'version' => class_exists('BuddyPress') ? bp_get_version() : null,
                'min_version' => '7.0.0',
            ],
            'bbpress' => [
                'name' => 'bbPress',
                'required' => false,
                'active' => class_exists('bbPress'),
                'version' => class_exists('bbPress') ? bbp_get_version() : null,
                'min_version' => '2.6.0',
            ],
            'wp_cli' => [
                'name' => 'WP-CLI',
                'required' => true,
                'active' => defined('WP_CLI') && WP_CLI,
                'version' => defined('WP_CLI_VERSION') ? WP_CLI_VERSION : null,
                'min_version' => '2.0.0',
            ],
        ];
        
        foreach ($dependencies as $key => &$dependency) {
            $dependency['status'] = 'ok';
            
            if ($dependency['required'] && !$dependency['active']) {
                $dependency['status'] = 'missing';
            } elseif ($dependency['active'] && $dependency['version'] && $dependency['min_version']) {
                if (version_compare($dependency['version'], $dependency['min_version'], '<')) {
                    $dependency['status'] = 'outdated';
                }
            }
        }
        
        return $dependencies;
    }

    /**
     * Validate email address
     *
     * @since 1.0.0
     * @param string $email Email address to validate
     * @return bool Whether email is valid
     */
    public static function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL
     *
     * @since 1.0.0
     * @param string $url URL to validate
     * @return bool Whether URL is valid
     */
    public static function validate_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Generate unique identifier
     *
     * @since 1.0.0
     * @param string $prefix Optional prefix
     * @return string Unique identifier
     */
    public static function generate_unique_id($prefix = '') {
        $unique_id = uniqid($prefix, true);
        return str_replace('.', '', $unique_id);
    }

    /**
     * Check if running in CLI mode
     *
     * @since 1.0.0
     * @return bool Whether running in CLI
     */
    public static function is_cli() {
        return defined('WP_CLI') && WP_CLI;
    }

    /**
     * Check if running in development environment
     *
     * @since 1.0.0
     * @return bool Whether in development
     */
    public static function is_development() {
        return WP_DEBUG || wp_get_environment_type() === 'development';
    }

    /**
     * Get plugin directory structure
     *
     * @since 1.0.0
     * @return array Directory structure
     */
    public static function get_directory_structure() {
        $base_dir = BP_PLAYGROUND_PLUGIN_DIR;
        
        return [
            'base' => $base_dir,
            'includes' => $base_dir . 'includes/',
            'modules' => $base_dir . 'includes/modules/',
            'cli' => $base_dir . 'includes/cli/',
            'abstracts' => $base_dir . 'includes/abstracts/',
            'interfaces' => $base_dir . 'includes/interfaces/',
            'languages' => $base_dir . 'languages/',
            'assets' => $base_dir . 'assets/',
            'logs' => wp_upload_dir()['basedir'] . '/bp-playground-logs/',
        ];
    }

    /**
     * Log debug information
     *
     * @since 1.0.0
     * @param mixed $data Data to log
     * @param string $label Optional label
     * @return void
     */
    public static function debug_log($data, $label = '') {
        if (!self::is_development()) {
            return;
        }
        
        $message = $label ? "{$label}: " : '';
        $message .= is_string($data) ? $data : print_r($data, true);
        
        error_log("[BP Playground Debug] {$message}");
    }

    /**
     * Measure execution time
     *
     * @since 1.0.0
     * @param callable $callback Function to measure
     * @param array $args Arguments for the function
     * @return array Result and execution time
     */
    public static function measure_execution_time($callback, $args = []) {
        $start_time = microtime(true);
        $result = call_user_func_array($callback, $args);
        $end_time = microtime(true);
        
        return [
            'result' => $result,
            'execution_time' => round($end_time - $start_time, 4),
            'memory_used' => memory_get_peak_usage(true),
        ];
    }

    /**
     * Get readable time difference
     *
     * @since 1.0.0
     * @param int $timestamp Timestamp to compare
     * @param int $compare_to Timestamp to compare to (default: current time)
     * @return string Human readable time difference
     */
    public static function time_ago($timestamp, $compare_to = null) {
        if ($compare_to === null) {
            $compare_to = time();
        }
        
        $diff = abs($compare_to - $timestamp);
        
        if ($diff < 60) {
            return $diff . ' seconds ago';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' days ago';
        } else {
            return date('Y-m-d H:i:s', $timestamp);
        }
    }

    /**
     * Format number with appropriate units
     *
     * @since 1.0.0
     * @param int $number Number to format
     * @return string Formatted number
     */
    public static function format_number($number) {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        } else {
            return number_format($number);
        }
    }

    /**
     * Get memory usage information
     *
     * @since 1.0.0
     * @return array Memory usage details
     */
    public static function get_memory_usage() {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => self::parse_memory_limit(ini_get('memory_limit')),
            'formatted' => [
                'current' => self::format_file_size(memory_get_usage(true)),
                'peak' => self::format_file_size(memory_get_peak_usage(true)),
                'limit' => ini_get('memory_limit'),
            ],
            'percentage' => [
                'current' => round((memory_get_usage(true) / self::parse_memory_limit(ini_get('memory_limit'))) * 100, 2),
                'peak' => round((memory_get_peak_usage(true) / self::parse_memory_limit(ini_get('memory_limit'))) * 100, 2),
            ],
        ];
    }

    /**
     * Clean up temporary files
     *
     * @since 1.0.0
     * @param string $directory Directory to clean
     * @param int $max_age Maximum age in seconds
     * @return int Number of files cleaned
     */
    public static function cleanup_temp_files($directory, $max_age = 3600) {
        if (!is_dir($directory)) {
            return 0;
        }
        
        $cleaned = 0;
        $files = scandir($directory);
        $cutoff_time = time() - $max_age;
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $file_path = $directory . '/' . $file;
            
            if (is_file($file_path) && filemtime($file_path) < $cutoff_time) {
                if (unlink($file_path)) {
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }

    /**
     * Generate progress bar for CLI
     *
     * @since 1.0.0
     * @param int $current Current progress
     * @param int $total Total items
     * @param int $width Progress bar width
     * @return string Progress bar string
     */
    public static function generate_progress_bar($current, $total, $width = 50) {
        if (!self::is_cli()) {
            return '';
        }
        
        $percentage = $total > 0 ? ($current / $total) : 0;
        $filled = floor($percentage * $width);
        $empty = $width - $filled;
        
        $bar = '[' . str_repeat('=', $filled) . str_repeat(' ', $empty) . ']';
        $percent = round($percentage * 100, 1);
        
        return "{$bar} {$percent}% ({$current}/{$total})";
    }

    /**
     * Validate JSON schema
     *
     * @since 1.0.0
     * @param mixed $data Data to validate
     * @param array $schema JSON schema rules
     * @return bool|array True if valid, array of errors if invalid
     */
    public static function validate_json_schema($data, $schema) {
        $errors = [];
        
        foreach ($schema as $field => $rules) {
            $value = isset($data[$field]) ? $data[$field] : null;
            
            // Required field check
            if (isset($rules['required']) && $rules['required'] && ($value === null || $value === '')) {
                $errors[] = "Field '{$field}' is required";
                continue;
            }
            
            // Skip validation if field is not provided and not required
            if ($value === null || $value === '') {
                continue;
            }
            
            // Type validation
            if (isset($rules['type'])) {
                switch ($rules['type']) {
                    case 'string':
                        if (!is_string($value)) {
                            $errors[] = "Field '{$field}' must be a string";
                        }
                        break;
                    case 'integer':
                        if (!is_int($value) && !ctype_digit($value)) {
                            $errors[] = "Field '{$field}' must be an integer";
                        }
                        break;
                    case 'boolean':
                        if (!is_bool($value)) {
                            $errors[] = "Field '{$field}' must be a boolean";
                        }
                        break;
                    case 'array':
                        if (!is_array($value)) {
                            $errors[] = "Field '{$field}' must be an array";
                        }
                        break;
                }
            }
            
            // Range validation
            if (isset($rules['min']) && $value < $rules['min']) {
                $errors[] = "Field '{$field}' must be at least {$rules['min']}";
            }
            
            if (isset($rules['max']) && $value > $rules['max']) {
                $errors[] = "Field '{$field}' must be at most {$rules['max']}";
            }
            
            // Length validation for strings
            if (is_string($value)) {
                if (isset($rules['minLength']) && strlen($value) < $rules['minLength']) {
                    $errors[] = "Field '{$field}' must be at least {$rules['minLength']} characters";
                }
                
                if (isset($rules['maxLength']) && strlen($value) > $rules['maxLength']) {
                    $errors[] = "Field '{$field}' must be at most {$rules['maxLength']} characters";
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
}