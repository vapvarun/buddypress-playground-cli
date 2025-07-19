<?php
/**
 * BuddyPress Playground CLI Activities Command
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
 * CLI command for activity generation
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Activities extends WP_CLI_Command {

    /**
     * Generate activities and engagement
     *
     * ## OPTIONS
     *
     * [--count=<count>]
     * : Number of activities to generate
     * ---
     * default: 10000
     * ---
     *
     * [--with-mentions]
     * : Include @mentions
     *
     * [--with-comments]
     * : Generate comments
     *
     * [--favorite-rate=<rate>]
     * : Favorite rate (0.0-1.0)
     * ---
     * default: 0.15
     * ---
     *
     * [--comment-rate=<rate>]
     * : Comment rate (0.0-1.0)
     * ---
     * default: 0.25
     * ---
     *
     * ## EXAMPLES
     *
     *     wp bp playground activities --count=5000 --with-mentions
     *     wp bp playground activities --count=10000 --favorite-rate=0.2
     *
     * @since 1.0.0
     */
    public function __invoke($args, $assoc_args) {
        $count = WP_CLI\Utils\get_flag_value($assoc_args, 'count', 10000);
        $with_mentions = isset($assoc_args['with-mentions']) ? true : false;
        $with_comments = isset($assoc_args['with-comments']) ? true : false;
        $favorite_rate = WP_CLI\Utils\get_flag_value($assoc_args, 'favorite-rate', 0.15);
        $comment_rate = WP_CLI\Utils\get_flag_value($assoc_args, 'comment-rate', 0.25);

        WP_CLI::line("Generating {$count} activities...");

        $activities_module = bp_playground_get_module('activities');
        if (!$activities_module) {
            WP_CLI::error('Activities module not available.');
        }

        $options = [
            'count' => $count,
            'with_mentions' => $with_mentions,
            'with_comments' => $with_comments,
            'with_favorites' => true,
            'favorite_rate' => $favorite_rate,
            'comment_rate' => $comment_rate,
        ];

        $result = $activities_module->generate($options);

        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }

        WP_CLI::success("Generated {$result['activities_created']} activities successfully!");
    }
}