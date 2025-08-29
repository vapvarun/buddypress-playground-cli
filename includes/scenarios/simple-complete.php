<?php
/**
 * Simple Complete Scenario
 * Uses existing modules in the correct sequence
 */

function bp_playground_scenario_simple_complete($args = array()) {
    // Default configuration
    $defaults = array(
        'users' => 50,
        'cleanup_first' => true,
        'verbose' => true
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $results = array(
        'success' => true,
        'steps' => array(),
        'errors' => array()
    );
    
    // Helper function for output
    $log = function($message) use ($args) {
        if ($args['verbose']) {
            WP_CLI::log($message);
        }
    };
    
    $log("╔════════════════════════════════════════════════════════╗");
    $log("║     BuddyPress Complete Scenario                       ║");
    $log("╚════════════════════════════════════════════════════════╝");
    $log("");
    
    try {
        // Step 1: Create Users
        $log("Step 1: Creating Users ({$args['users']} users)");
        $log("──────────────────────────────────");
        
        $users_module = bp_playground_get_module('users');
        $users_result = $users_module->generate(array(
            'count' => $args['users'],
            'role_distribution' => 'natural',
            'name_style' => 'realistic',
            'batch_size' => 10
        ));
        
        $results['steps']['users'] = $users_result;
        $log("✓ Created {$users_result['users_created']} users");
        $log("");
        
        // Step 2: XProfile Setup and Population
        $log("Step 2: XProfile Setup");
        $log("──────────────────────");
        
        // Load XProfile functions
        require_once BP_PLAYGROUND_PLUGIN_DIR . 'includes/functions-bp-playground-xprofile.php';
        
        // Register member types
        bp_playground_register_member_types();
        $log("✓ Registered member types");
        
        // Create XProfile structure
        $xprofile_structure = bp_playground_create_xprofile_structure();
        $log("✓ Created XProfile structure: {$xprofile_structure['groups']} groups, {$xprofile_structure['fields']} fields");
        
        // Populate XProfile for all users
        $all_users = get_users(array('fields' => 'ID'));
        $xprofile_result = bp_playground_populate_xprofile($all_users, true);
        $results['steps']['xprofile'] = $xprofile_result;
        $log("✓ Populated XProfile for {$xprofile_result['users_populated']} users with member types");
        $log("");
        
        // Step 3: Friends
        $log("Step 3: Creating Friendships");
        $log("─────────────────────────────");
        
        $friends_module = bp_playground_get_module('friends');
        $friends_result = $friends_module->generate(array(
            'friendship_patterns' => true,
            'mutual_friendships' => true,
            'friendship_distribution' => 'natural',
            'acceptance_rate' => 85,
            'batch_size' => 50
        ));
        
        $results['steps']['friends'] = $friends_result;
        $log("✓ Created {$friends_result['friendships_created']} friendships");
        $log("");
        
        // Step 4: Groups (with members)
        $log("Step 4: Creating Groups");
        $log("────────────────────────");
        
        $groups_module = bp_playground_get_module('groups');
        $groups_result = $groups_module->generate(array(
            'count' => 15,
            'types' => 'mixed',
            'membership_patterns' => true,
            'batch_size' => 5
        ));
        
        $results['steps']['groups'] = $groups_result;
        $log("✓ Created {$groups_result['groups_created']} groups with memberships");
        $log("");
        
        // Step 5: Messages
        $log("Step 5: Creating Messages");
        $log("──────────────────────────");
        
        $messages_module = bp_playground_get_module('messages');
        $messages_result = $messages_module->generate(array(
            'count' => 100,
            'thread_variations' => true,
            'conversation_depth' => 'mixed',
            'timeframe_days' => 30,
            'batch_size' => 10
        ));
        
        $results['steps']['messages'] = $messages_result;
        $log("✓ Created {$messages_result['threads_created']} message threads");
        $log("");
        
        // Step 6: Activities (after groups and friends exist)
        $log("Step 6: Creating Activities");
        $log("────────────────────────────");
        
        $activities_module = bp_playground_get_module('activities');
        $activities_result = $activities_module->generate(array(
            'count' => 200,
            'with_comments' => true,
            'with_favorites' => true,
            'engagement_patterns' => true,
            'realistic_timing' => true,
            'timeframe_days' => 30,
            'batch_size' => 20
        ));
        
        $results['steps']['activities'] = $activities_result;
        $log("✓ Created {$activities_result['activities_created']} activities");
        $log("✓ Added {$activities_result['comments_created']} comments");
        $log("✓ Added {$activities_result['favorites_created']} favorites");
        $log("");
        
        // Step 7: BBPress Forums (optional)
        if (class_exists('bbPress')) {
            $log("Step 7: Creating Forums");
            $log("────────────────────────");
            
            $bbpress_module = bp_playground_get_module('bbpress');
            $bbpress_result = $bbpress_module->generate(array(
                'forums' => 5,
                'topics_per_forum' => 10,
                'replies_per_topic' => 5,
                'with_tags' => true,
                'batch_size' => 10
            ));
            
            $results['steps']['bbpress'] = $bbpress_result;
            $log("✓ Created {$bbpress_result['forums_created']} forums");
            $log("✓ Created {$bbpress_result['topics_created']} topics");
            $log("✓ Created {$bbpress_result['replies_created']} replies");
            $log("");
        }
        
        // Summary
        $log("╔════════════════════════════════════════════════════════╗");
        $log("║                    SCENARIO COMPLETE                    ║");
        $log("╚════════════════════════════════════════════════════════╝");
        $log("");
        $log("✅ Complete community has been successfully created!");
        
    } catch (Exception $e) {
        $results['success'] = false;
        $results['errors'][] = $e->getMessage();
        $log("❌ Error: " . $e->getMessage());
    }
    
    return $results;
}

// Register as WP-CLI command
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('bp playground scenario:simple', function($args, $assoc_args) {
        $defaults = array(
            'users' => 50,
            'cleanup' => false,
            'verbose' => true
        );
        
        $params = wp_parse_args($assoc_args, $defaults);
        $params['cleanup_first'] = $params['cleanup'];
        
        // Clean if requested
        if ($params['cleanup_first']) {
            WP_CLI::confirm("This will DELETE all existing BuddyPress data. Continue?");
            
            WP_CLI::log("Cleaning existing data...");
            
            // Clean in reverse order
            global $wpdb;
            
            // BBPress
            if (class_exists('bbPress')) {
                $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type IN ('forum', 'topic', 'reply')");
                WP_CLI::log("✓ Cleaned BBPress content");
            }
            
            // Activities
            if (bp_is_active('activity')) {
                BP_Activity_Activity::delete(array('type' => null));
                WP_CLI::log("✓ Cleaned activities");
            }
            
            // Messages
            if (bp_is_active('messages')) {
                $wpdb->query("TRUNCATE TABLE {$wpdb->base_prefix}bp_messages_messages");
                $wpdb->query("TRUNCATE TABLE {$wpdb->base_prefix}bp_messages_recipients");
                $wpdb->query("TRUNCATE TABLE {$wpdb->base_prefix}bp_messages_meta");
                WP_CLI::log("✓ Cleaned messages");
            }
            
            // Friends
            if (bp_is_active('friends')) {
                $wpdb->query("TRUNCATE TABLE {$wpdb->base_prefix}bp_friends");
                WP_CLI::log("✓ Cleaned friendships");
            }
            
            // Groups
            if (bp_is_active('groups')) {
                $wpdb->query("DELETE FROM {$wpdb->base_prefix}bp_groups");
                $wpdb->query("DELETE FROM {$wpdb->base_prefix}bp_groups_members");
                $wpdb->query("DELETE FROM {$wpdb->base_prefix}bp_groups_groupmeta");
                WP_CLI::log("✓ Cleaned groups");
            }
            
            // Users (except admin)
            $users = get_users(array('exclude' => array(1)));
            foreach ($users as $user) {
                wp_delete_user($user->ID);
            }
            WP_CLI::log("✓ Cleaned users (except admin)");
            WP_CLI::log("");
        }
        
        $result = bp_playground_scenario_simple_complete($params);
        
        if ($result['success']) {
            WP_CLI::success('Complete scenario executed successfully!');
        } else {
            WP_CLI::error('Scenario failed: ' . implode(', ', $result['errors']));
        }
    });
}