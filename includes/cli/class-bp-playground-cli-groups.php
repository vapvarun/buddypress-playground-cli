<?php
/**
 * BuddyPress Playground CLI Groups Command
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
 * CLI command for group generation
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Groups extends WP_CLI_Command {

    /**
     * Generate groups with memberships
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of groups to generate
     * ---
     * default: 100
     * ---
     *
     * [--types=<types>]
     * : Group types (public, private, hidden, mixed)
     * ---
     * default: mixed
     * ---
     *
     * [--with-hierarchy]
     * : Create group hierarchies
     *
     * [--membership-patterns]
     * : Use realistic membership patterns
     *
     * ## EXAMPLES
     *
     *     wp bp playground groups --count=50 --types=mixed
     *     wp bp playground groups --count=25 --with-hierarchy
     *
     * @since 1.0.0
     */
    public function __invoke($args, $assoc_args) {
        $count = WP_CLI\Utils\get_flag_value($assoc_args, 'count', 100);
        $types = WP_CLI\Utils\get_flag_value($assoc_args, 'types', 'mixed');
        $with_hierarchy = WP_CLI\Utils\get_flag_value($assoc_args, 'with-hierarchy', false);
        $membership_patterns = WP_CLI\Utils\get_flag_value($assoc_args, 'membership-patterns', true);

        WP_CLI::line("Generating {$count} groups...");

        $groups_module = bp_playground_get_module('groups');
        if (!$groups_module) {
            WP_CLI::error('Groups module not available.');
        }

        $options = [
            'count' => $count,
            'types' => $types,
            'with_hierarchy' => $with_hierarchy,
            'membership_patterns' => $membership_patterns,
        ];

        $result = $groups_module->generate($options);

        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }

        WP_CLI::success("Generated {$result['groups_created']} groups successfully!");
    }
}