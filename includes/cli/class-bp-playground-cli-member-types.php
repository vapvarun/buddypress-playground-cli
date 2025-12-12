<?php
/**
 * BuddyPress Playground CLI Member Types Command
 *
 * @package BuddyPress_Playground
 * @subpackage CLI
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CLI command for member types management
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Member_Types extends WP_CLI_Command {

    /**
     * Default member types with their configurations
     *
     * @var array
     */
    private $default_types = [
        'student' => [
            'labels' => ['name' => 'Students', 'singular_name' => 'Student'],
            'has_directory' => 'students',
            'show_in_list' => true,
            'weight' => 40,
        ],
        'teacher' => [
            'labels' => ['name' => 'Teachers', 'singular_name' => 'Teacher'],
            'has_directory' => 'teachers',
            'show_in_list' => true,
            'weight' => 10,
        ],
        'professional' => [
            'labels' => ['name' => 'Professionals', 'singular_name' => 'Professional'],
            'has_directory' => 'professionals',
            'show_in_list' => true,
            'weight' => 30,
        ],
        'alumni' => [
            'labels' => ['name' => 'Alumni', 'singular_name' => 'Alumni'],
            'has_directory' => 'alumni',
            'show_in_list' => true,
            'weight' => 20,
        ],
        'moderator' => [
            'labels' => ['name' => 'Moderators', 'singular_name' => 'Moderator'],
            'has_directory' => false,
            'show_in_list' => false,
            'weight' => 0,
        ],
    ];

    /**
     * List all registered member types
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - csv
     * ---
     *
     * ## EXAMPLES
     *
     *     wp bp playground member-types list
     *     wp bp playground member-types list --format=json
     *
     * @since 1.0.0
     * @subcommand list
     */
    public function list_types($args, $assoc_args) {
        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');

        $member_types = bp_get_member_types([], 'objects');

        if (empty($member_types)) {
            WP_CLI::warning('No member types are registered. Run "wp bp playground member-types register" first.');
            return;
        }

        $items = [];
        foreach ($member_types as $type_name => $type_obj) {
            $items[] = [
                'name' => $type_name,
                'singular' => $type_obj->labels['singular_name'] ?? $type_name,
                'plural' => $type_obj->labels['name'] ?? $type_name,
                'has_directory' => $type_obj->has_directory ? 'yes' : 'no',
                'show_in_list' => $type_obj->show_in_list ? 'yes' : 'no',
            ];
        }

        WP_CLI\Utils\format_items($format, $items, ['name', 'singular', 'plural', 'has_directory', 'show_in_list']);
    }

    /**
     * Register default member types
     *
     * ## OPTIONS
     *
     * [--force]
     * : Re-register even if types already exist
     *
     * ## EXAMPLES
     *
     *     wp bp playground member-types register
     *     wp bp playground member-types register --force
     *
     * @since 1.0.0
     */
    public function register($args, $assoc_args) {
        $force = isset($assoc_args['force']);

        // Ensure member types function exists
        if (function_exists('bp_playground_register_member_types')) {
            bp_playground_register_member_types();
            WP_CLI::success('Default member types registered successfully.');

            // Show registered types
            $this->list_types([], ['format' => 'table']);
            return;
        }

        // Manual registration if function doesn't exist
        $registered = 0;
        foreach ($this->default_types as $type_name => $type_config) {
            if (!$force && bp_get_member_type_object($type_name)) {
                WP_CLI::line("Member type '{$type_name}' already exists, skipping.");
                continue;
            }

            $result = bp_register_member_type($type_name, [
                'labels' => $type_config['labels'],
                'has_directory' => $type_config['has_directory'],
                'show_in_list' => $type_config['show_in_list'],
            ]);

            if ($result) {
                $registered++;
                WP_CLI::line("Registered member type: {$type_name}");
            }
        }

        WP_CLI::success("Registered {$registered} member types.");
    }

    /**
     * Assign member types to users
     *
     * ## OPTIONS
     *
     * [--type=<type>]
     * : Specific member type to assign (student, teacher, professional, alumni, moderator)
     *
     * [--random]
     * : Randomly assign types with weighted distribution
     *
     * [--user-ids=<ids>]
     * : Comma-separated list of user IDs to assign types to
     *
     * [--all]
     * : Assign to all users (excluding admin)
     *
     * [--overwrite]
     * : Overwrite existing member type assignments
     *
     * ## EXAMPLES
     *
     *     wp bp playground member-types assign --random --all
     *     wp bp playground member-types assign --type=student --user-ids=5,6,7
     *     wp bp playground member-types assign --random --all --overwrite
     *
     * @since 1.0.0
     */
    public function assign($args, $assoc_args) {
        $type = WP_CLI\Utils\get_flag_value($assoc_args, 'type', '');
        $random = isset($assoc_args['random']);
        $user_ids_str = WP_CLI\Utils\get_flag_value($assoc_args, 'user-ids', '');
        $all = isset($assoc_args['all']);
        $overwrite = isset($assoc_args['overwrite']);

        // Validate inputs
        if (!$random && empty($type)) {
            WP_CLI::error('Please specify --type=<type> or use --random flag.');
        }

        if ($type && !bp_get_member_type_object($type)) {
            WP_CLI::error("Member type '{$type}' is not registered. Run 'wp bp playground member-types register' first.");
        }

        // Get user IDs
        $user_ids = [];
        if (!empty($user_ids_str)) {
            $user_ids = array_map('intval', explode(',', $user_ids_str));
        } elseif ($all) {
            $user_ids = get_users([
                'fields' => 'ID',
                'exclude' => [1], // Exclude admin
            ]);
        } else {
            WP_CLI::error('Please specify --user-ids=<ids> or use --all flag.');
        }

        if (empty($user_ids)) {
            WP_CLI::error('No users found to assign member types.');
        }

        WP_CLI::line(sprintf("Assigning member types to %d users...", count($user_ids)));

        $assigned = 0;
        $skipped = 0;

        $progress = WP_CLI\Utils\make_progress_bar('Assigning member types', count($user_ids));

        foreach ($user_ids as $user_id) {
            // Check existing type
            $existing_type = bp_get_member_type($user_id);
            if ($existing_type && !$overwrite) {
                $skipped++;
                $progress->tick();
                continue;
            }

            // Determine type to assign
            $assign_type = $type;
            if ($random) {
                $assign_type = $this->get_weighted_random_type();
            }

            // Assign the type
            if (bp_set_member_type($user_id, $assign_type)) {
                $assigned++;
            }

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::success(sprintf(
            "Assigned member types to %d users. %d skipped (already had types, use --overwrite to change).",
            $assigned,
            $skipped
        ));
    }

    /**
     * Show member type statistics
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - csv
     * ---
     *
     * ## EXAMPLES
     *
     *     wp bp playground member-types stats
     *
     * @since 1.0.0
     */
    public function stats($args, $assoc_args) {
        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');

        $member_types = bp_get_member_types([], 'names');

        if (empty($member_types)) {
            WP_CLI::warning('No member types registered.');
            return;
        }

        $items = [];
        $total_with_type = 0;

        foreach ($member_types as $type_name) {
            $count = count(bp_get_members_of_type($type_name));
            $total_with_type += $count;

            $items[] = [
                'type' => $type_name,
                'count' => $count,
            ];
        }

        // Count users without type
        $total_users = count(get_users(['fields' => 'ID', 'exclude' => [1]]));
        $without_type = $total_users - $total_with_type;

        $items[] = [
            'type' => '(no type)',
            'count' => $without_type,
        ];

        WP_CLI\Utils\format_items($format, $items, ['type', 'count']);
        WP_CLI::line(sprintf("Total users: %d", $total_users));
    }

    /**
     * Get weighted random member type
     *
     * @return string Member type name
     */
    private function get_weighted_random_type() {
        $weights = [];
        foreach ($this->default_types as $type => $config) {
            if ($config['weight'] > 0) {
                $weights[$type] = $config['weight'];
            }
        }

        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $type => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $type;
            }
        }

        return 'student'; // Default fallback
    }
}

/**
 * Helper function to get members of a specific type
 *
 * @param string $type Member type name
 * @return array Array of user IDs
 */
if (!function_exists('bp_get_members_of_type')) {
    function bp_get_members_of_type($type) {
        global $wpdb;

        $user_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT object_id FROM {$wpdb->prefix}term_relationships tr
             INNER JOIN {$wpdb->prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
             INNER JOIN {$wpdb->prefix}terms t ON tt.term_id = t.term_id
             WHERE tt.taxonomy = 'bp_member_type' AND t.slug = %s",
            $type
        ));

        return $user_ids ?: [];
    }
}
