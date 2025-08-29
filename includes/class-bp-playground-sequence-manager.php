<?php
/**
 * BuddyPress Playground Sequence Manager
 * 
 * Manages the proper sequence of data generation to ensure dependencies are met
 * and no data is missed
 */

class BP_Playground_Sequence_Manager {
    
    /**
     * Execution phases in proper order
     */
    const PHASES = [
        'prepare'       => 'Prepare Environment',
        'xprofile'      => 'XProfile Structure',
        'users'         => 'User Accounts',
        'profile_data'  => 'Profile Data',
        'groups'        => 'Groups',
        'friendships'   => 'Friendships',
        'messages'      => 'Messages',
        'activities'    => 'Activities',
        'forums'        => 'Forums',
        'finalize'      => 'Finalize'
    ];
    
    /**
     * Current execution state
     */
    private $state = [];
    
    /**
     * Execution log
     */
    private $log = [];
    
    /**
     * Module instances
     */
    private $modules = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->initialize_state();
    }
    
    /**
     * Initialize execution state
     */
    private function initialize_state() {
        foreach (self::PHASES as $phase => $name) {
            $this->state[$phase] = [
                'status' => 'pending',
                'started_at' => null,
                'completed_at' => null,
                'results' => null,
                'errors' => []
            ];
        }
    }
    
    /**
     * Execute a complete scenario with proper sequencing
     */
    public function execute_scenario($scenario_config) {
        $this->log('Starting scenario execution: ' . ($scenario_config['name'] ?? 'Custom'));
        
        $overall_results = [
            'success' => true,
            'phases_completed' => [],
            'total_time' => 0,
            'summary' => []
        ];
        
        $start_time = microtime(true);
        
        // Execute each phase in sequence
        foreach (self::PHASES as $phase => $phase_name) {
            // Check if phase should be executed
            if (!$this->should_execute_phase($phase, $scenario_config)) {
                $this->log("Skipping phase: $phase_name");
                $this->state[$phase]['status'] = 'skipped';
                continue;
            }
            
            $this->log("Executing phase: $phase_name");
            $phase_result = $this->execute_phase($phase, $scenario_config);
            
            if (!$phase_result['success']) {
                $overall_results['success'] = false;
                $this->log("Phase failed: $phase_name", 'error');
                
                // Determine if we should continue
                if ($this->is_critical_phase($phase)) {
                    $this->log("Critical phase failed. Stopping execution.", 'error');
                    break;
                }
            }
            
            $overall_results['phases_completed'][] = $phase;
            $overall_results['summary'][$phase] = $phase_result;
        }
        
        $overall_results['total_time'] = microtime(true) - $start_time;
        $this->log(sprintf("Scenario execution completed in %.2f seconds", $overall_results['total_time']));
        
        return $overall_results;
    }
    
    /**
     * Execute a single phase
     */
    private function execute_phase($phase, $config) {
        $this->state[$phase]['status'] = 'running';
        $this->state[$phase]['started_at'] = time();
        
        $result = [
            'success' => true,
            'data' => null,
            'errors' => []
        ];
        
        try {
            switch ($phase) {
                case 'prepare':
                    $result = $this->phase_prepare($config);
                    break;
                    
                case 'xprofile':
                    $result = $this->phase_xprofile($config);
                    break;
                    
                case 'users':
                    $result = $this->phase_users($config);
                    break;
                    
                case 'profile_data':
                    $result = $this->phase_profile_data($config);
                    break;
                    
                case 'groups':
                    $result = $this->phase_groups($config);
                    break;
                    
                case 'friendships':
                    $result = $this->phase_friendships($config);
                    break;
                    
                case 'messages':
                    $result = $this->phase_messages($config);
                    break;
                    
                case 'activities':
                    $result = $this->phase_activities($config);
                    break;
                    
                case 'forums':
                    $result = $this->phase_forums($config);
                    break;
                    
                case 'finalize':
                    $result = $this->phase_finalize($config);
                    break;
            }
        } catch (Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
            $this->log("Phase exception: " . $e->getMessage(), 'error');
        }
        
        $this->state[$phase]['status'] = $result['success'] ? 'completed' : 'failed';
        $this->state[$phase]['completed_at'] = time();
        $this->state[$phase]['results'] = $result['data'];
        $this->state[$phase]['errors'] = $result['errors'];
        
        return $result;
    }
    
    /**
     * PHASE 1: Prepare environment
     */
    private function phase_prepare($config) {
        $this->log("Preparing environment...");
        
        // Check BuddyPress is active
        if (!function_exists('buddypress')) {
            return [
                'success' => false,
                'errors' => ['BuddyPress is not active']
            ];
        }
        
        // Clean up if requested
        if (!empty($config['clean_existing'])) {
            $this->log("Cleaning existing data...");
            $cleanup = bp_playground_get_module('cleanup');
            if ($cleanup) {
                $cleanup->cleanup_all();
            }
        }
        
        // Set up member types if needed
        if (!empty($config['member_types'])) {
            $this->setup_member_types($config['member_types']);
        }
        
        return [
            'success' => true,
            'data' => [
                'bp_version' => bp_get_version(),
                'cleaned' => !empty($config['clean_existing'])
            ]
        ];
    }
    
    /**
     * PHASE 2: Create XProfile structure (groups and fields)
     */
    private function phase_xprofile($config) {
        $this->log("Creating XProfile structure...");
        
        if (!bp_is_active('xprofile')) {
            $this->log("XProfile component not active, skipping");
            return ['success' => true, 'data' => ['skipped' => true]];
        }
        
        $results = [
            'groups_created' => 0,
            'fields_created' => 0,
            'options_created' => 0
        ];
        
        // Use predefined structure if specified
        if (!empty($config['use_predefined_xprofile'])) {
            // Load XProfile functions if not already loaded
            $xprofile_functions = BP_PLAYGROUND_PLUGIN_DIR . 'includes/functions-bp-playground-xprofile.php';
            if (file_exists($xprofile_functions)) {
                require_once $xprofile_functions;
            }
            
            // Register member types first
            bp_playground_register_member_types();
            
            // Create XProfile structure
            $result = bp_playground_create_xprofile_structure();
            
            $results = array_merge($results, $result);
            $this->log(sprintf(
                "Created %d groups, %d fields",
                $result['groups'],
                $result['fields']
            ));
        } else if (!empty($config['xprofile_structure'])) {
            // Custom structure provided
            $xprofile = bp_playground_get_module('xprofile');
            if ($xprofile) {
                $result = $xprofile->generate($config['xprofile_structure']);
                if (!is_wp_error($result)) {
                    $results = $result;
                }
            }
        }
        
        return [
            'success' => true,
            'data' => $results
        ];
    }
    
    /**
     * PHASE 3: Create users
     */
    private function phase_users($config) {
        $this->log("Creating users...");
        
        $user_count = $config['users']['count'] ?? 100;
        $this->log("Creating $user_count users");
        
        $users_module = bp_playground_get_module('users');
        if (!$users_module) {
            return [
                'success' => false,
                'errors' => ['Users module not available']
            ];
        }
        
        $result = $users_module->generate([
            'count' => $user_count,
            'with_avatar' => $config['users']['with_avatar'] ?? false,
            'with_cover_image' => $config['users']['with_cover_image'] ?? false,
            'batch_size' => 50
        ]);
        
        if (is_wp_error($result)) {
            return [
                'success' => false,
                'errors' => [$result->get_error_message()]
            ];
        }
        
        $this->log(sprintf("Created %d users", $result['created']));
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * PHASE 4: Populate profile data for users
     */
    private function phase_profile_data($config) {
        $this->log("Populating user profile data...");
        
        if (!bp_is_active('xprofile')) {
            return ['success' => true, 'data' => ['skipped' => true]];
        }
        
        // Get all users
        $users = get_users(['fields' => 'ID']);
        $total_users = count($users);
        
        $this->log("Populating profiles for $total_users users");
        
        // Load XProfile functions if not already loaded
        $xprofile_functions = BP_PLAYGROUND_PLUGIN_DIR . 'includes/functions-bp-playground-xprofile.php';
        if (file_exists($xprofile_functions)) {
            require_once $xprofile_functions;
        }
        
        $populated = 0;
        
        // Determine completeness distribution
        $completeness_levels = [
            'complete' => 0.60,
            'mostly_complete' => 0.25,
            'partial' => 0.10,
            'minimal' => 0.05
        ];
        
        // Populate all users at once with XProfile data and member types
        $assign_member_types = !empty($config['member_types']);
        $result = bp_playground_populate_xprofile($users, $assign_member_types);
        
        if ($result['success']) {
            $populated = $result['users_populated'];
            
        }
        
        $this->log("Profile data populated for $populated users");
        
        return [
            'success' => true,
            'data' => [
                'users_populated' => $populated,
                'total_users' => $total_users
            ]
        ];
    }
    
    /**
     * PHASE 5: Create groups
     */
    private function phase_groups($config) {
        $this->log("Creating groups...");
        
        if (!bp_is_active('groups')) {
            return ['success' => true, 'data' => ['skipped' => true]];
        }
        
        $groups_module = bp_playground_get_module('groups');
        if (!$groups_module) {
            return [
                'success' => false,
                'errors' => ['Groups module not available']
            ];
        }
        
        $group_count = $config['groups']['count'] ?? 20;
        $result = $groups_module->generate([
            'count' => $group_count,
            'min_members' => $config['groups']['min_members'] ?? 5,
            'max_members' => $config['groups']['max_members'] ?? 50,
            'with_forums' => $config['groups']['with_forums'] ?? false
        ]);
        
        if (is_wp_error($result)) {
            return [
                'success' => false,
                'errors' => [$result->get_error_message()]
            ];
        }
        
        $this->log(sprintf("Created %d groups", $result['created']));
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * PHASE 6: Create friendships
     */
    private function phase_friendships($config) {
        $this->log("Creating friendships...");
        
        if (!bp_is_active('friends')) {
            return ['success' => true, 'data' => ['skipped' => true]];
        }
        
        $friends_module = bp_playground_get_module('friends');
        if (!$friends_module) {
            return [
                'success' => false,
                'errors' => ['Friends module not available']
            ];
        }
        
        $result = $friends_module->generate([
            'connections_per_user' => $config['friendships']['per_user'] ?? 10,
            'acceptance_rate' => $config['friendships']['acceptance_rate'] ?? 0.8
        ]);
        
        if (is_wp_error($result)) {
            return [
                'success' => false,
                'errors' => [$result->get_error_message()]
            ];
        }
        
        $this->log(sprintf("Created %d friendships", $result['created']));
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * PHASE 7: Create messages
     */
    private function phase_messages($config) {
        $this->log("Creating messages...");
        
        if (!bp_is_active('messages')) {
            return ['success' => true, 'data' => ['skipped' => true]];
        }
        
        $messages_module = bp_playground_get_module('messages');
        if (!$messages_module) {
            return [
                'success' => false,
                'errors' => ['Messages module not available']
            ];
        }
        
        $result = $messages_module->generate([
            'threads' => $config['messages']['threads'] ?? 100,
            'min_messages' => $config['messages']['min_messages'] ?? 1,
            'max_messages' => $config['messages']['max_messages'] ?? 10
        ]);
        
        if (is_wp_error($result)) {
            return [
                'success' => false,
                'errors' => [$result->get_error_message()]
            ];
        }
        
        $this->log(sprintf("Created %d message threads", $result['threads_created']));
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * PHASE 8: Create activities
     */
    private function phase_activities($config) {
        $this->log("Creating activities...");
        
        if (!bp_is_active('activity')) {
            return ['success' => true, 'data' => ['skipped' => true]];
        }
        
        $activities_module = bp_playground_get_module('activities');
        if (!$activities_module) {
            return [
                'success' => false,
                'errors' => ['Activities module not available']
            ];
        }
        
        $result = $activities_module->generate([
            'count' => $config['activities']['count'] ?? 500,
            'with_comments' => $config['activities']['with_comments'] ?? true,
            'with_favorites' => $config['activities']['with_favorites'] ?? true
        ]);
        
        if (is_wp_error($result)) {
            return [
                'success' => false,
                'errors' => [$result->get_error_message()]
            ];
        }
        
        $this->log(sprintf("Created %d activities", $result['created']));
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * PHASE 9: Create forums (if bbPress is active)
     */
    private function phase_forums($config) {
        $this->log("Creating forums...");
        
        if (!class_exists('bbPress')) {
            return ['success' => true, 'data' => ['skipped' => true]];
        }
        
        $bbpress_module = bp_playground_get_module('bbpress');
        if (!$bbpress_module) {
            return [
                'success' => false,
                'errors' => ['bbPress module not available']
            ];
        }
        
        $result = $bbpress_module->generate([
            'forums' => $config['forums']['count'] ?? 5,
            'topics_per_forum' => $config['forums']['topics_per_forum'] ?? 10,
            'replies_per_topic' => $config['forums']['replies_per_topic'] ?? 5
        ]);
        
        if (is_wp_error($result)) {
            return [
                'success' => false,
                'errors' => [$result->get_error_message()]
            ];
        }
        
        $this->log(sprintf("Created %d forums", $result['forums_created']));
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
    
    /**
     * PHASE 10: Finalize
     */
    private function phase_finalize($config) {
        $this->log("Finalizing scenario...");
        
        // Clear caches
        wp_cache_flush();
        
        // Update counts
        if (function_exists('bp_core_reset_cache_incrementor')) {
            bp_core_reset_cache_incrementor('bp');
        }
        
        return [
            'success' => true,
            'data' => [
                'cache_cleared' => true,
                'execution_time' => $this->get_total_execution_time()
            ]
        ];
    }
    
    /**
     * Check if phase should be executed
     */
    private function should_execute_phase($phase, $config) {
        // Check if phase is explicitly disabled
        if (isset($config['skip_phases']) && in_array($phase, $config['skip_phases'])) {
            return false;
        }
        
        // Check component-specific phases
        switch ($phase) {
            case 'xprofile':
            case 'profile_data':
                return bp_is_active('xprofile');
                
            case 'groups':
                return bp_is_active('groups') && !empty($config['groups']['count']);
                
            case 'friendships':
                return bp_is_active('friends') && !empty($config['friendships']);
                
            case 'messages':
                return bp_is_active('messages') && !empty($config['messages']);
                
            case 'activities':
                return bp_is_active('activity') && !empty($config['activities']);
                
            case 'forums':
                return class_exists('bbPress') && !empty($config['forums']);
        }
        
        return true;
    }
    
    /**
     * Check if phase is critical (failure should stop execution)
     */
    private function is_critical_phase($phase) {
        return in_array($phase, ['prepare', 'xprofile', 'users']);
    }
    
    /**
     * Set up member types
     */
    private function setup_member_types($member_types) {
        foreach ($member_types as $type_name => $type_config) {
            bp_register_member_type($type_name, $type_config);
        }
    }
    
    /**
     * Get total execution time
     */
    private function get_total_execution_time() {
        $total = 0;
        foreach ($this->state as $phase_state) {
            if ($phase_state['started_at'] && $phase_state['completed_at']) {
                $total += ($phase_state['completed_at'] - $phase_state['started_at']);
            }
        }
        return $total;
    }
    
    /**
     * Log a message
     */
    private function log($message, $level = 'info') {
        $this->log[] = [
            'time' => microtime(true),
            'level' => $level,
            'message' => $message
        ];
        
        // Output to CLI if available
        if (defined('WP_CLI') && WP_CLI) {
            if ($level === 'error') {
                WP_CLI::warning($message);
            } else {
                WP_CLI::log($message);
            }
        }
    }
    
    /**
     * Get execution state
     */
    public function get_state() {
        return $this->state;
    }
    
    /**
     * Get execution log
     */
    public function get_log() {
        return $this->log;
    }
}