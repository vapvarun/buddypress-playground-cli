<?php
/**
 * BuddyPress Playground Users Module
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
 * Users Module - Generate users with realistic profiles and data
 *
 * @since 1.0.0
 */
class BP_Playground_Users_Module extends BP_Playground_Abstract_Module {

    /**
     * Module name
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_name = 'users';

    /**
     * Module description
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_description = 'Generate users with realistic profiles and data';

    /**
     * User personas for realistic data generation
     *
     * @since 1.0.0
     * @var array
     */
    private $user_personas = [
        'tech_professional' => [
            'weight' => 0.25,
            'first_names' => ['Alex', 'Jordan', 'Sam', 'Taylor', 'Morgan', 'Casey', 'Riley'],
            'last_names' => ['Smith', 'Johnson', 'Chen', 'Patel', 'Rodriguez', 'Kim', 'Anderson'],
            'domains' => ['techcorp.com', 'devstudio.com', 'codelab.io', 'innovate.tech'],
            'bio_templates' => [
                'Passionate software developer with {years} years of experience in {tech_stack}.',
                'Full-stack developer specializing in {tech_stack} and modern web technologies.',
                'Tech enthusiast and problem solver. Love building scalable applications.',
            ],
        ],
        'creative_professional' => [
            'weight' => 0.20,
            'first_names' => ['Luna', 'River', 'Sage', 'Phoenix', 'Quinn', 'Blake', 'Rowan'],
            'last_names' => ['Williams', 'Brown', 'Davis', 'Miller', 'Wilson', 'Moore', 'Taylor'],
            'domains' => ['creative.agency', 'design.studio', 'artworks.com', 'visual.co'],
            'bio_templates' => [
                'Creative designer with a passion for visual storytelling and user experience.',
                'Digital artist exploring the intersection of technology and creativity.',
                'Brand strategist helping businesses tell their stories through design.',
            ],
        ],
        'business_professional' => [
            'weight' => 0.20,
            'first_names' => ['Michael', 'Sarah', 'David', 'Jennifer', 'Robert', 'Lisa', 'James'],
            'last_names' => ['Garcia', 'Martinez', 'Lopez', 'Gonzalez', 'Hernandez', 'Young', 'King'],
            'domains' => ['bizgroup.com', 'consulting.pro', 'strategy.co', 'corporate.net'],
            'bio_templates' => [
                'Business strategist with expertise in market analysis and growth planning.',
                'Project manager passionate about delivering results and building teams.',
                'Entrepreneur focused on innovation and sustainable business practices.',
            ],
        ],
        'educator' => [
            'weight' => 0.15,
            'first_names' => ['Emma', 'William', 'Olivia', 'Benjamin', 'Sophia', 'Daniel', 'Isabella'],
            'last_names' => ['Lee', 'Walker', 'Hall', 'Allen', 'Wright', 'Scott', 'Green'],
            'domains' => ['university.edu', 'school.org', 'learning.edu', 'academy.edu'],
            'bio_templates' => [
                'Educator passionate about fostering learning and inspiring students.',
                'Academic researcher focused on {subject} and innovative teaching methods.',
                'Lifelong learner dedicated to making education accessible and engaging.',
            ],
        ],
        'student' => [
            'weight' => 0.20,
            'first_names' => ['Ethan', 'Ava', 'Noah', 'Mia', 'Lucas', 'Charlotte', 'Oliver'],
            'last_names' => ['Adams', 'Baker', 'Clark', 'Evans', 'Fisher', 'Gray', 'Hill'],
            'domains' => ['student.edu', 'university.edu', 'college.edu', 'academy.org'],
            'bio_templates' => [
                'Student pursuing {degree} with interests in {interests}.',
                'Passionate learner exploring {field} and looking for opportunities to grow.',
                'Future {profession} currently studying and building practical skills.',
            ],
        ],
    ];

    /**
     * Generate users
     *
     * @since 1.0.0
     * @param array $args Generation arguments
     * @return array|WP_Error Generation results
     */
    public function generate($args = []) {
        $defaults = [
            'count' => 1000,
            'with_avatar' => false,
            'with_cover_image' => false,
            'activation_rate' => 0.95,
            'admin_rate' => 0.02,
            'batch_size' => 50,
            'persona_distribution' => 'default',
        ];

        $args = wp_parse_args($args, $defaults);

        // Validate arguments
        $validation_rules = [
            'count' => ['type' => 'int', 'min' => 1, 'max' => 50000],
            'activation_rate' => ['type' => 'float', 'min' => 0.0, 'max' => 1.0],
            'admin_rate' => ['type' => 'float', 'min' => 0.0, 'max' => 0.1],
            'batch_size' => ['type' => 'int', 'min' => 1, 'max' => 200],
            'with_avatar' => ['type' => 'bool'],
            'with_cover_image' => ['type' => 'bool'],
        ];

        $validated_args = $this->validate_args($args, $validation_rules);
        if (is_wp_error($validated_args)) {
            return $validated_args;
        }

        $this->start_generation('User Creation');

        $results = [
            'users_created' => 0,
            'users_activated' => 0,
            'admins_created' => 0,
            'errors' => [],
        ];

        try {
            $batch_processor = bp_playground_get_module('batch_processor');
            
            $callback = function($start_index, $batch_size, $options) use ($validated_args) {
                return $this->create_user_batch($start_index, $batch_size, $validated_args);
            };
    
            $progress_callback = function($progress) {
                $this->show_progress(
                    $progress['processed'], 
                    $progress['total'], 
                    'Creating users'
                );
            };
    
            $batch_result = $batch_processor->process_in_batches(
                $validated_args['count'],
                $callback,
                $progress_callback
            );
    
            if (is_wp_error($batch_result)) {
                throw new Exception($batch_result->get_error_message());
            }
    
            $results['users_created'] = $batch_result['successful_items'];
            $results['errors'] = $batch_result['errors'];
    
            // POPULATE XPROFILE DATA AFTER ALL USERS ARE CREATED
            if (isset($validated_args['with_xprofile']) && $validated_args['with_xprofile'] && $results['users_created'] > 0) {
                $this->log('Populating XProfile data for created users...', 'info');
                
                $xprofile_module = bp_playground_get_module('xprofile');
                if ($xprofile_module) {
                    // Get the user IDs we just created
                    global $wpdb;
                    $created_user_ids = $wpdb->get_col(
                        "SELECT user_id FROM {$wpdb->usermeta} 
                         WHERE meta_key = 'bp_playground_created' 
                         ORDER BY meta_id DESC 
                         LIMIT {$results['users_created']}"
                    );
    
                    if (!empty($created_user_ids)) {
                        $populate_result = $xprofile_module->populate_profile_data($created_user_ids, 0.85);
                        if (!is_wp_error($populate_result)) {
                            $results['users_with_profiles'] = $populate_result;
                            $this->log("Populated XProfile data for {$populate_result} users", 'info');
                        }
                    }
                }
            }
    
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->log_error('User generation failed: ' . $e->getMessage());
        }
    
        $this->end_generation();
        return $results;
    }

    /**
     * Create a batch of users
     *
     * @since 1.0.0
     * @param int $start_index Starting index
     * @param int $batch_size Batch size
     * @param array $options Generation options
     * @return array Batch results
     */
    private function create_user_batch($start_index, $batch_size, $options) {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        for ($i = 0; $i < $batch_size; $i++) {
            $user_index = $start_index + $i;
            $results['processed']++;

            try {
                $user_data = $this->generate_user_data($user_index, $options);
                $user_id = $this->create_single_user($user_data, $options);

                if ($user_id) {
                    $results['successful']++;
                    
                    // Update core stats
                    $core = bp_playground_get_module('core');
                    if ($core) {
                        $core->increment_stat('users_created');
                    }
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to create user at index {$user_index}";
                }

            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error creating user at index {$user_index}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Generate user data based on persona
     *
     * @since 1.0.0
     * @param int $user_index User index for consistent data
     * @param array $options Generation options
     * @return array User data
     */
    private function generate_user_data($user_index, $options) {
        // Seed random generator for consistent data
        mt_srand($user_index);

        // Select persona based on weights
        $persona_key = $this->select_persona();
        $persona = $this->user_personas[$persona_key];

        // Generate basic user data
        $first_name = $persona['first_names'][array_rand($persona['first_names'])];
        $last_name = $persona['last_names'][array_rand($persona['last_names'])];
        $username = $this->generate_username($first_name, $last_name, $user_index);
        $email = $this->generate_email($username, $persona['domains']);

        // Determine user role - with proper array key check
        $admin_rate = isset($options['admin_rate']) ? $options['admin_rate'] : 0.02;
        $is_admin = (mt_rand() / mt_getrandmax()) < $admin_rate;
        $role = $is_admin ? 'administrator' : 'subscriber';

        // Generate bio
        $bio = $this->generate_bio($persona, $first_name);

        // Determine activation status - with proper array key check
        $activation_rate = isset($options['activation_rate']) ? $options['activation_rate'] : 0.95;
        $is_activated = (mt_rand() / mt_getrandmax()) < $activation_rate;

        // Reset random seed
        mt_srand();

        return [
            'username' => $username,
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name,
            'role' => $role,
            'bio' => $bio,
            'persona' => $persona_key,
            'is_activated' => $is_activated,
        ];
    }

    /**
     * Create a single user
     *
     * @since 1.0.0
     * @param array $user_data User data
     * @param array $options Creation options
     * @return int|false User ID on success, false on failure
     */
    private function create_single_user($user_data, $options) {
        // Check if username or email already exists
        if (username_exists($user_data['username']) || email_exists($user_data['email'])) {
            // Generate alternative username
            $user_data['username'] = $this->generate_unique_username($user_data['username']);
            $user_data['email'] = $this->generate_unique_email($user_data['email']);
        }

        $user_args = [
            'user_login' => $user_data['username'],
            'user_email' => $user_data['email'],
            'user_pass' => wp_generate_password(12, false),
            'first_name' => $user_data['first_name'],
            'last_name' => $user_data['last_name'],
            'display_name' => $user_data['display_name'],
            'role' => $user_data['role'],
            'description' => $user_data['bio'],
        ];

        $user_id = wp_insert_user($user_args);

        if (is_wp_error($user_id)) {
            $this->log_error('Failed to create user: ' . $user_id->get_error_message());
            return false;
        }

        // Set user activation status for BuddyPress
        if (function_exists('bp_core_activate_account')) {
            if ($user_data['is_activated']) {
                bp_core_activate_account($user_id);
            }
        }

        // Add user meta
        $this->add_user_meta($user_id, $user_data);

        // Handle avatar if requested - with proper array key check
        if (!empty($options['with_avatar'])) {
            $this->maybe_add_avatar($user_id);
        }

        // Handle cover image if requested - with proper array key check
        if (!empty($options['with_cover_image'])) {
            $this->maybe_add_cover_image($user_id);
        }

        return $user_id;
    }

    /**
     * Add user meta data
     *
     * @since 1.0.0
     * @param int $user_id User ID
     * @param array $user_data User data
     * @return void
     */
    private function add_user_meta($user_id, $user_data) {
        // Add persona for later reference
        update_user_meta($user_id, 'bp_playground_persona', $user_data['persona']);
        
        // Add generation timestamp
        update_user_meta($user_id, 'bp_playground_created', time());
        
        // Set last activity time to a random time in the past week
        if (function_exists('bp_update_user_last_activity')) {
            $last_activity = date('Y-m-d H:i:s', time() - rand(0, 7 * 24 * 3600));
            bp_update_user_last_activity($user_id, $last_activity);
        }

        // Add BuddyPress specific meta
        if (bp_is_active('members')) {
            // Set member type if XProfile module has created them
            $member_types = bp_get_member_types();
            if (!empty($member_types)) {
                $persona_mapping = [
                    'tech_professional' => 'professional',
                    'creative_professional' => 'professional', 
                    'business_professional' => 'professional',
                    'educator' => 'instructor',
                    'student' => 'student',
                ];

                $member_type = isset($persona_mapping[$user_data['persona']]) 
                    ? $persona_mapping[$user_data['persona']] 
                    : 'professional';

                if (isset($member_types[$member_type])) {
                    bp_set_member_type($user_id, $member_type);
                }
            }
        }
    }

    /**
     * Select persona based on weights
     *
     * @since 1.0.0
     * @return string Selected persona key
     */
    private function select_persona() {
        $rand = mt_rand() / mt_getrandmax();
        $cumulative_weight = 0;

        foreach ($this->user_personas as $persona_key => $persona) {
            $cumulative_weight += $persona['weight'];
            if ($rand <= $cumulative_weight) {
                return $persona_key;
            }
        }

        // Fallback to first persona
        return array_keys($this->user_personas)[0];
    }

    /**
     * Generate username
     *
     * @since 1.0.0
     * @param string $first_name First name
     * @param string $last_name Last name
     * @param int $index User index
     * @return string Generated username
     */
    private function generate_username($first_name, $last_name, $index) {
        $username_patterns = [
            strtolower($first_name . $last_name),
            strtolower($first_name . '.' . $last_name),
            strtolower($first_name . '_' . $last_name),
            strtolower(substr($first_name, 0, 1) . $last_name),
            strtolower($first_name . substr($last_name, 0, 1)),
        ];

        $base_username = $username_patterns[array_rand($username_patterns)];
        
        // Add random number for uniqueness
        $username = $base_username . rand(10, 999);
        
        // Ensure it's not too long
        if (strlen($username) > 60) {
            $username = substr($username, 0, 57) . rand(10, 99);
        }

        return $username;
    }

    /**
     * Generate unique username
     *
     * @since 1.0.0
     * @param string $base_username Base username
     * @return string Unique username
     */
    private function generate_unique_username($base_username) {
        $counter = 1;
        $username = $base_username;

        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
            
            // Prevent infinite loop
            if ($counter > 1000) {
                $username = $base_username . rand(1000, 9999);
                break;
            }
        }

        return $username;
    }

    /**
     * Generate email
     *
     * @since 1.0.0
     * @param string $username Username
     * @param array $domains Available domains
     * @return string Generated email
     */
    private function generate_email($username, $domains) {
        $domain = $domains[array_rand($domains)];
        return $username . '@' . $domain;
    }

    /**
     * Generate unique email
     *
     * @since 1.0.0
     * @param string $base_email Base email
     * @return string Unique email
     */
    private function generate_unique_email($base_email) {
        $parts = explode('@', $base_email);
        $local = $parts[0];
        $domain = $parts[1];
        
        $counter = 1;
        $email = $base_email;

        while (email_exists($email)) {
            $email = $local . $counter . '@' . $domain;
            $counter++;
            
            // Prevent infinite loop
            if ($counter > 1000) {
                $email = $local . rand(1000, 9999) . '@' . $domain;
                break;
            }
        }

        return $email;
    }

    /**
     * Generate bio based on persona
     *
     * @since 1.0.0
     * @param array $persona Persona data
     * @param string $first_name User's first name
     * @return string Generated bio
     */
    private function generate_bio($persona, $first_name) {
        $template = $persona['bio_templates'][array_rand($persona['bio_templates'])];
        
        // Replace placeholders
        $replacements = [
            '{years}' => rand(2, 15),
            '{tech_stack}' => $this->get_random_tech_stack(),
            '{subject}' => $this->get_random_subject(),
            '{interests}' => $this->get_random_interests(),
            '{degree}' => $this->get_random_degree(),
            '{field}' => $this->get_random_field(),
            '{profession}' => $this->get_random_profession(),
        ];

        $bio = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        return $bio;
    }

    /**
     * Get random tech stack
     *
     * @since 1.0.0
     * @return string Random tech stack
     */
    private function get_random_tech_stack() {
        $stacks = [
            'PHP and WordPress',
            'JavaScript and React',
            'Python and Django',
            'Node.js and Express',
            'Java and Spring',
            'C# and .NET',
            'Ruby on Rails',
            'Vue.js and Laravel',
        ];

        return $stacks[array_rand($stacks)];
    }

    /**
     * Get random academic subject
     *
     * @since 1.0.0
     * @return string Random subject
     */
    private function get_random_subject() {
        $subjects = [
            'Computer Science',
            'Digital Marketing',
            'Psychology',
            'Business Administration',
            'Data Science',
            'Education Technology',
            'Graphic Design',
            'Project Management',
        ];

        return $subjects[array_rand($subjects)];
    }

    /**
     * Get random interests
     *
     * @since 1.0.0
     * @return string Random interests
     */
    private function get_random_interests() {
        $interests = [
            'technology and innovation',
            'design and creativity',
            'business and entrepreneurship',
            'education and learning',
            'travel and photography',
            'music and arts',
            'sports and fitness',
            'reading and writing',
        ];

        $selected = array_rand($interests, rand(2, 3));
        if (!is_array($selected)) {
            $selected = [$selected];
        }

        $result = [];
        foreach ($selected as $index) {
            $result[] = $interests[$index];
        }

        return implode(', ', $result);
    }

    /**
     * Get random degree
     *
     * @since 1.0.0
     * @return string Random degree
     */
    private function get_random_degree() {
        $degrees = [
            'Computer Science',
            'Business Administration',
            'Marketing',
            'Graphic Design',
            'Psychology',
            'Education',
            'Engineering',
            'Communications',
        ];

        return $degrees[array_rand($degrees)];
    }

    /**
     * Get random field
     *
     * @since 1.0.0
     * @return string Random field
     */
    private function get_random_field() {
        $fields = [
            'web development',
            'digital marketing',
            'user experience design',
            'data analysis',
            'project management',
            'content creation',
            'business strategy',
            'education technology',
        ];

        return $fields[array_rand($fields)];
    }

    /**
     * Get random profession
     *
     * @since 1.0.0
     * @return string Random profession
     */
    private function get_random_profession() {
        $professions = [
            'software developer',
            'digital marketer',
            'UX designer',
            'data scientist',
            'project manager',
            'content creator',
            'business analyst',
            'educator',
        ];

        return $professions[array_rand($professions)];
    }

    /**
     * Maybe add avatar for user
     *
     * @since 1.0.0
     * @param int $user_id User ID
     * @return void
     */
    private function maybe_add_avatar($user_id) {
        // This would integrate with avatar generation or placeholder services
        // For now, we'll just add a flag that avatar was requested
        update_user_meta($user_id, 'bp_playground_has_avatar', true);
    }

    /**
     * Maybe add cover image for user
     *
     * @since 1.0.0
     * @param int $user_id User ID
     * @return void
     */
    private function maybe_add_cover_image($user_id) {
        // This would integrate with cover image generation
        // For now, we'll just add a flag that cover image was requested
        update_user_meta($user_id, 'bp_playground_has_cover', true);
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
            'total_users' => 0,
            'playground_users' => 0,
            'activated_users' => 0,
            'admin_users' => 0,
            'users_with_avatars' => 0,
            'users_with_covers' => 0,
        ];

        // Total users
        $stats['total_users'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");

        // Playground generated users
        $stats['playground_users'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'bp_playground_created'"
        );

        // Users with avatars (placeholder)
        $stats['users_with_avatars'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'bp_playground_has_avatar'"
        );

        // Users with cover images (placeholder)
        $stats['users_with_covers'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'bp_playground_has_cover'"
        );

        // Admin users (playground generated)
        $admin_user_ids = $wpdb->get_col(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'bp_playground_created'"
        );

        if (!empty($admin_user_ids)) {
            $admin_count = 0;
            foreach ($admin_user_ids as $user_id) {
                $user = new WP_User($user_id);
                if ($user->has_cap('administrator')) {
                    $admin_count++;
                }
            }
            $stats['admin_users'] = $admin_count;
        }

        return $stats;
    }

    /**
     * Clean up users data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function cleanup($options = []) {
        global $wpdb;

        $defaults = [
            'remove_users' => true,
            'older_than_days' => 0,
            'dry_run' => false,
            'preserve_admins' => true,
        ];

        $options = wp_parse_args($options, $defaults);
        
        $results = [
            'users_removed' => 0,
            'meta_cleaned' => 0,
        ];

        if (!$options['remove_users']) {
            return $results;
        }

        // Get playground users
        $user_query = "
            SELECT u.ID, u.user_login 
            FROM {$wpdb->users} u 
            INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id 
            WHERE um.meta_key = 'bp_playground_created'
        ";

        if ($options['older_than_days'] > 0) {
            $timestamp = time() - ($options['older_than_days'] * 24 * 3600);
            $user_query .= $wpdb->prepare(" AND um.meta_value < %d", $timestamp);
        }

        $users_to_remove = $wpdb->get_results($user_query);

        if (!$options['dry_run']) {
            foreach ($users_to_remove as $user_data) {
                // Check if we should preserve admins
                if ($options['preserve_admins']) {
                    $user = new WP_User($user_data->ID);
                    if ($user->has_cap('administrator')) {
                        continue;
                    }
                }

                // Remove user and all associated data
                if (wp_delete_user($user_data->ID)) {
                    $results['users_removed']++;
                }
            }

            // Clean up any remaining meta
            $meta_cleaned = $wpdb->query(
                "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'bp_playground_%'"
            );
            $results['meta_cleaned'] = $meta_cleaned;
        }

        return $results;
    }
}