<?php
/**
 * BuddyPress Playground Data Model
 *
 * @package BuddyPress_Playground
 * @subpackage Core
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Data modeling and realistic data generation patterns
 *
 * @since 1.0.0
 */
class BP_Playground_Data_Model {

    /**
     * User distribution patterns
     *
     * @since 1.0.0
     * @var array
     */
    private $user_patterns = [
        'activity_levels' => [
            'highly_active' => 0.15,    // 15% - Power users
            'moderately_active' => 0.25, // 25% - Regular users
            'occasionally_active' => 0.35, // 35% - Casual users
            'rarely_active' => 0.20,    // 20% - Lurkers
            'inactive' => 0.05,         // 5% - Inactive accounts
        ],
        'engagement_patterns' => [
            'content_creators' => 0.10,  // 10% - Create most content
            'active_participants' => 0.30, // 30% - Regular participants
            'passive_consumers' => 0.50,  // 50% - Mostly consume content
            'lurkers' => 0.10,           // 10% - Rarely engage
        ],
    ];

    /**
     * Content distribution patterns (Pareto principle)
     *
     * @since 1.0.0
     * @var array
     */
    private $content_patterns = [
        'viral_content' => 0.05,      // 5% gets 50% of engagement
        'popular_content' => 0.15,    // 15% gets 30% of engagement
        'average_content' => 0.60,    // 60% gets 15% of engagement
        'low_engagement' => 0.20,     // 20% gets 5% of engagement
    ];

    /**
     * Social network patterns
     *
     * @since 1.0.0
     * @var array
     */
    private $social_patterns = [
        'influencers' => 0.05,        // 5% - High connection count
        'connectors' => 0.15,         // 15% - Above average connections
        'typical_users' => 0.60,      // 60% - Average connections
        'introverts' => 0.20,         // 20% - Few connections
    ];

    /**
     * Generate user personas with realistic distribution
     *
     * @since 1.0.0
     * @param int $user_count Total number of users
     * @return array User persona distribution
     */
    public function generate_user_personas($user_count) {
        $personas = [
            'tech_professional' => [
                'weight' => 0.25,
                'activity_level' => 'moderately_active',
                'engagement' => 'active_participants',
                'social_level' => 'connectors',
            ],
            'creative_professional' => [
                'weight' => 0.20,
                'activity_level' => 'highly_active',
                'engagement' => 'content_creators',
                'social_level' => 'connectors',
            ],
            'business_professional' => [
                'weight' => 0.20,
                'activity_level' => 'occasionally_active',
                'engagement' => 'passive_consumers',
                'social_level' => 'typical_users',
            ],
            'educator' => [
                'weight' => 0.15,
                'activity_level' => 'moderately_active',
                'engagement' => 'content_creators',
                'social_level' => 'typical_users',
            ],
            'student' => [
                'weight' => 0.20,
                'activity_level' => 'highly_active',
                'engagement' => 'active_participants',
                'social_level' => 'typical_users',
            ],
        ];

        $distribution = [];
        foreach ($personas as $persona_key => $persona_data) {
            $count = round($user_count * $persona_data['weight']);
            $distribution[$persona_key] = [
                'count' => $count,
                'data' => $persona_data,
            ];
        }

        return $distribution;
    }

    /**
     * Generate realistic activity patterns
     *
     * @since 1.0.0
     * @param array $user_ids Array of user IDs
     * @param int $total_activities Total activities to distribute
     * @return array Activity distribution per user
     */
    public function generate_activity_patterns($user_ids, $total_activities) {
        $user_count = count($user_ids);
        $activity_distribution = [];

        // Assign activity levels to users
        $activity_levels = $this->distribute_users_by_pattern($user_ids, $this->user_patterns['activity_levels']);

        // Calculate activity counts based on levels
        $level_multipliers = [
            'highly_active' => 10,
            'moderately_active' => 5,
            'occasionally_active' => 2,
            'rarely_active' => 0.5,
            'inactive' => 0.1,
        ];

        $total_weighted_activities = 0;
        foreach ($activity_levels as $level => $users) {
            $total_weighted_activities += count($users) * $level_multipliers[$level];
        }

        $base_activity_count = $total_activities / max($total_weighted_activities, 1);

        foreach ($activity_levels as $level => $users) {
            $activities_per_user = round($base_activity_count * $level_multipliers[$level]);
            foreach ($users as $user_id) {
                $activity_distribution[$user_id] = max(0, $activities_per_user + rand(-2, 3));
            }
        }

        return $activity_distribution;
    }

    /**
     * Generate realistic group membership patterns
     *
     * @since 1.0.0
     * @param array $user_ids Array of user IDs
     * @param array $group_ids Array of group IDs
     * @return array Membership patterns
     */
    public function generate_group_membership_patterns($user_ids, $group_ids) {
        $membership_patterns = [];
        $group_count = count($group_ids);

        // Assign social levels to users
        $social_levels = $this->distribute_users_by_pattern($user_ids, $this->social_patterns);

        // Define group membership ranges by social level
        $membership_ranges = [
            'influencers' => ['min' => round($group_count * 0.3), 'max' => round($group_count * 0.7)],
            'connectors' => ['min' => round($group_count * 0.15), 'max' => round($group_count * 0.4)],
            'typical_users' => ['min' => 2, 'max' => round($group_count * 0.2)],
            'introverts' => ['min' => 0, 'max' => 3],
        ];

        foreach ($social_levels as $level => $users) {
            $range = $membership_ranges[$level];
            foreach ($users as $user_id) {
                $group_count_for_user = rand($range['min'], $range['max']);
                $selected_groups = array_rand(array_flip($group_ids), min($group_count_for_user, count($group_ids)));
                
                if (!is_array($selected_groups)) {
                    $selected_groups = [$selected_groups];
                }

                $membership_patterns[$user_id] = $selected_groups;
            }
        }

        return $membership_patterns;
    }

    /**
     * Generate realistic friend connection patterns
     *
     * @since 1.0.0
     * @param array $user_ids Array of user IDs
     * @return array Friend connection patterns
     */
    public function generate_friend_patterns($user_ids) {
        $user_count = count($user_ids);
        $friend_patterns = [];

        // Assign social levels
        $social_levels = $this->distribute_users_by_pattern($user_ids, $this->social_patterns);

        // Define friend count ranges by social level
        $friend_ranges = [
            'influencers' => ['min' => round($user_count * 0.05), 'max' => round($user_count * 0.15)],
            'connectors' => ['min' => round($user_count * 0.02), 'max' => round($user_count * 0.08)],
            'typical_users' => ['min' => 5, 'max' => round($user_count * 0.03)],
            'introverts' => ['min' => 1, 'max' => 8],
        ];

        foreach ($social_levels as $level => $users) {
            $range = $friend_ranges[$level];
            foreach ($users as $user_id) {
                $friend_count = rand($range['min'], min($range['max'], $user_count - 1));
                
                // Select random friends (excluding self)
                $potential_friends = array_diff($user_ids, [$user_id]);
                $selected_friends = array_rand(array_flip($potential_friends), min($friend_count, count($potential_friends)));
                
                if (!is_array($selected_friends)) {
                    $selected_friends = [$selected_friends];
                }

                $friend_patterns[$user_id] = $selected_friends;
            }
        }

        return $friend_patterns;
    }

    /**
     * Generate realistic message patterns
     *
     * @since 1.0.0
     * @param array $user_ids Array of user IDs
     * @param int $total_messages Total messages to distribute
     * @return array Message patterns
     */
    public function generate_message_patterns($user_ids, $total_messages) {
        $message_patterns = [];
        
        // Assign engagement levels
        $engagement_levels = $this->distribute_users_by_pattern($user_ids, $this->user_patterns['engagement_patterns']);

        // Calculate message distribution
        $level_multipliers = [
            'content_creators' => 8,
            'active_participants' => 4,
            'passive_consumers' => 1,
            'lurkers' => 0.2,
        ];

        $total_weighted_messages = 0;
        foreach ($engagement_levels as $level => $users) {
            $total_weighted_messages += count($users) * $level_multipliers[$level];
        }

        $base_message_count = $total_messages / max($total_weighted_messages, 1);

        foreach ($engagement_levels as $level => $users) {
            $messages_per_user = round($base_message_count * $level_multipliers[$level]);
            foreach ($users as $user_id) {
                $message_patterns[$user_id] = max(0, $messages_per_user + rand(-1, 2));
            }
        }

        return $message_patterns;
    }

    /**
     * Generate content engagement patterns (likes, comments, shares)
     *
     * @since 1.0.0
     * @param array $content_ids Array of content IDs
     * @param array $user_ids Array of user IDs
     * @param string $engagement_type Type of engagement (likes, comments, shares)
     * @return array Engagement patterns
     */
    public function generate_engagement_patterns($content_ids, $user_ids, $engagement_type = 'likes') {
        $engagement_patterns = [];
        $content_count = count($content_ids);
        
        // Distribute content by popularity using Pareto principle
        $content_distribution = $this->distribute_content_by_popularity($content_ids);
        
        // Define engagement rates by content popularity
        $engagement_rates = [
            'viral_content' => ['min' => 0.3, 'max' => 0.8],      // 30-80% engagement
            'popular_content' => ['min' => 0.1, 'max' => 0.3],    // 10-30% engagement
            'average_content' => ['min' => 0.02, 'max' => 0.1],   // 2-10% engagement
            'low_engagement' => ['min' => 0.001, 'max' => 0.02],  // 0.1-2% engagement
        ];

        foreach ($content_distribution as $popularity => $content_list) {
            $rate_range = $engagement_rates[$popularity];
            
            foreach ($content_list as $content_id) {
                $engagement_rate = $this->random_float($rate_range['min'], $rate_range['max']);
                $engagement_count = round(count($user_ids) * $engagement_rate);
                
                // Select random users for engagement
                if ($engagement_count > 0) {
                    $engaging_users = array_rand(array_flip($user_ids), min($engagement_count, count($user_ids)));
                    if (!is_array($engaging_users)) {
                        $engaging_users = [$engaging_users];
                    }
                    $engagement_patterns[$content_id] = $engaging_users;
                } else {
                    $engagement_patterns[$content_id] = [];
                }
            }
        }

        return $engagement_patterns;
    }

    /**
     * Generate temporal activity patterns (time-based activity distribution)
     *
     * @since 1.0.0
     * @param int $days_back Number of days to generate patterns for
     * @param int $total_activities Total activities to distribute
     * @return array Temporal patterns with timestamps
     */
    public function generate_temporal_patterns($days_back = 90, $total_activities = 10000) {
        $temporal_patterns = [];
        $now = time();
        
        // Activity distribution over time (more recent = more activity)
        $time_weights = [];
        for ($day = 0; $day < $days_back; $day++) {
            // Exponential decay - recent days have more weight
            $weight = exp(-$day / 30); // 30-day half-life
            $time_weights[$day] = $weight;
        }
        
        $total_weight = array_sum($time_weights);
        
        // Distribute activities across days
        $daily_activities = [];
        foreach ($time_weights as $day => $weight) {
            $activity_count = round(($weight / $total_weight) * $total_activities);
            $daily_activities[$day] = $activity_count;
        }
        
        // Generate specific timestamps for each day
        foreach ($daily_activities as $day => $activity_count) {
            $day_start = $now - ($day * 24 * 3600);
            $day_end = $day_start + (24 * 3600);
            
            for ($i = 0; $i < $activity_count; $i++) {
                // Use realistic daily activity patterns (peak hours)
                $hour_weights = [
                    0 => 0.5, 1 => 0.3, 2 => 0.2, 3 => 0.1, 4 => 0.1, 5 => 0.2,
                    6 => 0.5, 7 => 1.0, 8 => 1.5, 9 => 2.0, 10 => 2.5, 11 => 3.0,
                    12 => 3.5, 13 => 3.0, 14 => 2.5, 15 => 2.8, 16 => 3.2, 17 => 3.5,
                    18 => 3.8, 19 => 4.0, 20 => 3.5, 21 => 3.0, 22 => 2.0, 23 => 1.0,
                ];
                
                $hour = $this->weighted_random($hour_weights);
                $minute = rand(0, 59);
                $second = rand(0, 59);
                
                $timestamp = $day_start + ($hour * 3600) + ($minute * 60) + $second;
                $temporal_patterns[] = date('Y-m-d H:i:s', $timestamp);
            }
        }
        
        // Sort timestamps in chronological order
        sort($temporal_patterns);
        
        return $temporal_patterns;
    }

    /**
     * Generate realistic forum activity patterns
     *
     * @since 1.0.0
     * @param array $forum_ids Array of forum IDs
     * @param array $user_ids Array of user IDs
     * @return array Forum activity patterns
     */
    public function generate_forum_patterns($forum_ids, $user_ids) {
        $forum_patterns = [];
        
        // Assign forum popularity levels
        $forum_popularity = $this->distribute_content_by_popularity($forum_ids);
        
        // Define topic and reply counts by popularity
        $activity_ranges = [
            'viral_content' => ['topics' => [50, 200], 'replies_per_topic' => [10, 100]],
            'popular_content' => ['topics' => [20, 80], 'replies_per_topic' => [5, 30]],
            'average_content' => ['topics' => [5, 25], 'replies_per_topic' => [1, 10]],
            'low_engagement' => ['topics' => [1, 8], 'replies_per_topic' => [0, 3]],
        ];
        
        foreach ($forum_popularity as $popularity => $forum_list) {
            $ranges = $activity_ranges[$popularity];
            
            foreach ($forum_list as $forum_id) {
                $topic_count = rand($ranges['topics'][0], $ranges['topics'][1]);
                $topics = [];
                
                for ($i = 0; $i < $topic_count; $i++) {
                    $reply_count = rand($ranges['replies_per_topic'][0], $ranges['replies_per_topic'][1]);
                    $topic_author = $user_ids[array_rand($user_ids)];
                    
                    // Select reply authors
                    $reply_authors = [];
                    for ($j = 0; $j < $reply_count; $j++) {
                        $reply_authors[] = $user_ids[array_rand($user_ids)];
                    }
                    
                    $topics[] = [
                        'author' => $topic_author,
                        'replies' => $reply_authors,
                        'reply_count' => $reply_count,
                    ];
                }
                
                $forum_patterns[$forum_id] = [
                    'topic_count' => $topic_count,
                    'topics' => $topics,
                ];
            }
        }
        
        return $forum_patterns;
    }

    /**
     * Distribute users by pattern weights
     *
     * @since 1.0.0
     * @param array $user_ids Array of user IDs
     * @param array $pattern Pattern weights
     * @return array Users distributed by pattern
     */
    private function distribute_users_by_pattern($user_ids, $pattern) {
        $distribution = [];
        $user_count = count($user_ids);
        $shuffled_users = $user_ids;
        shuffle($shuffled_users);
        
        $start_index = 0;
        foreach ($pattern as $level => $weight) {
            $count = round($user_count * $weight);
            $end_index = min($start_index + $count, $user_count);
            
            $distribution[$level] = array_slice($shuffled_users, $start_index, $end_index - $start_index);
            $start_index = $end_index;
        }
        
        return $distribution;
    }

    /**
     * Distribute content by popularity using Pareto principle
     *
     * @since 1.0.0
     * @param array $content_ids Array of content IDs
     * @return array Content distributed by popularity
     */
    private function distribute_content_by_popularity($content_ids) {
        $distribution = [];
        $content_count = count($content_ids);
        $shuffled_content = $content_ids;
        shuffle($shuffled_content);
        
        $start_index = 0;
        foreach ($this->content_patterns as $popularity => $weight) {
            $count = round($content_count * $weight);
            $end_index = min($start_index + $count, $content_count);
            
            $distribution[$popularity] = array_slice($shuffled_content, $start_index, $end_index - $start_index);
            $start_index = $end_index;
        }
        
        return $distribution;
    }

    /**
     * Generate random float between min and max
     *
     * @since 1.0.0
     * @param float $min Minimum value
     * @param float $max Maximum value
     * @return float Random float
     */
    private function random_float($min = 0, $max = 1) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    /**
     * Select weighted random value
     *
     * @since 1.0.0
     * @param array $weights Array of weights
     * @return mixed Selected key
     */
    private function weighted_random($weights) {
        $total_weight = array_sum($weights);
        $random = mt_rand() / mt_getrandmax() * $total_weight;
        
        $cumulative = 0;
        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $key;
            }
        }
        
        // Fallback to first key
        return array_keys($weights)[0];
    }

    /**
     * Get realistic interaction relationships
     *
     * @since 1.0.0
     * @param array $user_ids Array of user IDs
     * @param float $density Connection density (0.0 to 1.0)
     * @return array Interaction relationships
     */
    public function get_interaction_relationships($user_ids, $density = 0.1) {
        $relationships = [];
        $user_count = count($user_ids);
        $max_connections = round($user_count * $density);
        
        foreach ($user_ids as $user_id) {
            $connection_count = rand(1, min($max_connections, $user_count - 1));
            $potential_connections = array_diff($user_ids, [$user_id]);
            
            $connections = array_rand(array_flip($potential_connections), min($connection_count, count($potential_connections)));
            if (!is_array($connections)) {
                $connections = [$connections];
            }
            
            $relationships[$user_id] = $connections;
        }
        
        return $relationships;
    }

    /**
     * Calculate optimal batch sizes based on data size
     *
     * @since 1.0.0
     * @param int $total_items Total items to process
     * @param int $memory_limit Memory limit in bytes
     * @return array Optimal batch configuration
     */
    public function calculate_optimal_batch_size($total_items, $memory_limit = null) {
        if (!$memory_limit) {
            $memory_limit = $this->parse_memory_limit(ini_get('memory_limit'));
        }
        
        // Estimate memory per item (conservative estimate)
        $memory_per_item = 1024; // 1KB per item
        $safety_margin = 0.7; // Use only 70% of available memory
        
        $available_memory = $memory_limit * $safety_margin;
        $optimal_batch_size = floor($available_memory / $memory_per_item);
        
        // Ensure reasonable bounds
        $optimal_batch_size = max(10, min($optimal_batch_size, 1000));
        
        $batch_count = ceil($total_items / $optimal_batch_size);
        
        return [
            'batch_size' => $optimal_batch_size,
            'batch_count' => $batch_count,
            'memory_per_batch' => $optimal_batch_size * $memory_per_item,
            'estimated_total_memory' => $memory_per_item * $total_items,
        ];
    }

    /**
     * Parse memory limit string to bytes
     *
     * @since 1.0.0
     * @param string $memory_limit Memory limit string
     * @return int Memory limit in bytes
     */
    private function parse_memory_limit($memory_limit) {
        $unit = strtoupper(substr($memory_limit, -1));
        $value = (int) $memory_limit;
        
        switch ($unit) {
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
        }
        
        return $value;
    }
}