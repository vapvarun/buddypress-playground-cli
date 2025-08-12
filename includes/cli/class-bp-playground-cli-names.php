<?php
/**
 * BuddyPress Playground CLI Names Commands
 *
 * @package BuddyPress_Playground
 * @subpackage CLI
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manage user names and nicknames
 *
 * @since 1.0.0
 */
class BP_Playground_CLI_Names extends WP_CLI_Command {
    
    /**
     * Update all user names
     *
     * ## OPTIONS
     *
     * [--batch-size=<number>]
     * : Number of users to process at once
     * default: 50
     *
     * [--skip-admin]
     * : Skip the admin user (ID: 1)
     * default: true
     *
     * ## EXAMPLES
     *
     *     # Update all user names
     *     $ wp bp playground names update
     *
     *     # Update with custom batch size
     *     $ wp bp playground names update --batch-size=100
     *
     * @when after_wp_load
     */
    public function update($args, $assoc_args) {
        $batch_size = isset($assoc_args['batch-size']) ? intval($assoc_args['batch-size']) : 50;
        $skip_admin = isset($assoc_args['skip-admin']) ? true : false;
        
        WP_CLI::log('Starting name update process...');
        
        // Get total users
        $user_query_args = ['count_total' => true];
        if ($skip_admin) {
            $user_query_args['exclude'] = [1];
        }
        
        $user_count = count_users();
        $total_users = $user_count['total_users'];
        if ($skip_admin && $total_users > 0) {
            $total_users--;
        }
        
        WP_CLI::log("Found {$total_users} users to process");
        
        // Initialize name handler
        $name_handler = new BP_Playground_Name_Handler();
        
        // Process users in batches
        $offset = 0;
        $processed = 0;
        $updated = 0;
        
        $progress = \WP_CLI\Utils\make_progress_bar('Updating names', $total_users);
        
        while ($offset < $total_users) {
            $query_args = [
                'number' => $batch_size,
                'offset' => $offset,
                'orderby' => 'ID',
                'order' => 'ASC'
            ];
            
            if ($skip_admin) {
                $query_args['exclude'] = [1];
            }
            
            $users = get_users($query_args);
            
            foreach ($users as $user) {
                $processed++;
                
                // Check if user needs update
                $first_name = xprofile_get_field_data($this->get_field_id('First Name'), $user->ID);
                $last_name = xprofile_get_field_data($this->get_field_id('Last Name'), $user->ID);
                
                // Generate names if missing or looks like placeholder
                if (empty($first_name) || empty($last_name) || 
                    strpos($first_name, '{') !== false || strpos($last_name, '{') !== false) {
                    
                    $name_handler->generate_user_names($user->ID);
                    $updated++;
                }
                
                $progress->tick();
            }
            
            $offset += $batch_size;
        }
        
        $progress->finish();
        
        WP_CLI::success("Name update complete! Processed: {$processed}, Updated: {$updated}");
    }
    
    /**
     * Fix nicenames for all users
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format (friendly or username)
     * default: friendly
     *
     * ## EXAMPLES
     *
     *     # Fix all nicenames to friendly format
     *     $ wp bp playground names fix-nicenames
     *
     *     # Use username format
     *     $ wp bp playground names fix-nicenames --format=username
     *
     * @when after_wp_load
     */
    public function fix_nicenames($args, $assoc_args) {
        global $wpdb;
        
        $format = isset($assoc_args['format']) ? $assoc_args['format'] : 'friendly';
        
        WP_CLI::log("Fixing nicenames with format: {$format}");
        
        $users = get_users(['orderby' => 'ID', 'order' => 'ASC']);
        $total = count($users);
        $updated = 0;
        
        $progress = \WP_CLI\Utils\make_progress_bar('Updating nicenames', $total);
        
        $used_nicenames = [];
        
        foreach ($users as $user) {
            // Skip admin
            if ($user->ID == 1) {
                $progress->tick();
                continue;
            }
            
            if ($format === 'friendly') {
                // Get names from XProfile
                $first_name = xprofile_get_field_data($this->get_field_id('First Name'), $user->ID);
                $last_name = xprofile_get_field_data($this->get_field_id('Last Name'), $user->ID);
                
                if (!empty($first_name) && !empty($last_name)) {
                    $base_nicename = sanitize_title(strtolower($first_name . '-' . $last_name));
                } else {
                    $base_nicename = sanitize_title($user->user_login);
                }
            } else {
                $base_nicename = sanitize_title($user->user_login);
            }
            
            // Ensure uniqueness
            $nicename = $base_nicename;
            $counter = 1;
            
            while (isset($used_nicenames[$nicename])) {
                $counter++;
                $nicename = $base_nicename . '-' . $counter;
            }
            
            $used_nicenames[$nicename] = true;
            
            // Update if different
            if ($nicename !== $user->user_nicename) {
                $wpdb->update(
                    $wpdb->users,
                    ['user_nicename' => $nicename],
                    ['ID' => $user->ID]
                );
                $updated++;
            }
            
            $progress->tick();
        }
        
        $progress->finish();
        
        WP_CLI::success("Nicename update complete! Updated: {$updated} users");
    }
    
    /**
     * Show name statistics
     *
     * ## EXAMPLES
     *
     *     # Show name statistics
     *     $ wp bp playground names stats
     *
     * @when after_wp_load
     */
    public function stats($args, $assoc_args) {
        global $wpdb, $bp;
        
        WP_CLI::log("Gathering name statistics...\n");
        
        $total_users = count_users()['total_users'];
        
        // Get field IDs
        $name_field_id = 1;
        $first_name_field_id = $this->get_field_id('First Name');
        $last_name_field_id = $this->get_field_id('Last Name');
        $nickname_field_id = $this->get_field_id('Nickname');
        
        // Count populated fields
        $with_name = $wpdb->get_var("SELECT COUNT(*) FROM {$bp->profile->table_name_data} WHERE field_id = $name_field_id");
        $with_first = $wpdb->get_var("SELECT COUNT(*) FROM {$bp->profile->table_name_data} WHERE field_id = $first_name_field_id");
        $with_last = $wpdb->get_var("SELECT COUNT(*) FROM {$bp->profile->table_name_data} WHERE field_id = $last_name_field_id");
        $with_nick = $wpdb->get_var("SELECT COUNT(*) FROM {$bp->profile->table_name_data} WHERE field_id = $nickname_field_id");
        
        // Count unique values
        $unique_names = $wpdb->get_var("SELECT COUNT(DISTINCT value) FROM {$bp->profile->table_name_data} WHERE field_id = $name_field_id");
        $unique_nicks = $wpdb->get_var("SELECT COUNT(DISTINCT value) FROM {$bp->profile->table_name_data} WHERE field_id = $nickname_field_id");
        $unique_nicenames = $wpdb->get_var("SELECT COUNT(DISTINCT user_nicename) FROM {$wpdb->users}");
        
        // Display statistics
        WP_CLI::log("=== Name Field Statistics ===");
        WP_CLI::log("Total Users: {$total_users}");
        WP_CLI::log("");
        WP_CLI::log("Field Coverage:");
        WP_CLI::log("  Name field:       {$with_name} users (" . round(($with_name/$total_users)*100, 1) . "%)");
        WP_CLI::log("  First Name:       {$with_first} users (" . round(($with_first/$total_users)*100, 1) . "%)");
        WP_CLI::log("  Last Name:        {$with_last} users (" . round(($with_last/$total_users)*100, 1) . "%)");
        WP_CLI::log("  Nickname:         {$with_nick} users (" . round(($with_nick/$total_users)*100, 1) . "%)");
        WP_CLI::log("");
        WP_CLI::log("Uniqueness:");
        WP_CLI::log("  Unique full names:    {$unique_names}");
        WP_CLI::log("  Unique nicknames:     {$unique_nicks}");
        WP_CLI::log("  Unique nicenames:     {$unique_nicenames}");
        
        // Check for problematic names
        $bad_display = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->users}
            WHERE display_name LIKE '%{%' 
            OR LENGTH(display_name) > 50
        ");
        
        if ($bad_display > 0) {
            WP_CLI::warning("Found {$bad_display} users with problematic display names");
        } else {
            WP_CLI::success("All display names are properly formatted!");
        }
    }
    
    /**
     * Get field ID by name
     */
    private function get_field_id($field_name) {
        global $wpdb, $bp;
        
        if (!bp_is_active('xprofile')) {
            return false;
        }
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$bp->profile->table_name_fields} 
             WHERE name = %s AND parent_id = 0",
            $field_name
        ));
    }
}