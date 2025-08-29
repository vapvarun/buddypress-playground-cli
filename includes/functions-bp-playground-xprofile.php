<?php
/**
 * BuddyPress Playground XProfile Functions
 * 
 * Optimized XProfile field generation and population
 * Generates values on-the-fly for efficient memory usage
 * Also handles member types creation and assignment
 * 
 * @package BuddyPress_Playground
 * @since 1.0.0
 */

/**
 * Register BuddyPress member types on bp_init
 * 
 * @since 1.0.0
 */
function bp_playground_register_member_types() {
    // Student
    bp_register_member_type('student', array(
        'labels' => array(
            'name' => 'Students',
            'singular_name' => 'Student'
        ),
        'has_directory' => 'students',
        'show_in_list' => true
    ));
    
    // Teacher
    bp_register_member_type('teacher', array(
        'labels' => array(
            'name' => 'Teachers',
            'singular_name' => 'Teacher'
        ),
        'has_directory' => 'teachers',
        'show_in_list' => true
    ));
    
    // Professional
    bp_register_member_type('professional', array(
        'labels' => array(
            'name' => 'Professionals',
            'singular_name' => 'Professional'
        ),
        'has_directory' => 'professionals',
        'show_in_list' => true
    ));
    
    // Alumni
    bp_register_member_type('alumni', array(
        'labels' => array(
            'name' => 'Alumni',
            'singular_name' => 'Alumni'
        ),
        'has_directory' => 'alumni',
        'show_in_list' => true
    ));
    
    // Moderator
    bp_register_member_type('moderator', array(
        'labels' => array(
            'name' => 'Moderators',
            'singular_name' => 'Moderator'
        ),
        'has_directory' => false,
        'show_in_list' => false
    ));
}

// Hook member types registration to bp_init
add_action('bp_init', 'bp_playground_register_member_types', 20);

/**
 * Create XProfile field groups and fields (if they don't exist)
 * This is a one-time operation - checks for existing groups/fields
 * 
 * @since 1.0.0
 * @return array Results with groups_created and fields_created counts
 */
function bp_playground_create_xprofile_structure() {
    if (!bp_is_active('xprofile')) {
        return array('error' => 'XProfile not active');
    }
    
    global $wpdb;
    $groups_table = $wpdb->prefix . 'bp_xprofile_groups';
    $fields_table = $wpdb->prefix . 'bp_xprofile_fields';
    
    $results = array(
        'groups_created' => 0,
        'fields_created' => 0
    );
    
    // Define 3 groups with ONLY 1 field of each type for testing
    $xprofile_structure = array(
        'Single Fields' => array(
            'description' => 'Fields with single values',
            'fields' => array(
                array('name' => 'Full Name', 'type' => 'textbox'),
                array('name' => 'About Me', 'type' => 'textarea'),
                array('name' => 'Birth Date', 'type' => 'datebox'),
                array('name' => 'Age', 'type' => 'number'),
                array('name' => 'Website', 'type' => 'url')
            )
        ),
        'Choice Fields' => array(
            'description' => 'Fields with options',
            'fields' => array(
                array(
                    'name' => 'Country',
                    'type' => 'selectbox',
                    'options' => array('USA', 'Canada', 'UK', 'Australia')
                ),
                array(
                    'name' => 'Gender',
                    'type' => 'radio',
                    'options' => array('Male', 'Female', 'Other')
                ),
                array(
                    'name' => 'Interests',
                    'type' => 'checkbox',
                    'options' => array('Sports', 'Music', 'Tech', 'Art')
                ),
                array(
                    'name' => 'Languages',
                    'type' => 'multiselectbox',
                    'options' => array('English', 'Spanish', 'French', 'German')
                )
            )
        ),
        'Special Fields' => array(
            'description' => 'Other field types',
            'fields' => array(
                array('name' => 'Phone', 'type' => 'telephone')
            )
        )
    );
    
    // Create groups and fields
    foreach ($xprofile_structure as $group_name => $group_data) {
        // Check if group exists
        $group_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$groups_table} WHERE name = %s",
            $group_name
        ));
        
        if (!$group_id) {
            $group_id = xprofile_insert_field_group(array(
                'name' => $group_name,
                'description' => $group_data['description']
            ));
            
            if ($group_id) {
                $results['groups_created']++;
            }
        }
        
        if (!$group_id) continue;
        
        // Create fields for this group
        foreach ($group_data['fields'] as $field_data) {
            // Check if field exists
            $field_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$fields_table} WHERE name = %s AND group_id = %d",
                $field_data['name'],
                $group_id
            ));
            
            if ($field_exists) continue;
            
            // Create field
            $field_id = xprofile_insert_field(array(
                'field_group_id' => $group_id,
                'name' => $field_data['name'],
                'type' => $field_data['type']
            ));
            
            if ($field_id) {
                $results['fields_created']++;
                
                // Add options if needed
                if (!empty($field_data['options'])) {
                    $option_order = 1;
                    foreach ($field_data['options'] as $option) {
                        xprofile_insert_field(array(
                            'field_group_id' => $group_id,
                            'parent_id' => $field_id,
                            'type' => 'option',
                            'name' => $option,
                            'option_order' => $option_order++
                        ));
                    }
                }
            }
        }
    }
    
    return $results;
}

/**
 * Populate XProfile data for users and assign member types
 * Generates values on-the-fly for optimal performance
 * 
 * @since 1.0.0
 * @param array $user_ids Optional array of user IDs to populate. If empty, populates all users.
 * @param bool $assign_member_types Whether to assign member types to users
 * @return array Results with fields_populated and member_types_assigned counts
 */
function bp_playground_populate_xprofile($user_ids = array(), $assign_member_types = true) {
    if (!bp_is_active('xprofile')) {
        return array('error' => 'XProfile not active');
    }
    
    // Member types should already be registered via bp_init hook
    // Just verify they exist if we're going to assign them
    if ($assign_member_types && !bp_get_member_type_object('student')) {
        // If member types aren't registered, do it now (for CLI usage)
        bp_playground_register_member_types();
    }
    
    // Get all users if none specified
    if (empty($user_ids)) {
        $user_ids = get_users(array('fields' => 'ID'));
    }
    
    $count = 0;
    $member_types_assigned = 0;
    
    // Get all fields INCLUDING the default Name field (id = 1)
    global $wpdb;
    $fields_table = $wpdb->prefix . 'bp_xprofile_fields';
    $fields = $wpdb->get_results(
        "SELECT id, name, type FROM {$fields_table} WHERE parent_id = 0"
    );
    
    // Static data that will be reused
    static $first_names = null;
    static $last_names = null;
    
    // Load name data only once
    if ($first_names === null) {
        $data_dir = WP_PLUGIN_DIR . '/buddypress-playground-cli/data/';
        $first_names_data = json_decode(file_get_contents($data_dir . 'first-names.json'), true);
        $last_names_data = json_decode(file_get_contents($data_dir . 'last-names.json'), true);
        
        $first_names = array_merge(
            $first_names_data['male'] ?? array(),
            $first_names_data['female'] ?? array()
        );
        $last_names = $last_names_data['common'] ?? array('Smith', 'Johnson', 'Williams');
    }
    
    // Available member types for random assignment
    $available_member_types = array('student', 'teacher', 'professional', 'alumni');
    
    // Populate each user
    foreach ($user_ids as $user_id) {
        $user = get_userdata($user_id);
        
        // Assign member type if enabled (skip admin user)
        if ($assign_member_types && $user_id != 1) {
            // Check if user already has a member type
            $existing_type = bp_get_member_type($user_id);
            if (!$existing_type) {
                // Assign random member type (weighted distribution)
                $type_weights = array(
                    'student' => 40,      // 40% students
                    'professional' => 30, // 30% professionals
                    'alumni' => 20,       // 20% alumni
                    'teacher' => 10       // 10% teachers
                );
                
                $random = rand(1, 100);
                $cumulative = 0;
                $selected_type = 'student'; // default
                
                foreach ($type_weights as $type => $weight) {
                    $cumulative += $weight;
                    if ($random <= $cumulative) {
                        $selected_type = $type;
                        break;
                    }
                }
                
                // Assign the member type
                if (bp_set_member_type($user_id, $selected_type)) {
                    $member_types_assigned++;
                }
            }
        }
        
        foreach ($fields as $field) {
            $value = null;
            
            // Generate value based on field name and type
            switch ($field->name) {
                case 'Name': // Default BuddyPress field
                    $value = $user ? $user->display_name : 
                        $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];
                    break;
                    
                case 'Full Name':
                    $value = $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];
                    break;
                    
                case 'About Me':
                    $templates = array(
                        'Passionate professional with extensive experience.',
                        'Creative problem solver and team player.',
                        'Dedicated to excellence and continuous learning.',
                        'Experienced professional seeking new challenges.'
                    );
                    $value = $templates[array_rand($templates)];
                    break;
                    
                case 'Birth Date':
                    $year = rand(1970, 2000);
                    $month = rand(1, 12);
                    $day = rand(1, 28);
                    $value = sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day);
                    break;
                    
                case 'Age':
                    $value = rand(20, 60);
                    break;
                    
                case 'Website':
                    $value = 'https://' . strtolower($user->user_login ?? 'user' . $user_id) . '.example.com';
                    break;
                    
                case 'Phone':
                    $value = '+1 (555) ' . rand(100, 999) . '-' . rand(1000, 9999);
                    break;
                    
                default:
                    // For fields with options, get random option(s)
                    if (in_array($field->type, array('selectbox', 'radio', 'checkbox', 'multiselectbox'))) {
                        $options = $wpdb->get_col($wpdb->prepare(
                            "SELECT name FROM {$fields_table} WHERE parent_id = %d",
                            $field->id
                        ));
                        
                        if (!empty($options)) {
                            if ($field->type == 'selectbox' || $field->type == 'radio') {
                                $value = $options[array_rand($options)];
                            } else {
                                // Multi-select: pick 1-3 options
                                $count_to_select = min(rand(1, 3), count($options));
                                if ($count_to_select == 1) {
                                    $value = $options[array_rand($options)];
                                } else {
                                    $selected_keys = array_rand($options, $count_to_select);
                                    $value = array();
                                    foreach ($selected_keys as $key) {
                                        $value[] = $options[$key];
                                    }
                                }
                            }
                        }
                    }
            }
            
            // Set the field data
            if ($value !== null) {
                xprofile_set_field_data($field->id, $user_id, $value, false);
                $count++;
            }
        }
    }
    
    return array(
        'fields_populated' => $count,
        'member_types_assigned' => $member_types_assigned
    );
}