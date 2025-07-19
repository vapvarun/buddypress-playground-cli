<?php
/**
 * BuddyPress Playground Main CLI Command
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
 * Main CLI command for BuddyPress Playground
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Main extends WP_CLI_Command {

    /**
     * Generate comprehensive BuddyPress test data
     *
     * ## OPTIONS
     *
     * [--scale=<scale>]
     * : Scale of data generation (small, medium, large, enterprise)
     * ---
     * default: medium
     * options:
     *   - small
     *   - medium
     *   - large
     *   - enterprise
     * ---
     *
     * [--users=<users>]
     * : Number of users to generate
     * ---
     * default: 2000
     * ---
     *
     * [--groups=<groups>]
     * : Number of groups to generate
     * ---
     * default: 100
     * ---
     *
     * [--activities=<activities>]
     * : Number of activities to generate
     * ---
     * default: 50000
     * ---
     *
     * [--messages=<messages>]
     * : Number of messages to generate
     * ---
     * default: 8000
     * ---
     *
     * [--forums=<forums>]
     * : Number of forums to generate
     * ---
     * default: 40
     * ---
     *
     * [--dry-run]
     * : Perform a dry run without creating actual data
     *
     * [--skip-components=<components>]
     * : Comma-separated list of components to skip
     * ---
     * default: none
     * ---
     *
     * ## EXAMPLES
     *
     *     # Generate medium-scale test data
     *     wp bp playground generate-all
     *
     *     # Generate large-scale test data
     *     wp bp playground generate-all --scale=large
     *
     *     # Generate custom data amounts
     *     wp bp playground generate-all --users=5000 --groups=200
     *
     *     # Dry run to see what would be generated
     *     wp bp playground generate-all --dry-run
     *
     * @since 1.0.0
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     * @return void
     */
    public function generate_all($args, $assoc_args) {
        $scale = WP_CLI\Utils\get_flag_value($assoc_args, 'scale', 'medium');
        $dry_run = WP_CLI\Utils\get_flag_value($assoc_args, 'dry-run', false);
        $skip_components = WP_CLI\Utils\get_flag_value($assoc_args, 'skip-components', '');

        // Parse skip components
        $skip_components = $skip_components ? array_map('trim', explode(',', $skip_components)) : [];

        // Get scale configuration
        $config = $this->get_scale_config($scale, $assoc_args);
        if (is_wp_error($config)) {
            WP_CLI::error($config->get_error_message());
        }

        WP_CLI::line(WP_CLI::colorize('%GStarting comprehensive BuddyPress data generation...%n'));
        WP_CLI::line("Scale: {$scale}");
        WP_CLI::line("Dry Run: " . ($dry_run ? 'Yes' : 'No'));
        
        if (!empty($skip_components)) {
            WP_CLI::line("Skipping components: " . implode(', ', $skip_components));
        }

        $this->display_generation_plan($config);

        if (!$dry_run) {
            // Confirm before proceeding with large datasets
            if ($config['users'] > 5000) {
                WP_CLI::confirm('This will generate a large dataset. Continue?');
            }

            $core = bp_playground_get_module('core');
            $result = $core->generate_scenario('custom', array_merge($config, [
                'dry_run' => false,
                'skip_components' => $skip_components,
            ]));

            $this->display_generation_results($result);
        } else {
            WP_CLI::success('Dry run completed. Use --no-dry-run to actually generate data.');
        }
    }

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
     * : Perform a dry run without creating actual data
     *
     * [--customize=<params>]
     * : JSON string of custom parameters to override scenario defaults
     *
     * ## EXAMPLES
     *
     *     # Generate small community scenario
     *     wp bp playground scenario small-community
     *
     *     # Generate addon testing scenario
     *     wp bp playground scenario addon-testing
     *
     *     # Customize scenario parameters
     *     wp bp playground scenario medium-community --customize='{"users":3000,"groups":150}'
     *
     * @since 1.0.0
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     * @return void
     */
    public function scenario($args, $assoc_args) {
        if (empty($args[0])) {
            WP_CLI::error('Please specify a scenario name.');
        }

        $scenario_name = $args[0];
        $dry_run = WP_CLI\Utils\get_flag_value($assoc_args, 'dry-run', false);
        $customize = WP_CLI\Utils\get_flag_value($assoc_args, 'customize', '');

        // Parse customization parameters
        $custom_params = [];
        if ($customize) {
            $custom_params = json_decode($customize, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                WP_CLI::error('Invalid JSON in customize parameter.');
            }
        }

        $core = bp_playground_get_module('core');
        $available_scenarios = $core->get_available_scenarios();

        if (!isset($available_scenarios[$scenario_name])) {
            WP_CLI::error(sprintf(
                'Unknown scenario: %s. Available scenarios: %s',
                $scenario_name,
                implode(', ', array_keys($available_scenarios))
            ));
        }

        $scenario_info = $available_scenarios[$scenario_name];

        WP_CLI::line(WP_CLI::colorize('%GGenerating scenario: ' . $scenario_info['name'] . '%n'));
        WP_CLI::line($scenario_info['description']);
        WP_CLI::line("Estimated time: {$scenario_info['estimated_time']}");
        WP_CLI::line("Dry Run: " . ($dry_run ? 'Yes' : 'No'));

        if (!empty($custom_params)) {
            WP_CLI::line("Custom parameters: " . json_encode($custom_params));
        }

        if (!$dry_run) {
            if ($scenario_info['users'] > 2000) {
                WP_CLI::confirm('This scenario will generate a significant amount of data. Continue?');
            }

            $result = $core->generate_scenario($scenario_name, $custom_params);
            $this->display_generation_results($result);
        } else {
            WP_CLI::success('Dry run completed. Remove --dry-run to generate actual data.');
        }
    }

    /**
     * Display system information and plugin status
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Format of the output
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *     # Display system info as table
     *     wp bp playground info
     *
     *     # Display system info as JSON
     *     wp bp playground info --format=json
     *
     * @since 1.0.0
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     * @return void
     */
    public function info($args, $assoc_args) {
        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');

        $core = bp_playground_get_module('core');
        $system_info = $core->get_system_info();

        if ($format === 'table') {
            WP_CLI::line(WP_CLI::colorize('%GBuddyPress Playground System Information%n'));
            WP_CLI::line('');

            $table_data = [];
            foreach ($system_info as $key => $value) {
                $formatted_key = ucfirst(str_replace('_', ' ', $key));
                
                if (is_array($value)) {
                    $value = implode(', ', array_keys(array_filter($value)));
                } elseif (is_bool($value)) {
                    $value = $value ? 'Yes' : 'No';
                }
                
                $table_data[] = [
                    'Property' => $formatted_key,
                    'Value' => $value,
                ];
            }

            WP_CLI\Utils\format_items('table', $table_data, ['Property', 'Value']);
        } else {
            WP_CLI\Utils\format_items($format, [$system_info], array_keys($system_info));
        }
    }

    /**
     * Display comprehensive statistics about generated data
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Format of the output
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - yaml
     * ---
     *
     * [--detailed]
     * : Show detailed statistics for each component
     *
     * ## EXAMPLES
     *
     *     # Display basic statistics
     *     wp bp playground stats
     *
     *     # Display detailed statistics
     *     wp bp playground stats --detailed
     *
     *     # Export statistics as JSON
     *     wp bp playground stats --format=json
     *
     * @since 1.0.0
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     * @return void
     */
    public function stats($args, $assoc_args) {
        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');
        $detailed = WP_CLI\Utils\get_flag_value($assoc_args, 'detailed', false);

        $all_stats = [];
        $total_items = 0;

        // Get stats from all modules
        $modules = ['users', 'xprofile', 'groups', 'activities', 'messages', 'friends', 'bbpress'];
        
        foreach ($modules as $module_name) {
            $module = bp_playground_get_module($module_name);
            if ($module) {
                $module_stats = $module->get_stats();
                $all_stats[$module_name] = $module_stats;
                
                // Sum up total items
                foreach ($module_stats as $stat_value) {
                    if (is_numeric($stat_value)) {
                        $total_items += $stat_value;
                    }
                }
            }
        }

        if ($format === 'table') {
            WP_CLI::line(WP_CLI::colorize('%GBuddyPress Playground Statistics%n'));
            WP_CLI::line("Total generated items: " . number_format($total_items));
            WP_CLI::line('');

            if ($detailed) {
                foreach ($all_stats as $module_name => $stats) {
                    if (empty($stats) || array_sum(array_filter($stats, 'is_numeric')) === 0) {
                        continue;
                    }

                    WP_CLI::line(WP_CLI::colorize('%Y' . ucfirst($module_name) . ' Module:%n'));
                    
                    $table_data = [];
                    foreach ($stats as $key => $value) {
                        if (is_numeric($value) && $value > 0) {
                            $table_data[] = [
                                'Metric' => ucfirst(str_replace('_', ' ', $key)),
                                'Count' => number_format($value),
                            ];
                        }
                    }
                    
                    if (!empty($table_data)) {
                        WP_CLI\Utils\format_items('table', $table_data, ['Metric', 'Count']);
                    }
                    WP_CLI::line('');
                }
            } else {
                $summary_data = [];
                foreach ($all_stats as $module_name => $stats) {
                    $total_module_items = array_sum(array_filter($stats, 'is_numeric'));
                    if ($total_module_items > 0) {
                        $summary_data[] = [
                            'Component' => ucfirst($module_name),
                            'Total Items' => number_format($total_module_items),
                        ];
                    }
                }
                
                if (!empty($summary_data)) {
                    WP_CLI\Utils\format_items('table', $summary_data, ['Component', 'Total Items']);
                }
            }
        } else {
            WP_CLI\Utils\format_items($format, [$all_stats], array_keys($all_stats));
        }
    }

    /**
     * Clean up generated data
     *
     * ## OPTIONS
     *
     * [<component>]
     * : Component to clean up (users, groups, activities, messages, etc.)
     * ---
     * default: all
     * ---
     *
     * [--older-than=<days>]
     * : Only clean up data older than specified days
     * ---
     * default: 0
     * ---
     *
     * [--dry-run]
     * : Show what would be cleaned up without actually doing it
     *
     * [--force]
     * : Skip confirmation prompts
     *
     * ## EXAMPLES
     *
     *     # Clean up all generated data (with confirmation)
     *     wp bp playground cleanup
     *
     *     # Clean up only activities
     *     wp bp playground cleanup activities
     *
     *     # Clean up data older than 30 days
     *     wp bp playground cleanup --older-than=30
     *
     *     # Dry run to see what would be cleaned
     *     wp bp playground cleanup --dry-run
     *
     * @since 1.0.0
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     * @return void
     */
    public function cleanup($args, $assoc_args) {
        $component = isset($args[0]) ? $args[0] : 'all';
        $older_than = WP_CLI\Utils\get_flag_value($assoc_args, 'older-than', 0);
        $dry_run = WP_CLI\Utils\get_flag_value($assoc_args, 'dry-run', false);
        $force = WP_CLI\Utils\get_flag_value($assoc_args, 'force', false);

        $cleanup_options = [
            'component' => $component,
            'older_than_days' => intval($older_than),
            'dry_run' => $dry_run,
        ];

        WP_CLI::line(WP_CLI::colorize('%YStarting cleanup operation...%n'));
        WP_CLI::line("Component: {$component}");
        WP_CLI::line("Older than: " . ($older_than > 0 ? "{$older_than} days" : "all data"));
        WP_CLI::line("Dry run: " . ($dry_run ? 'Yes' : 'No'));

        if (!$dry_run && !$force) {
            WP_CLI::confirm('This will permanently delete data. Continue?');
        }

        $core = bp_playground_get_module('core');
        $results = $core->cleanup_orphaned_data($cleanup_options);

        if (is_wp_error($results)) {
            WP_CLI::error($results->get_error_message());
        }

        $this->display_cleanup_results($results, $dry_run);
    }

    /**
     * Verify data integrity and relationships
     *
     * ## OPTIONS
     *
     * [--fix]
     * : Attempt to fix found issues automatically
     *
     * [--relationships]
     * : Check relationship integrity between components
     *
     * ## EXAMPLES
     *
     *     # Basic data verification
     *     wp bp playground verify
     *
     *     # Check relationships and fix issues
     *     wp bp playground verify --relationships --fix
     *
     * @since 1.0.0
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     * @return void
     */
    public function verify($args, $assoc_args) {
        $fix = WP_CLI\Utils\get_flag_value($assoc_args, 'fix', false);
        $check_relationships = WP_CLI\Utils\get_flag_value($assoc_args, 'relationships', false);

        WP_CLI::line(WP_CLI::colorize('%GVerifying BuddyPress data integrity...%n'));

        $validator = bp_playground_get_module('validator');
        if (!$validator) {
            WP_CLI::error('Validator module not available.');
        }

        $verification_options = [
            'fix_issues' => $fix,
            'check_relationships' => $check_relationships,
        ];

        $results = $validator->verify_data_integrity($verification_options);

        if (is_wp_error($results)) {
            WP_CLI::error($results->get_error_message());
        }

        $this->display_verification_results($results, $fix);
    }

    /**
     * List available scenarios with descriptions
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Format of the output
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *     # List available scenarios
     *     wp bp playground list-scenarios
     *
     * @since 1.0.0
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     * @return void
     */
    public function list_scenarios($args, $assoc_args) {
        $format = WP_CLI\Utils\get_flag_value($assoc_args, 'format', 'table');

        $core = bp_playground_get_module('core');
        $scenarios = $core->get_available_scenarios();

        if ($format === 'table') {
            WP_CLI::line(WP_CLI::colorize('%GAvailable BuddyPress Playground Scenarios%n'));
            WP_CLI::line('');

            $table_data = [];
            foreach ($scenarios as $key => $scenario) {
                $table_data[] = [
                    'Scenario' => $key,
                    'Name' => $scenario['name'],
                    'Users' => number_format($scenario['users']),
                    'Est. Time' => $scenario['estimated_time'],
                    'Description' => $scenario['description'],
                ];
            }

            WP_CLI\Utils\format_items('table', $table_data, ['Scenario', 'Name', 'Users', 'Est. Time', 'Description']);
        } else {
            WP_CLI\Utils\format_items($format, [$scenarios], array_keys($scenarios));
        }
    }

    /**
     * Export scenario configuration to JSON file
     *
     * ## OPTIONS
     *
     * <scenario>
     * : Scenario name to export
     *
     * [--file=<file>]
     * : Output file path
     * ---
     * default: scenario-export.json
     * ---
     *
     * [--customize=<params>]
     * : JSON string of custom parameters to include
     *
     * ## EXAMPLES
     *
     *     # Export medium community scenario
     *     wp bp playground export medium-community
     *
     *     # Export with custom parameters
     *     wp bp playground export addon-testing --customize='{"users":1000}'
     *
     * @since 1.0.0
     * @param array $args Positional arguments
     * @param array $assoc_args Associative arguments
     * @return void
     */
    public function export($args, $assoc_args) {
        if (empty($args[0])) {
            WP_CLI::error('Please specify a scenario name to export.');
        }

        $scenario_name = $args[0];
        $file = WP_CLI\Utils\get_flag_value($assoc_args, 'file', 'scenario-export.json');
        $customize = WP_CLI\Utils\get_flag_value($assoc_args, 'customize', '');

        $core = bp_playground_get_module('core');
        $available_scenarios = $core->get_available_scenarios();

        if (!isset($available_scenarios[$scenario_name])) {
            WP_CLI::error("Unknown scenario: {$scenario_name}");
        }

        // Get base scenario configuration
        $scenario_config = $core->get_scenario_config($scenario_name);
        if (is_wp_error($scenario_config)) {
            WP_CLI::error($scenario_config->get_error_message());
        }

        // Apply customizations
        if ($customize) {
            $custom_params = json_decode($customize, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                WP_CLI::error('Invalid JSON in customize parameter.');
            }
            $scenario_config = array_merge($scenario_config, $custom_params);
        }

        // Add metadata
        $export_data = [
            'scenario_name' => $scenario_name,
            'exported_at' => current_time('mysql'),
            'plugin_version' => BP_PLAYGROUND_VERSION,
            'configuration' => $scenario_config,
        ];

        // Write to file
        $json_data = json_encode($export_data, JSON_PRETTY_PRINT);
        if (file_put_contents($file, $json_data) === false) {
            WP_CLI::error("Failed to write to file: {$file}");
        }

        WP_CLI::success("Scenario '{$scenario_name}' exported to: {$file}");
    }

    /**
     * Get scale configuration based on scale name and overrides
     *
     * @since 1.0.0
     * @param string $scale Scale name
     * @param array $overrides Override values
     * @return array|WP_Error Scale configuration
     */
    private function get_scale_config($scale, $overrides = []) {
        $scale_configs = [
            'small' => [
                'users' => 500,
                'groups' => 25,
                'activities' => 10000,
                'messages' => 2000,
                'forums' => 15,
            ],
            'medium' => [
                'users' => 2000,
                'groups' => 100,
                'activities' => 50000,
                'messages' => 8000,
                'forums' => 40,
            ],
            'large' => [
                'users' => 10000,
                'groups' => 500,
                'activities' => 200000,
                'messages' => 25000,
                'forums' => 100,
            ],
            'enterprise' => [
                'users' => 25000,
                'groups' => 1000,
                'activities' => 500000,
                'messages' => 50000,
                'forums' => 200,
            ],
        ];

        if (!isset($scale_configs[$scale])) {
            return new WP_Error('invalid_scale', "Invalid scale: {$scale}. Available scales: " . implode(', ', array_keys($scale_configs)));
        }

        $config = $scale_configs[$scale];

        // Apply overrides
        foreach (['users', 'groups', 'activities', 'messages', 'forums'] as $param) {
            if (isset($overrides[$param])) {
                $config[$param] = intval($overrides[$param]);
            }
        }

        return $config;
    }

    /**
     * Display generation plan
     *
     * @since 1.0.0
     * @param array $config Generation configuration
     * @return void
     */
    private function display_generation_plan($config) {
        WP_CLI::line('');
        WP_CLI::line(WP_CLI::colorize('%YGeneration Plan:%n'));
        WP_CLI::line("Users: " . number_format($config['users']));
        WP_CLI::line("Groups: " . number_format($config['groups']));
        WP_CLI::line("Activities: " . number_format($config['activities']));
        WP_CLI::line("Messages: " . number_format($config['messages']));
        WP_CLI::line("Forums: " . number_format($config['forums']));
        WP_CLI::line('');
    }

    /**
     * Display generation results
     *
     * @since 1.0.0
     * @param array $results Generation results
     * @return void
     */
    private function display_generation_results($results) {
        if (!empty($results['errors'])) {
            WP_CLI::warning('Generation completed with errors:');
            foreach ($results['errors'] as $error) {
                WP_CLI::line("  - {$error}");
            }
        }

        if (isset($results['stats'])) {
            $stats = $results['stats'];
            WP_CLI::line('');
            WP_CLI::line(WP_CLI::colorize('%GGeneration completed successfully!%n'));
            WP_CLI::line("Duration: {$stats['duration']} seconds");
            WP_CLI::line("Peak memory: {$stats['memory_peak']} MB");
        }

        if (!empty($results['components'])) {
            WP_CLI::line('');
            WP_CLI::line(WP_CLI::colorize('%YComponent Results:%n'));
            foreach ($results['components'] as $component => $component_result) {
                if (is_array($component_result)) {
                    $total = 0;
                    foreach ($component_result as $key => $value) {
                        if (is_numeric($value) && strpos($key, 'created') !== false) {
                            $total += $value;
                        }
                    }
                    WP_CLI::line("  {$component}: {$total} items created");
                }
            }
        }
    }

    /**
     * Display cleanup results
     *
     * @since 1.0.0
     * @param array $results Cleanup results
     * @param bool $dry_run Whether this was a dry run
     * @return void
     */
    private function display_cleanup_results($results, $dry_run = false) {
        $action = $dry_run ? 'would be cleaned' : 'cleaned';
        
        WP_CLI::line('');
        WP_CLI::line(WP_CLI::colorize('%GCleanup Results:%n'));
        
        $total_cleaned = 0;
        foreach ($results as $component => $count) {
            // Skip non-component keys
            if (in_array($component, ['start_time', 'end_time', 'duration', 'memory_peak', 'errors'])) {
                continue;
            }
            
            if (is_numeric($count) && $count > 0) {
                WP_CLI::line("  {$component}: {$count} items {$action}");
                $total_cleaned += $count;
            }
        }

        if ($total_cleaned > 0) {
            WP_CLI::line("Total: {$total_cleaned} items {$action}");
            
            if (!$dry_run) {
                WP_CLI::success('Cleanup completed successfully!');
            } else {
                WP_CLI::success('Dry run completed. Use --no-dry-run to perform actual cleanup.');
            }
        } else {
            WP_CLI::line('No items found to clean up.');
        }
    }

    /**
     * Display verification results
     *
     * @since 1.0.0
     * @param array $results Verification results
     * @param bool $fix Whether fixes were attempted
     * @return void
     */
    private function display_verification_results($results, $fix = false) {
        WP_CLI::line('');
        WP_CLI::line(WP_CLI::colorize('%GData Integrity Verification Results:%n'));

        if (!empty($results['issues_found'])) {
            WP_CLI::warning("Found {$results['issues_found']} integrity issues:");
            
            foreach ($results['details'] as $component => $issues) {
                if (!empty($issues)) {
                    WP_CLI::line("  {$component}:");
                    foreach ($issues as $issue) {
                        WP_CLI::line("    - {$issue}");
                    }
                }
            }

            if ($fix && !empty($results['issues_fixed'])) {
                WP_CLI::success("Fixed {$results['issues_fixed']} issues automatically.");
            } elseif (!$fix) {
                WP_CLI::line('');
                WP_CLI::line('Use --fix to attempt automatic repairs.');
            }
        } else {
            WP_CLI::success('No integrity issues found. Data is consistent.');
        }

        if (isset($results['performance_notes'])) {
            WP_CLI::line('');
            WP_CLI::line(WP_CLI::colorize('%YPerformance Notes:%n'));
            foreach ($results['performance_notes'] as $note) {
                WP_CLI::line("  - {$note}");
            }
        }
    }
}