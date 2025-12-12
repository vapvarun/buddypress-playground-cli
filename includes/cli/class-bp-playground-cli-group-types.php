<?php
/**
 * BuddyPress Playground CLI Group Types Command
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
 * CLI command for group types management
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Group_Types extends WP_CLI_Command {

    /**
     * Default group types with their configurations
     *
     * @var array
     */
    private $default_types = [
        'community' => [
            'labels' => ['name' => 'Community Groups', 'singular_name' => 'Community Group'],
            'has_directory' => true,
            'show_in_create_screen' => true,
            'show_in_list' => true,
            'description' => 'Open community groups for general discussions and networking.',
            'weight' => 30,
        ],
        'project' => [
            'labels' => ['name' => 'Project Groups', 'singular_name' => 'Project Group'],
            'has_directory' => true,
            'show_in_create_screen' => true,
            'show_in_list' => true,
            'description' => 'Project-based collaboration groups.',
            'weight' => 25,
        ],
        'course' => [
            'labels' => ['name' => 'Course Groups', 'singular_name' => 'Course Group'],
            'has_directory' => true,
            'show_in_create_screen' => true,
            'show_in_list' => true,
            'description' => 'Learning and course-related groups.',
            'weight' => 20,
        ],
        'team' => [
            'labels' => ['name' => 'Team Groups', 'singular_name' => 'Team Group'],
            'has_directory' => true,
            'show_in_create_screen' => true,
            'show_in_list' => true,
            'description' => 'Team and work-related groups.',
            'weight' => 15,
        ],
        'support' => [
            'labels' => ['name' => 'Support Groups', 'singular_name' => 'Support Group'],
            'has_directory' => true,
            'show_in_create_screen' => true,
            'show_in_list' => true,
            'description' => 'Support and help groups.',
            'weight' => 10,
        ],
    ];

    /**
     * List all registered group types
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
     *     wp bp playground group-types list
     *     wp bp playground group-types list --format=json
     *
     * @since 1.0.0
     * @subcommand list
     */
    public function list_types($args, $assoc_args) {
        if (!bp_is_active('groups')) {
            WP_CLI::error('Groups component is not active.');
        }

        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');

        $group_types = bp_groups_get_group_types([], 'objects');

        if (empty($group_types)) {
            WP_CLI::warning('No group types are registered. Run "wp bp playground group-types register" first.');
            return;
        }

        $items = [];
        foreach ($group_types as $type_name => $type_obj) {
            $items[] = [
                'name' => $type_name,
                'singular' => $type_obj->labels['singular_name'] ?? $type_name,
                'plural' => $type_obj->labels['name'] ?? $type_name,
                'has_directory' => $type_obj->has_directory ? 'yes' : 'no',
                'show_in_create' => isset($type_obj->show_in_create_screen) && $type_obj->show_in_create_screen ? 'yes' : 'no',
            ];
        }

        WP_CLI\Utils\format_items($format, $items, ['name', 'singular', 'plural', 'has_directory', 'show_in_create']);
    }

    /**
     * Register default group types
     *
     * ## OPTIONS
     *
     * [--force]
     * : Re-register even if types already exist
     *
     * ## EXAMPLES
     *
     *     wp bp playground group-types register
     *     wp bp playground group-types register --force
     *
     * @since 1.0.0
     */
    public function register($args, $assoc_args) {
        if (!bp_is_active('groups')) {
            WP_CLI::error('Groups component is not active.');
        }

        if (!function_exists('bp_groups_register_group_type')) {
            WP_CLI::error('Group types require BuddyPress 2.6 or higher.');
        }

        $force = isset($assoc_args['force']);
        $registered = 0;

        foreach ($this->default_types as $type_name => $type_config) {
            if (!$force && bp_groups_get_group_type_object($type_name)) {
                WP_CLI::line("Group type '{$type_name}' already exists, skipping.");
                continue;
            }

            $result = bp_groups_register_group_type($type_name, [
                'labels' => $type_config['labels'],
                'has_directory' => $type_config['has_directory'],
                'show_in_create_screen' => $type_config['show_in_create_screen'],
                'show_in_list' => $type_config['show_in_list'],
                'description' => $type_config['description'],
            ]);

            if ($result) {
                $registered++;
                WP_CLI::line("Registered group type: {$type_name}");
            }
        }

        WP_CLI::success("Registered {$registered} group types.");

        // Show registered types
        $this->list_types([], ['format' => 'table']);
    }

    /**
     * Ensure default group types are registered
     *
     * @since 1.0.0
     * @return void
     */
    private function ensure_types_registered() {
        foreach ($this->default_types as $type_name => $type_config) {
            if (!bp_groups_get_group_type_object($type_name)) {
                bp_groups_register_group_type($type_name, [
                    'labels' => $type_config['labels'],
                    'has_directory' => $type_config['has_directory'],
                    'show_in_create_screen' => $type_config['show_in_create_screen'],
                    'show_in_list' => $type_config['show_in_list'],
                    'description' => $type_config['description'],
                ]);
            }
        }
    }

    /**
     * Assign group types to groups
     *
     * ## OPTIONS
     *
     * [--type=<type>]
     * : Specific group type to assign (community, project, course, team, support)
     *
     * [--random]
     * : Randomly assign types with weighted distribution
     *
     * [--group-ids=<ids>]
     * : Comma-separated list of group IDs to assign types to
     *
     * [--all]
     * : Assign to all groups
     *
     * [--overwrite]
     * : Overwrite existing group type assignments
     *
     * ## EXAMPLES
     *
     *     wp bp playground group-types assign --random --all
     *     wp bp playground group-types assign --type=course --group-ids=5,6,7
     *     wp bp playground group-types assign --random --all --overwrite
     *
     * @since 1.0.0
     */
    public function assign($args, $assoc_args) {
        if (!bp_is_active('groups')) {
            WP_CLI::error('Groups component is not active.');
        }

        // Ensure group types are registered first
        $this->ensure_types_registered();

        $type = WP_CLI\Utils\get_flag_value($assoc_args, 'type', '');
        $random = isset($assoc_args['random']);
        $group_ids_str = WP_CLI\Utils\get_flag_value($assoc_args, 'group-ids', '');
        $all = isset($assoc_args['all']);
        $overwrite = isset($assoc_args['overwrite']);

        // Validate inputs
        if (!$random && empty($type)) {
            WP_CLI::error('Please specify --type=<type> or use --random flag.');
        }

        if ($type && !bp_groups_get_group_type_object($type)) {
            WP_CLI::error("Group type '{$type}' is not registered.");
        }

        // Get group IDs
        $group_ids = [];
        if (!empty($group_ids_str)) {
            $group_ids = array_map('intval', explode(',', $group_ids_str));
        } elseif ($all) {
            global $wpdb;
            $group_ids = $wpdb->get_col("SELECT id FROM {$wpdb->base_prefix}bp_groups");
        } else {
            WP_CLI::error('Please specify --group-ids=<ids> or use --all flag.');
        }

        if (empty($group_ids)) {
            WP_CLI::error('No groups found to assign types.');
        }

        WP_CLI::line(sprintf("Assigning group types to %d groups...", count($group_ids)));

        $assigned = 0;
        $skipped = 0;

        $progress = WP_CLI\Utils\make_progress_bar('Assigning group types', count($group_ids));

        foreach ($group_ids as $group_id) {
            // Check existing type
            $existing_types = bp_groups_get_group_type($group_id, false);
            if (!empty($existing_types) && !$overwrite) {
                $skipped++;
                $progress->tick();
                continue;
            }

            // Determine type to assign
            $assign_type = $type;
            if ($random) {
                $assign_type = $this->get_weighted_random_type();
            }

            // Assign the type (append=false replaces existing types)
            if (bp_groups_set_group_type($group_id, $assign_type, false)) {
                $assigned++;
            }

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::success(sprintf(
            "Assigned group types to %d groups. %d skipped (already had types, use --overwrite to change).",
            $assigned,
            $skipped
        ));
    }

    /**
     * Show group type statistics
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
     *     wp bp playground group-types stats
     *
     * @since 1.0.0
     */
    public function stats($args, $assoc_args) {
        if (!bp_is_active('groups')) {
            WP_CLI::error('Groups component is not active.');
        }

        // Ensure group types are registered first
        $this->ensure_types_registered();

        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');

        $group_types = bp_groups_get_group_types([], 'names');

        if (empty($group_types)) {
            WP_CLI::warning('No group types registered.');
            return;
        }

        $items = [];
        $total_with_type = 0;

        foreach ($group_types as $type_name) {
            $groups = groups_get_groups([
                'group_type' => $type_name,
                'per_page' => -1,
                'show_hidden' => true,
            ]);

            $count = $groups['total'] ?? 0;
            $total_with_type += $count;

            $items[] = [
                'type' => $type_name,
                'count' => $count,
            ];
        }

        // Count groups without type
        global $wpdb;
        $total_groups = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_groups");
        $without_type = $total_groups - $total_with_type;

        $items[] = [
            'type' => '(no type)',
            'count' => $without_type,
        ];

        WP_CLI\Utils\format_items($format, $items, ['type', 'count']);
        WP_CLI::line(sprintf("Total groups: %d", $total_groups));
    }

    /**
     * Get weighted random group type
     *
     * @return string Group type name
     */
    private function get_weighted_random_type() {
        $weights = [];
        foreach ($this->default_types as $type => $config) {
            $weights[$type] = $config['weight'];
        }

        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $type => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $type;
            }
        }

        return 'community'; // Default fallback
    }
}
