<?php
/**
 * BuddyPress Playground Name Handler
 * 
 * Handles automatic generation and synchronization of user names across
 * WordPress and BuddyPress XProfile fields
 */

class BP_Playground_Name_Handler {
    
    /**
     * Processing flag to prevent infinite loops
     */
    private static $processing = [];
    
    /**
     * Name pools for generation
     */
    private $first_names_male = [
        'James', 'John', 'Robert', 'Michael', 'William', 'David', 'Richard', 'Joseph', 'Thomas', 'Charles',
        'Christopher', 'Daniel', 'Matthew', 'Anthony', 'Donald', 'Mark', 'Paul', 'Steven', 'Andrew', 'Kenneth',
        'Joshua', 'Kevin', 'Brian', 'George', 'Edward', 'Ronald', 'Timothy', 'Jason', 'Jeffrey', 'Ryan',
        'Jacob', 'Gary', 'Nicholas', 'Eric', 'Jonathan', 'Stephen', 'Larry', 'Justin', 'Scott', 'Brandon'
    ];
    
    private $first_names_female = [
        'Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth', 'Barbara', 'Susan', 'Jessica', 'Sarah', 'Karen',
        'Nancy', 'Betty', 'Helen', 'Sandra', 'Donna', 'Carol', 'Ruth', 'Sharon', 'Michelle', 'Laura',
        'Kimberly', 'Deborah', 'Dorothy', 'Lisa', 'Amy', 'Angela', 'Ashley', 'Brenda', 'Emma', 'Nicole',
        'Samantha', 'Katherine', 'Christine', 'Debra', 'Rachel', 'Janet', 'Catherine', 'Maria', 'Olivia', 'Christina'
    ];
    
    private $last_names = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
        'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
        'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
        'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores',
        'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts'
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Hook into user creation
        add_action('user_register', [$this, 'handle_user_registration'], 10, 1);
        add_action('bp_core_signup_user', [$this, 'handle_bp_signup'], 10, 5);
        
        // Hook into profile updates
        add_action('xprofile_data_after_save', [$this, 'handle_xprofile_update'], 10, 1);
        add_action('profile_update', [$this, 'handle_profile_update'], 10, 2);
        
        // Filter for display name
        add_filter('pre_user_display_name', [$this, 'filter_display_name'], 10, 1);
        
        // Filter for nicename generation
        add_filter('pre_user_nicename', [$this, 'filter_nicename'], 10, 1);
    }
    
    /**
     * Handle user registration
     */
    public function handle_user_registration($user_id) {
        // Generate and set names for new user
        $this->generate_user_names($user_id);
    }
    
    /**
     * Handle BuddyPress signup
     */
    public function handle_bp_signup($user_id, $user_login, $user_password, $user_email, $usermeta) {
        // Generate names after BuddyPress signup
        $this->generate_user_names($user_id);
    }
    
    /**
     * Generate all name fields for a user
     */
    public function generate_user_names($user_id, $first_name = null, $last_name = null) {
        global $wpdb, $bp;
        
        // Prevent infinite loops
        if (isset(self::$processing[$user_id])) {
            return;
        }
        self::$processing[$user_id] = true;
        
        // Skip if BuddyPress is not active
        if (!function_exists('bp_is_active') || !bp_is_active('xprofile')) {
            unset(self::$processing[$user_id]);
            return;
        }
        
        // Get field IDs
        $name_field_id = 1; // Default BuddyPress Name field
        $first_name_field_id = $this->get_field_id_by_name('First Name');
        $last_name_field_id = $this->get_field_id_by_name('Last Name');
        $nickname_field_id = $this->get_field_id_by_name('Nickname');
        
        // Generate names if not provided
        if (empty($first_name) || empty($last_name)) {
            $names = $this->generate_random_names($user_id);
            $first_name = $first_name ?: $names['first'];
            $last_name = $last_name ?: $names['last'];
        }
        
        // Create display name
        $display_name = $first_name . ' ' . $last_name;
        
        // Generate unique nickname
        $nickname = $this->generate_unique_nickname($user_id, $first_name, $last_name);
        
        // Update XProfile fields
        if ($first_name_field_id) {
            xprofile_set_field_data($first_name_field_id, $user_id, $first_name);
        }
        
        if ($last_name_field_id) {
            xprofile_set_field_data($last_name_field_id, $user_id, $last_name);
        }
        
        if ($name_field_id) {
            xprofile_set_field_data($name_field_id, $user_id, $display_name);
        }
        
        if ($nickname_field_id) {
            xprofile_set_field_data($nickname_field_id, $user_id, $nickname);
        }
        
        // Update WordPress user meta
        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);
        update_user_meta($user_id, 'nickname', $nickname);
        
        // Update WordPress display_name
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $display_name
        ]);
        
        // Generate and update nicename
        $nicename = $this->generate_unique_nicename($user_id, $first_name, $last_name);
        if (!empty($nicename)) {
            $wpdb->update(
                $wpdb->users,
                ['user_nicename' => $nicename],
                ['ID' => $user_id]
            );
        }
        
        // Clear processing flag
        unset(self::$processing[$user_id]);
    }
    
    /**
     * Generate random first and last names
     */
    private function generate_random_names($user_id) {
        // Use user ID for consistent but varied selection
        $is_female = ($user_id % 2 == 0);
        
        if ($is_female) {
            $first_name = $this->first_names_female[$user_id % count($this->first_names_female)];
        } else {
            $first_name = $this->first_names_male[$user_id % count($this->first_names_male)];
        }
        
        // Add variation to last name selection
        $last_index = $user_id % count($this->last_names);
        if ($user_id % 7 == 0) {
            $last_index = ($user_id + 13) % count($this->last_names);
        }
        $last_name = $this->last_names[$last_index];
        
        return [
            'first' => $first_name,
            'last' => $last_name
        ];
    }
    
    /**
     * Generate unique nickname
     */
    private function generate_unique_nickname($user_id, $first_name, $last_name) {
        global $wpdb, $bp;
        
        $user = get_userdata($user_id);
        if (!$user) {
            return strtolower($first_name);
        }
        
        // Get existing nicknames to ensure uniqueness
        $existing_nicknames = [];
        if (bp_is_active('xprofile')) {
            $nickname_field_id = $this->get_field_id_by_name('Nickname');
            if ($nickname_field_id) {
                $existing = $wpdb->get_col("
                    SELECT value FROM {$bp->profile->table_name_data} 
                    WHERE field_id = $nickname_field_id AND user_id != $user_id
                ");
                foreach ($existing as $nick) {
                    $existing_nicknames[strtolower($nick)] = true;
                }
            }
        }
        
        // Generate nickname options
        $options = [];
        
        // Based on username
        $username_parts = preg_split('/[_\-\.]/', $user->user_login);
        if (!empty($username_parts[0]) && !is_numeric($username_parts[0])) {
            $options[] = $username_parts[0];
        }
        
        // Based on first name
        $options[] = strtolower($first_name);
        $options[] = strtolower(substr($first_name, 0, 3)) . rand(10, 999);
        $options[] = strtolower($first_name) . rand(1, 99);
        
        // Combinations
        $options[] = strtolower(substr($first_name, 0, 1)) . strtolower($last_name);
        $options[] = strtolower($first_name) . '_' . strtolower(substr($last_name, 0, 3));
        $options[] = substr($first_name, 0, 1) . '.' . $last_name;
        
        // Find unique option
        foreach ($options as $option) {
            $option = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $option);
            if (strlen($option) >= 3 && !isset($existing_nicknames[strtolower($option)])) {
                return $option;
            }
        }
        
        // Fallback with unique suffix
        return strtolower(substr($first_name, 0, 3)) . '_' . substr(md5($user_id . time()), 0, 5);
    }
    
    /**
     * Generate unique nicename (URL-friendly)
     */
    private function generate_unique_nicename($user_id, $first_name, $last_name) {
        global $wpdb;
        
        // Create base nicename from names
        $base_nicename = sanitize_title(strtolower($first_name . '-' . $last_name));
        
        // Check for existing nicenames
        $existing = $wpdb->get_col($wpdb->prepare(
            "SELECT user_nicename FROM {$wpdb->users} WHERE ID != %d",
            $user_id
        ));
        
        $existing_nicenames = array_flip($existing);
        
        // Find unique nicename
        $nicename = $base_nicename;
        $counter = 1;
        
        while (isset($existing_nicenames[$nicename])) {
            $counter++;
            $nicename = $base_nicename . '-' . $counter;
        }
        
        return $nicename;
    }
    
    /**
     * Handle XProfile field updates
     */
    public function handle_xprofile_update($data) {
        // Prevent processing if already handling this user
        if (isset(self::$processing[$data->user_id])) {
            return;
        }
        
        // Check if First Name or Last Name was updated
        $first_name_field_id = $this->get_field_id_by_name('First Name');
        $last_name_field_id = $this->get_field_id_by_name('Last Name');
        
        if ($data->field_id == $first_name_field_id || $data->field_id == $last_name_field_id) {
            // Get both names
            $first_name = xprofile_get_field_data($first_name_field_id, $data->user_id);
            $last_name = xprofile_get_field_data($last_name_field_id, $data->user_id);
            
            if (!empty($first_name) && !empty($last_name)) {
                // Update display name and nicename
                $display_name = $first_name . ' ' . $last_name;
                
                // Update Name field
                xprofile_set_field_data(1, $data->user_id, $display_name);
                
                // Update WordPress display_name
                wp_update_user([
                    'ID' => $data->user_id,
                    'display_name' => $display_name
                ]);
                
                // Update nicename
                $nicename = $this->generate_unique_nicename($data->user_id, $first_name, $last_name);
                if (!empty($nicename)) {
                    global $wpdb;
                    $wpdb->update(
                        $wpdb->users,
                        ['user_nicename' => $nicename],
                        ['ID' => $data->user_id]
                    );
                }
            }
        }
    }
    
    /**
     * Handle WordPress profile updates
     */
    public function handle_profile_update($user_id, $old_user_data) {
        // Sync WordPress names to XProfile
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }
        
        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name = get_user_meta($user_id, 'last_name', true);
        
        if (!empty($first_name) && !empty($last_name)) {
            // Update XProfile fields
            $first_name_field_id = $this->get_field_id_by_name('First Name');
            $last_name_field_id = $this->get_field_id_by_name('Last Name');
            
            if ($first_name_field_id) {
                xprofile_set_field_data($first_name_field_id, $user_id, $first_name);
            }
            
            if ($last_name_field_id) {
                xprofile_set_field_data($last_name_field_id, $user_id, $last_name);
            }
            
            // Update Name field
            $display_name = $first_name . ' ' . $last_name;
            xprofile_set_field_data(1, $user_id, $display_name);
        }
    }
    
    /**
     * Filter display name before save
     */
    public function filter_display_name($display_name) {
        // If display name is empty or looks like a username, try to generate from names
        if (empty($display_name) || !strpos($display_name, ' ')) {
            $user_id = get_current_user_id();
            if ($user_id) {
                $first_name = get_user_meta($user_id, 'first_name', true);
                $last_name = get_user_meta($user_id, 'last_name', true);
                
                if (!empty($first_name) && !empty($last_name)) {
                    return $first_name . ' ' . $last_name;
                }
            }
        }
        
        return $display_name;
    }
    
    /**
     * Filter nicename before save
     */
    public function filter_nicename($nicename) {
        // Ensure nicename is URL-friendly
        return sanitize_title($nicename);
    }
    
    /**
     * Get XProfile field ID by name
     */
    private function get_field_id_by_name($field_name) {
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
    
    /**
     * Batch update existing users
     */
    public function batch_update_existing_users() {
        $users = get_users(['orderby' => 'ID', 'order' => 'ASC']);
        $updated = 0;
        
        foreach ($users as $user) {
            // Skip admin
            if ($user->ID == 1) {
                continue;
            }
            
            // Check if user needs update
            $first_name = xprofile_get_field_data($this->get_field_id_by_name('First Name'), $user->ID);
            $last_name = xprofile_get_field_data($this->get_field_id_by_name('Last Name'), $user->ID);
            
            if (empty($first_name) || empty($last_name)) {
                $this->generate_user_names($user->ID);
                $updated++;
            }
        }
        
        return $updated;
    }
}