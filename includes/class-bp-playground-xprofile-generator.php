<?php
/**
 * BuddyPress Playground XProfile Generator
 * 
 * Generates XProfile fields and data using predefined structure
 */

class BP_Playground_XProfile_Generator {
    
    /**
     * XProfile structure configuration
     */
    private $structure;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->load_structure();
    }
    
    /**
     * Load the predefined structure
     */
    private function load_structure() {
        $structure_file = BP_PLAYGROUND_PLUGIN_DIR . 'includes/data/xprofile-structure.php';
        if (file_exists($structure_file)) {
            $this->structure = require $structure_file;
        } else {
            $this->structure = $this->get_default_structure();
        }
    }
    
    /**
     * Create all XProfile groups and fields from structure
     */
    public function create_xprofile_structure() {
        $results = [
            'groups_created' => 0,
            'fields_created' => 0,
            'options_created' => 0,
            'member_types_created' => 0,
            'errors' => []
        ];
        
        // Create member types first
        if (!empty($this->structure['member_types'])) {
            foreach ($this->structure['member_types'] as $type_name => $type_config) {
                $result = bp_register_member_type($type_name, $type_config);
                if (!is_wp_error($result)) {
                    $results['member_types_created']++;
                }
            }
        }
        
        // Create field groups and fields
        foreach ($this->structure['field_groups'] as $group_key => $group_config) {
            $group_id = $this->create_field_group($group_config);
            
            if ($group_id) {
                $results['groups_created']++;
                
                // Create fields for this group
                foreach ($group_config['fields'] as $field_key => $field_config) {
                    $field_id = $this->create_field($group_id, $field_config);
                    
                    if ($field_id) {
                        $results['fields_created']++;
                        
                        // Create options for select/radio/checkbox fields
                        if (!empty($field_config['options'])) {
                            $options_created = $this->create_field_options($group_id, $field_id, $field_config);
                            $results['options_created'] += $options_created;
                        }
                    } else {
                        $results['errors'][] = "Failed to create field: {$field_config['name']}";
                    }
                }
            } else {
                $results['errors'][] = "Failed to create group: {$group_config['name']}";
            }
        }
        
        return $results;
    }
    
    /**
     * Create a field group
     */
    private function create_field_group($group_config) {
        global $wpdb, $bp;
        
        // Special handling for Base group
        if (!empty($group_config['use_existing']) || $group_config['name'] === 'Base') {
            // Find the Base group (usually ID 1)
            $base_group_id = $wpdb->get_var("SELECT id FROM {$bp->profile->table_name_groups} WHERE name = 'Base' OR id = 1 ORDER BY id ASC LIMIT 1");
            
            if ($base_group_id) {
                // Update Base group description if provided
                if (!empty($group_config['description'])) {
                    $wpdb->update(
                        $bp->profile->table_name_groups,
                        ['description' => $group_config['description']],
                        ['id' => $base_group_id]
                    );
                }
                return $base_group_id;
            }
        }
        
        // Check if group already exists
        $existing_group_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$bp->profile->table_name_groups} WHERE name = %s",
            $group_config['name']
        ));
        
        if ($existing_group_id) {
            return $existing_group_id;
        }
        
        // Create new group
        return xprofile_insert_field_group([
            'name' => $group_config['name'],
            'description' => $group_config['description'] ?? '',
            'can_delete' => $group_config['can_delete'] ?? true
        ]);
    }
    
    /**
     * Create a field
     */
    private function create_field($group_id, $field_config) {
        global $wpdb, $bp;
        
        // For Base group, skip the default "Name" field that BuddyPress creates
        if ($group_id == 1) {
            // Check if this is attempting to create a duplicate of the default Name field
            $default_name_field = $wpdb->get_row(
                "SELECT * FROM {$bp->profile->table_name_fields} 
                 WHERE group_id = 1 AND id = 1"
            );
            
            // If the field name matches the default field, skip it
            if ($default_name_field && strtolower($field_config['name']) === strtolower($default_name_field->name)) {
                return 1; // Return the default field ID
            }
        }
        
        // Check if field already exists
        $existing_field_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$bp->profile->table_name_fields} 
             WHERE group_id = %d AND name = %s AND parent_id = 0",
            $group_id,
            $field_config['name']
        ));
        
        if ($existing_field_id) {
            // Update field properties if needed
            if ($group_id == 1) {
                // For Base group fields, update properties to match our config
                $wpdb->update(
                    $bp->profile->table_name_fields,
                    [
                        'description' => $field_config['description'] ?? '',
                        'is_required' => $field_config['is_required'] ?? 0,
                        'field_order' => $field_config['field_order'] ?? 0
                    ],
                    ['id' => $existing_field_id]
                );
            }
            return $existing_field_id;
        }
        
        // Create new field
        return xprofile_insert_field([
            'field_group_id' => $group_id,
            'type' => $field_config['type'],
            'name' => $field_config['name'],
            'description' => $field_config['description'] ?? '',
            'is_required' => $field_config['is_required'] ?? false,
            'can_delete' => $field_config['can_delete'] ?? true,
            'field_order' => $field_config['field_order'] ?? 0,
            'order_by' => $field_config['order_by'] ?? 'custom'
        ]);
    }
    
    /**
     * Create field options
     */
    private function create_field_options($group_id, $field_id, $field_config) {
        $options_created = 0;
        
        foreach ($field_config['options'] as $index => $option_name) {
            $option_id = xprofile_insert_field([
                'field_group_id' => $group_id,
                'parent_id' => $field_id,
                'type' => 'option',
                'name' => $option_name,
                'can_delete' => true,
                'is_default_option' => ($option_name === ($field_config['default_option'] ?? false)),
                'option_order' => $index + 1
            ]);
            
            if ($option_id) {
                $options_created++;
            }
        }
        
        return $options_created;
    }
    
    /**
     * Generate profile data for a user
     */
    public function generate_user_profile_data($user_id, $completeness = 'complete') {
        $data_generated = 0;
        $user = get_userdata($user_id);
        
        if (!$user) {
            return 0;
        }
        
        // Determine which fields to populate based on completeness
        $skip_probability = $this->get_skip_probability($completeness);
        
        foreach ($this->structure['field_groups'] as $group_key => $group_config) {
            foreach ($group_config['fields'] as $field_key => $field_config) {
                // Skip some fields based on completeness level
                if (!$field_config['is_required'] && rand(1, 100) <= $skip_probability) {
                    continue;
                }
                
                // Get the field ID from database
                global $wpdb, $bp;
                $field_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT f.id FROM {$bp->profile->table_name_fields} f
                     JOIN {$bp->profile->table_name_groups} g ON f.group_id = g.id
                     WHERE g.name = %s AND f.name = %s AND f.parent_id = 0",
                    $group_config['name'],
                    $field_config['name']
                ));
                
                if (!$field_id) {
                    continue;
                }
                
                // Generate value based on field type
                $value = $this->generate_field_value($field_config, $user);
                
                if ($value !== null) {
                    $result = xprofile_set_field_data($field_id, $user_id, $value);
                    if ($result) {
                        $data_generated++;
                    }
                }
            }
        }
        
        // Set member type based on generated data
        $this->assign_member_type($user_id);
        
        return $data_generated;
    }
    
    /**
     * Generate value for a field
     */
    private function generate_field_value($field_config, $user) {
        switch ($field_config['type']) {
            case 'textbox':
                return $this->generate_textbox_value($field_config, $user);
                
            case 'textarea':
                return $this->generate_textarea_value($field_config);
                
            case 'datebox':
                return $this->generate_date_value($field_config);
                
            case 'number':
                return $this->generate_number_value($field_config);
                
            case 'url':
                return $this->generate_url_value($field_config, $user);
                
            case 'telephone':
                return $this->generate_phone_value($field_config);
                
            case 'selectbox':
            case 'radio':
                return $this->generate_single_choice_value($field_config);
                
            case 'checkbox':
            case 'multiselectbox':
                return $this->generate_multiple_choice_value($field_config);
                
            case 'checkbox_acceptance':
                return true; // Always accepted for generated data
                
            default:
                return null;
        }
    }
    
    /**
     * Generate textbox value
     */
    private function generate_textbox_value($field_config, $user) {
        if (!empty($field_config['values'])) {
            // Handle special cases
            if ($field_config['values'] === ['generate_from_name']) {
                // Generate nickname from username
                $parts = explode('_', $user->user_login);
                return ucfirst($parts[0]);
            }
            
            // Handle gender-based names
            if (isset($field_config['values']['male']) && isset($field_config['values']['female'])) {
                $gender = rand(0, 1) ? 'male' : 'female';
                $values = $field_config['values'][$gender];
            } else {
                $values = $field_config['values'];
            }
            
            return $values[array_rand($values)];
        }
        
        return 'Sample ' . $field_config['name'];
    }
    
    /**
     * Generate textarea value
     */
    private function generate_textarea_value($field_config) {
        if (!empty($field_config['values'])) {
            // Handle templates
            if (isset($field_config['values']['templates'])) {
                $template = $field_config['values']['templates'][array_rand($field_config['values']['templates'])];
                $variables = $field_config['values']['variables'] ?? [];
                
                // Replace variables in template
                foreach ($variables as $var_name => $var_values) {
                    if (strpos($template, '{' . $var_name . '}') !== false) {
                        $value = $var_values[array_rand($var_values)];
                        $template = str_replace('{' . $var_name . '}', $value, $template);
                    }
                }
                
                return $template;
            }
            
            // Handle skill sets
            if (isset($field_config['values']['skill_sets'])) {
                $all_skills = [];
                foreach ($field_config['values']['skill_sets'] as $category => $skills) {
                    // Pick some skills from different categories
                    $selected = array_rand(array_flip($skills), min(2, count($skills)));
                    if (!is_array($selected)) {
                        $selected = [$selected];
                    }
                    $all_skills = array_merge($all_skills, $selected);
                }
                
                // Return 3-7 random skills
                $count = rand(3, min(7, count($all_skills)));
                $final_skills = array_slice($all_skills, 0, $count);
                return implode(', ', $final_skills);
            }
        }
        
        return 'This is a sample ' . strtolower($field_config['name']) . ' text.';
    }
    
    /**
     * Generate date value
     */
    private function generate_date_value($field_config) {
        $min_year = $field_config['values']['min_year'] ?? 1950;
        $max_year = $field_config['values']['max_year'] ?? 2005;
        
        $year = rand($min_year, $max_year);
        $month = rand(1, 12);
        $day = rand(1, 28); // Safe day range
        
        return sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day);
    }
    
    /**
     * Generate number value
     */
    private function generate_number_value($field_config) {
        $min = $field_config['values']['min'] ?? 0;
        $max = $field_config['values']['max'] ?? 100;
        
        // Handle weighted distribution
        if (isset($field_config['values']['distribution']) && $field_config['values']['distribution'] === 'weighted') {
            $weights = $field_config['values']['weights'];
            $rand = rand(1, 100) / 100;
            $cumulative = 0;
            
            foreach ($weights as $range => $weight) {
                $cumulative += $weight;
                if ($rand <= $cumulative) {
                    // Parse range like "0-2" or "21+"
                    if (strpos($range, '-') !== false) {
                        list($range_min, $range_max) = explode('-', $range);
                        return rand((int)$range_min, (int)$range_max);
                    } elseif (strpos($range, '+') !== false) {
                        $range_min = (int)str_replace('+', '', $range);
                        return rand($range_min, min($range_min + 10, $max));
                    }
                }
            }
        }
        
        return rand($min, $max);
    }
    
    /**
     * Generate URL value
     */
    private function generate_url_value($field_config, $user) {
        if (!empty($field_config['values']['patterns'])) {
            $pattern = $field_config['values']['patterns'][array_rand($field_config['values']['patterns'])];
            
            // Replace placeholders
            $url = str_replace('{username}', $user->user_login, $pattern);
            $url = str_replace('{firstname}', strtolower($user->first_name ?: 'john'), $url);
            $url = str_replace('{lastname}', strtolower($user->last_name ?: 'doe'), $url);
            $url = str_replace('{random}', rand(1000, 9999), $url);
            
            return $url;
        }
        
        return 'https://example.com/' . $user->user_login;
    }
    
    /**
     * Generate phone value
     */
    private function generate_phone_value($field_config) {
        if (!empty($field_config['values']['patterns'])) {
            $pattern = $field_config['values']['patterns'][array_rand($field_config['values']['patterns'])];
            
            // Replace placeholders
            if (isset($field_config['values']['area'])) {
                $area = $field_config['values']['area'][array_rand($field_config['values']['area'])];
                $pattern = str_replace('{area}', $area, $pattern);
            }
            
            // Handle ranges like {exchange} => ['0100-9999']
            if (strpos($pattern, '{exchange}') !== false) {
                $exchange = sprintf('%04d', rand(100, 9999));
                $pattern = str_replace('{exchange}', $exchange, $pattern);
            }
            
            // Replace other number placeholders
            $pattern = str_replace('{num1}', rand(1000, 9999), $pattern);
            $pattern = str_replace('{num2}', rand(1000, 9999), $pattern);
            
            return $pattern;
        }
        
        return '+1 (555) ' . rand(100, 999) . '-' . rand(1000, 9999);
    }
    
    /**
     * Generate single choice value (selectbox, radio)
     */
    private function generate_single_choice_value($field_config) {
        if (!empty($field_config['options'])) {
            return $field_config['options'][array_rand($field_config['options'])];
        }
        return null;
    }
    
    /**
     * Generate multiple choice value (checkbox, multiselectbox)
     */
    private function generate_multiple_choice_value($field_config) {
        if (!empty($field_config['options'])) {
            $min = $field_config['values']['min_selections'] ?? 1;
            $max = $field_config['values']['max_selections'] ?? count($field_config['options']);
            $count = rand($min, min($max, count($field_config['options'])));
            
            $selected = [];
            
            // Handle always_include
            if (!empty($field_config['values']['always_include'])) {
                $selected = $field_config['values']['always_include'];
                $count -= count($selected);
            }
            
            // Select random options
            $available = array_diff($field_config['options'], $selected);
            if ($count > 0 && !empty($available)) {
                $random_selections = array_rand(array_flip($available), min($count, count($available)));
                if (!is_array($random_selections)) {
                    $random_selections = [$random_selections];
                }
                $selected = array_merge($selected, $random_selections);
            }
            
            return $selected;
        }
        return [];
    }
    
    /**
     * Get skip probability based on completeness level
     */
    private function get_skip_probability($completeness) {
        switch ($completeness) {
            case 'complete':
                return 0; // Don't skip any fields
            case 'mostly_complete':
                return 15; // Skip 15% of optional fields
            case 'partial':
                return 40; // Skip 40% of optional fields
            case 'minimal':
                return 80; // Skip 80% of optional fields
            default:
                return 0;
        }
    }
    
    /**
     * Assign member type based on profile data
     */
    private function assign_member_type($user_id) {
        // Get employment status to determine member type
        global $wpdb, $bp;
        
        $employment_status = xprofile_get_field_data('Employment Status', $user_id);
        
        $member_type_map = [
            'Student' => 'student',
            'Full-time' => 'professional',
            'Part-time' => 'professional',
            'Freelance' => 'professional',
            'Self-employed' => 'mentor',
            'Retired' => 'alumni',
            'Unemployed' => 'professional'
        ];
        
        $member_type = $member_type_map[$employment_status] ?? 'professional';
        
        if (bp_get_member_type($user_id) !== $member_type) {
            bp_set_member_type($user_id, $member_type);
        }
    }
    
    /**
     * Get default structure (fallback)
     */
    private function get_default_structure() {
        return [
            'field_groups' => [
                'basic' => [
                    'name' => 'Basic Information',
                    'description' => 'Basic profile information',
                    'fields' => [
                        'location' => [
                            'type' => 'textbox',
                            'name' => 'Location',
                            'description' => 'Where are you from?',
                            'is_required' => false,
                            'values' => ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix']
                        ]
                    ]
                ]
            ],
            'member_types' => [],
            'generation_rules' => []
        ];
    }
}