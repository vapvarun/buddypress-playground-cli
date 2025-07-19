<?php
/**
 * BuddyPress Playground XProfile Module
 *
 * @package BuddyPress_Playground
 * @subpackage Modules
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * XProfile System Module - Critical for comprehensive BuddyPress testing
 *
 * @since 1.0.0
 */
class BP_Playground_XProfile_Module extends BP_Playground_Abstract_Module {

    /**
     * Module name
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_name = 'xprofile';

    /**
     * Default field types and their configurations
     *
     * @since 1.0.0
     * @var array
     */
    private $field_types = [
        'textbox' => [
            'name' => 'Text Box',
            'allow_custom_visibility' => 'allowed',
            'fields' => ['First Name', 'Last Name', 'Job Title', 'Company', 'City', 'Nickname'],
        ],
        'textarea' => [
            'name' => 'Multi-line Text Box',
            'allow_custom_visibility' => 'allowed',
            'fields' => ['Bio', 'About Me', 'Skills', 'Experience', 'Interests', 'Goals'],
        ],
        'selectbox' => [
            'name' => 'Drop Down Select Box',
            'allow_custom_visibility' => 'allowed',
            'fields' => ['Country', 'Industry', 'Experience Level', 'Education Level'],
            'options' => [
                'Country' => ['United States', 'Canada', 'United Kingdom', 'Australia', 'Germany', 'France', 'India', 'Japan'],
                'Industry' => ['Technology', 'Healthcare', 'Education', 'Finance', 'Marketing', 'Design', 'Sales'],
                'Experience Level' => ['Entry Level', 'Mid Level', 'Senior Level', 'Executive', 'Consultant'],
                'Education Level' => ['High School', 'Bachelor\'s Degree', 'Master\'s Degree', 'PhD', 'Professional Certification'],
            ],
        ],
        'multiselectbox' => [
            'name' => 'Multi Select Box',
            'allow_custom_visibility' => 'allowed',
            'fields' => ['Skills', 'Languages', 'Interests', 'Certifications'],
            'options' => [
                'Skills' => ['PHP', 'JavaScript', 'Python', 'Design', 'Marketing', 'Project Management', 'Data Analysis'],
                'Languages' => ['English', 'Spanish', 'French', 'German', 'Chinese', 'Japanese', 'Portuguese', 'Russian'],
                'Interests' => ['Technology', 'Travel', 'Photography', 'Music', 'Sports', 'Reading', 'Cooking', 'Gaming'],
                'Certifications' => ['PMP', 'AWS', 'Google Analytics', 'Salesforce', 'Adobe Creative', 'Microsoft Office'],
            ],
        ],
        'radio' => [
            'name' => 'Radio Buttons',
            'allow_custom_visibility' => 'allowed',
            'fields' => ['Gender', 'Employment Status', 'Relationship Status', 'Communication Preference'],
            'options' => [
                'Gender' => ['Male', 'Female', 'Non-binary', 'Prefer not to say'],
                'Employment Status' => ['Employed', 'Self-employed', 'Unemployed', 'Student', 'Retired'],
                'Relationship Status' => ['Single', 'In a relationship', 'Married', 'It\'s complicated'],
                'Communication Preference' => ['Email', 'Phone', 'Video Call', 'In Person'],
            ],
        ],
        'checkbox' => [
            'name' => 'Checkboxes',
            'allow_custom_visibility' => 'allowed',
            'fields' => ['Newsletter Subscription', 'Privacy Settings', 'Notifications', 'Marketing Preferences'],
            'options' => [
                'Newsletter Subscription' => ['Weekly Newsletter', 'Monthly Updates', 'Event Announcements', 'Product Updates'],
                'Privacy Settings' => ['Public Profile', 'Show Email', 'Show Activity', 'Allow Messages'],
                'Notifications' => ['Email Notifications', 'SMS Notifications', 'Push Notifications', 'Desktop Notifications'],
                'Marketing Preferences' => ['Promotional Emails', 'Partner Offers', 'Event Invitations', 'Survey Requests'],
            ],
        ],
        'datebox' => [
            'name' => 'Date Selector',
            'allow_custom_visibility' => 'allowed',
            'fields' => ['Date of Birth', 'Start Date', 'Graduation Date', 'Anniversary'],
        ],
        'number' => [
            'name' => 'Number',
            'allow_custom_visibility' => 'allowed',
            'fields' => ['Years of Experience', 'Age', 'Team Size', 'Budget Range'],
        ],
        'url' => [
            'name' => 'URL',
            'allow_custom_visibility' => 'allowed',
            'fields' => ['Website', 'LinkedIn', 'Portfolio', 'GitHub', 'Twitter'],
        ],
        'member_types' => [
            'name' => 'Member Types',
            'allow_custom_visibility' => 'disabled',
            'fields' => ['Member Type'],
        ],
    ];

    /**
     * Default field groups
     *
     * @since 1.0.0
     * @var array
     */
    private $field_groups = [
        'Personal Information' => [
            'description' => 'Basic personal details and preferences',
            'fields' => ['First Name', 'Last Name', 'Date of Birth', 'Gender', 'Bio'],
        ],
        'Professional Details' => [
            'description' => 'Work-related information and experience',
            'fields' => ['Job Title', 'Company', 'Industry', 'Experience Level', 'Skills'],
        ],
        'Contact Information' => [
            'description' => 'Ways to get in touch and connect',
            'fields' => ['City', 'Country', 'Website', 'LinkedIn', 'Communication Preference'],
        ],
        'Interests & Hobbies' => [
            'description' => 'Personal interests and recreational activities',
            'fields' => ['Interests', 'Languages', 'About Me', 'Goals'],
        ],
        'Social Media' => [
            'description' => 'Social media profiles and online presence',
            'fields' => ['Twitter', 'GitHub', 'Portfolio', 'Nickname'],
        ],
        'Settings & Preferences' => [
            'description' => 'Account settings and privacy preferences',
            'fields' => ['Newsletter Subscription', 'Privacy Settings', 'Notifications'],
        ],
    ];

    /**
     * Member types configuration
     *
     * @since 1.0.0
     * @var array
     */
    private $member_types = [
        'student' => [
            'labels' => [
                'name' => 'Students',
                'singular_name' => 'Student',
            ],
            'has_directory' => true,
            'display_in_list' => true,
        ],
        'professional' => [
            'labels' => [
                'name' => 'Professionals',
                'singular_name' => 'Professional',
            ],
            'has_directory' => true,
            'display_in_list' => true,
        ],
        'alumni' => [
            'labels' => [
                'name' => 'Alumni',
                'singular_name' => 'Alumnus',
            ],
            'has_directory' => true,
            'display_in_list' => true,
        ],
        'instructor' => [
            'labels' => [
                'name' => 'Instructors',
                'singular_name' => 'Instructor',
            ],
            'has_directory' => true,
            'display_in_list' => true,
        ],
        'admin' => [
            'labels' => [
                'name' => 'Administrators',
                'singular_name' => 'Administrator',
            ],
            'has_directory' => false,
            'display_in_list' => false,
        ],
    ];

    /**
     * Generate XProfile data
     *
     * @since 1.0.0
     * @param array $args Generation arguments
     * @return array|WP_Error Generation results
     */
    public function generate($args = []) {
        $defaults = [
            'field_groups' => 6,
            'fields_per_group' => 8,
            'member_types' => true,
            'populate_data' => true,
            'completion_rate' => 0.85,
            'user_ids' => [],
        ];

        $args = wp_parse_args($args, $defaults);

        // Validate arguments
        $validation_rules = [
            'field_groups' => ['type' => 'int', 'min' => 1, 'max' => 20],
            'fields_per_group' => ['type' => 'int', 'min' => 1, 'max' => 50],
            'completion_rate' => ['type' => 'float', 'min' => 0.0, 'max' => 1.0],
            'member_types' => ['type' => 'bool'],
            'populate_data' => ['type' => 'bool'],
        ];

        $validated_args = $this->validate_args($args, $validation_rules);
        if (is_wp_error($validated_args)) {
            return $validated_args;
        }

        $this->start_generation('XProfile System');

        $results = [
            'field_groups_created' => 0,
            'fields_created' => 0,
            'member_types_created' => 0,
            'users_populated' => 0,
            'errors' => [],
        ];

        try {
            // Create member types first if requested
            if ($validated_args['member_types']) {
                $member_types_result = $this->create_member_types();
                if (is_wp_error($member_types_result)) {
                    $results['errors'][] = $member_types_result->get_error_message();
                } else {
                    $results['member_types_created'] = $member_types_result;
                }
            }

            // Create field groups and fields
            $field_groups_result = $this->create_field_groups($validated_args['field_groups']);
            if (is_wp_error($field_groups_result)) {
                $results['errors'][] = $field_groups_result->get_error_message();
            } else {
                $results['field_groups_created'] = $field_groups_result['groups_created'];
                $results['fields_created'] = $field_groups_result['fields_created'];
            }

            // Populate user data if requested
            if ($validated_args['populate_data']) {
                $user_ids = !empty($validated_args['user_ids']) 
                    ? $validated_args['user_ids'] 
                    : $this->get_all_user_ids();

                if (!empty($user_ids)) {
                    $population_result = $this->populate_profile_data(
                        $user_ids, 
                        $validated_args['completion_rate']
                    );
                    
                    if (is_wp_error($population_result)) {
                        $results['errors'][] = $population_result->get_error_message();
                    } else {
                        $results['users_populated'] = $population_result;
                    }
                }
            }

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->log_error('XProfile generation failed: ' . $e->getMessage());
        }

        $this->end_generation();
        return $results;
    }

    /**
     * Create XProfile field groups
     *
     * @since 1.0.0
     * @param int $count Number of field groups to create
     * @return array|WP_Error Creation results
     */
    public function create_field_groups($count = 6) {
        if (!bp_is_active('xprofile')) {
            return new WP_Error('xprofile_disabled', __('XProfile component is not active.', BP_PLAYGROUND_TEXT_DOMAIN));
        }

        $created_groups = 0;
        $created_fields = 0;
        $group_names = array_keys($this->field_groups);

        // Ensure we don't create more groups than we have configurations for
        $count = min($count, count($group_names));

        for ($i = 0; $i < $count; $i++) {
            $group_name = $group_names[$i];
            $group_config = $this->field_groups[$group_name];

            // Create field group
            $group_id = xprofile_insert_field_group([
                'name' => $group_name,
                'description' => $group_config['description'],
                'can_delete' => true,
            ]);

            if ($group_id) {
                $created_groups++;
                
                // Create fields for this group
                $fields_result = $this->create_profile_fields($group_id, $group_config['fields']);
                if (!is_wp_error($fields_result)) {
                    $created_fields += $fields_result;
                }

                $this->log("Created XProfile field group: {$group_name} (ID: {$group_id})");
            } else {
                $this->log_error("Failed to create XProfile field group: {$group_name}");
            }
        }

        return [
            'groups_created' => $created_groups,
            'fields_created' => $created_fields,
        ];
    }

    /**
     * Create profile fields for a field group
     *
     * @since 1.0.0
     * @param int $group_id Field group ID
     * @param array $field_names Array of field names to create
     * @return int|WP_Error Number of fields created
     */
    public function create_profile_fields($group_id, $field_names = []) {
        if (empty($field_names)) {
            // Get default field names from all field types
            $field_names = [];
            foreach ($this->field_types as $type_config) {
                $field_names = array_merge($field_names, $type_config['fields']);
            }
            $field_names = array_slice(array_unique($field_names), 0, 10);
        }

        $created_fields = 0;

        foreach ($field_names as $field_name) {
            $field_type = $this->get_field_type_for_name($field_name);
            $field_config = $this->field_types[$field_type];

            $field_args = [
                'field_group_id' => $group_id,  // This is correct for xprofile_insert_field
                'name' => $field_name,
                'type' => $field_type,
                'is_required' => $this->is_field_required($field_name),
                'can_delete' => true,
                'field_order' => $created_fields + 1,
                'allow_custom_visibility' => $field_config['allow_custom_visibility'],
            ];

            $field_id = xprofile_insert_field($field_args);

            if ($field_id) {
                $created_fields++;
                
                // Add options for select fields after creating the field
                if (in_array($field_type, ['selectbox', 'multiselectbox', 'radio', 'checkbox'])) {
                    $this->set_field_options($field_id, $field_name, $field_type);
                }
                
                $this->log("Created XProfile field: {$field_name} ({$field_type}) in group {$group_id}");
            } else {
                $this->log_error("Failed to create XProfile field: {$field_name} in group {$group_id}");
            }
        }

        return $created_fields;
    }

    /**
     * Set options for choice-based fields
     *
     * @since 1.0.0
     * @param int $field_id Field ID
     * @param string $field_name Field name
     * @param string $field_type Field type
     * @return void
     */
    private function set_field_options($field_id, $field_name, $field_type) {
        if (!in_array($field_type, ['selectbox', 'multiselectbox', 'radio', 'checkbox'])) {
            return;
        }

        $field_config = $this->field_types[$field_type];
        if (!isset($field_config['options'][$field_name])) {
            return;
        }

        // Get the parent field to get its group_id
        $parent_field = new BP_XProfile_Field($field_id);
        if (!$parent_field->id) {
            $this->log_error("Parent field {$field_id} not found for options");
            return;
        }

        $options = $field_config['options'][$field_name];
        $option_order = 1;

        foreach ($options as $index => $option) {
            $option_args = [
                'field_group_id' => $parent_field->group_id, // Use parent field's group_id
                'parent_id' => $field_id,
                'name' => $option,
                'type' => 'option',
                'option_order' => $option_order++, // Use option_order instead of field_order
                'can_delete' => true,
            ];
            
            // Only set is_default_option if it's the first option
            if ($index === 0) {
                $option_args['is_default_option'] = true;
            }
            
            $option_id = xprofile_insert_field($option_args);

            if (!$option_id) {
                $this->log_error("Failed to create option '{$option}' for field {$field_id}");
            } else {
                $this->log("Created option '{$option}' for field {$field_id}");
            }
        }
    }

    /**
     * Create member types
     *
     * @since 1.0.0
     * @param array $types Optional custom member types
     * @return int|WP_Error Number of member types created
     */
    public function create_member_types($types = []) {
        if (empty($types)) {
            $types = $this->member_types;
        }

        $created_types = 0;

        foreach ($types as $type_key => $type_config) {
            $result = bp_register_member_type($type_key, $type_config);
            
            if (!is_wp_error($result)) {
                $created_types++;
                $this->log("Created member type: {$type_key}");
            } else {
                $this->log_error("Failed to create member type {$type_key}: " . $result->get_error_message());
            }
        }

        return $created_types;
    }

    /**
     * Populate profile data for users
     *
     * @since 1.0.0
     * @param array $user_ids User IDs to populate
     * @param float $completion_rate Profile completion rate (0.0 to 1.0)
     * @return int|WP_Error Number of users populated
     */
    public function populate_profile_data($user_ids, $completion_rate = 0.85) {
        if (empty($user_ids)) {
            return new WP_Error('no_users', __('No users provided for profile population.', BP_PLAYGROUND_TEXT_DOMAIN));
        }

        $batch_processor = bp_playground_get_module('batch_processor');
        
        $callback = function($start_index, $batch_size, $options) use ($user_ids, $completion_rate) {
            $batch_user_ids = array_slice($user_ids, $start_index, $batch_size);
            $populated = 0;

            foreach ($batch_user_ids as $user_id) {
                if ($this->populate_user_profile($user_id, $completion_rate)) {
                    $populated++;
                }
            }

            return ['successful' => $populated, 'processed' => count($batch_user_ids)];
        };

        $progress_callback = function($progress) {
            $this->show_progress(
                $progress['processed'], 
                $progress['total'], 
                'Populating user profiles'
            );
        };

        $result = $batch_processor->process_in_batches(
            count($user_ids),
            $callback,
            $progress_callback
        );

        if (is_wp_error($result)) {
            return $result;
        }

        return $result['successful_items'];
    }

    /**
     * Populate profile data for a single user
     *
     * @since 1.0.0
     * @param int $user_id User ID
     * @param float $completion_rate Profile completion rate
     * @return bool Success status
     */
    private function populate_user_profile($user_id, $completion_rate = 0.85) {
        // Get all profile fields
        $profile_groups = bp_xprofile_get_groups([
            'fetch_fields' => true,
            'hide_empty_groups' => false,
        ]);

        if (empty($profile_groups)) {
            return false;
        }

        $populated_fields = 0;
        $total_fields = 0;

        // Generate persona for consistent data
        $user_persona = $this->generate_user_persona($user_id);

        foreach ($profile_groups as $group) {
            if (empty($group->fields)) {
                continue;
            }

            foreach ($group->fields as $field) {
                $total_fields++;

                // Skip if we've reached completion rate
                if (($populated_fields / max($total_fields, 1)) >= $completion_rate && rand(1, 100) > 30) {
                    continue;
                }

                // Check if field already has a value
                $existing_value = xprofile_get_field_data($field->id, $user_id);
                if (!empty($existing_value) && $existing_value !== '') {
                    // Skip fields that already have values
                    continue;
                }

                $field_value = $this->generate_field_value($field, $user_persona);
                
                if ($field_value !== null) {
                    $success = xprofile_set_field_data($field->id, $user_id, $field_value);
                    if ($success) {
                        $populated_fields++;
                    }
                }
            }
        }

        // Set member type if enabled
        if (!empty($user_persona['member_type'])) {
            bp_set_member_type($user_id, $user_persona['member_type']);
        }

        return $populated_fields > 0;
    }

    /**
     * Generate user persona for consistent profile data
     *
     * @since 1.0.0
     * @param int $user_id User ID
     * @return array User persona data
     */
    private function generate_user_persona($user_id) {
        // Seed random generator with user ID for consistency
        mt_srand($user_id);

        $user = get_userdata($user_id);
        $personas = [
            'tech_professional' => [
                'industry' => 'Technology',
                'skills' => ['PHP', 'JavaScript', 'Python'],
                'interests' => ['Technology', 'Gaming', 'Reading'],
                'experience_level' => 'Senior Level',
                'member_type' => 'professional',
            ],
            'creative_professional' => [
                'industry' => 'Design',
                'skills' => ['Design', 'Adobe Creative', 'Project Management'],
                'interests' => ['Photography', 'Travel', 'Music'],
                'experience_level' => 'Mid Level',
                'member_type' => 'professional',
            ],
            'student' => [
                'industry' => 'Education',
                'skills' => ['Research', 'Data Analysis'],
                'interests' => ['Reading', 'Sports', 'Technology'],
                'experience_level' => 'Entry Level',
                'member_type' => 'student',
            ],
            'educator' => [
                'industry' => 'Education',
                'skills' => ['Teaching', 'Curriculum Development', 'Project Management'],
                'interests' => ['Reading', 'Travel', 'Cooking'],
                'experience_level' => 'Senior Level',
                'member_type' => 'instructor',
            ],
        ];

        $persona_keys = array_keys($personas);
        $selected_persona = $personas[$persona_keys[$user_id % count($persona_keys)]];

        // Add user-specific data
        $selected_persona['first_name'] = $user->first_name ?: $this->generate_first_name();
        $selected_persona['last_name'] = $user->last_name ?: $this->generate_last_name();
        $selected_persona['email'] = $user->user_email;
        $selected_persona['username'] = $user->user_login;

        // Reset random seed
        mt_srand();

        return $selected_persona;
    }

    /**
     * Generate field value based on field type and user persona
     *
     * @since 1.0.0
     * @param object $field XProfile field object
     * @param array $persona User persona data
     * @return mixed Field value
     */
    private function generate_field_value($field, $persona) {
        $field_name = $field->name;
        $field_type = $field->type;

        // Handle specific field names first
        switch (strtolower($field_name)) {
            case 'first name':
                return $persona['first_name'];
            case 'last name':
                return $persona['last_name'];
            case 'industry':
                return $persona['industry'];
            case 'experience level':
                return $persona['experience_level'];
            case 'member type':
                return $persona['member_type'];
        }

        // Map field names to sample data types
        $field_type_mapping = [
            'about' => 'about',
            'bio' => 'bio',
            'interests' => 'interests',
            'skills' => 'skills',
            'location' => 'location',
            'website' => 'website',
            'languages' => 'languages',
            'education' => 'education',
        ];
        
        // Check if field name maps to a sample data type
        $normalized_name = strtolower($field_name);
        foreach ($field_type_mapping as $key => $data_type) {
            if (strpos($normalized_name, $key) !== false) {
                $options = [
                    'type' => $field_type,
                    'name' => $field_name,
                ];
                
                // Get field options if selectbox/radio/checkbox
                if (in_array($field_type, ['selectbox', 'radio', 'multiselectbox', 'checkbox'])) {
                    $options['options'] = $this->get_field_options($field);
                }
                
                return BP_Playground_Sample_Data::generate_profile_field_value($data_type, $options);
            }
        }

        // Handle by field type with sample data
        switch ($field_type) {
            case 'textbox':
            case 'textarea':
                return BP_Playground_Sample_Data::generate_profile_field_value('about', ['type' => $field_type]);
            case 'selectbox':
            case 'radio':
                $options = $this->get_field_options($field);
                return BP_Playground_Sample_Data::generate_profile_field_value($field_type, ['options' => $options, 'type' => $field_type]);
            case 'multiselectbox':
            case 'checkbox':
                $options = $this->get_field_options($field);
                return BP_Playground_Sample_Data::generate_profile_field_value($field_type, ['options' => $options, 'type' => $field_type]);
            case 'datebox':
                return BP_Playground_Sample_Data::generate_profile_field_value('datebox', []);
            case 'number':
                return BP_Playground_Sample_Data::generate_profile_field_value('number', []);
            case 'url':
                return BP_Playground_Sample_Data::generate_profile_field_value('url', []);
            default:
                return BP_Playground_Sample_Data::generate_profile_field_value($field_type, ['name' => $field_name]);
        }
    }

    /**
     * Get field type for a given field name
     *
     * @since 1.0.0
     * @param string $field_name Field name
     * @return string Field type
     */
    private function get_field_type_for_name($field_name) {
        foreach ($this->field_types as $type => $config) {
            if (in_array($field_name, $config['fields'])) {
                return $type;
            }
        }
        return 'textbox'; // Default fallback
    }

    /**
     * Check if field should be required
     *
     * @since 1.0.0
     * @param string $field_name Field name
     * @return bool Whether field is required
     */
    private function is_field_required($field_name) {
        $required_fields = ['First Name', 'Last Name', 'Member Type'];
        return in_array($field_name, $required_fields);
    }

    /**
     * Generate textbox value
     *
     * @since 1.0.0
     * @param string $field_name Field name
     * @param array $persona User persona
     * @return string Generated value
     */
    private function generate_textbox_value($field_name, $persona) {
        $values = [
            'Job Title' => ['Software Developer', 'Product Manager', 'Designer', 'Marketing Specialist', 'Teacher', 'Consultant'],
            'Company' => ['Tech Corp', 'Creative Agency', 'University', 'Startup Inc', 'Global Solutions', 'Innovation Labs'],
            'City' => ['New York', 'San Francisco', 'London', 'Toronto', 'Sydney', 'Berlin', 'Tokyo'],
            'Nickname' => ['Tech Guru', 'Creative Mind', 'Problem Solver', 'Team Player', 'Innovator'],
        ];

        if (isset($values[$field_name])) {
            return $values[$field_name][array_rand($values[$field_name])];
        }

        return 'Sample ' . $field_name;
    }

    /**
     * Generate textarea value
     *
     * @since 1.0.0
     * @param string $field_name Field name
     * @param array $persona User persona
     * @return string Generated value
     */
    private function generate_textarea_value($field_name, $persona) {
        $templates = [
            'Bio' => "I'm a {$persona['experience_level']} professional in {$persona['industry']} with expertise in " . implode(', ', $persona['skills']) . ". I'm passionate about " . implode(', ', $persona['interests']) . " and always looking to learn new things.",
            'About Me' => "Welcome to my profile! I enjoy working on challenging projects and collaborating with talented teams. In my free time, I love " . implode(', ', $persona['interests']) . ".",
            'Skills' => "My core competencies include:\n• " . implode("\n• ", $persona['skills']) . "\n\nI'm always expanding my skill set and staying current with industry trends.",
            'Experience' => "I have worked on various projects in {$persona['industry']}, developing expertise in " . implode(', ', $persona['skills']) . ". I believe in continuous learning and professional growth.",
            'Interests' => "I'm passionate about " . implode(', ', $persona['interests']) . ". I believe in maintaining a good work-life balance and pursuing hobbies that inspire creativity.",
            'Goals' => "My professional goals include advancing in {$persona['industry']}, expanding my skills in " . implode(' and ', $persona['skills']) . ", and contributing to meaningful projects.",
        ];

        return isset($templates[$field_name]) ? $templates[$field_name] : "This is my {$field_name}.";
    }

    /**
     * Generate date value
     *
     * @since 1.0.0
     * @param string $field_name Field name
     * @return string Generated date
     */
    private function generate_date_value($field_name) {
        switch (strtolower($field_name)) {
            case 'date of birth':
                $year = rand(1970, 2000);
                $month = rand(1, 12);
                $day = rand(1, 28);
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            case 'start date':
                $year = rand(2015, 2023);
                $month = rand(1, 12);
                $day = rand(1, 28);
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            case 'graduation date':
                $year = rand(2010, 2022);
                $month = rand(5, 6); // May or June
                $day = rand(15, 30);
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            default:
                return date('Y-m-d');
        }
    }

    /**
     * Generate number value
     *
     * @since 1.0.0
     * @param string $field_name Field name
     * @return int Generated number
     */
    private function generate_number_value($field_name) {
        switch (strtolower($field_name)) {
            case 'years of experience':
                return rand(1, 20);
            case 'age':
                return rand(22, 65);
            case 'team size':
                return rand(2, 50);
            case 'budget range':
                return rand(10000, 1000000);
            default:
                return rand(1, 100);
        }
    }

    /**
     * Generate URL value
     *
     * @since 1.0.0
     * @param string $field_name Field name
     * @param array $persona User persona
     * @return string Generated URL
     */
    private function generate_url_value($field_name, $persona) {
        $username = strtolower($persona['username']);
        
        switch (strtolower($field_name)) {
            case 'website':
                return "https://www.{$username}.com";
            case 'linkedin':
                return "https://linkedin.com/in/{$username}";
            case 'portfolio':
                return "https://portfolio.{$username}.com";
            case 'github':
                return "https://github.com/{$username}";
            case 'twitter':
                return "https://twitter.com/{$username}";
            default:
                return "https://www.example.com/{$username}";
        }
    }

    /**
     * Get random option from field
     *
     * @since 1.0.0
     * @param object $field XProfile field object
     * @return string|null Random option
     */
    private function get_random_field_option($field) {
        $options = $this->get_field_options($field);
        return !empty($options) ? $options[array_rand($options)] : null;
    }

    /**
     * Get multiple random options from field
     *
     * @since 1.0.0
     * @param object $field XProfile field object
     * @param int $count Number of options to select
     * @return array Random options
     */
    private function get_random_field_options($field, $count = 2) {
        $options = $this->get_field_options($field);
        if (empty($options)) {
            return [];
        }

        $count = min($count, count($options));
        $selected = array_rand($options, $count);
        
        if (!is_array($selected)) {
            $selected = [$selected];
        }

        return array_intersect_key($options, array_flip($selected));
    }

    /**
     * Get field options
     *
     * @since 1.0.0
     * @param object $field XProfile field object
     * @return array Field options
     */
    private function get_field_options($field) {
        global $wpdb;

        $options = $wpdb->get_col($wpdb->prepare(
            "SELECT name FROM {$wpdb->base_prefix}bp_xprofile_fields 
             WHERE parent_id = %d AND type = 'option' 
             ORDER BY field_order",
            $field->id
        ));

        return $options ?: [];
    }

    /**
     * Generate random first name
     *
     * @since 1.0.0
     * @return string Random first name
     */
    private function generate_first_name() {
        $names = BP_Playground_Sample_Data::get_first_names('all');
        return $names[array_rand($names)];
    }

    /**
     * Generate random last name
     *
     * @since 1.0.0
     * @return string Random last name
     */
    private function generate_last_name() {
        $names = BP_Playground_Sample_Data::get_last_names('all');
        return $names[array_rand($names)];
    }

    /**
     * Get all user IDs
     *
     * @since 1.0.0
     * @return array User IDs
     */
    private function get_all_user_ids() {
        global $wpdb;
        return $wpdb->get_col("SELECT ID FROM {$wpdb->users}");
    }

    /**
     * Get module statistics
     *
     * @since 1.0.0
     * @return array Module statistics
     */
    public function get_stats() {
        global $wpdb;

        $stats = [
            'field_groups' => 0,
            'fields' => 0,
            'populated_users' => 0,
            'member_types' => 0,
        ];

        if (bp_is_active('xprofile')) {
            // Count field groups (excluding default group)
            $stats['field_groups'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_xprofile_groups WHERE id > 1"
            );

            // Count fields (excluding Name field)
            $stats['fields'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_xprofile_fields 
                 WHERE type != 'option' AND id > 1"
            );

            // Count users with populated profile data
            $stats['populated_users'] = $wpdb->get_var(
                "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->base_prefix}bp_xprofile_data"
            );
        }

        // Count registered member types
        $registered_types = bp_get_member_types([], 'objects');
        $stats['member_types'] = count($registered_types);

        return $stats;
    }

    /**
     * Clean up XProfile data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function cleanup($options = []) {
        global $wpdb;

        $defaults = [
            'remove_field_groups' => true,
            'remove_profile_data' => true,
            'remove_member_types' => false, // Keep member types by default
            'dry_run' => false,
        ];

        $options = wp_parse_args($options, $defaults);
        $results = [
            'field_groups_removed' => 0,
            'fields_removed' => 0,
            'profile_data_removed' => 0,
            'member_types_removed' => 0,
        ];

        if (!$options['dry_run']) {
            if ($options['remove_profile_data']) {
                $data_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_xprofile_data");
                $wpdb->query("DELETE FROM {$wpdb->base_prefix}bp_xprofile_data");
                $results['profile_data_removed'] = $data_count;
            }

            if ($options['remove_field_groups']) {
                // Remove custom field groups (keep default group with id = 1)
                $groups = $wpdb->get_results("SELECT id FROM {$wpdb->base_prefix}bp_xprofile_groups WHERE id > 1");
                
                foreach ($groups as $group) {
                    $result = xprofile_delete_field_group($group->id);
                    if ($result) {
                        $results['field_groups_removed']++;
                    }
                }
            }
        }

        return $results;
    }
}