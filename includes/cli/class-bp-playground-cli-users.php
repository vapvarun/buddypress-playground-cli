<?php
/**
 * BuddyPress Playground CLI Users Command
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
 * CLI command for user generation
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Users extends WP_CLI_Command {

    /**
     * Generate users with profiles
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of users to generate
     * ---
     * default: 1000
     * ---
     *
     * [--member-types]
     * : Create member types
     *
     * [--activation-rate=<rate>]
     * : User activation rate (0.0-1.0)
     * ---
     * default: 0.95
     * ---
     *
     * ## EXAMPLES
     *
     *     wp bp playground users --count=500
     *     wp bp playground users --count=1000 --activation-rate=0.9
     *
     * @since 1.0.0
     */
    public function __invoke($args, $assoc_args) {
        $count = WP_CLI\Utils\get_flag_value($assoc_args, 'count', 1000);
        $member_types = WP_CLI\Utils\get_flag_value($assoc_args, 'member-types', false);
        $activation_rate = WP_CLI\Utils\get_flag_value($assoc_args, 'activation-rate', 0.95);

        WP_CLI::line("Generating {$count} users...");

        $users_module = bp_playground_get_module('users');
        if (!$users_module) {
            WP_CLI::error('Users module not available.');
        }

        $options = [
            'count' => $count,
            'member_types' => $member_types,
            'activation_rate' => $activation_rate,
        ];

        $result = $users_module->generate($options);

        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }

        WP_CLI::success("Generated {$result['users_created']} users successfully!");
    }
}