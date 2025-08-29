<?php
/**
 * BuddyPress Playground Activities Module
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
 * Activities Module - Generate realistic activity streams with engagement
 *
 * @since 1.0.0
 */
class BP_Playground_Activities_Module extends BP_Playground_Abstract_Module {

    /**
     * Module name
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_name = 'activities';

    /**
     * Module description
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_description = 'Generate realistic activity streams with mentions, comments, and favorites';

    /**
     * Activity types and their configurations
     *
     * @since 1.0.0
     * @var array
     */
    private $activity_types = [
        'activity_update' => [
            'weight' => 0.4,
            'component' => 'activity',
            'type' => 'activity_update',
            'templates' => [
                'Just finished working on an exciting new project! 🚀',
                'Had a great meeting with the team today. Lots of good ideas!',
                'Learning something new every day. Love the journey! 📚',
                'Beautiful day outside. Perfect for some inspiration! ☀️',
                'Grateful for all the opportunities coming my way.',
                'Working on improving my skills. Practice makes perfect!',
                'Excited about the upcoming conference. Can\'t wait to learn!',
                'Team collaboration at its finest. Amazing what we can achieve together!',
            ],
        ],
        'new_member' => [
            'weight' => 0.1,
            'component' => 'members',
            'type' => 'new_member',
            'templates' => [
                'Welcome to the community!',
            ],
        ],
        'friendship_created' => [
            'weight' => 0.1,
            'component' => 'friends',
            'type' => 'friendship_created',
            'templates' => [
                'New friendship formed!',
            ],
        ],
        'joined_group' => [
            'weight' => 0.15,
            'component' => 'groups',
            'type' => 'joined_group',
            'templates' => [
                'Joined a new group!',
            ],
        ],
        'group_activity_update' => [
            'weight' => 0.15,
            'component' => 'groups',
            'type' => 'activity_update',
            'templates' => [
                'Sharing some thoughts with the group!',
                'Found this interesting article to share with everyone.',
                'Looking forward to our next group discussion.',
                'Great to be part of such an engaged community!',
            ],
        ],
        'updated_profile' => [
            'weight' => 0.05,
            'component' => 'xprofile',
            'type' => 'updated_profile',
            'templates' => [
                'Updated profile information.',
            ],
        ],
        'new_blog_post' => [
            'weight' => 0.05,
            'component' => 'blogs',
            'type' => 'new_blog_post',
            'templates' => [
                'Published a new blog post!',
            ],
        ],
    ];

    /**
     * Content templates for different contexts
     *
     * @since 1.0.0
     * @var array
     */
    private $content_templates = [
        'professional' => [
            'Just wrapped up an amazing workshop on {topic}. So many insights to apply!',
            'Excited to announce our team\'s latest project milestone. Hard work pays off! 💪',
            'Attending the {event} conference next week. Who else will be there?',
            'Sharing some resources I found helpful for {skill}. Hope others find them useful too!',
            'Grateful for the mentorship and guidance from amazing colleagues.',
            'Working late but loving every minute of this challenging project.',
        ],
        'educational' => [
            'Today\'s lesson on {topic} was incredibly enlightening. Education never stops!',
            'Completed another course module. Learning is a lifelong journey! 📖',
            'Preparing for tomorrow\'s presentation. Research is so fascinating.',
            'Found an excellent resource for {subject}. Knowledge sharing is powerful.',
            'Study group session was productive. Collaboration makes learning easier.',
            'Research findings are looking promising. Science is amazing!',
        ],
        'creative' => [
            'Just finished a new {creative_work}. Creativity flows when you follow your passion! 🎨',
            'Inspiration strikes at the most unexpected moments.',
            'Collaborating with fellow creatives on an exciting project.',
            'Experimenting with new techniques. Growth happens outside comfort zones.',
            'Art has the power to connect us all. Grateful for this community.',
            'Behind the scenes of today\'s creative process. Magic in the making!',
        ],
        'casual' => [
            'Beautiful weather today! Perfect for some outdoor inspiration. ☀️',
            'Coffee and contemplation. Sometimes the best ideas come quietly. ☕',
            'Weekend plans include some well-deserved relaxation.',
            'Grateful for the little moments that make life special.',
            'Trying something new today. Adventure awaits!',
            'Good vibes and positive energy all around. 😊',
        ],
    ];

    /**
     * Hashtag collections
     *
     * @since 1.0.0
     * @var array
     */
    private $hashtags = [
        'professional' => ['#productivity', '#teamwork', '#innovation', '#growth', '#success', '#leadership'],
        'educational' => ['#learning', '#education', '#knowledge', '#research', '#study', '#development'],
        'creative' => ['#creativity', '#design', '#art', '#inspiration', '#passion', '#innovation'],
        'technology' => ['#tech', '#coding', '#development', '#programming', '#webdev', '#innovation'],
        'general' => ['#community', '#collaboration', '#sharing', '#networking', '#grateful', '#motivation'],
    ];

    /**
     * Generate activities
     *
     * @since 1.0.0
     * @param array $args Generation arguments
     * @return array|WP_Error Generation results
     */
    public function generate($args = []) {
        if (!bp_is_active('activity')) {
            return new WP_Error('activity_disabled', __('Activity component is not active.', BP_PLAYGROUND_TEXT_DOMAIN));
        }

        $defaults = [
            'count' => 10000,
            'with_mentions' => true,
            'with_comments' => true,
            'with_favorites' => true,
            'favorite_rate' => 0.15,
            'comment_rate' => 0.25,
            'mention_rate' => 0.1,
            'timeframe_days' => 90,
            'batch_size' => 50,
        ];

        $args = wp_parse_args($args, $defaults);

        // Validate arguments
        $validation_rules = [
            'count' => ['type' => 'int', 'min' => 1, 'max' => 1000000],
            'favorite_rate' => ['type' => 'float', 'min' => 0.0, 'max' => 1.0],
            'comment_rate' => ['type' => 'float', 'min' => 0.0, 'max' => 1.0],
            'mention_rate' => ['type' => 'float', 'min' => 0.0, 'max' => 1.0],
            'timeframe_days' => ['type' => 'int', 'min' => 1, 'max' => 365],
            'batch_size' => ['type' => 'int', 'min' => 1, 'max' => 200],
        ];

        $validated_args = $this->validate_args($args, $validation_rules);
        if (is_wp_error($validated_args)) {
            return $validated_args;
        }

        $this->start_generation('Activity Stream Creation');

        $results = [
            'activities_created' => 0,
            'comments_created' => 0,
            'favorites_created' => 0,
            'mentions_created' => 0,
            'errors' => [],
        ];

        try {
            // Get available users and groups
            $user_ids = $this->get_available_user_ids();
            $group_ids = $this->get_available_group_ids();

            if (empty($user_ids)) {
                throw new Exception('No users available for activity creation. Please create users first.');
            }

            // Generate temporal distribution
            $data_model = bp_playground_get_module('data_model');
            $temporal_patterns = $data_model->generate_temporal_patterns(
                $validated_args['timeframe_days'], 
                $validated_args['count']
            );

            $batch_processor = bp_playground_get_module('batch_processor');
            
            $callback = function($start_index, $batch_size, $options) use ($validated_args, $user_ids, $group_ids, $temporal_patterns) {
                return $this->create_activities_batch($start_index, $batch_size, $validated_args, $user_ids, $group_ids, $temporal_patterns);
            };

            $progress_callback = function($progress) {
                $this->show_progress(
                    $progress['processed'], 
                    $progress['total'], 
                    'Creating activities'
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

            $results['activities_created'] = $batch_result['successful_items'];
            $results['errors'] = $batch_result['errors'];

            // Add engagement (comments, favorites) if activities were created
            if ($results['activities_created'] > 0) {
                $engagement_results = $this->add_activity_engagement($validated_args, $user_ids);
                if (!is_wp_error($engagement_results)) {
                    $results = array_merge($results, $engagement_results);
                }
            }

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->log_error('Activities generation failed: ' . $e->getMessage());
        }

        $this->end_generation();
        return $results;
    }

    /**
     * Create a batch of activities
     *
     * @since 1.0.0
     * @param int $start_index Starting index
     * @param int $batch_size Batch size
     * @param array $options Generation options
     * @param array $user_ids Available user IDs
     * @param array $group_ids Available group IDs
     * @param array $temporal_patterns Temporal patterns
     * @return array Batch results
     */
    private function create_activities_batch($start_index, $batch_size, $options, $user_ids, $group_ids, $temporal_patterns) {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        for ($i = 0; $i < $batch_size; $i++) {
            $activity_index = $start_index + $i;
            $results['processed']++;

            try {
                $activity_data = $this->generate_activity_data($activity_index, $options, $user_ids, $group_ids, $temporal_patterns);
                $activity_id = $this->create_single_activity($activity_data, $options);

                if ($activity_id) {
                    $results['successful']++;
                    
                    // Update core stats
                    $core = bp_playground_get_module('core');
                    if ($core) {
                        $core->increment_stat('activities_created');
                    }
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to create activity at index {$activity_index}";
                }

            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error creating activity at index {$activity_index}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Generate activity data
     *
     * @since 1.0.0
     * @param int $activity_index Activity index
     * @param array $options Generation options
     * @param array $user_ids Available user IDs
     * @param array $group_ids Available group IDs
     * @param array $temporal_patterns Temporal patterns
     * @return array Activity data
     */
    private function generate_activity_data($activity_index, $options, $user_ids, $group_ids, $temporal_patterns) {
        // Seed random generator for consistent data
        mt_srand($activity_index);

        // Select activity type
        $activity_type = $this->select_activity_type();
        $type_config = $this->activity_types[$activity_type];

        // Select user
        $user_id = $user_ids[array_rand($user_ids)];

        // Get timestamp from temporal patterns
        $timestamp = isset($temporal_patterns[$activity_index]) 
            ? $temporal_patterns[$activity_index] 
            : $this->generate_random_datetime('-90 days', 'now');

        // Generate content
        $content = $this->generate_activity_content($activity_type, $user_id, $group_ids, $options);

        // Select group if group activity
        $group_id = 0;
        if (in_array($activity_type, ['joined_group', 'group_activity_update']) && !empty($group_ids)) {
            $group_id = $group_ids[array_rand($group_ids)];
        }

        // Reset random seed
        mt_srand();

        return [
            'user_id' => $user_id,
            'component' => $type_config['component'],
            'type' => $type_config['type'],
            'action' => $this->generate_activity_action($activity_type, $user_id, $group_id),
            'content' => $content,
            'primary_link' => $this->generate_primary_link($activity_type, $user_id, $group_id),
            'item_id' => $this->get_item_id($activity_type, $user_id, $group_id),
            'secondary_item_id' => 0,
            'date_recorded' => $timestamp,
            'hide_sitewide' => $this->should_hide_sitewide($activity_type),
        ];
    }

    /**
     * Create a single activity
     *
     * @since 1.0.0
     * @param array $activity_data Activity data
     * @param array $options Creation options
     * @return int|false Activity ID on success, false on failure
     */
    private function create_single_activity($activity_data, $options) {
        $activity_id = bp_activity_add($activity_data);

        if (!$activity_id) {
            return false;
        }

        // Add activity meta
        $this->add_activity_meta($activity_id, $activity_data);

        return $activity_id;
    }

    /**
     * Add activity metadata
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID
     * @param array $activity_data Activity data
     * @return void
     */
    private function add_activity_meta($activity_id, $activity_data) {
        // Add playground identification
        bp_activity_update_meta($activity_id, 'bp_playground_created', time());
        bp_activity_update_meta($activity_id, 'bp_playground_type', $activity_data['type']);

        // Add engagement tracking meta
        bp_activity_update_meta($activity_id, 'bp_playground_engagement_score', mt_rand(1, 100));
    }

    /**
     * Add engagement to activities (comments, favorites, mentions)
     *
     * @since 1.0.0
     * @param array $options Generation options
     * @param array $user_ids Available user IDs
     * @return array Engagement results
     */
    private function add_activity_engagement($options, $user_ids) {
        // Ensure options have defaults
        $defaults = [
            'with_mentions' => true,
            'with_comments' => true,
            'with_favorites' => true,
            'mention_rate' => 0.1,
            'comment_rate' => 0.25,
            'favorite_rate' => 0.15,
        ];
        $options = wp_parse_args($options, $defaults);
        
        $results = [
            'comments_created' => 0,
            'favorites_created' => 0,
            'mentions_created' => 0,
        ];

        // Get recently created playground activities
        $activities = bp_activity_get([
            'meta_query' => [
                [
                    'key' => 'bp_playground_created',
                    'compare' => 'EXISTS',
                ],
            ],
            'per_page' => 1000,
            'show_hidden' => true,
        ]);

        if (empty($activities['activities'])) {
            return $results;
        }

        $data_model = bp_playground_get_module('data_model');

        // Generate engagement patterns
        $activity_ids = wp_list_pluck($activities['activities'], 'id');

        // Add favorites
        if ($options['with_favorites']) {
            $favorite_patterns = $data_model->generate_engagement_patterns(
                $activity_ids, 
                $user_ids, 
                'favorites'
            );

            foreach ($favorite_patterns as $activity_id => $favoriting_users) {
                $actual_favorites = array_slice($favoriting_users, 0, round(count($favoriting_users) * $options['favorite_rate']));
                foreach ($actual_favorites as $user_id) {
                    if (function_exists('bp_activity_add_user_favorite')) {
                        bp_activity_add_user_favorite($activity_id, $user_id);
                        $results['favorites_created']++;
                    }
                }
            }
        }

        // Add comments
        if ($options['with_comments']) {
            $comment_patterns = $data_model->generate_engagement_patterns(
                $activity_ids, 
                $user_ids, 
                'comments'
            );

            foreach ($comment_patterns as $activity_id => $commenting_users) {
                $actual_commenters = array_slice($commenting_users, 0, round(count($commenting_users) * $options['comment_rate']));
                foreach ($actual_commenters as $user_id) {
                    $comment_content = $this->generate_comment_content();
                    $comment_id = bp_activity_new_comment([
                        'activity_id' => $activity_id,
                        'content' => $comment_content,
                        'user_id' => $user_id,
                    ]);

                    if ($comment_id) {
                        bp_activity_update_meta($comment_id, 'bp_playground_created', time());
                        $results['comments_created']++;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Select activity type based on weights
     *
     * @since 1.0.0
     * @return string Selected activity type
     */
    private function select_activity_type() {
        $rand = mt_rand() / mt_getrandmax();
        $cumulative_weight = 0;

        foreach ($this->activity_types as $type => $config) {
            $cumulative_weight += $config['weight'];
            if ($rand <= $cumulative_weight) {
                return $type;
            }
        }

        // Fallback
        return 'activity_update';
    }

    /**
     * Generate activity content
     *
     * @since 1.0.0
     * @param string $activity_type Activity type
     * @param int $user_id User ID
     * @param array $group_ids Available group IDs
     * @param array $options Generation options
     * @return string Generated content
     */
    private function generate_activity_content($activity_type, $user_id, $group_ids, $options) {
        // Ensure options have defaults
        $defaults = [
            'with_mentions' => true,
            'with_comments' => true,
            'with_favorites' => true,
            'mention_rate' => 0.1,
            'comment_rate' => 0.25,
            'favorite_rate' => 0.15,
        ];
        $options = wp_parse_args($options, $defaults);
        
        $type_config = $this->activity_types[$activity_type];
        
        if (count($type_config['templates']) === 1 && $type_config['templates'][0] === 'Welcome to the community!') {
            return ''; // No content for system activities
        }

        // Use sample data for content generation
        $content = BP_Playground_Sample_Data::generate_activity_content($activity_type);

        // Add mentions if enabled
        if ($options['with_mentions'] && mt_rand(1, 100) <= ($options['mention_rate'] * 100)) {
            $content = $this->add_mentions_to_content($content, $user_id);
        }

        // Add hashtags occasionally
        if (mt_rand(1, 100) <= 30) { // 30% chance
            $user_persona = get_user_meta($user_id, 'bp_playground_persona', true);
            $context = $this->get_content_context($user_persona);
            $content = $this->add_hashtags_to_content($content, $context);
        }

        return $content;
    }

    /**
     * Get content context based on user persona
     *
     * @since 1.0.0
     * @param string $persona User persona
     * @return string Content context
     */
    private function get_content_context($persona) {
        $context_mapping = [
            'tech_professional' => 'professional',
            'creative_professional' => 'creative',
            'business_professional' => 'professional',
            'educator' => 'educational',
            'student' => 'educational',
        ];

        return isset($context_mapping[$persona]) ? $context_mapping[$persona] : 'casual';
    }

    /**
     * Replace content placeholders
     *
     * @since 1.0.0
     * @param string $template Content template
     * @param string $context Content context
     * @return string Content with placeholders replaced
     */
    private function replace_content_placeholders($template, $context) {
        $replacements = [
            '{topic}' => $this->get_random_topic($context),
            '{event}' => $this->get_random_event($context),
            '{skill}' => $this->get_random_skill($context),
            '{subject}' => $this->get_random_subject($context),
            '{creative_work}' => $this->get_random_creative_work(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Get random topic based on context
     *
     * @since 1.0.0
     * @param string $context Content context
     * @return string Random topic
     */
    private function get_random_topic($context) {
        $topics = [
            'professional' => ['project management', 'team leadership', 'digital transformation', 'innovation'],
            'educational' => ['machine learning', 'data science', 'research methodology', 'educational technology'],
            'creative' => ['design thinking', 'visual storytelling', 'creative process', 'artistic expression'],
            'casual' => ['productivity', 'work-life balance', 'personal growth', 'mindfulness'],
        ];

        $context_topics = isset($topics[$context]) ? $topics[$context] : $topics['casual'];
        return $context_topics[array_rand($context_topics)];
    }

    /**
     * Get random event based on context
     *
     * @since 1.0.0
     * @param string $context Content context
     * @return string Random event
     */
    private function get_random_event($context) {
        $events = [
            'professional' => ['TechCrunch Disrupt', 'SXSW', 'CES', 'Web Summit'],
            'educational' => ['Academic Conference', 'Research Symposium', 'Educational Summit', 'Learning Expo'],
            'creative' => ['Design Conference', 'Creative Week', 'Art Festival', 'Innovation Summit'],
            'casual' => ['Community Meetup', 'Local Conference', 'Workshop Series', 'Networking Event'],
        ];

        $context_events = isset($events[$context]) ? $events[$context] : $events['casual'];
        return $context_events[array_rand($context_events)];
    }

    /**
     * Get random skill based on context
     *
     * @since 1.0.0
     * @param string $context Content context
     * @return string Random skill
     */
    private function get_random_skill($context) {
        $skills = [
            'professional' => ['project management', 'data analysis', 'strategic planning', 'team leadership'],
            'educational' => ['research', 'curriculum development', 'assessment design', 'educational technology'],
            'creative' => ['design', 'storytelling', 'visual communication', 'creative problem-solving'],
            'casual' => ['communication', 'time management', 'problem-solving', 'collaboration'],
        ];

        $context_skills = isset($skills[$context]) ? $skills[$context] : $skills['casual'];
        return $context_skills[array_rand($context_skills)];
    }

    /**
     * Get random subject based on context
     *
     * @since 1.0.0
     * @param string $context Content context
     * @return string Random subject
     */
    private function get_random_subject($context) {
        $subjects = [
            'professional' => ['business strategy', 'digital marketing', 'data analytics', 'innovation management'],
            'educational' => ['computer science', 'psychology', 'education', 'research methodology'],
            'creative' => ['graphic design', 'digital art', 'photography', 'creative writing'],
            'casual' => ['personal development', 'wellness', 'productivity', 'lifestyle'],
        ];

        $context_subjects = isset($subjects[$context]) ? $subjects[$context] : $subjects['casual'];
        return $context_subjects[array_rand($context_subjects)];
    }

    /**
     * Get random creative work
     *
     * @since 1.0.0
     * @return string Random creative work
     */
    private function get_random_creative_work() {
        $works = ['design', 'illustration', 'photography', 'video', 'article', 'presentation', 'infographic', 'logo'];
        return $works[array_rand($works)];
    }

    /**
     * Add mentions to content
     *
     * @since 1.0.0
     * @param string $content Original content
     * @param int $current_user_id Current user ID (to exclude from mentions)
     * @return string Content with mentions added
     */
    private function add_mentions_to_content($content, $current_user_id) {
        $user_ids = $this->get_available_user_ids();
        $potential_mentions = array_diff($user_ids, [$current_user_id]);
        
        if (empty($potential_mentions)) {
            return $content;
        }

        // Add 1-2 mentions
        $mention_count = mt_rand(1, 2);
        $mentioned_users = array_rand(array_flip($potential_mentions), min($mention_count, count($potential_mentions)));
        
        if (!is_array($mentioned_users)) {
            $mentioned_users = [$mentioned_users];
        }

        $mention_phrases = [
            'Thanks to @%s for the inspiration!',
            'Shoutout to @%s for the great collaboration.',
            'Working with @%s on this project.',
            '@%s might find this interesting.',
            'Learned this from @%s.',
        ];

        foreach ($mentioned_users as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                $phrase = sprintf($mention_phrases[array_rand($mention_phrases)], $user->user_login);
                $content .= ' ' . $phrase;
            }
        }

        return $content;
    }

    /**
     * Add hashtags to content
     *
     * @since 1.0.0
     * @param string $content Original content
     * @param string $context Content context
     * @return string Content with hashtags added
     */
    private function add_hashtags_to_content($content, $context) {
        $available_hashtags = [];
        
        // Add context-specific hashtags
        if (isset($this->hashtags[$context])) {
            $available_hashtags = array_merge($available_hashtags, $this->hashtags[$context]);
        }
        
        // Add general hashtags
        $available_hashtags = array_merge($available_hashtags, $this->hashtags['general']);

        // Select 1-3 hashtags
        $hashtag_count = mt_rand(1, 3);
        $selected_hashtags = array_rand(array_flip($available_hashtags), min($hashtag_count, count($available_hashtags)));
        
        if (!is_array($selected_hashtags)) {
            $selected_hashtags = [$selected_hashtags];
        }

        $hashtag_string = ' ' . implode(' ', $selected_hashtags);
        return $content . $hashtag_string;
    }

    /**
     * Generate activity action text
     *
     * @since 1.0.0
     * @param string $activity_type Activity type
     * @param int $user_id User ID
     * @param int $group_id Group ID (if applicable)
     * @return string Activity action
     */
    private function generate_activity_action($activity_type, $user_id, $group_id = 0) {
        $user = get_userdata($user_id);
        $user_link = function_exists('bp_members_get_user_url') ? bp_members_get_user_url($user_id) : bp_core_get_user_domain($user_id);
        $user_name = $user->display_name;

        switch ($activity_type) {
            case 'activity_update':
                return sprintf('<a href="%s">%s</a> posted an update', $user_link, $user_name);

            case 'new_member':
                return sprintf('<a href="%s">%s</a> became a registered member', $user_link, $user_name);

            case 'friendship_created':
                return sprintf('<a href="%s">%s</a> and someone are now friends', $user_link, $user_name);

            case 'joined_group':
                if ($group_id && function_exists('groups_get_group')) {
                    $group = groups_get_group($group_id);
                    $group_link = function_exists('bp_get_group_url') ? bp_get_group_url($group) : bp_get_group_permalink($group);
                    return sprintf('<a href="%s">%s</a> joined the group <a href="%s">%s</a>', 
                        $user_link, $user_name, $group_link, $group->name);
                }
                return sprintf('<a href="%s">%s</a> joined a group', $user_link, $user_name);

            case 'group_activity_update':
                if ($group_id && function_exists('groups_get_group')) {
                    $group = groups_get_group($group_id);
                    $group_link = function_exists('bp_get_group_url') ? bp_get_group_url($group) : bp_get_group_permalink($group);
                    return sprintf('<a href="%s">%s</a> posted an update in the group <a href="%s">%s</a>', 
                        $user_link, $user_name, $group_link, $group->name);
                }
                return sprintf('<a href="%s">%s</a> posted a group update', $user_link, $user_name);

            case 'updated_profile':
                return sprintf('<a href="%s">%s</a> updated their profile', $user_link, $user_name);

            case 'new_blog_post':
                return sprintf('<a href="%s">%s</a> wrote a new blog post', $user_link, $user_name);

            default:
                return sprintf('<a href="%s">%s</a> was active', $user_link, $user_name);
        }
    }

    /**
     * Generate primary link for activity
     *
     * @since 1.0.0
     * @param string $activity_type Activity type
     * @param int $user_id User ID
     * @param int $group_id Group ID (if applicable)
     * @return string Primary link
     */
    private function generate_primary_link($activity_type, $user_id, $group_id = 0) {
        switch ($activity_type) {
            case 'joined_group':
            case 'group_activity_update':
                if ($group_id) {
                    $group = groups_get_group($group_id);
                    return function_exists('bp_get_group_url') ? bp_get_group_url($group) : (function_exists('bp_get_group_permalink') ? bp_get_group_permalink($group) : '');
                }
                break;

            case 'friendship_created':
                $user_url = function_exists('bp_members_get_user_url') ? bp_members_get_user_url($user_id) : bp_core_get_user_domain($user_id);
                return $user_url . 'friends/';

            case 'updated_profile':
                $user_url = function_exists('bp_members_get_user_url') ? bp_members_get_user_url($user_id) : bp_core_get_user_domain($user_id);
                return $user_url . 'profile/';

            case 'new_blog_post':
                return home_url('/blog/');
        }

        return function_exists('bp_members_get_user_url') ? bp_members_get_user_url($user_id) : bp_core_get_user_domain($user_id);
    }

    /**
     * Get item ID for activity
     *
     * @since 1.0.0
     * @param string $activity_type Activity type
     * @param int $user_id User ID
     * @param int $group_id Group ID (if applicable)
     * @return int Item ID
     */
    private function get_item_id($activity_type, $user_id, $group_id = 0) {
        switch ($activity_type) {
            case 'joined_group':
            case 'group_activity_update':
                return $group_id;

            case 'friendship_created':
            case 'updated_profile':
            case 'new_member':
                return $user_id;

            default:
                return 0;
        }
    }

    /**
     * Check if activity should be hidden sitewide
     *
     * @since 1.0.0
     * @param string $activity_type Activity type
     * @return bool Whether to hide sitewide
     */
    private function should_hide_sitewide($activity_type) {
        // Hide certain activities from sitewide stream
        $hidden_types = ['updated_profile'];
        return in_array($activity_type, $hidden_types);
    }

    /**
     * Generate comment content
     *
     * @since 1.0.0
     * @return string Comment content
     */
    private function generate_comment_content() {
        return BP_Playground_Sample_Data::generate_comment_content();
    }

    /**
     * Get available user IDs
     *
     * @since 1.0.0
     * @return array User IDs
     */
    private function get_available_user_ids() {
        global $wpdb;

        // Prefer playground-generated users
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
     * Get available group IDs
     *
     * @since 1.0.0
     * @return array Group IDs
     */
    private function get_available_group_ids() {
        if (!bp_is_active('groups')) {
            return [];
        }

        global $wpdb;

        $group_ids = $wpdb->get_col(
            "SELECT group_id FROM {$wpdb->base_prefix}bp_groups_groupmeta WHERE meta_key = 'bp_playground_created'"
        );

        return array_map('intval', $group_ids);
    }

    /**
     * Get module statistics
     *
     * @since 1.0.0
     * @return array Module statistics
     */
    public function get_stats() {
        if (!bp_is_active('activity')) {
            return [];
        }

        global $wpdb;

        $stats = [
            'total_activities' => 0,
            'playground_activities' => 0,
            'activity_updates' => 0,
            'group_activities' => 0,
            'total_comments' => 0,
            'playground_comments' => 0,
            'total_favorites' => 0,
            'activities_with_mentions' => 0,
        ];

        // Total activities
        $stats['total_activities'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_activity WHERE type != 'activity_comment'");

        // Playground activities
        $stats['playground_activities'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_activity_meta WHERE meta_key = 'bp_playground_created'"
        );

        if ($stats['playground_activities'] > 0) {
            // Get playground activity IDs
            $playground_activity_ids = $wpdb->get_col(
                "SELECT activity_id FROM {$wpdb->base_prefix}bp_activity_meta WHERE meta_key = 'bp_playground_created'"
            );

            if (!empty($playground_activity_ids)) {
                $activity_ids_list = implode(',', array_map('intval', $playground_activity_ids));

                // Count by type
                $type_counts = $wpdb->get_results(
                    "SELECT type, COUNT(*) as count 
                     FROM {$wpdb->base_prefix}bp_activity 
                     WHERE id IN ({$activity_ids_list}) AND type != 'activity_comment'
                     GROUP BY type"
                );

                foreach ($type_counts as $type_count) {
                    if ($type_count->type === 'activity_update') {
                        $stats['activity_updates'] = $type_count->count;
                    } elseif (strpos($type_count->type, 'group') !== false) {
                        $stats['group_activities'] += $type_count->count;
                    }
                }

                // Count comments
                $stats['playground_comments'] = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_activity 
                     WHERE type = 'activity_comment' AND item_id IN ({$activity_ids_list})"
                );

                // Count activities with mentions (approximate)
                $stats['activities_with_mentions'] = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_activity 
                     WHERE id IN ({$activity_ids_list}) AND content LIKE '%@%'"
                );

                // Count favorites (if available)
                if (function_exists('bp_get_user_meta')) {
                    $stats['total_favorites'] = $wpdb->get_var(
                        "SELECT COUNT(*) FROM {$wpdb->usermeta} 
                         WHERE meta_key = 'bp_favorite_activities'"
                    );
                }
            }
        }

        // Total comments
        $stats['total_comments'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_activity WHERE type = 'activity_comment'");

        return $stats;
    }

    /**
     * Clean up activities data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function cleanup($options = []) {
        if (!bp_is_active('activity')) {
            return ['activities_removed' => 0];
        }

        global $wpdb;

        $defaults = [
            'remove_activities' => true,
            'remove_comments' => true,
            'remove_favorites' => true,
            'older_than_days' => 0,
            'dry_run' => false,
        ];

        $options = wp_parse_args($options, $defaults);
        
        $results = [
            'activities_removed' => 0,
            'comments_removed' => 0,
            'favorites_removed' => 0,
            'meta_cleaned' => 0,
        ];

        if (!$options['remove_activities']) {
            return $results;
        }

        // Get playground activities
        $activity_query = "
            SELECT a.id, a.content 
            FROM {$wpdb->base_prefix}bp_activity a 
            INNER JOIN {$wpdb->base_prefix}bp_activity_meta am ON a.id = am.activity_id 
            WHERE am.meta_key = 'bp_playground_created'
        ";

        if ($options['older_than_days'] > 0) {
            $timestamp = time() - ($options['older_than_days'] * 24 * 3600);
            $activity_query .= $wpdb->prepare(" AND am.meta_value < %d", $timestamp);
        }

        $activities_to_remove = $wpdb->get_results($activity_query);

        if (!$options['dry_run']) {
            foreach ($activities_to_remove as $activity_data) {
                // Count comments before deletion
                $comment_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_activity WHERE type = 'activity_comment' AND item_id = %d",
                    $activity_data->id
                ));

                // Delete activity (this handles comments automatically)
                if (bp_activity_delete(['id' => $activity_data->id])) {
                    $results['activities_removed']++;
                    $results['comments_removed'] += $comment_count;
                }
            }

            // Clean up favorites
            if ($options['remove_favorites']) {
                // This would need more complex logic to only remove favorites for deleted activities
                $results['favorites_removed'] = 0;
            }

            // Clean up any remaining meta
            $meta_cleaned = $wpdb->query(
                "DELETE FROM {$wpdb->base_prefix}bp_activity_meta WHERE meta_key LIKE 'bp_playground_%'"
            );
            $results['meta_cleaned'] = $meta_cleaned;
        } else {
            $results['activities_removed'] = count($activities_to_remove);
            // Estimate comments for dry run
            foreach ($activities_to_remove as $activity_data) {
                $comment_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_activity WHERE type = 'activity_comment' AND item_id = %d",
                    $activity_data->id
                ));
                $results['comments_removed'] += $comment_count;
            }
        }

        return $results;
    }
}