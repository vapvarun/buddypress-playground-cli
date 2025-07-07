<?php
/**
 * BuddyPress Playground Messages Module
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
 * Messages Module - Generate realistic private message conversations
 *
 * @since 1.0.0
 */
class BP_Playground_Messages_Module extends BP_Playground_Abstract_Module {

    /**
     * Module name
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_name = 'messages';

    /**
     * Module description
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_description = 'Generate realistic private message conversations and threads';

    /**
     * Message conversation types
     *
     * @since 1.0.0
     * @var array
     */
    private $conversation_types = [
        'introduction' => [
            'weight' => 0.2,
            'min_messages' => 2,
            'max_messages' => 5,
            'subjects' => [
                'Welcome to the community!',
                'Nice to meet you',
                'Introduction',
                'Hello there!',
                'Getting started',
            ],
        ],
        'collaboration' => [
            'weight' => 0.25,
            'min_messages' => 3,
            'max_messages' => 8,
            'subjects' => [
                'Project collaboration opportunity',
                'Working together on {project}',
                'Team up for {initiative}',
                'Partnership proposal',
                'Collaboration idea',
            ],
        ],
        'question_answer' => [
            'weight' => 0.3,
            'min_messages' => 2,
            'max_messages' => 6,
            'subjects' => [
                'Quick question about {topic}',
                'Need help with {subject}',
                'Advice needed',
                'Can you help me with something?',
                'Question regarding {area}',
            ],
        ],
        'networking' => [
            'weight' => 0.15,
            'min_messages' => 2,
            'max_messages' => 4,
            'subjects' => [
                'Professional networking',
                'Connecting with fellow {professionals}',
                'Industry connections',
                'Building our network',
                'Professional introduction',
            ],
        ],
        'casual' => [
            'weight' => 0.1,
            'min_messages' => 2,
            'max_messages' => 10,
            'subjects' => [
                'How are things going?',
                'Checking in',
                'Catching up',
                'Hope you\'re doing well',
                'Just saying hi!',
            ],
        ],
    ];

    /**
     * Message content templates
     *
     * @since 1.0.0
     * @var array
     */
    private $message_templates = [
        'introduction' => [
            'initial' => [
                'Hi {name}! Welcome to our community. I noticed you just joined and wanted to say hello.',
                'Hello {name}, I saw your profile and thought I\'d reach out to introduce myself.',
                'Welcome to the group, {name}! I\'m excited to have you here.',
                'Hi there! I\'m {sender_name} and I wanted to personally welcome you to our community.',
            ],
            'response' => [
                'Thank you so much for the warm welcome! I\'m excited to be here.',
                'Hi {sender_name}! Thanks for reaching out. Looking forward to connecting with everyone.',
                'I really appreciate the welcome message. The community seems very friendly!',
                'Thanks for the introduction! I\'m looking forward to learning and contributing.',
            ],
        ],
        'collaboration' => [
            'initial' => [
                'Hi {name}, I came across your profile and I think we might have some common interests for a potential collaboration.',
                'Hello! I\'m working on {project} and thought you might be interested in joining forces.',
                'I\'ve been following your work and would love to discuss a collaboration opportunity.',
                'Hi {name}, I have an idea for a project that I think would benefit from your expertise.',
            ],
            'response' => [
                'That sounds really interesting! I\'d love to learn more about what you have in mind.',
                'Thanks for thinking of me! Can you tell me more details about the project?',
                'I\'m definitely interested in collaboration opportunities. What did you have in mind?',
                'This could be a great fit. When would be a good time to discuss further?',
            ],
        ],
        'question_answer' => [
            'initial' => [
                'Hi {name}, I hope you don\'t mind me reaching out. I have a question about {topic} and thought you might be able to help.',
                'Hello! I saw your expertise in {area} and was wondering if you could provide some guidance.',
                'Hi there, I\'m working on {subject} and could use some advice from someone with your experience.',
                'I hope this message finds you well. I have a quick question about {topic} if you have a moment.',
            ],
            'response' => [
                'Of course! I\'d be happy to help. What specifically are you looking to understand?',
                'Sure thing! I have some experience with that. What\'s your specific question?',
                'I\'m glad to help if I can. Can you give me more details about what you\'re working on?',
                'Absolutely! That\'s actually something I\'ve dealt with before. What\'s the issue?',
            ],
        ],
        'networking' => [
            'initial' => [
                'Hi {name}, I noticed we work in similar fields and thought it would be great to connect professionally.',
                'Hello! I\'m always looking to expand my professional network and thought we might have some common ground.',
                'I came across your profile and thought it would be valuable for us to connect.',
                'Hi there, I\'m building my network in {industry} and would love to connect with fellow professionals.',
            ],
            'response' => [
                'I\'d be happy to connect! It\'s always great to meet other professionals in our field.',
                'Absolutely! Networking is so important. I\'d love to learn more about your work.',
                'Thanks for reaching out! I believe in the power of professional connections.',
                'Great to meet you! I\'m always interested in connecting with like-minded professionals.',
            ],
        ],
        'casual' => [
            'initial' => [
                'Hey {name}! Just wanted to check in and see how things are going.',
                'Hi there! It\'s been a while since we last chatted. How are you doing?',
                'Hello! Hope you\'re having a great day. Just thought I\'d say hi.',
                'Hi {name}, hope everything is going well with you. Just wanted to touch base.',
            ],
            'response' => [
                'Thanks for checking in! Things are going really well. How about you?',
                'It\'s so nice to hear from you! I\'m doing great, thanks for asking.',
                'I appreciate you reaching out! Things are busy but good. How are you?',
                'Thanks for the message! Always nice to catch up. What\'s new with you?',
            ],
        ],
    ];

    /**
     * Generate messages
     *
     * @since 1.0.0
     * @param array $args Generation arguments
     * @return array|WP_Error Generation results
     */
    public function generate($args = []) {
        if (!bp_is_active('messages')) {
            return new WP_Error('messages_disabled', __('Messages component is not active.', BP_PLAYGROUND_TEXT_DOMAIN));
        }

        $defaults = [
            'count' => 1000,
            'thread_variations' => true,
            'conversation_depth' => 'mixed', // short, medium, long, mixed
            'read_status_distribution' => 'realistic',
            'timeframe_days' => 90,
            'batch_size' => 25,
        ];

        $args = wp_parse_args($args, $defaults);

        // Validate arguments
        $validation_rules = [
            'count' => ['type' => 'int', 'min' => 1, 'max' => 100000],
            'timeframe_days' => ['type' => 'int', 'min' => 1, 'max' => 365],
            'batch_size' => ['type' => 'int', 'min' => 1, 'max' => 100],
            'thread_variations' => ['type' => 'bool'],
        ];

        $validated_args = $this->validate_args($args, $validation_rules);
        if (is_wp_error($validated_args)) {
            return $validated_args;
        }

        $this->start_generation('Message Conversations');

        $results = [
            'threads_created' => 0,
            'messages_created' => 0,
            'recipients_added' => 0,
            'errors' => [],
        ];

        try {
            // Get available users
            $user_ids = $this->get_available_user_ids();
            if (count($user_ids) < 2) {
                throw new Exception('At least 2 users are required for message generation. Please create users first.');
            }

            // Generate message patterns
            $data_model = bp_playground_get_module('data_model');
            $message_patterns = $data_model->generate_message_patterns($user_ids, $validated_args['count']);

            // Calculate number of threads based on total messages
            $estimated_threads = max(1, round($validated_args['count'] / 4)); // Average 4 messages per thread

            $batch_processor = bp_playground_get_module('batch_processor');
            
            $callback = function($start_index, $batch_size, $options) use ($validated_args, $user_ids, $message_patterns) {
                return $this->create_message_threads_batch($start_index, $batch_size, $validated_args, $user_ids, $message_patterns);
            };

            $progress_callback = function($progress) {
                $this->show_progress(
                    $progress['processed'], 
                    $progress['total'], 
                    'Creating message threads'
                );
            };

            $batch_result = $batch_processor->process_in_batches(
                $estimated_threads,
                $callback,
                $progress_callback
            );

            if (is_wp_error($batch_result)) {
                throw new Exception($batch_result->get_error_message());
            }

            $results['threads_created'] = $batch_result['successful_items'];
            $results['errors'] = $batch_result['errors'];

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->log_error('Messages generation failed: ' . $e->getMessage());
        }

        $this->end_generation();
        return $results;
    }

    /**
     * Create a batch of message threads
     *
     * @since 1.0.0
     * @param int $start_index Starting index
     * @param int $batch_size Batch size
     * @param array $options Generation options
     * @param array $user_ids Available user IDs
     * @param array $message_patterns Message patterns
     * @return array Batch results
     */
    private function create_message_threads_batch($start_index, $batch_size, $options, $user_ids, $message_patterns) {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
            'messages_created' => 0,
            'recipients_added' => 0,
        ];

        for ($i = 0; $i < $batch_size; $i++) {
            $thread_index = $start_index + $i;
            $results['processed']++;

            try {
                $thread_data = $this->generate_thread_data($thread_index, $options, $user_ids);
                $thread_result = $this->create_single_thread($thread_data, $options);

                if ($thread_result && !is_wp_error($thread_result)) {
                    $results['successful']++;
                    $results['messages_created'] += $thread_result['messages_created'];
                    $results['recipients_added'] += $thread_result['recipients_added'];
                    
                    // Update core stats
                    $core = bp_playground_get_module('core');
                    if ($core) {
                        $core->increment_stat('messages_created', $thread_result['messages_created']);
                    }
                } else {
                    $results['failed']++;
                    $error_message = is_wp_error($thread_result) ? $thread_result->get_error_message() : "Failed to create thread at index {$thread_index}";
                    $results['errors'][] = $error_message;
                }

            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error creating thread at index {$thread_index}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Generate thread data
     *
     * @since 1.0.0
     * @param int $thread_index Thread index
     * @param array $options Generation options
     * @param array $user_ids Available user IDs
     * @return array Thread data
     */
    private function generate_thread_data($thread_index, $options, $user_ids) {
        // Seed random generator for consistent data
        mt_srand($thread_index);

        // Select conversation type
        $conversation_type = $this->select_conversation_type();
        $type_config = $this->conversation_types[$conversation_type];

        // Select participants (2-4 users per thread)
        $participant_count = mt_rand(2, 4);
        $participants = array_rand(array_flip($user_ids), min($participant_count, count($user_ids)));
        if (!is_array($participants)) {
            $participants = [$participants];
        }

        // First participant is the sender
        $sender_id = $participants[0];
        $recipients = array_slice($participants, 1);

        // Generate subject
        $subject = $this->generate_message_subject($conversation_type, $sender_id);

        // Determine conversation length
        $message_count = $this->get_conversation_length($conversation_type, $options['conversation_depth']);

        // Generate timestamps
        $start_time = time() - mt_rand(0, $options['timeframe_days'] * 24 * 3600);
        $timestamps = $this->generate_conversation_timestamps($start_time, $message_count);

        // Reset random seed
        mt_srand();

        return [
            'sender_id' => $sender_id,
            'recipients' => $recipients,
            'subject' => $subject,
            'conversation_type' => $conversation_type,
            'message_count' => $message_count,
            'timestamps' => $timestamps,
            'participants' => $participants,
        ];
    }

    /**
     * Create a single message thread
     *
     * @since 1.0.0
     * @param array $thread_data Thread data
     * @param array $options Creation options
     * @return array|WP_Error Thread creation results
     */
    private function create_single_thread($thread_data, $options) {
        $messages_created = 0;
        $recipients_added = 0;

        // Create initial message
        $initial_content = $this->generate_message_content(
            $thread_data['conversation_type'], 
            'initial', 
            $thread_data['sender_id'], 
            $thread_data['recipients'][0]
        );

        $thread_id = messages_new_message([
            'sender_id' => $thread_data['sender_id'],
            'recipients' => $thread_data['recipients'],
            'subject' => $thread_data['subject'],
            'content' => $initial_content,
            'date_sent' => date('Y-m-d H:i:s', strtotime($thread_data['timestamps'][0])),
        ]);

        if (!$thread_id) {
            return new WP_Error('thread_creation_failed', 'Failed to create message thread');
        }

        $messages_created++;
        $recipients_added += count($thread_data['recipients']);

        // Add thread metadata
        $this->add_thread_meta($thread_id, $thread_data);

        // Create follow-up messages
        $current_sender_index = 1; // Start with first recipient responding
        for ($i = 1; $i < $thread_data['message_count']; $i++) {
            // Alternate between participants
            $current_sender = $thread_data['participants'][$current_sender_index % count($thread_data['participants'])];
            $current_sender_index++;

            $message_type = $i === 1 ? 'response' : 'followup';
            $message_content = $this->generate_message_content(
                $thread_data['conversation_type'],
                $message_type,
                $current_sender,
                $thread_data['sender_id']
            );

            $message_id = messages_new_message([
                'sender_id' => $current_sender,
                'thread_id' => $thread_id,
                'content' => $message_content,
                'date_sent' => date('Y-m-d H:i:s', strtotime($thread_data['timestamps'][$i])),
            ]);

            if ($message_id) {
                $messages_created++;
                // Add message metadata
                bp_messages_update_meta($message_id, 'bp_playground_created', time());
            }
        }

        // Set read status for realistic patterns
        $this->set_realistic_read_status($thread_id, $thread_data['participants'], $options);

        return [
            'thread_id' => $thread_id,
            'messages_created' => $messages_created,
            'recipients_added' => $recipients_added,
        ];
    }

    /**
     * Add thread metadata
     *
     * @since 1.0.0
     * @param int $thread_id Thread ID
     * @param array $thread_data Thread data
     * @return void
     */
    private function add_thread_meta($thread_id, $thread_data) {
        // Add playground identification
        bp_messages_update_meta($thread_id, 'bp_playground_created', time());
        bp_messages_update_meta($thread_id, 'bp_playground_conversation_type', $thread_data['conversation_type']);
        bp_messages_update_meta($thread_id, 'bp_playground_message_count', $thread_data['message_count']);
    }

    /**
     * Set realistic read status for thread participants
     *
     * @since 1.0.0
     * @param int $thread_id Thread ID
     * @param array $participants Participant user IDs
     * @param array $options Generation options
     * @return void
     */
    private function set_realistic_read_status($thread_id, $participants, $options) {
        global $wpdb;

        if ($options['read_status_distribution'] !== 'realistic') {
            return;
        }

        // Set read status based on realistic patterns
        foreach ($participants as $user_id) {
            $read_probability = mt_rand(1, 100);
            
            // 70% chance the message is read
            if ($read_probability <= 70) {
                $is_read = 1;
                // Read sometime after the last message
                $read_date = date('Y-m-d H:i:s', time() - mt_rand(0, 7 * 24 * 3600));
            } else {
                $is_read = 0;
                $read_date = '0000-00-00 00:00:00';
            }

            // Update recipient read status
            $wpdb->update(
                $wpdb->base_prefix . 'bp_messages_recipients',
                [
                    'is_read' => $is_read,
                    'last_read' => $read_date,
                ],
                [
                    'thread_id' => $thread_id,
                    'user_id' => $user_id,
                ],
                ['%d', '%s'],
                ['%d', '%d']
            );
        }
    }

    /**
     * Select conversation type based on weights
     *
     * @since 1.0.0
     * @return string Selected conversation type
     */
    private function select_conversation_type() {
        $rand = mt_rand() / mt_getrandmax();
        $cumulative_weight = 0;

        foreach ($this->conversation_types as $type => $config) {
            $cumulative_weight += $config['weight'];
            if ($rand <= $cumulative_weight) {
                return $type;
            }
        }

        return 'casual'; // Fallback
    }

    /**
     * Generate message subject
     *
     * @since 1.0.0
     * @param string $conversation_type Conversation type
     * @param int $sender_id Sender user ID
     * @return string Generated subject
     */
    private function generate_message_subject($conversation_type, $sender_id) {
        $type_config = $this->conversation_types[$conversation_type];
        $subject_template = $type_config['subjects'][array_rand($type_config['subjects'])];

        // Replace placeholders
        $replacements = [
            '{project}' => $this->get_random_project_name(),
            '{initiative}' => $this->get_random_initiative(),
            '{topic}' => $this->get_random_topic(),
            '{subject}' => $this->get_random_subject(),
            '{area}' => $this->get_random_area(),
            '{professionals}' => $this->get_random_professional_group(),
            '{industry}' => $this->get_random_industry(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $subject_template);
    }

    /**
     * Get conversation length based on type and depth setting
     *
     * @since 1.0.0
     * @param string $conversation_type Conversation type
     * @param string $depth_setting Depth setting
     * @return int Number of messages
     */
    private function get_conversation_length($conversation_type, $depth_setting) {
        $type_config = $this->conversation_types[$conversation_type];
        $base_min = $type_config['min_messages'];
        $base_max = $type_config['max_messages'];

        // Adjust based on depth setting
        switch ($depth_setting) {
            case 'short':
                $max_messages = min($base_max, $base_min + 2);
                break;
            case 'medium':
                $max_messages = $base_max;
                break;
            case 'long':
                $max_messages = $base_max + mt_rand(2, 5);
                break;
            case 'mixed':
            default:
                $max_messages = $base_max;
                break;
        }

        return mt_rand($base_min, $max_messages);
    }

    /**
     * Generate conversation timestamps
     *
     * @since 1.0.0
     * @param int $start_time Start timestamp
     * @param int $message_count Number of messages
     * @return array Array of timestamps
     */
    private function generate_conversation_timestamps($start_time, $message_count) {
        $timestamps = [$start_time];

        for ($i = 1; $i < $message_count; $i++) {
            // Add realistic delays between messages (1 hour to 3 days)
            $delay = mt_rand(3600, 3 * 24 * 3600);
            $timestamps[] = $timestamps[$i - 1] + $delay;
        }

        return $timestamps;
    }

    /**
     * Generate message content
     *
     * @since 1.0.0
     * @param string $conversation_type Conversation type
     * @param string $message_type Message type (initial, response, followup)
     * @param int $sender_id Sender user ID
     * @param int $recipient_id Recipient user ID
     * @return string Generated content
     */
    private function generate_message_content($conversation_type, $message_type, $sender_id, $recipient_id) {
        $sender = get_userdata($sender_id);
        $recipient = get_userdata($recipient_id);

        // Get templates for this conversation type
        $templates = $this->message_templates[$conversation_type];

        // Select template based on message type
        if ($message_type === 'followup' || !isset($templates[$message_type])) {
            // Use response templates for follow-up messages
            $message_type = 'response';
        }

        if (!isset($templates[$message_type])) {
            $message_type = 'initial'; // Fallback
        }

        $template = $templates[$message_type][array_rand($templates[$message_type])];

        // Replace placeholders
        $replacements = [
            '{name}' => $recipient->display_name,
            '{sender_name}' => $sender->display_name,
            '{project}' => $this->get_random_project_name(),
            '{topic}' => $this->get_random_topic(),
            '{subject}' => $this->get_random_subject(),
            '{area}' => $this->get_random_area(),
            '{industry}' => $this->get_random_industry(),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $template);

        // Add some variation for follow-up messages
        if ($message_type === 'response' && mt_rand(1, 10) > 7) {
            $additional_phrases = [
                ' Looking forward to hearing back from you!',
                ' Let me know what you think.',
                ' Thanks again for reaching out.',
                ' Hope this helps!',
                ' Feel free to ask if you have any other questions.',
            ];
            $content .= $additional_phrases[array_rand($additional_phrases)];
        }

        return $content;
    }

    /**
     * Get random project name
     *
     * @since 1.0.0
     * @return string Random project name
     */
    private function get_random_project_name() {
        $projects = [
            'the new website launch', 'mobile app development', 'user research study',
            'marketing campaign', 'data analysis project', 'community outreach program',
            'product redesign', 'content strategy', 'user experience improvement',
            'digital transformation initiative', 'innovation workshop', 'team collaboration tool'
        ];
        return $projects[array_rand($projects)];
    }

    /**
     * Get random initiative
     *
     * @since 1.0.0
     * @return string Random initiative
     */
    private function get_random_initiative() {
        $initiatives = [
            'community building', 'knowledge sharing', 'mentorship program',
            'skill development', 'networking event', 'innovation challenge',
            'sustainability project', 'diversity and inclusion', 'wellness program'
        ];
        return $initiatives[array_rand($initiatives)];
    }

    /**
     * Get random topic
     *
     * @since 1.0.0
     * @return string Random topic
     */
    private function get_random_topic() {
        $topics = [
            'project management', 'web development', 'digital marketing',
            'user experience', 'data analysis', 'team collaboration',
            'innovation strategies', 'professional development', 'industry trends'
        ];
        return $topics[array_rand($topics)];
    }

    /**
     * Get random subject
     *
     * @since 1.0.0
     * @return string Random subject
     */
    private function get_random_subject() {
        $subjects = [
            'the new platform features', 'best practices', 'implementation strategies',
            'team coordination', 'resource allocation', 'timeline planning',
            'quality assurance', 'user feedback', 'performance optimization'
        ];
        return $subjects[array_rand($subjects)];
    }

    /**
     * Get random area
     *
     * @since 1.0.0
     * @return string Random area
     */
    private function get_random_area() {
        $areas = [
            'technology', 'design', 'marketing', 'business strategy',
            'product development', 'user research', 'data science',
            'project management', 'team leadership', 'innovation'
        ];
        return $areas[array_rand($areas)];
    }

    /**
     * Get random professional group
     *
     * @since 1.0.0
     * @return string Random professional group
     */
    private function get_random_professional_group() {
        $groups = [
            'developers', 'designers', 'marketers', 'entrepreneurs',
            'consultants', 'managers', 'analysts', 'researchers',
            'educators', 'innovators', 'strategists', 'specialists'
        ];
        return $groups[array_rand($groups)];
    }

    /**
     * Get random industry
     *
     * @since 1.0.0
     * @return string Random industry
     */
    private function get_random_industry() {
        $industries = [
            'technology', 'healthcare', 'education', 'finance',
            'marketing', 'consulting', 'media', 'retail',
            'manufacturing', 'non-profit', 'government', 'startup'
        ];
        return $industries[array_rand($industries)];
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
     * Get module statistics
     *
     * @since 1.0.0
     * @return array Module statistics
     */
    public function get_stats() {
        if (!bp_is_active('messages')) {
            return [];
        }

        global $wpdb;

        $stats = [
            'total_threads' => 0,
            'total_messages' => 0,
            'playground_threads' => 0,
            'playground_messages' => 0,
            'unread_messages' => 0,
            'average_thread_length' => 0,
        ];

        // Total threads and messages
        $stats['total_threads'] = $wpdb->get_var("SELECT COUNT(DISTINCT thread_id) FROM {$wpdb->base_prefix}bp_messages_messages");
        $stats['total_messages'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_messages_messages");

        // Playground threads
        $stats['playground_threads'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_messages_meta WHERE meta_key = 'bp_playground_created'"
        );

        if ($stats['playground_threads'] > 0) {
            // Get playground thread IDs
            $playground_thread_ids = $wpdb->get_col(
                "SELECT message_id FROM {$wpdb->base_prefix}bp_messages_meta WHERE meta_key = 'bp_playground_created'"
            );

            if (!empty($playground_thread_ids)) {
                $thread_ids_list = implode(',', array_map('intval', $playground_thread_ids));

                // Count playground messages
                $stats['playground_messages'] = $wpdb->get_var(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_messages_messages WHERE thread_id IN ({$thread_ids_list})"
                );

                // Calculate average thread length
                if ($stats['playground_threads'] > 0) {
                    $stats['average_thread_length'] = round($stats['playground_messages'] / $stats['playground_threads'], 1);
                }
            }
        }

        // Unread messages
        $stats['unread_messages'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_messages_recipients WHERE is_read = 0"
        );

        return $stats;
    }

    /**
     * Clean up messages data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function cleanup($options = []) {
        if (!bp_is_active('messages')) {
            return ['threads_removed' => 0];
        }

        global $wpdb;

        $defaults = [
            'remove_threads' => true,
            'older_than_days' => 0,
            'dry_run' => false,
        ];

        $options = wp_parse_args($options, $defaults);
        
        $results = [
            'threads_removed' => 0,
            'messages_removed' => 0,
            'recipients_removed' => 0,
            'meta_cleaned' => 0,
        ];

        if (!$options['remove_threads']) {
            return $results;
        }

        // Get playground threads
        $thread_query = "
            SELECT mm.thread_id, COUNT(mm.id) as message_count
            FROM {$wpdb->base_prefix}bp_messages_messages mm 
            INNER JOIN {$wpdb->base_prefix}bp_messages_meta mmt ON mm.thread_id = mmt.message_id 
            WHERE mmt.meta_key = 'bp_playground_created'
        ";

        if ($options['older_than_days'] > 0) {
            $timestamp = time() - ($options['older_than_days'] * 24 * 3600);
            $thread_query .= $wpdb->prepare(" AND mmt.meta_value < %d", $timestamp);
        }

        $thread_query .= " GROUP BY mm.thread_id";

        $threads_to_remove = $wpdb->get_results($thread_query);

        if (!$options['dry_run']) {
            foreach ($threads_to_remove as $thread_data) {
                // Count recipients before deletion
                $recipient_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_messages_recipients WHERE thread_id = %d",
                    $thread_data->thread_id
                ));

                // Delete thread (this handles messages and recipients automatically)
                if (messages_delete_thread($thread_data->thread_id)) {
                    $results['threads_removed']++;
                    $results['messages_removed'] += $thread_data->message_count;
                    $results['recipients_removed'] += $recipient_count;
                }
            }

            // Clean up any remaining meta
            $meta_cleaned = $wpdb->query(
                "DELETE FROM {$wpdb->base_prefix}bp_messages_meta WHERE meta_key LIKE 'bp_playground_%'"
            );
            $results['meta_cleaned'] = $meta_cleaned;
        } else {
            foreach ($threads_to_remove as $thread_data) {
                $results['threads_removed']++;
                $results['messages_removed'] += $thread_data->message_count;
                
                $recipient_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_messages_recipients WHERE thread_id = %d",
                    $thread_data->thread_id
                ));
                $results['recipients_removed'] += $recipient_count;
            }
        }

        return $results;
    }
}