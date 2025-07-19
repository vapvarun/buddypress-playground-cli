<?php
/**
 * BuddyPress Playground Groups Module
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
 * Groups Module - Generate groups with realistic membership patterns
 *
 * @since 1.0.0
 */
class BP_Playground_Groups_Module extends BP_Playground_Abstract_Module {

    /**
     * Module name
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_name = 'groups';

    /**
     * Module description
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_description = 'Generate groups with realistic membership patterns and hierarchies';

    /**
     * Group types and their configurations
     *
     * @since 1.0.0
     * @var array
     */
    private $group_types = [
        'public' => [
            'weight' => 0.4,
            'status' => 'public',
            'enable_forum' => 0.7,
            'average_members' => 50,
            'activity_level' => 'high',
        ],
        'private' => [
            'weight' => 0.35,
            'status' => 'private',
            'enable_forum' => 0.5,
            'average_members' => 25,
            'activity_level' => 'medium',
        ],
        'hidden' => [
            'weight' => 0.15,
            'status' => 'hidden',
            'enable_forum' => 0.3,
            'average_members' => 12,
            'activity_level' => 'low',
        ],
        'course' => [
            'weight' => 0.1,
            'status' => 'public',
            'enable_forum' => 0.9,
            'average_members' => 30,
            'activity_level' => 'high',
        ],
    ];

    /**
     * Group categories and their names
     *
     * @since 1.0.0
     * @var array
     */
    private $group_categories = [
        'technology' => [
            'names' => [
                'Web Developers Unite', 'JavaScript Enthusiasts', 'Python Programmers',
                'Tech Innovators', 'DevOps Engineers', 'Mobile App Developers',
                'Data Scientists', 'Cybersecurity Experts', 'Cloud Computing',
                'Open Source Contributors', 'Software Architects', 'AI/ML Researchers'
            ],
            'descriptions' => [
                'A community for developers to share knowledge and collaborate on projects.',
                'Discussing the latest trends and best practices in modern development.',
                'Sharing resources, tutorials, and helping each other grow professionally.',
            ],
        ],
        'creative' => [
            'names' => [
                'Digital Artists', 'Photography Masters', 'Graphic Design Hub',
                'Creative Writers', 'Video Production', 'Music Producers',
                'UI/UX Designers', 'Illustrators Guild', 'Content Creators',
                'Marketing Creatives', 'Brand Designers', 'Animation Studio'
            ],
            'descriptions' => [
                'A space for creative professionals to showcase work and get feedback.',
                'Collaboration opportunities and inspiration for creative projects.',
                'Sharing techniques, tools, and resources for creative excellence.',
            ],
        ],
        'business' => [
            'names' => [
                'Entrepreneurs Network', 'Project Managers', 'Sales Professionals',
                'Marketing Strategy', 'Business Analytics', 'Startup Founders',
                'Digital Marketing', 'Business Development', 'Finance Professionals',
                'HR Leaders', 'Consultants Corner', 'Executive Leadership'
            ],
            'descriptions' => [
                'Networking and knowledge sharing for business professionals.',
                'Discussing strategies, trends, and best practices in business.',
                'Building connections and growing professional networks.',
            ],
        ],
        'education' => [
            'names' => [
                'Online Learning', 'Educators Hub', 'Student Community',
                'Academic Research', 'Teaching Resources', 'Lifelong Learners',
                'Professional Development', 'Course Creation', 'Study Groups',
                'Academic Writing', 'Education Technology', 'Knowledge Sharing'
            ],
            'descriptions' => [
                'Supporting education and continuous learning initiatives.',
                'Sharing teaching resources and educational best practices.',
                'Building a community of learners and educators.',
            ],
        ],
        'hobbies' => [
            'names' => [
                'Photography Club', 'Book Lovers', 'Travel Enthusiasts',
                'Fitness Motivation', 'Cooking & Recipes', 'Gaming Community',
                'Music Appreciation', 'Sports Fans', 'Outdoor Adventures',
                'Art & Crafts', 'Movie Buffs', 'Wellness & Health'
            ],
            'descriptions' => [
                'Connecting people with shared interests and hobbies.',
                'Sharing experiences, tips, and inspiration.',
                'Building friendships through common interests.',
            ],
        ],
        'local' => [
            'names' => [
                'City Network', 'Local Events', 'Community Support',
                'Neighborhood Watch', 'Local Business', 'Volunteer Group',
                'Cultural Society', 'Sports League', 'Parent Network',
                'Senior Citizens', 'Youth Program', 'Environmental Action'
            ],
            'descriptions' => [
                'Connecting local community members and organizing events.',
                'Supporting local initiatives and community development.',
                'Building stronger neighborhoods and local networks.',
            ],
        ],
    ];

    /**
     * Generate groups
     *
     * @since 1.0.0
     * @param array $args Generation arguments
     * @return array|WP_Error Generation results
     */
    public function generate($args = []) {
        if (!bp_is_active('groups')) {
            return new WP_Error('groups_disabled', __('Groups component is not active.', BP_PLAYGROUND_TEXT_DOMAIN));
        }

        $defaults = [
            'count' => 100,
            'types' => 'mixed', // Add default value
            'with_hierarchy' => false,
            'membership_patterns' => true,
            'enable_forums' => 'auto', // Add default value
            'member_distribution' => 'realistic',
            'batch_size' => 25,
        ];
    
        $args = wp_parse_args($args, $defaults);

        // Validate arguments
        $validation_rules = [
            'count' => ['type' => 'int', 'min' => 1, 'max' => 5000],
            'batch_size' => ['type' => 'int', 'min' => 1, 'max' => 100],
            'with_hierarchy' => ['type' => 'bool'],
            'membership_patterns' => ['type' => 'bool'],
        ];

        $validated_args = $this->validate_args($args, $validation_rules);
        if (is_wp_error($validated_args)) {
            return $validated_args;
        }

        $this->start_generation('Groups Creation');

        $results = [
            'groups_created' => 0,
            'memberships_created' => 0,
            'forums_created' => 0,
            'hierarchies_created' => 0,
            'errors' => [],
        ];

        try {
            // Get available users for group membership
            $user_ids = $this->get_available_user_ids();
            if (empty($user_ids)) {
                throw new Exception('No users available for group membership. Please create users first.');
            }

            $batch_processor = bp_playground_get_module('batch_processor');
            
            $callback = function($start_index, $batch_size, $options) use ($validated_args, $user_ids) {
                return $this->create_groups_batch($start_index, $batch_size, $validated_args, $user_ids);
            };

            $progress_callback = function($progress) {
                $this->show_progress(
                    $progress['processed'], 
                    $progress['total'], 
                    'Creating groups'
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

            $results['groups_created'] = $batch_result['successful_items'];
            $results['errors'] = $batch_result['errors'];

            // Create hierarchies if requested
            if ($validated_args['with_hierarchy'] && $results['groups_created'] > 10) {
                $hierarchy_results = $this->create_group_hierarchies($user_ids);
                if (!is_wp_error($hierarchy_results)) {
                    $results['hierarchies_created'] = $hierarchy_results;
                }
            }

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->log_error('Groups generation failed: ' . $e->getMessage());
        }

        $this->end_generation();
        return $results;
    }

    /**
     * Create a batch of groups
     *
     * @since 1.0.0
     * @param int $start_index Starting index
     * @param int $batch_size Batch size
     * @param array $options Generation options
     * @param array $user_ids Available user IDs
     * @return array Batch results
     */
    private function create_groups_batch($start_index, $batch_size, $options, $user_ids) {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        for ($i = 0; $i < $batch_size; $i++) {
            $group_index = $start_index + $i;
            $results['processed']++;

            try {
                $group_data = $this->generate_group_data($group_index, $options);
                $group_id = $this->create_single_group($group_data, $options, $user_ids);

                if ($group_id) {
                    $results['successful']++;
                    
                    // Update core stats
                    $core = bp_playground_get_module('core');
                    if ($core) {
                        $core->increment_stat('groups_created');
                    }
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to create group at index {$group_index}";
                }

            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error creating group at index {$group_index}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Generate group data
     *
     * @since 1.0.0
     * @param int $group_index Group index for consistent data
     * @param array $options Generation options
     * @return array Group data
     */
    private function generate_group_data($group_index, $options) {
        // Seed random generator for consistent data
        mt_srand($group_index);

        // Select group type - with proper array key checking
        $group_type = $this->select_group_type(isset($options['types']) ? $options['types'] : 'mixed');
        $type_config = $this->group_types[$group_type];

        // Select category and name
        $category = array_rand($this->group_categories);
        $category_data = $this->group_categories[$category];
        $name = $category_data['names'][array_rand($category_data['names'])];
        $description = $category_data['descriptions'][array_rand($category_data['descriptions'])];

        // Add some variation to names to avoid duplicates
        if (mt_rand(1, 10) > 7) {
            $name .= ' ' . mt_rand(1, 999);
        }

        // Generate slug
        $slug = sanitize_title($name . '-' . $group_index);

        // Determine forum enablement - with proper array key checking
        $enable_forum = false;
        $enable_forums_setting = isset($options['enable_forums']) ? $options['enable_forums'] : 'auto';
        
        if ($enable_forums_setting === true) {
            $enable_forum = true;
        } elseif ($enable_forums_setting === 'auto') {
            $enable_forum = mt_rand(1, 100) <= ($type_config['enable_forum'] * 100);
        }
        // If $enable_forums_setting is false, $enable_forum stays false

        // Reset random seed
        mt_srand();

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => $type_config['status'],
            'enable_forum' => $enable_forum,
            'group_type' => $group_type,
            'category' => $category,
            'average_members' => $type_config['average_members'],
            'activity_level' => $type_config['activity_level'],
        ];
    }

    /**
     * Create a single group
     *
     * @since 1.0.0
     * @param array $group_data Group data
     * @param array $options Creation options
     * @param array $user_ids Available user IDs
     * @return int|false Group ID on success, false on failure
     */
    private function create_single_group($group_data, $options, $user_ids) {
        // Select creator from available users
        $creator_id = $user_ids[array_rand($user_ids)];

        $group_args = [
            'name' => $group_data['name'],
            'slug' => $group_data['slug'],
            'description' => $group_data['description'],
            'status' => $group_data['status'],
            'enable_forum' => $group_data['enable_forum'],
            'creator_id' => $creator_id,
        ];

        $group_id = groups_create_group($group_args);

        if (!$group_id) {
            $this->log_error('Failed to create group: ' . $group_data['name']);
            return false;
        }

        // Add group meta
        $this->add_group_meta($group_id, $group_data);

        // Add members if membership patterns are enabled
        if ($options['membership_patterns']) {
            $this->add_group_members($group_id, $group_data, $user_ids, $creator_id);
        }

        // Create forum if bbPress is active and enabled for this group
        if ($group_data['enable_forum'] && class_exists('bbPress')) {
            $this->maybe_create_group_forum($group_id, $group_data);
        }

        return $group_id;
    }

    /**
     * Add group metadata
     *
     * @since 1.0.0
     * @param int $group_id Group ID
     * @param array $group_data Group data
     * @return void
     */
    private function add_group_meta($group_id, $group_data) {
        // Add playground identification
        groups_update_groupmeta($group_id, 'bp_playground_created', time());
        groups_update_groupmeta($group_id, 'bp_playground_type', $group_data['group_type']);
        groups_update_groupmeta($group_id, 'bp_playground_category', $group_data['category']);
        groups_update_groupmeta($group_id, 'bp_playground_activity_level', $group_data['activity_level']);

        // Add some random group settings
        $invite_options = ['members', 'mods', 'admins'];
        $settings = [
            'invite_status' => $invite_options[array_rand($invite_options)],
            'activity_feed' => mt_rand(0, 1),
            'photo_uploads' => mt_rand(0, 1),
            'cover_image' => mt_rand(0, 1),
        ];

        foreach ($settings as $key => $value) {
            groups_update_groupmeta($group_id, $key, $value);
        }

        // Set last activity
        $last_activity = date('Y-m-d H:i:s', time() - mt_rand(0, 30 * 24 * 3600)); // Random within last 30 days
        groups_update_groupmeta($group_id, 'last_activity', $last_activity);
    }

    /**
     * Add members to group with realistic patterns
     *
     * @since 1.0.0
     * @param int $group_id Group ID
     * @param array $group_data Group data
     * @param array $user_ids Available user IDs
     * @param int $creator_id Creator user ID
     * @return void
     */
    private function add_group_members($group_id, $group_data, $user_ids, $creator_id) {
        // Calculate member count based on group type and some randomness
        $base_member_count = $group_data['average_members'];
        $variation = mt_rand(-20, 30); // -20% to +30% variation
        $member_count = max(5, $base_member_count + round($base_member_count * ($variation / 100)));
        
        // Don't exceed available users
        $member_count = min($member_count, count($user_ids) - 1); // -1 for creator

        // Remove creator from potential members
        $potential_members = array_diff($user_ids, [$creator_id]);
        
        if (empty($potential_members)) {
            return;
        }

        // Select random members
        $selected_members = array_rand(array_flip($potential_members), min($member_count, count($potential_members)));
        if (!is_array($selected_members)) {
            $selected_members = [$selected_members];
        }

        // Add members with different roles
        $admin_count = max(1, round($member_count * 0.05)); // 5% admins
        $mod_count = max(1, round($member_count * 0.1));   // 10% mods
        
        $role_assignments = array_merge(
            array_fill(0, $admin_count, 'admin'),
            array_fill(0, $mod_count, 'mod'),
            array_fill(0, $member_count - $admin_count - $mod_count, 'member')
        );
        
        shuffle($role_assignments);

        foreach ($selected_members as $index => $user_id) {
            $role = isset($role_assignments[$index]) ? $role_assignments[$index] : 'member';
            
            // Add member to group
            $member_added = groups_join_group($group_id, $user_id);
            
            if ($member_added && $role !== 'member') {
                // Promote to admin or mod
                $member = new BP_Groups_Member($user_id, $group_id);
                if ($role === 'admin') {
                    $member->promote('admin');
                } elseif ($role === 'mod') {
                    $member->promote('mod');
                }
            }

            // Set join date to random time in the past
            if ($member_added) {
                global $wpdb;
                $join_date = date('Y-m-d H:i:s', time() - mt_rand(0, 365 * 24 * 3600));
                $wpdb->update(
                    $wpdb->base_prefix . 'bp_groups_members',
                    ['date_modified' => $join_date],
                    ['group_id' => $group_id, 'user_id' => $user_id],
                    ['%s'],
                    ['%d', '%d']
                );
            }
        }

        // Update group member count
        $actual_member_count = groups_get_groupmeta($group_id, 'total_member_count');
        if (!$actual_member_count) {
            groups_update_groupmeta($group_id, 'total_member_count', count($selected_members) + 1); // +1 for creator
        }
    }

    /**
     * Maybe create a forum for the group
     *
     * @since 1.0.0
     * @param int $group_id Group ID
     * @param array $group_data Group data
     * @return void
     */
    private function maybe_create_group_forum($group_id, $group_data) {
        if (!function_exists('bbp_insert_forum')) {
            return;
        }

        $forum_args = [
            'post_title' => $group_data['name'] . ' Forum',
            'post_content' => 'Discussion forum for ' . $group_data['name'],
            'post_status' => 'publish',
            'post_type' => bbp_get_forum_post_type(),
            'post_author' => 0,
            'menu_order' => 0,
        ];

        $forum_id = bbp_insert_forum($forum_args);

        if ($forum_id) {
            // Link forum to group
            groups_update_groupmeta($group_id, 'forum_id', $forum_id);
            
            // Add forum meta
            add_post_meta($forum_id, '_bbp_group_ids', [$group_id]);
            add_post_meta($forum_id, 'bp_playground_created', time());
            
            $this->log("Created forum for group: {$group_data['name']}");
        }
    }

    /**
     * Create group hierarchies
     *
     * @since 1.0.0
     * @param array $user_ids Available user IDs
     * @return int|WP_Error Number of hierarchies created
     */
    private function create_group_hierarchies($user_ids) {
        // Get recently created playground groups
        $groups = groups_get_groups([
            'meta_query' => [
                [
                    'key' => 'bp_playground_created',
                    'compare' => 'EXISTS',
                ],
            ],
            'per_page' => 50,
            'show_hidden' => true,
        ]);

        if (empty($groups['groups'])) {
            return new WP_Error('no_groups', 'No groups available for hierarchy creation');
        }

        $hierarchy_count = 0;
        $available_groups = $groups['groups'];
        
        // Create parent groups (departments/organizations)
        $parent_groups = array_slice($available_groups, 0, min(5, count($available_groups)));
        
        foreach ($parent_groups as $parent_group) {
            // Create 2-4 child groups for each parent
            $child_count = mt_rand(2, 4);
            $child_groups = array_slice($available_groups, $hierarchy_count + 5, $child_count);
            
            foreach ($child_groups as $child_group) {
                if ($child_group->id !== $parent_group->id) {
                    // Set parent relationship
                    groups_update_groupmeta($child_group->id, 'bp_playground_parent_group', $parent_group->id);
                    groups_update_groupmeta($parent_group->id, 'bp_playground_has_children', true);
                    
                    // Update child group name to reflect hierarchy
                    $child_name = $parent_group->name . ' - ' . $child_group->name;
                    groups_edit_base_group_details([
                        'group_id' => $child_group->id,
                        'name' => $child_name,
                    ]);
                    
                    $hierarchy_count++;
                }
            }
        }

        $this->log("Created {$hierarchy_count} group hierarchies");
        return $hierarchy_count;
    }

    /**
     * Select group type based on options
     *
     * @since 1.0.0
     * @param string $types Type selection mode
     * @return string Selected group type
     */
    private function select_group_type($types) {
        if ($types !== 'mixed' && isset($this->group_types[$types])) {
            return $types;
        }

        // Use weighted random selection
        $rand = mt_rand() / mt_getrandmax();
        $cumulative_weight = 0;

        foreach ($this->group_types as $type => $config) {
            $cumulative_weight += $config['weight'];
            if ($rand <= $cumulative_weight) {
                return $type;
            }
        }

        // Fallback
        return 'public';
    }

    /**
     * Get available user IDs
     *
     * @since 1.0.0
     * @return array User IDs
     */
    private function get_available_user_ids() {
        global $wpdb;

        // Prefer playground-generated users, but include all if needed
        $playground_users = $wpdb->get_col(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'bp_playground_created'"
        );

        if (count($playground_users) >= 10) {
            return $playground_users;
        }

        // Include all users if not enough playground users
        $all_users = $wpdb->get_col("SELECT ID FROM {$wpdb->users} WHERE ID > 1 LIMIT 1000");
        return array_merge($playground_users, $all_users);
    }

    /**
     * Get module statistics
     *
     * @since 1.0.0
     * @return array Module statistics
     */
    public function get_stats() {
        if (!bp_is_active('groups')) {
            return [];
        }

        global $wpdb;

        $stats = [
            'total_groups' => 0,
            'playground_groups' => 0,
            'public_groups' => 0,
            'private_groups' => 0,
            'hidden_groups' => 0,
            'groups_with_forums' => 0,
            'groups_with_hierarchies' => 0,
            'total_memberships' => 0,
        ];

        // Total groups
        $stats['total_groups'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_groups");

        // Playground groups
        $stats['playground_groups'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_groupmeta WHERE meta_key = 'bp_playground_created'"
        );

        if ($stats['playground_groups'] > 0) {
            // Get playground group IDs
            $playground_group_ids = $wpdb->get_col(
                "SELECT group_id FROM {$wpdb->base_prefix}bp_groupmeta WHERE meta_key = 'bp_playground_created'"
            );

            if (!empty($playground_group_ids)) {
                $group_ids_list = implode(',', array_map('intval', $playground_group_ids));

                // Count by status
                $status_counts = $wpdb->get_results(
                    "SELECT status, COUNT(*) as count 
                     FROM {$wpdb->base_prefix}bp_groups 
                     WHERE id IN ({$group_ids_list}) 
                     GROUP BY status"
                );

                foreach ($status_counts as $status_count) {
                    $stats[$status_count->status . '_groups'] = $status_count->count;
                }

                // Groups with forums
                $stats['groups_with_forums'] = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_groupmeta 
                     WHERE meta_key = 'forum_id' AND group_id IN ({$group_ids_list})"
                );

                // Groups with hierarchies
                $stats['groups_with_hierarchies'] = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_groupmeta 
                     WHERE meta_key = 'bp_playground_parent_group' AND group_id IN ({$group_ids_list})"
                );

                // Total memberships
                $stats['total_memberships'] = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_groups_members 
                     WHERE group_id IN ({$group_ids_list})"
                );
            }
        }

        return $stats;
    }

    /**
     * Clean up groups data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function cleanup($options = []) {
        if (!bp_is_active('groups')) {
            return ['groups_removed' => 0];
        }

        global $wpdb;

        $defaults = [
            'remove_groups' => true,
            'remove_memberships' => true,
            'remove_forums' => true,
            'older_than_days' => 0,
            'dry_run' => false,
        ];

        $options = wp_parse_args($options, $defaults);
        
        $results = [
            'groups_removed' => 0,
            'memberships_removed' => 0,
            'forums_removed' => 0,
            'meta_cleaned' => 0,
        ];

        if (!$options['remove_groups']) {
            return $results;
        }

        // Get playground groups
        $group_query = "
            SELECT g.id, g.name 
            FROM {$wpdb->base_prefix}bp_groups g 
            INNER JOIN {$wpdb->base_prefix}bp_groupmeta gm ON g.id = gm.group_id 
            WHERE gm.meta_key = 'bp_playground_created'
        ";

        if ($options['older_than_days'] > 0) {
            $timestamp = time() - ($options['older_than_days'] * 24 * 3600);
            $group_query .= $wpdb->prepare(" AND gm.meta_value < %d", $timestamp);
        }

        $groups_to_remove = $wpdb->get_results($group_query);

        if (!$options['dry_run']) {
            foreach ($groups_to_remove as $group_data) {
                // Remove associated forum if exists
                if ($options['remove_forums']) {
                    $forum_id = groups_get_groupmeta($group_data->id, 'forum_id');
                    if ($forum_id && function_exists('bbp_delete_forum')) {
                        wp_delete_post($forum_id, true);
                        $results['forums_removed']++;
                    }
                }

                // Count memberships before deletion
                $membership_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_groups_members WHERE group_id = %d",
                    $group_data->id
                ));

                // Delete group (this handles memberships automatically)
                if (groups_delete_group($group_data->id)) {
                    $results['groups_removed']++;
                    $results['memberships_removed'] += $membership_count;
                }
            }

            // Clean up any remaining meta
            $meta_cleaned = $wpdb->query(
                "DELETE FROM {$wpdb->base_prefix}bp_groupmeta WHERE meta_key LIKE 'bp_playground_%'"
            );
            $results['meta_cleaned'] = $meta_cleaned;
        } else {
            $results['groups_removed'] = count($groups_to_remove);
            // Estimate memberships for dry run
            foreach ($groups_to_remove as $group_data) {
                $membership_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_groups_members WHERE group_id = %d",
                    $group_data->id
                ));
                $results['memberships_removed'] += $membership_count;
            }
        }

        return $results;
    }
}