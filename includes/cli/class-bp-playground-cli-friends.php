<?php
/**
 * BuddyPress Playground CLI Friends Command
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
 * CLI command for friendship generation
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Friends extends WP_CLI_Command {

    /**
     * Generate friendships between users
     *
     * ## OPTIONS
     *
     * [--density=<density>]
     * : Network density (0.01-0.5) - percentage of possible connections
     * ---
     * default: 0.1
     * ---
     *
     * [--pending-rate=<rate>]
     * : Rate of pending requests (0.0-0.5)
     * ---
     * default: 0.15
     * ---
     *
     * [--no-clustering]
     * : Disable realistic clustering of friendships
     *
     * [--no-pending]
     * : Don't create pending friend requests
     *
     * ## EXAMPLES
     *
     *     wp bp playground friends
     *     wp bp playground friends --density=0.2
     *     wp bp playground friends --density=0.15 --pending-rate=0.1
     *     wp bp playground friends --no-pending
     *
     * @since 1.0.0
     */
    public function __invoke($args, $assoc_args) {
        $density = (float) WP_CLI\Utils\get_flag_value($assoc_args, 'density', 0.1);
        $pending_rate = (float) WP_CLI\Utils\get_flag_value($assoc_args, 'pending-rate', 0.15);
        $clustering = !isset($assoc_args['no-clustering']);
        $pending_requests = !isset($assoc_args['no-pending']);

        WP_CLI::line("Generating friendships (density: {$density})...");

        $friends_module = bp_playground_get_module('friends');
        if (!$friends_module) {
            WP_CLI::error('Friends module not available. Make sure BuddyPress Friends component is active.');
        }

        $options = [
            'network_density' => $density,
            'pending_rate' => $pending_rate,
            'clustering' => $clustering,
            'pending_requests' => $pending_requests,
            'mutual_connections' => true,
        ];

        $result = $friends_module->generate($options);

        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }

        $created = $result['friendships_created'] ?? $result['created'] ?? 0;
        $pending = $result['pending_requests'] ?? 0;

        WP_CLI::success("Generated {$created} friendships" . ($pending > 0 ? " ({$pending} pending)" : "") . "!");
    }
}
