<?php
/**
 * BuddyPress Playground CLI Scenario Command
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
 * CLI command for scenario-based generation
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Scenario extends WP_CLI_Command {

    /**
     * Generate data using predefined scenarios
     *
     * ## OPTIONS
     *
     * <scenario>
     * : Scenario name
     * ---
     * options:
     *   - small-community
     *   - medium-community
     *   - large-community
     *   - addon-testing
     * ---
     *
     * [--dry-run]
     * : Show what would be generated
     *
     * [--customize=<params>]
     * : JSON string of custom parameters
     *
     * ## EXAMPLES
     *
     *     wp bp playground scenario small-community
     *     wp bp playground scenario addon-testing --dry-run
     *     wp bp playground scenario medium-community --customize='{"users":3000}'
     *
     * @since 1.0.0
     */
    public function __invoke($args, $assoc_args) {
        if (empty($args[0])) {
            WP_CLI::error('Please specify a scenario name.');
        }

        $scenario_name = $args[0];
        $dry_run = WP_CLI\Utils\get_flag_value($assoc_args, 'dry-run', false);
        $customize = WP_CLI\Utils\get_flag_value($assoc_args, 'customize', '');

        $custom_params = [];
        if ($customize) {
            $custom_params = json_decode($customize, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                WP_CLI::error('Invalid JSON in customize parameter.');
            }
        }

        $core = bp_playground_get_module('core');
        if (!$core) {
            WP_CLI::error('Core module not available.');
        }

        $available_scenarios = $core->get_available_scenarios();
        if (!isset($available_scenarios[$scenario_name])) {
            WP_CLI::error("Unknown scenario: {$scenario_name}");
        }

        $scenario_info = $available_scenarios[$scenario_name];
        WP_CLI::line("Generating scenario: {$scenario_info['name']}");
        WP_CLI::line($scenario_info['description']);

        if ($dry_run) {
            WP_CLI::line("Dry run - showing what would be generated:");
            WP_CLI::line("Estimated time: {$scenario_info['estimated_time']}");
            WP_CLI::success('Dry run completed.');
            return;
        }

        $result = $core->generate_scenario($scenario_name, $custom_params);

        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }

        if (!empty($result['errors'])) {
            WP_CLI::warning('Scenario completed with errors:');
            foreach ($result['errors'] as $error) {
                WP_CLI::line("  - {$error}");
            }
        }

        WP_CLI::success("Scenario '{$scenario_name}' generated successfully!");
    }
}