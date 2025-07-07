<?php
/**
 * BuddyPress Playground Cleanup Utilities
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
 * Comprehensive cleanup utilities for BuddyPress Playground data
 *
 * @since 1.0.0
 */
class BP_Playground_Cleanup {

    /**
     * Available cleanup components
     *
     * @since 1.0.0
     * @var array
     */
    private $cleanup_components = [
        'users' => 'Users and user meta',
        'xprofile' => 'Extended profile data',
        'groups' => 'Groups and memberships',
        'activities' => 'Activities and comments',
        'messages' => 'Private messages',
        'friends' => 'Friend connections',
        'bbpress' => 'Forums, topics, and replies',
        'logs' => 'Plugin logs',
        'meta' => 'Orphaned metadata',
    ];

    /**
     * Cleanup all BuddyPress Playground data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function cleanup_all($options = []) {
        $defaults = [
            'dry_run' => false,
            'older_than_days' => 0,
            'components' => array_keys($this->cleanup_components),
            'preserve_admins' => true,
            'batch_size' => 100,
        ];

        $options = wp_parse_args($options, $defaults);

        $results = [
            'total_items_removed' => 0,
            'components_cleaned' => [],
            'errors' => [],
            'start_time' => microtime(true),
        ];

        foreach ($options['components'] as $component) {
            if (!array_key_exists($component, $this->cleanup_components)) {
                $results['errors'][] = "Unknown component: {$component}";
                continue;
            }

            try {
                $component_result = $this->cleanup_component($component, $options);
                $results['components_cleaned'][$component] = $component_result;
                
                // Sum up total items removed
                if (is_array($component_result)) {
                    foreach ($component_result as $key => $value) {
                        if (is_numeric($value) && strpos($key, 'removed') !== false) {
                            $results['total_items_removed'] += $value;
                        }
                    }
                }

            } catch (Exception $e) {
                $results['errors'][] = "Error cleaning {$component}: " . $e->getMessage();
            }
        }

        $results['end_time'] = microtime(true);
        $results['duration'] = round($results['end_time'] - $results['start_time'], 2);

        return $results;
    }

    /**
     * Cleanup a specific component
     *
     * @since 1.0.0
     * @param string $component Component name
     * @param array $options Cleanup options
     * @return array|WP_Error Cleanup results
     */
    public function cleanup_component($component, $options = []) {
        $defaults = [
            'dry_run' => false,
            'older_than_days' => 0,
        ];

        $options = wp_parse_args($options, $defaults);

        switch ($component) {
            case 'users':
                return $this->cleanup_users($options);
            case 'xprofile':
                return $this->cleanup_xprofile($options);
            case 'groups':
                return $this->cleanup_groups($options);
            case 'activities':
                return $this->cleanup_activities($options);
            case 'messages':
                return $this->cleanup_messages($options);
            case 'friends':
                return $this->cleanup_friends($options);
            case 'bbpress':
                return $this->cleanup_bbpress($options);
            case 'logs':
                return $this->cleanup_logs($options);
            case 'meta':
                return $this->cleanup_orphaned_meta($options);
            default:
                return new WP_Error('invalid_component', "Invalid component: {$component}");
        }
    }

    /**
     * Cleanup users data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    private function cleanup_users($options) {
        $users_module = bp_playground_get_module('users');
        return $users_module ? $users_module->cleanup($options) : ['users_removed' => 0];
    }

    /**
     * Cleanup XProfile data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    private function cleanup_xprofile($options) {
        $xprofile_module = bp_playground_get_module('xprofile');
        return $xprofile_module ? $xprofile_module->cleanup($options) : ['field_groups_removed' => 0];
    }

    /**
     * Cleanup groups data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    private function cleanup_groups($options) {
        $groups_module = bp_playground_get_module('groups');
        return $groups_module ? $groups_module->cleanup($options) : ['groups_removed' => 0];
    }

    /**
     * Cleanup activities data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    private function cleanup_activities($options) {
        $activities_module = bp_playground_get_module('activities');
        return $activities_module ? $activities_module->cleanup($options) : ['activities_removed' => 0];
    }

    /**
     * Cleanup messages data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    private function cleanup_messages($options) {
        $messages_module = bp_playground_get_module('messages');
        return $messages_module ? $messages_module->cleanup($options) : ['threads_removed' => 0];
    }

    /**
     * Cleanup friends data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    private function cleanup_friends($options) {
        $friends_module = bp_playground_get_module('friends');
        return $friends_module ? $friends_module->cleanup($options) : ['friendships_removed' => 0];
    }

    /**
     * Cleanup bbPress data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    private function cleanup_bbpress($options) {
        if (!class_exists('bbPress')) {
            return ['forums_removed' => 0];
        }

        global $wpdb;

        $results = [
            'forums_removed' => 0,
            'topics_removed' => 0,
            'replies_removed' => 0,
        ];

        if ($options['dry_run']) {
            // Count what would be removed
            $results['forums_removed'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 WHERE p.post_type = 'forum' AND pm.meta_key = 'bp_playground_created'"
            );
            
            $results['topics_removed'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 WHERE p.post_type = 'topic' AND pm.meta_key = 'bp_playground_created'"
            );
            
            $results['replies_removed'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 WHERE p.post_type = 'reply' AND pm.meta_key = 'bp_playground_created'"
            );
        } else {
            // Remove playground-created bbPress content
            $post_types = ['forum', 'topic', 'reply'];
            
            foreach ($post_types as $post_type) {
                $posts = $wpdb->get_col($wpdb->prepare(
                    "SELECT p.ID FROM {$wpdb->posts} p
                     INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                     WHERE p.post_type = %s AND pm.meta_key = 'bp_playground_created'",
                    $post_type
                ));

                foreach ($posts as $post_id) {
                    if (wp_delete_post($post_id, true)) {
                        $results[$post_type . 's_removed']++;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Cleanup plugin logs
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    private function cleanup_logs($options) {
        $logger = bp_playground_get_module('logger');
        if (!$logger) {
            return ['logs_removed' => 0];
        }

        $cleanup_options = [
            'older_than_days' => isset($options['older_than_days']) ? $options['older_than_days'] : 30,
        ];

        if ($options['dry_run']) {
            // Count logs that would be removed
            global $wpdb;
            $table_name = $wpdb->prefix . 'bp_playground_logs';
            
            if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name) {
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$table_name} WHERE created_at < %s",
                    date('Y-m-d H:i:s', time() - ($cleanup_options['older_than_days'] * 24 * 3600))
                ));
                return ['logs_removed' => intval($count)];
            }
            
            return ['logs_removed' => 0];
        }

        $deleted = $logger->clear_logs($cleanup_options);
        return ['logs_removed' => $deleted];
    }

    /**
     * Cleanup orphaned metadata
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    private function cleanup_orphaned_meta($options) {
        global $wpdb;

        $results = [
            'user_meta_removed' => 0,
            'post_meta_removed' => 0,
            'comment_meta_removed' => 0,
            'term_meta_removed' => 0,
            'bp_meta_removed' => 0,
        ];

        if ($options['dry_run']) {
            // Count orphaned meta
            $results['user_meta_removed'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->usermeta} um
                 LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
                 WHERE u.ID IS NULL"
            );

            $results['post_meta_removed'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
                 LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                 WHERE p.ID IS NULL"
            );

            $results['comment_meta_removed'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->commentmeta} cm
                 LEFT JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID
                 WHERE c.comment_ID IS NULL"
            );

            // BuddyPress specific meta
            if (bp_is_active('xprofile')) {
                $results['bp_meta_removed'] += $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_xprofile_data xd
                     LEFT JOIN {$wpdb->users} u ON xd.user_id = u.ID
                     WHERE u.ID IS NULL"
                );
            }

        } else {
            // Remove orphaned meta
            $results['user_meta_removed'] = $wpdb->query(
                "DELETE um FROM {$wpdb->usermeta} um
                 LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
                 WHERE u.ID IS NULL"
            );

            $results['post_meta_removed'] = $wpdb->query(
                "DELETE pm FROM {$wpdb->postmeta} pm
                 LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                 WHERE p.ID IS NULL"
            );

            $results['comment_meta_removed'] = $wpdb->query(
                "DELETE cm FROM {$wpdb->commentmeta} cm
                 LEFT JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID
                 WHERE c.comment_ID IS NULL"
            );

            // BuddyPress specific orphaned data
            if (bp_is_active('xprofile')) {
                $bp_removed = $wpdb->query(
                    "DELETE xd FROM {$wpdb->base_prefix}bp_xprofile_data xd
                     LEFT JOIN {$wpdb->users} u ON xd.user_id = u.ID
                     WHERE u.ID IS NULL"
                );
                $results['bp_meta_removed'] += $bp_removed;
            }

            if (bp_is_active('activity')) {
                $bp_removed = $wpdb->query(
                    "DELETE a FROM {$wpdb->base_prefix}bp_activity a
                     LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID
                     WHERE u.ID IS NULL AND a.user_id != 0"
                );
                $results['bp_meta_removed'] += $bp_removed;
            }
        }

        return $results;
    }

    /**
     * Clean up specific playground metadata
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function cleanup_playground_meta($options = []) {
        global $wpdb;

        $defaults = [
            'dry_run' => false,
            'older_than_days' => 0,
        ];

        $options = wp_parse_args($options, $defaults);

        $results = [
            'user_meta_removed' => 0,
            'post_meta_removed' => 0,
            'bp_meta_removed' => 0,
        ];

        $meta_tables = [
            'usermeta' => $wpdb->usermeta,
            'postmeta' => $wpdb->postmeta,
        ];

        // Add BuddyPress meta tables if active
        if (function_exists('buddypress')) {
            $meta_tables['bp_activity_meta'] = $wpdb->base_prefix . 'bp_activity_meta';
            $meta_tables['bp_groups_groupmeta'] = $wpdb->base_prefix . 'bp_groups_groupmeta';
            $meta_tables['bp_messages_meta'] = $wpdb->base_prefix . 'bp_messages_meta';
            $meta_tables['bp_friends_meta'] = $wpdb->base_prefix . 'bp_friends_meta';
        }

        foreach ($meta_tables as $table_type => $table_name) {
            // Skip if table doesn't exist
            if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
                continue;
            }

            $where_clause = "meta_key LIKE 'bp_playground_%'";
            
            if ($options['older_than_days'] > 0) {
                $timestamp = time() - ($options['older_than_days'] * 24 * 3600);
                $where_clause .= $wpdb->prepare(" AND meta_value < %d", $timestamp);
            }

            if ($options['dry_run']) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}");
                $results[str_replace(['bp_', '_'], ['', '_'], $table_type) . '_removed'] = intval($count);
            } else {
                $deleted = $wpdb->query("DELETE FROM {$table_name} WHERE {$where_clause}");
                $results[str_replace(['bp_', '_'], ['', '_'], $table_type) . '_removed'] = $deleted;
            }
        }

        return $results;
    }

    /**
     * Get cleanup preview
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Preview data
     */
    public function get_cleanup_preview($options = []) {
        $options['dry_run'] = true;
        return $this->cleanup_all($options);
    }

    /**
     * Get cleanup statistics
     *
     * @since 1.0.0
     * @return array Cleanup statistics
     */
    public function get_cleanup_stats() {
        global $wpdb;

        $stats = [
            'total_playground_items' => 0,
            'components' => [],
        ];

        // Count playground items in each component
        $meta_queries = [
            'users' => "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'bp_playground_created'",
            'posts' => "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'bp_playground_created'",
        ];

        // Add BuddyPress specific counts
        if (function_exists('buddypress')) {
            if (bp_is_active('xprofile')) {
                $meta_queries['xprofile_groups'] = "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_xprofile_groups WHERE id > 1";
                $meta_queries['xprofile_data'] = "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_xprofile_data";
            }

            if (bp_is_active('groups')) {
                $meta_queries['groups'] = "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_groups_groupmeta WHERE meta_key = 'bp_playground_created'";
            }

            if (bp_is_active('activity')) {
                $meta_queries['activities'] = "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_activity_meta WHERE meta_key = 'bp_playground_created'";
            }

            if (bp_is_active('messages')) {
                $meta_queries['messages'] = "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_messages_meta WHERE meta_key = 'bp_playground_created'";
            }

            if (bp_is_active('friends')) {
                $meta_queries['friends'] = "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_friends_meta WHERE meta_key = 'bp_playground_created'";
            }
        }

        foreach ($meta_queries as $component => $query) {
            $count = $wpdb->get_var($query);
            $stats['components'][$component] = intval($count);
            $stats['total_playground_items'] += intval($count);
        }

        return $stats;
    }

    /**
     * Schedule automatic cleanup
     *
     * @since 1.0.0
     * @param array $schedule_options Schedule options
     * @return bool Success status
     */
    public function schedule_cleanup($schedule_options = []) {
        $defaults = [
            'interval' => 'weekly',
            'retention_days' => 30,
            'components' => ['logs'],
            'enabled' => true,
        ];

        $schedule_options = wp_parse_args($schedule_options, $defaults);

        // Clear existing schedule
        wp_clear_scheduled_hook('bp_playground_scheduled_cleanup');

        if (!$schedule_options['enabled']) {
            return true;
        }

        // Schedule new cleanup
        $next_run = wp_next_scheduled('bp_playground_scheduled_cleanup');
        if (!$next_run) {
            wp_schedule_event(time(), $schedule_options['interval'], 'bp_playground_scheduled_cleanup', [$schedule_options]);
        }

        // Add the cleanup action
        add_action('bp_playground_scheduled_cleanup', [$this, 'run_scheduled_cleanup']);

        return true;
    }

    /**
     * Run scheduled cleanup
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return void
     */
    public function run_scheduled_cleanup($options = []) {
        $defaults = [
            'retention_days' => 30,
            'components' => ['logs'],
        ];

        $options = wp_parse_args($options, $defaults);
        $options['older_than_days'] = $options['retention_days'];
        $options['dry_run'] = false;

        $results = $this->cleanup_all($options);

        // Log cleanup results
        $logger = bp_playground_get_module('logger');
        if ($logger) {
            $message = sprintf(
                'Scheduled cleanup completed: %d items removed in %.2f seconds',
                $results['total_items_removed'],
                $results['duration']
            );
            $logger->info($message, ['scheduled_cleanup' => true, 'results' => $results]);
        }
    }

    /**
     * Export cleanup report
     *
     * @since 1.0.0
     * @param array $results Cleanup results
     * @param string $format Export format
     * @return string|WP_Error Export data
     */
    public function export_cleanup_report($results, $format = 'json') {
        $report_data = [
            'timestamp' => current_time('mysql'),
            'plugin_version' => BP_PLAYGROUND_VERSION,
            'cleanup_results' => $results,
            'system_info' => [
                'wp_version' => get_bloginfo('version'),
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
            ],
        ];

        switch ($format) {
            case 'json':
                return json_encode($report_data, JSON_PRETTY_PRINT);
            case 'csv':
                return $this->convert_cleanup_to_csv($report_data);
            default:
                return new WP_Error('invalid_format', 'Invalid export format');
        }
    }

    /**
     * Convert cleanup results to CSV
     *
     * @since 1.0.0
     * @param array $report_data Report data
     * @return string CSV data
     */
    private function convert_cleanup_to_csv($report_data) {
        $csv_data = "Component,Items Removed,Duration\n";

        foreach ($report_data['cleanup_results']['components_cleaned'] as $component => $results) {
            $items_removed = 0;
            if (is_array($results)) {
                foreach ($results as $key => $value) {
                    if (is_numeric($value) && strpos($key, 'removed') !== false) {
                        $items_removed += $value;
                    }
                }
            }
            
            $csv_data .= sprintf('"%s","%d","%s"' . "\n",
                $component,
                $items_removed,
                isset($report_data['cleanup_results']['duration']) ? $report_data['cleanup_results']['duration'] : 'N/A'
            );
        }

        return $csv_data;
    }

    /**
     * Get available cleanup components
     *
     * @since 1.0.0
     * @return array Available components
     */
    public function get_available_components() {
        return $this->cleanup_components;
    }

    /**
     * Validate cleanup options
     *
     * @since 1.0.0
     * @param array $options Options to validate
     * @return array|WP_Error Validated options or error
     */
    public function validate_cleanup_options($options) {
        $defaults = [
            'dry_run' => false,
            'older_than_days' => 0,
            'components' => [],
            'preserve_admins' => true,
        ];

        $options = wp_parse_args($options, $defaults);

        // Validate components
        if (!empty($options['components'])) {
            $invalid_components = array_diff($options['components'], array_keys($this->cleanup_components));
            if (!empty($invalid_components)) {
                return new WP_Error('invalid_components', 'Invalid components: ' . implode(', ', $invalid_components));
            }
        }

        // Validate older_than_days
        if ($options['older_than_days'] < 0 || $options['older_than_days'] > 365) {
            return new WP_Error('invalid_days', 'older_than_days must be between 0 and 365');
        }

        return $options;
    }
}