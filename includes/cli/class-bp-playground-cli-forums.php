<?php
/**
 * BuddyPress Playground CLI Forums Command
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
 * CLI command for bbPress forum generation
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Forums extends WP_CLI_Command {

    /**
     * Generate bbPress forums, topics, and replies
     *
     * ## OPTIONS
     *
     * [--forums=<count>]
     * : Number of forums to generate
     * ---
     * default: 5
     * ---
     *
     * [--topics=<count>]
     * : Number of topics per forum
     * ---
     * default: 10
     * ---
     *
     * [--replies=<count>]
     * : Number of replies per topic
     * ---
     * default: 5
     * ---
     *
     * [--no-tags]
     * : Don't generate topic tags
     *
     * ## EXAMPLES
     *
     *     wp bp playground forums
     *     wp bp playground forums --forums=10 --topics=20 --replies=10
     *     wp bp playground forums --forums=3 --no-tags
     *
     * @since 1.0.0
     */
    public function __invoke($args, $assoc_args) {
        if (!class_exists('bbPress')) {
            WP_CLI::error('bbPress is not installed or active.');
        }

        $forums_count = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'forums', 5);
        $topics_per_forum = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'topics', 10);
        $replies_per_topic = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'replies', 5);
        $with_tags = !isset($assoc_args['no-tags']);

        WP_CLI::line("Generating {$forums_count} forums with {$topics_per_forum} topics each...");

        $bbpress_module = bp_playground_get_module('bbpress');
        if (!$bbpress_module) {
            WP_CLI::error('BBPress module not available.');
        }

        $options = [
            'forums' => $forums_count,
            'topics_per_forum' => $topics_per_forum,
            'replies_per_topic' => $replies_per_topic,
            'with_tags' => $with_tags,
        ];

        $result = $bbpress_module->generate($options);

        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }

        $forums = $result['forums_created'] ?? 0;
        $topics = $result['topics_created'] ?? 0;
        $replies = $result['replies_created'] ?? 0;

        WP_CLI::success("Generated {$forums} forums, {$topics} topics, and {$replies} replies!");
    }
}
