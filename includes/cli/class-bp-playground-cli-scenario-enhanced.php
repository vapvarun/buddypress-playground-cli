<?php
/**
 * BuddyPress Playground CLI Enhanced Scenario Commands
 *
 * @package BuddyPress_Playground
 * @subpackage CLI
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate complete BuddyPress scenarios with proper sequencing
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Scenario_Enhanced extends WP_CLI_Command {
    
    /**
     * Generate a complete scenario with proper sequencing
     *
     * ## OPTIONS
     *
     * <scenario>
     * : The scenario to generate (small_community, medium_community, large_community, educational, professional, minimal, development)
     *
     * [--clean]
     * : Clean existing data before generating
     *
     * [--skip-phases=<phases>]
     * : Comma-separated list of phases to skip
     *
     * [--dry-run]
     * : Show what would be generated without actually creating data
     *
     * [--yes]
     * : Skip confirmation prompts
     *
     * ## EXAMPLES
     *
     *     # Generate small community scenario
     *     $ wp bp playground scenario generate small_community
     *
     *     # Generate with clean start
     *     $ wp bp playground scenario generate medium_community --clean
     *
     *     # Skip certain phases
     *     $ wp bp playground scenario generate development --skip-phases=forums,messages
     *
     * @when after_wp_load
     */
    public function generate($args, $assoc_args) {
        $scenario_name = $args[0];
        
        // Load scenario configurations
        $configs_file = BP_PLAYGROUND_PLUGIN_DIR . 'includes/data/scenario-configs.php';
        if (!file_exists($configs_file)) {
            WP_CLI::error("Scenario configurations file not found");
            return;
        }
        
        $scenarios = require $configs_file;
        
        if (!isset($scenarios[$scenario_name])) {
            WP_CLI::error("Unknown scenario: $scenario_name");
            WP_CLI::log("\nAvailable scenarios:");
            foreach ($scenarios as $key => $config) {
                WP_CLI::log("  - $key: " . $config['description']);
            }
            return;
        }
        
        $config = $scenarios[$scenario_name];
        
        // Apply command line options
        if (isset($assoc_args['clean'])) {
            $config['clean_existing'] = true;
        }
        
        if (isset($assoc_args['skip-phases'])) {
            $config['skip_phases'] = explode(',', $assoc_args['skip-phases']);
        }
        
        $dry_run = isset($assoc_args['dry-run']);
        
        // Show scenario details
        WP_CLI::log("\n" . WP_CLI::colorize("%G=== BuddyPress Playground Scenario Generator ===%n"));
        WP_CLI::log("Scenario: " . WP_CLI::colorize("%Y{$config['name']}%n"));
        WP_CLI::log("Description: {$config['description']}");
        WP_CLI::log("");
        
        if ($dry_run) {
            $this->show_dry_run($config);
            return;
        }
        
        // Confirm before proceeding (skip if --yes flag is provided)
        $skip_confirm = isset($assoc_args['yes']);
        if ($config['clean_existing'] && !$skip_confirm) {
            WP_CLI::confirm("This will DELETE all existing BuddyPress data. Continue?");
        }
        
        // Initialize sequence manager
        $sequence_manager = new BP_Playground_Sequence_Manager();
        
        // Execute scenario
        WP_CLI::log("Starting scenario generation...\n");
        
        $start_time = microtime(true);
        $results = $sequence_manager->execute_scenario($config);
        $total_time = microtime(true) - $start_time;
        
        // Display results
        $this->display_results($results, $total_time);
        
        if ($results['success']) {
            WP_CLI::success("Scenario generation completed successfully!");
        } else {
            WP_CLI::warning("Scenario generation completed with errors");
        }
    }
    
    /**
     * List available scenarios
     *
     * ## EXAMPLES
     *
     *     $ wp bp playground scenario list
     *
     * @when after_wp_load
     */
    public function list($args, $assoc_args) {
        $configs_file = BP_PLAYGROUND_PLUGIN_DIR . 'includes/data/scenario-configs.php';
        if (!file_exists($configs_file)) {
            WP_CLI::error("Scenario configurations file not found");
            return;
        }
        
        $scenarios = require $configs_file;
        
        WP_CLI::log("\n" . WP_CLI::colorize("%G=== Available Scenarios ===%n\n"));
        
        $table_data = [];
        foreach ($scenarios as $key => $config) {
            $table_data[] = [
                'Scenario' => $key,
                'Name' => $config['name'],
                'Users' => $config['users']['count'] ?? 0,
                'Groups' => $config['groups']['count'] ?? 0,
                'Description' => $config['description']
            ];
        }
        
        WP_CLI\Utils\format_items('table', $table_data, ['Scenario', 'Name', 'Users', 'Groups', 'Description']);
    }
    
    /**
     * Show scenario details
     *
     * ## OPTIONS
     *
     * <scenario>
     * : The scenario to inspect
     *
     * ## EXAMPLES
     *
     *     $ wp bp playground scenario info small_community
     *
     * @when after_wp_load
     */
    public function info($args, $assoc_args) {
        $scenario_name = $args[0];
        
        $configs_file = BP_PLAYGROUND_PLUGIN_DIR . 'includes/data/scenario-configs.php';
        if (!file_exists($configs_file)) {
            WP_CLI::error("Scenario configurations file not found");
            return;
        }
        
        $scenarios = require $configs_file;
        
        if (!isset($scenarios[$scenario_name])) {
            WP_CLI::error("Unknown scenario: $scenario_name");
            return;
        }
        
        $config = $scenarios[$scenario_name];
        
        WP_CLI::log("\n" . WP_CLI::colorize("%G=== Scenario Details ===%n"));
        WP_CLI::log("Name: " . WP_CLI::colorize("%Y{$config['name']}%n"));
        WP_CLI::log("Description: {$config['description']}\n");
        
        // Users
        if (isset($config['users'])) {
            WP_CLI::log(WP_CLI::colorize("%BUsers:%n"));
            WP_CLI::log("  Count: " . $config['users']['count']);
            WP_CLI::log("  With Avatar: " . ($config['users']['with_avatar'] ? 'Yes' : 'No'));
            WP_CLI::log("  With Cover: " . ($config['users']['with_cover_image'] ? 'Yes' : 'No'));
        }
        
        // Groups
        if (isset($config['groups']) && $config['groups']['count'] > 0) {
            WP_CLI::log("\n" . WP_CLI::colorize("%BGroups:%n"));
            WP_CLI::log("  Count: " . $config['groups']['count']);
            WP_CLI::log("  Members: " . $config['groups']['min_members'] . "-" . $config['groups']['max_members']);
            WP_CLI::log("  With Forums: " . ($config['groups']['with_forums'] ? 'Yes' : 'No'));
        }
        
        // Friendships
        if (isset($config['friendships'])) {
            WP_CLI::log("\n" . WP_CLI::colorize("%BFriendships:%n"));
            WP_CLI::log("  Per User: " . $config['friendships']['per_user']);
            WP_CLI::log("  Acceptance Rate: " . ($config['friendships']['acceptance_rate'] * 100) . "%");
        }
        
        // Messages
        if (isset($config['messages'])) {
            WP_CLI::log("\n" . WP_CLI::colorize("%BMessages:%n"));
            WP_CLI::log("  Threads: " . $config['messages']['threads']);
            WP_CLI::log("  Messages per Thread: " . $config['messages']['min_messages'] . "-" . $config['messages']['max_messages']);
        }
        
        // Activities
        if (isset($config['activities'])) {
            WP_CLI::log("\n" . WP_CLI::colorize("%BActivities:%n"));
            WP_CLI::log("  Count: " . $config['activities']['count']);
            WP_CLI::log("  With Comments: " . ($config['activities']['with_comments'] ? 'Yes' : 'No'));
            WP_CLI::log("  With Favorites: " . ($config['activities']['with_favorites'] ? 'Yes' : 'No'));
        }
        
        // Forums
        if (isset($config['forums']) && $config['forums']['count'] > 0) {
            WP_CLI::log("\n" . WP_CLI::colorize("%BForums:%n"));
            WP_CLI::log("  Count: " . $config['forums']['count']);
            WP_CLI::log("  Topics per Forum: " . $config['forums']['topics_per_forum']);
            WP_CLI::log("  Replies per Topic: " . $config['forums']['replies_per_topic']);
        }
        
        // Member Types
        if (isset($config['member_types'])) {
            WP_CLI::log("\n" . WP_CLI::colorize("%BMember Types:%n"));
            foreach ($config['member_types'] as $type => $type_config) {
                WP_CLI::log("  - " . $type_config['labels']['name']);
            }
        }
    }
    
    /**
     * Show what would be generated (dry run)
     */
    private function show_dry_run($config) {
        WP_CLI::log(WP_CLI::colorize("%Y[DRY RUN MODE]%n\n"));
        
        WP_CLI::log("The following would be generated:\n");
        
        $phases = [
            'prepare' => 'Environment preparation',
            'xprofile' => 'XProfile structure creation',
            'users' => 'User account creation',
            'profile_data' => 'Profile data population',
            'groups' => 'Group creation',
            'friendships' => 'Friendship connections',
            'messages' => 'Private messages',
            'activities' => 'Activity stream items',
            'forums' => 'Forum content',
            'finalize' => 'Cache cleanup and finalization'
        ];
        
        foreach ($phases as $phase => $description) {
            $skip = isset($config['skip_phases']) && in_array($phase, $config['skip_phases']);
            $status = $skip ? WP_CLI::colorize("%R[SKIP]%n") : WP_CLI::colorize("%G[RUN]%n");
            
            WP_CLI::log("  $status $description");
            
            if (!$skip) {
                switch ($phase) {
                    case 'users':
                        WP_CLI::log("        → " . $config['users']['count'] . " users");
                        break;
                    case 'groups':
                        if (isset($config['groups']['count'])) {
                            WP_CLI::log("        → " . $config['groups']['count'] . " groups");
                        }
                        break;
                    case 'activities':
                        if (isset($config['activities']['count'])) {
                            WP_CLI::log("        → " . $config['activities']['count'] . " activities");
                        }
                        break;
                    case 'messages':
                        if (isset($config['messages']['threads'])) {
                            WP_CLI::log("        → " . $config['messages']['threads'] . " message threads");
                        }
                        break;
                }
            }
        }
        
        WP_CLI::log("\nNo data will be created in dry run mode.");
    }
    
    /**
     * Display execution results
     */
    private function display_results($results, $total_time) {
        WP_CLI::log("\n" . WP_CLI::colorize("%G=== Execution Summary ===%n\n"));
        
        // Phase results
        foreach ($results['summary'] as $phase => $phase_result) {
            if (!isset($phase_result['success'])) {
                continue;
            }
            
            $status = $phase_result['success'] 
                ? WP_CLI::colorize("%G✓%n")
                : WP_CLI::colorize("%R✗%n");
            
            $phase_name = BP_Playground_Sequence_Manager::PHASES[$phase] ?? $phase;
            WP_CLI::log("$status $phase_name");
            
            // Show phase-specific data
            if (isset($phase_result['data'])) {
                $data = $phase_result['data'];
                
                if (isset($data['skipped']) && $data['skipped']) {
                    WP_CLI::log("    → Skipped");
                } else {
                    switch ($phase) {
                        case 'xprofile':
                            if (isset($data['groups_created'])) {
                                WP_CLI::log("    → {$data['groups_created']} groups, {$data['fields_created']} fields");
                            }
                            break;
                        case 'users':
                            if (isset($data['created'])) {
                                WP_CLI::log("    → {$data['created']} users created");
                            }
                            break;
                        case 'profile_data':
                            if (isset($data['users_populated'])) {
                                WP_CLI::log("    → {$data['users_populated']} profiles populated");
                            }
                            break;
                        case 'groups':
                            if (isset($data['created'])) {
                                WP_CLI::log("    → {$data['created']} groups created");
                            }
                            break;
                        case 'friendships':
                            if (isset($data['created'])) {
                                WP_CLI::log("    → {$data['created']} friendships created");
                            }
                            break;
                        case 'messages':
                            if (isset($data['threads_created'])) {
                                WP_CLI::log("    → {$data['threads_created']} message threads created");
                            }
                            break;
                        case 'activities':
                            if (isset($data['created'])) {
                                WP_CLI::log("    → {$data['created']} activities created");
                            }
                            break;
                    }
                }
            }
            
            // Show errors if any
            if (!empty($phase_result['errors'])) {
                foreach ($phase_result['errors'] as $error) {
                    WP_CLI::log(WP_CLI::colorize("    %R→ Error: $error%n"));
                }
            }
        }
        
        // Total time
        WP_CLI::log("\n" . WP_CLI::colorize("%BTotal execution time:%n " . sprintf("%.2f seconds", $total_time)));
    }
}