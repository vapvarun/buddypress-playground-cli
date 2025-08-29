<?php
/**
 * BuddyPress Playground bbPress Module
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
 * bbPress Module - Generate forums, topics, and replies
 *
 * @since 1.0.0
 */
class BP_Playground_BBPress_Module extends BP_Playground_Abstract_Module {

    /**
     * Module name
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_name = 'bbpress';

    /**
     * Module description
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_description = 'Generate bbPress forums, topics, and replies';

    /**
     * Forum categories and their configurations
     *
     * @since 1.0.0
     * @var array
     */
    private $forum_categories = [
        'technology' => [
            'names' => [
                'Web Development', 'Mobile Apps', 'Programming Languages', 'DevOps & Tools',
                'Database Design', 'Software Architecture', 'Tech News & Trends', 'Bug Reports'
            ],
            'descriptions' => [
                'Discuss web development frameworks, best practices, and emerging technologies.',
                'Share knowledge about mobile app development for iOS and Android platforms.',
                'Programming language discussions, tips, and code sharing.',
                'DevOps tools, deployment strategies, and system administration.'
            ],
        ],
        'general' => [
            'names' => [
                'General Discussion', 'Introductions', 'Q&A', 'Project Showcase',
                'Feedback & Suggestions', 'Events & Meetups', 'Resources & Tools', 'Off Topic'
            ],
            'descriptions' => [
                'General discussions about community topics and interests.',
                'New member introductions and welcome messages.',
                'Ask questions and get help from the community.',
                'Showcase your projects and get feedback from peers.'
            ],
        ],
        'support' => [
            'names' => [
                'Technical Support', 'Getting Started', 'Troubleshooting', 'Documentation',
                'Feature Requests', 'Bug Reports', 'Installation Help', 'Configuration'
            ],
            'descriptions' => [
                'Get technical support and assistance with platform issues.',
                'Help for new users getting started with the platform.',
                'Troubleshoot common problems and find solutions.',
                'Documentation discussions and improvement suggestions.'
            ],
        ],
    ];

    /**
     * Topic templates by category
     *
     * @since 1.0.0
     * @var array
     */
    private $topic_templates = [
        'technology' => [
            'How to implement {feature} in {technology}?',
            'Best practices for {technology} development',
            '{technology} vs {technology} - Which is better?',
            'New {technology} release - What\'s your experience?',
            'Debugging {issue} in {technology}',
            'Performance optimization tips for {technology}',
            'Learning resources for {technology}',
            '{technology} integration challenges'
        ],
        'general' => [
            'Welcome new members!',
            'What projects are you working on?',
            'Community feedback wanted',
            'Upcoming events and meetups',
            'Share your success story',
            'Monthly challenge discussion',
            'Resource sharing thread',
            'Community guidelines discussion'
        ],
        'support' => [
            'Need help with {feature}',
            'Installation issues with {software}',
            'Configuration problems',
            'Error: {error_message}',
            'How to set up {feature}?',
            'Troubleshooting {issue}',
            'Documentation request for {topic}',
            'Feature request: {feature}'
        ],
    ];

    /**
     * Reply templates
     *
     * @since 1.0.0
     * @var array
     */
    private $reply_templates = [
        'helpful' => [
            'Thanks for sharing this! I had the same issue and this solution worked perfectly.',
            'Great explanation! This really helped me understand the concept better.',
            'I\'ve been looking for this information everywhere. Thank you!',
            'This is exactly what I needed. Bookmarking for future reference.',
            'Excellent tutorial! Step-by-step instructions are very clear.',
            'Your solution saved me hours of debugging. Much appreciated!',
        ],
        'question' => [
            'Have you tried {alternative_solution}? It might work better in your case.',
            'What version of {software} are you using? This could be version-specific.',
            'Can you provide more details about your setup?',
            'Are you getting any specific error messages?',
            'Have you checked the documentation for {feature}?',
            'Is this happening consistently or intermittently?',
        ],
        'answer' => [
            'I encountered this issue before. Here\'s how I solved it: {solution}',
            'The solution is to {action}. This should resolve your problem.',
            'You need to {instruction}. This is a common configuration issue.',
            'Try {suggestion}. This usually works for most users.',
            'The problem is likely {cause}. To fix it, {solution}.',
            'I recommend {approach}. It\'s more reliable than the standard method.',
        ],
        'discussion' => [
            'I agree with your point about {topic}. In my experience, {experience}.',
            'That\'s an interesting perspective. Have you considered {alternative}?',
            'I have a different approach to this problem. What do you think about {idea}?',
            'Building on what you said, I think {addition} is also important.',
            'Great discussion! I\'d like to add that {contribution}.',
            'This thread has been very informative. Thanks everyone for sharing!',
        ],
    ];

    /**
     * Generate bbPress data
     *
     * @since 1.0.0
     * @param array $args Generation arguments
     * @return array|WP_Error Generation results
     */
    public function generate($args = []) {
        if (!class_exists('bbPress')) {
            return new WP_Error('bbpress_not_active', __('bbPress is not active.', BP_PLAYGROUND_TEXT_DOMAIN));
        }

        $defaults = [
            'forums' => 20,
            'topics_per_forum' => 50,
            'replies_per_topic' => 10,
            'hierarchy_depth' => 2,
            'with_tags' => true,
            'batch_size' => 25,
        ];

        $args = wp_parse_args($args, $defaults);

        // Validate arguments
        $validation_rules = [
            'forums' => ['type' => 'int', 'min' => 1, 'max' => 200],
            'topics_per_forum' => ['type' => 'int', 'min' => 1, 'max' => 500],
            'replies_per_topic' => ['type' => 'int', 'min' => 0, 'max' => 100],
            'hierarchy_depth' => ['type' => 'int', 'min' => 1, 'max' => 5],
            'batch_size' => ['type' => 'int', 'min' => 1, 'max' => 100],
        ];

        $validated_args = $this->validate_args($args, $validation_rules);
        if (is_wp_error($validated_args)) {
            return $validated_args;
        }

        $this->start_generation('bbPress Content');

        $results = [
            'forums_created' => 0,
            'topics_created' => 0,
            'replies_created' => 0,
            'tags_created' => 0,
            'subscriptions_created' => 0,
            'favorites_created' => 0,
            'errors' => [],
        ];

        try {
            // Get available users
            $user_ids = $this->get_available_user_ids();
            if (empty($user_ids)) {
                throw new Exception('No users available for bbPress content creation. Please create users first.');
            }

            // Create forums first
            $forums_result = $this->create_forums($validated_args, $user_ids);
            if (is_wp_error($forums_result)) {
                throw new Exception($forums_result->get_error_message());
            }
            
            $results['forums_created'] = $forums_result['forums_created'];
            $results['errors'] = array_merge($results['errors'], $forums_result['errors']);

            // Create topics and replies for each forum
            if ($results['forums_created'] > 0) {
                $content_result = $this->create_forum_content($validated_args, $user_ids);
                if (!is_wp_error($content_result)) {
                    $results['topics_created'] = $content_result['topics_created'];
                    $results['replies_created'] = $content_result['replies_created'];
                    $results['tags_created'] = $content_result['tags_created'];
                    $results['subscriptions_created'] = isset($content_result['subscriptions_created']) ? $content_result['subscriptions_created'] : 0;
                    $results['favorites_created'] = isset($content_result['favorites_created']) ? $content_result['favorites_created'] : 0;
                    $results['errors'] = array_merge($results['errors'], $content_result['errors']);
                }
            }

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->log_error('bbPress generation failed: ' . $e->getMessage());
        }

        $this->end_generation();
        return $results;
    }

    /**
     * Create forums
     *
     * @since 1.0.0
     * @param array $args Generation arguments
     * @param array $user_ids Available user IDs
     * @return array|WP_Error Creation results
     */
    private function create_forums($args, $user_ids) {
        $results = [
            'forums_created' => 0,
            'errors' => [],
        ];

        $category_keys = array_keys($this->forum_categories);
        
        for ($i = 0; $i < $args['forums']; $i++) {
            try {
                $forum_data = $this->generate_forum_data($i, $category_keys);
                $forum_id = $this->create_single_forum($forum_data);

                if ($forum_id) {
                    $results['forums_created']++;
                    
                    // Update core stats
                    $core = bp_playground_get_module('core');
                    if ($core) {
                        $core->increment_stat('forums_created');
                    }
                } else {
                    $results['errors'][] = "Failed to create forum: {$forum_data['name']}";
                }

            } catch (Exception $e) {
                $results['errors'][] = "Error creating forum {$i}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Generate forum data
     *
     * @since 1.0.0
     * @param int $index Forum index
     * @param array $category_keys Available category keys
     * @return array Forum data
     */
    private function generate_forum_data($index, $category_keys) {
        // Seed for consistent data
        mt_srand($index);

        $category = $category_keys[array_rand($category_keys)];
        $category_data = $this->forum_categories[$category];
        
        $name = $category_data['names'][array_rand($category_data['names'])];
        $description = $category_data['descriptions'][array_rand($category_data['descriptions'])];

        // Add variation to avoid duplicates
        if (mt_rand(1, 10) > 7) {
            $name .= ' ' . mt_rand(1, 999);
        }

        mt_srand(); // Reset seed

        return [
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'slug' => sanitize_title($name . '-' . $index),
            'status' => 'publish',
            'visibility' => 'public',
        ];
    }

    /**
     * Create a single forum
     *
     * @since 1.0.0
     * @param array $forum_data Forum data
     * @return int|false Forum ID on success, false on failure
     */
    private function create_single_forum($forum_data) {
        if (!function_exists('bbp_insert_forum')) {
            return false;
        }

        $forum_args = [
            'post_title' => $forum_data['name'],
            'post_content' => $forum_data['description'],
            'post_status' => $forum_data['status'],
            'post_type' => bbp_get_forum_post_type(),
            'post_name' => $forum_data['slug'],
        ];

        $forum_id = bbp_insert_forum($forum_args);

        if ($forum_id) {
            // Add forum meta
            add_post_meta($forum_id, 'bp_playground_created', time());
            add_post_meta($forum_id, 'bp_playground_category', $forum_data['category']);
            
            $this->log("Created forum: {$forum_data['name']} (ID: {$forum_id})");
        }

        return $forum_id;
    }

    /**
     * Create content for forums (topics and replies)
     *
     * @since 1.0.0
     * @param array $args Generation arguments
     * @param array $user_ids Available user IDs
     * @return array Creation results
     */
    private function create_forum_content($args, $user_ids) {
        $results = [
            'topics_created' => 0,
            'replies_created' => 0,
            'tags_created' => 0,
            'errors' => [],
        ];

        // Get all created forums
        $forums = $this->get_playground_forums();
        if (empty($forums)) {
            return new WP_Error('no_forums', 'No forums available for content creation');
        }

        foreach ($forums as $forum) {
            $forum_meta = get_post_meta($forum->ID, 'bp_playground_category', true);
            $topic_count = mt_rand(
                max(1, $args['topics_per_forum'] - 10),
                $args['topics_per_forum'] + 10
            );

            for ($i = 0; $i < $topic_count; $i++) {
                try {
                    $topic_data = $this->generate_topic_data($forum, $forum_meta, $user_ids);
                    $topic_id = $this->create_single_topic($topic_data, $forum->ID);

                    if ($topic_id) {
                        $results['topics_created']++;

                        // Create replies for this topic
                        $reply_count = mt_rand(0, $args['replies_per_topic']);
                        for ($j = 0; $j < $reply_count; $j++) {
                            $reply_data = $this->generate_reply_data($topic_id, $user_ids);
                            $reply_id = $this->create_single_reply($reply_data, $topic_id, $forum->ID);

                            if ($reply_id) {
                                $results['replies_created']++;
                            }
                        }

                        // Add tags if enabled
                        if (!empty($args['with_tags'])) {
                            $tags_added = $this->add_topic_tags($topic_id, $forum_meta);
                            $results['tags_created'] += $tags_added;
                        }
                    }

                } catch (Exception $e) {
                    $results['errors'][] = "Error creating topic in forum {$forum->ID}: " . $e->getMessage();
                }
            }
        }

        return $results;
    }

    /**
     * Generate topic data
     *
     * @since 1.0.0
     * @param WP_Post $forum Forum post object
     * @param string $category Forum category
     * @param array $user_ids Available user IDs
     * @return array Topic data
     */
    private function generate_topic_data($forum, $category, $user_ids) {
        $templates = isset($this->topic_templates[$category]) 
            ? $this->topic_templates[$category] 
            : $this->topic_templates['general'];

        $template = $templates[array_rand($templates)];
        $title = $this->replace_topic_placeholders($template);

        return [
            'title' => $title,
            'content' => $this->generate_topic_content($title, $category),
            'author_id' => $user_ids[array_rand($user_ids)],
            'status' => 'publish',
        ];
    }

    /**
     * Replace topic template placeholders
     *
     * @since 1.0.0
     * @param string $template Topic template
     * @return string Processed title
     */
    private function replace_topic_placeholders($template) {
        $replacements = [
            '{feature}' => $this->get_random_feature(),
            '{technology}' => $this->get_random_technology(),
            '{issue}' => $this->get_random_issue(),
            '{error_message}' => $this->get_random_error(),
            '{software}' => $this->get_random_software(),
            '{topic}' => $this->get_random_topic(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Generate topic content
     *
     * @since 1.0.0
     * @param string $title Topic title
     * @param string $category Topic category
     * @return string Topic content
     */
    private function generate_topic_content($title, $category) {
        $content_templates = [
            'technology' => [
                "I'm working on a project and need help with {issue}. Has anyone dealt with this before?",
                "What's the best approach for implementing {feature}? Looking for recommendations.",
                "I've been comparing different solutions and wanted to get community input.",
                "Sharing my experience with {technology} and looking for feedback.",
            ],
            'general' => [
                "I wanted to start a discussion about {topic} and hear everyone's thoughts.",
                "This has been on my mind lately and I'd love to get community input.",
                "Sharing some insights and hoping to spark an interesting conversation.",
                "Looking forward to hearing different perspectives on this topic.",
            ],
            'support' => [
                "I'm experiencing {issue} and need help troubleshooting. Here are the details:",
                "Following the documentation but running into problems. Can someone help?",
                "This error is blocking my progress. Any suggestions would be appreciated.",
                "New to this and could use some guidance getting started.",
            ],
        ];

        $templates = isset($content_templates[$category]) 
            ? $content_templates[$category] 
            : $content_templates['general'];

        $template = $templates[array_rand($templates)];
        return $this->replace_topic_placeholders($template);
    }

    /**
     * Create a single topic
     *
     * @since 1.0.0
     * @param array $topic_data Topic data
     * @param int $forum_id Forum ID
     * @return int|false Topic ID on success, false on failure
     */
    private function create_single_topic($topic_data, $forum_id) {
        if (!function_exists('bbp_insert_topic')) {
            return false;
        }

        $topic_args = [
            'post_title' => $topic_data['title'],
            'post_content' => $topic_data['content'],
            'post_status' => $topic_data['status'],
            'post_type' => bbp_get_topic_post_type(),
            'post_author' => $topic_data['author_id'],
            'post_parent' => $forum_id,
        ];

        $topic_id = bbp_insert_topic($topic_args);

        if ($topic_id) {
            add_post_meta($topic_id, 'bp_playground_created', time());
            
            // Set random creation date in the past
            $random_date = date('Y-m-d H:i:s', time() - mt_rand(0, 90 * 24 * 3600));
            wp_update_post([
                'ID' => $topic_id,
                'post_date' => $random_date,
                'post_date_gmt' => get_gmt_from_date($random_date),
            ]);
        }

        return $topic_id;
    }

    /**
     * Generate reply data
     *
     * @since 1.0.0
     * @param int $topic_id Topic ID
     * @param array $user_ids Available user IDs
     * @return array Reply data
     */
    private function generate_reply_data($topic_id, $user_ids) {
        $reply_types = array_keys($this->reply_templates);
        $reply_type = $reply_types[array_rand($reply_types)];
        $templates = $this->reply_templates[$reply_type];
        
        $template = $templates[array_rand($templates)];
        $content = $this->replace_reply_placeholders($template);

        return [
            'content' => $content,
            'author_id' => $user_ids[array_rand($user_ids)],
            'status' => 'publish',
            'type' => $reply_type,
        ];
    }

    /**
     * Replace reply template placeholders
     *
     * @since 1.0.0
     * @param string $template Reply template
     * @return string Processed content
     */
    private function replace_reply_placeholders($template) {
        $replacements = [
            '{alternative_solution}' => $this->get_random_solution(),
            '{software}' => $this->get_random_software(),
            '{feature}' => $this->get_random_feature(),
            '{solution}' => $this->get_random_solution(),
            '{action}' => $this->get_random_action(),
            '{instruction}' => $this->get_random_instruction(),
            '{suggestion}' => $this->get_random_suggestion(),
            '{cause}' => $this->get_random_cause(),
            '{approach}' => $this->get_random_approach(),
            '{topic}' => $this->get_random_topic(),
            '{experience}' => $this->get_random_experience(),
            '{alternative}' => $this->get_random_alternative(),
            '{idea}' => $this->get_random_idea(),
            '{addition}' => $this->get_random_addition(),
            '{contribution}' => $this->get_random_contribution(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Create a single reply
     *
     * @since 1.0.0
     * @param array $reply_data Reply data
     * @param int $topic_id Topic ID
     * @param int $forum_id Forum ID
     * @return int|false Reply ID on success, false on failure
     */
    private function create_single_reply($reply_data, $topic_id, $forum_id) {
        if (!function_exists('bbp_insert_reply')) {
            return false;
        }

        $reply_args = [
            'post_content' => $reply_data['content'],
            'post_status' => $reply_data['status'],
            'post_type' => bbp_get_reply_post_type(),
            'post_author' => $reply_data['author_id'],
            'post_parent' => $topic_id,
        ];

        $reply_id = bbp_insert_reply($reply_args);

        if ($reply_id) {
            add_post_meta($reply_id, 'bp_playground_created', time());
            add_post_meta($reply_id, 'bp_playground_reply_type', $reply_data['type']);
            
            // Set random creation date in the past (but after topic creation)
            $topic_date = get_post_time('U', false, $topic_id);
            $random_date = date('Y-m-d H:i:s', mt_rand($topic_date, time()));
            wp_update_post([
                'ID' => $reply_id,
                'post_date' => $random_date,
                'post_date_gmt' => get_gmt_from_date($random_date),
            ]);
        }

        return $reply_id;
    }

    /**
     * Add tags to topic
     *
     * @since 1.0.0
     * @param int $topic_id Topic ID
     * @param string $category Topic category
     * @return int Number of tags added
     */
    private function add_topic_tags($topic_id, $category) {
        if (!function_exists('bbp_get_topic_tag_tax_id')) {
            return 0;
        }

        $tag_taxonomy = bbp_get_topic_tag_tax_id();
        $category_tags = $this->get_category_tags($category);
        
        $tag_count = mt_rand(1, 3);
        $selected_tags = array_rand($category_tags, min($tag_count, count($category_tags)));
        
        if (!is_array($selected_tags)) {
            $selected_tags = [$selected_tags];
        }

        $tags_to_add = [];
        foreach ($selected_tags as $index) {
            $tags_to_add[] = $category_tags[$index];
        }

        $result = wp_set_post_terms($topic_id, $tags_to_add, $tag_taxonomy);
        
        return is_array($result) ? count($result) : 0;
    }

    /**
     * Get category-specific tags
     *
     * @since 1.0.0
     * @param string $category Category name
     * @return array Category tags
     */
    private function get_category_tags($category) {
        $tags = [
            'technology' => ['javascript', 'php', 'python', 'react', 'nodejs', 'css', 'html', 'api'],
            'general' => ['discussion', 'community', 'feedback', 'showcase', 'help', 'resources'],
            'support' => ['help', 'troubleshooting', 'bug', 'installation', 'configuration', 'documentation'],
        ];

        return isset($tags[$category]) ? $tags[$category] : $tags['general'];
    }

    /**
     * Get available user IDs
     *
     * @since 1.0.0
     * @return array User IDs
     */
    private function get_available_user_ids() {
        global $wpdb;

        $playground_users = $wpdb->get_col(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'bp_playground_created'"
        );

        if (count($playground_users) >= 5) {
            return $playground_users;
        }

        $all_users = $wpdb->get_col("SELECT ID FROM {$wpdb->users} WHERE ID > 1 LIMIT 100");
        return array_merge($playground_users, $all_users);
    }

    /**
     * Get playground-created forums
     *
     * @since 1.0.0
     * @return array Forum posts
     */
    private function get_playground_forums() {
        if (!function_exists('bbp_get_forum_post_type')) {
            return [];
        }

        $args = [
            'post_type' => bbp_get_forum_post_type(),
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'bp_playground_created',
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        $query = new WP_Query($args);
        return $query->posts;
    }

    // Random content generators
    private function get_random_feature() {
        $features = ['authentication', 'user profiles', 'file upload', 'search functionality', 'notifications', 'API integration'];
        return $features[array_rand($features)];
    }

    private function get_random_technology() {
        $technologies = ['React', 'Vue.js', 'Angular', 'Node.js', 'PHP', 'Python', 'Laravel', 'WordPress'];
        return $technologies[array_rand($technologies)];
    }

    private function get_random_issue() {
        $issues = ['performance bottleneck', 'memory leak', 'database timeout', 'API rate limiting', 'security vulnerability'];
        return $issues[array_rand($issues)];
    }

    private function get_random_error() {
        $errors = ['Connection timeout', '404 Not Found', 'Permission denied', 'Invalid syntax', 'Memory exhausted'];
        return $errors[array_rand($errors)];
    }

    private function get_random_software() {
        $software = ['WordPress', 'Docker', 'MySQL', 'Apache', 'Nginx', 'Redis', 'Elasticsearch'];
        return $software[array_rand($software)];
    }

    private function get_random_topic() {
        $topics = ['performance optimization', 'security best practices', 'code organization', 'testing strategies'];
        return $topics[array_rand($topics)];
    }

    private function get_random_solution() {
        $solutions = ['updating the configuration', 'clearing the cache', 'restarting the service', 'checking permissions'];
        return $solutions[array_rand($solutions)];
    }

    private function get_random_action() {
        $actions = ['restart the server', 'clear the cache', 'update the configuration', 'check the logs'];
        return $actions[array_rand($actions)];
    }

    private function get_random_instruction() {
        $instructions = ['update your configuration file', 'install the latest version', 'check your environment variables'];
        return $instructions[array_rand($instructions)];
    }

    private function get_random_suggestion() {
        $suggestions = ['using a different approach', 'updating to the latest version', 'checking the documentation'];
        return $suggestions[array_rand($suggestions)];
    }

    private function get_random_cause() {
        $causes = ['a configuration issue', 'a version compatibility problem', 'a missing dependency'];
        return $causes[array_rand($causes)];
    }

    private function get_random_approach() {
        $approaches = ['using a more efficient algorithm', 'implementing caching', 'optimizing database queries'];
        return $approaches[array_rand($approaches)];
    }

    private function get_random_experience() {
        $experiences = ['this approach worked well for me', 'I found a better solution', 'this saved me a lot of time'];
        return $experiences[array_rand($experiences)];
    }

    private function get_random_alternative() {
        $alternatives = ['using a different library', 'trying a different approach', 'implementing it differently'];
        return $alternatives[array_rand($alternatives)];
    }

    private function get_random_idea() {
        $ideas = ['implementing automated testing', 'using a modular approach', 'adding better error handling'];
        return $ideas[array_rand($ideas)];
    }

    private function get_random_addition() {
        $additions = ['proper error handling is crucial', 'documentation is very important', 'testing should be comprehensive'];
        return $additions[array_rand($additions)];
    }

    private function get_random_contribution() {
        $contributions = ['security should be a priority', 'performance monitoring is essential', 'user experience matters'];
        return $contributions[array_rand($contributions)];
    }

    /**
     * Get module statistics
     *
     * @since 1.0.0
     * @return array Module statistics
     */
    public function get_stats() {
        if (!class_exists('bbPress')) {
            return [];
        }

        global $wpdb;

        $stats = [
            'total_forums' => 0,
            'total_topics' => 0,
            'total_replies' => 0,
            'playground_forums' => 0,
            'playground_topics' => 0,
            'playground_replies' => 0,
        ];

        if (function_exists('bbp_get_forum_post_type')) {
            // Total counts
            $stats['total_forums'] = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
                bbp_get_forum_post_type()
            ));

            $stats['total_topics'] = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
                bbp_get_topic_post_type()
            ));

            $stats['total_replies'] = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
                bbp_get_reply_post_type()
            ));

            // Playground counts
            $stats['playground_forums'] = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} p 
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                 WHERE p.post_type = %s AND pm.meta_key = 'bp_playground_created'",
                bbp_get_forum_post_type()
            ));

            $stats['playground_topics'] = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} p 
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                 WHERE p.post_type = %s AND pm.meta_key = 'bp_playground_created'",
                bbp_get_topic_post_type()
            ));

            $stats['playground_replies'] = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} p 
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                 WHERE p.post_type = %s AND pm.meta_key = 'bp_playground_created'",
                bbp_get_reply_post_type()
            ));
        }

        return $stats;
    }

    /**
     * Clean up bbPress data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function cleanup($options = []) {
        if (!class_exists('bbPress')) {
            return ['forums_removed' => 0, 'topics_removed' => 0, 'replies_removed' => 0];
        }

        global $wpdb;

        $defaults = [
            'remove_forums' => true,
            'remove_topics' => true,
            'remove_replies' => true,
            'older_than_days' => 0,
            'dry_run' => false,
        ];

        $options = wp_parse_args($options, $defaults);
        
        $results = [
            'forums_removed' => 0,
            'topics_removed' => 0,
            'replies_removed' => 0,
        ];

        if (!function_exists('bbp_get_forum_post_type')) {
            return $results;
        }

        // Remove replies first (to avoid orphans)
        if ($options['remove_replies']) {
            $reply_ids = $this->get_playground_post_ids(bbp_get_reply_post_type(), $options['older_than_days']);
            foreach ($reply_ids as $reply_id) {
                if (!$options['dry_run'] && wp_delete_post($reply_id, true)) {
                    $results['replies_removed']++;
                }
            }
            if ($options['dry_run']) {
                $results['replies_removed'] = count($reply_ids);
            }
        }

        // Remove topics
        if ($options['remove_topics']) {
            $topic_ids = $this->get_playground_post_ids(bbp_get_topic_post_type(), $options['older_than_days']);
            foreach ($topic_ids as $topic_id) {
                if (!$options['dry_run'] && wp_delete_post($topic_id, true)) {
                    $results['topics_removed']++;
                }
            }
            if ($options['dry_run']) {
                $results['topics_removed'] = count($topic_ids);
            }
        }

        // Remove forums last
        if ($options['remove_forums']) {
            $forum_ids = $this->get_playground_post_ids(bbp_get_forum_post_type(), $options['older_than_days']);
            foreach ($forum_ids as $forum_id) {
                if (!$options['dry_run'] && wp_delete_post($forum_id, true)) {
                    $results['forums_removed']++;
                }
            }
            if ($options['dry_run']) {
                $results['forums_removed'] = count($forum_ids);
            }
        }

        return $results;
    }

    /**
     * Get playground post IDs by type
     *
     * @since 1.0.0
     * @param string $post_type Post type
     * @param int $older_than_days Age filter in days
     * @return array Post IDs
     */
    private function get_playground_post_ids($post_type, $older_than_days = 0) {
        global $wpdb;

        $where_clause = $wpdb->prepare(
            "WHERE p.post_type = %s AND pm.meta_key = 'bp_playground_created'",
            $post_type
        );

        if ($older_than_days > 0) {
            $timestamp = time() - ($older_than_days * 24 * 3600);
            $where_clause .= $wpdb->prepare(" AND pm.meta_value < %d", $timestamp);
        }

        $post_ids = $wpdb->get_col(
            "SELECT p.ID FROM {$wpdb->posts} p 
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
             {$where_clause}"
        );

        return array_map('intval', $post_ids);
    }
}