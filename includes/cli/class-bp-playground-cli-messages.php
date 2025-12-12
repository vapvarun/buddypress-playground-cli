<?php
/**
 * BuddyPress Playground CLI Messages Command
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
 * CLI command for message generation
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Messages extends WP_CLI_Command {

    /**
     * Generate private message threads
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of message threads to generate
     * ---
     * default: 100
     * ---
     *
     * [--min-messages=<min>]
     * : Minimum messages per thread
     * ---
     * default: 1
     * ---
     *
     * [--max-messages=<max>]
     * : Maximum messages per thread
     * ---
     * default: 10
     * ---
     *
     * [--timeframe=<days>]
     * : Timeframe in days for message dates
     * ---
     * default: 90
     * ---
     *
     * ## EXAMPLES
     *
     *     wp bp playground messages --count=50
     *     wp bp playground messages --count=100 --min-messages=2 --max-messages=15
     *     wp bp playground messages --count=200 --timeframe=30
     *
     * @since 1.0.0
     */
    public function __invoke($args, $assoc_args) {
        $count = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'count', 100);
        $min_messages = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'min-messages', 1);
        $max_messages = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'max-messages', 10);
        $timeframe = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'timeframe', 90);

        WP_CLI::line("Generating {$count} message threads...");

        $messages_module = bp_playground_get_module('messages');
        if (!$messages_module) {
            WP_CLI::error('Messages module not available. Make sure BuddyPress Messages component is active.');
        }

        $options = [
            'count' => $count,
            'min_messages' => $min_messages,
            'max_messages' => $max_messages,
            'timeframe_days' => $timeframe,
            'thread_variations' => true,
        ];

        $result = $messages_module->generate($options);

        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }

        $threads_created = $result['threads_created'] ?? 0;
        $messages_created = $result['messages_created'] ?? 0;

        WP_CLI::success("Generated {$threads_created} message threads with {$messages_created} total messages!");
    }
}
