<?php
/**
 * BuddyPress Playground Friends Module
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
 * Friends Module - Generate realistic friendship networks and connections
 *
 * @since 1.0.0
 */
class BP_Playground_Friends_Module extends BP_Playground_Abstract_Module {

    /**
     * Module name
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_name = 'friends';

    /**
     * Module description
     *
     * @since 1.0.0
     * @var string
     */
    protected $module_description = 'Generate realistic friendship networks and social connections';

    /**
     * Friendship patterns for different user types
     *
     * @since 1.0.0
     * @var array
     */
    private $friendship_patterns = [
        'influencer' => [
            'weight' => 0.05,
            'friend_range' => [50, 200],
            'acceptance_rate' => 0.7,
            'initiation_rate' => 0.3,
        ],
        'connector' => [
            'weight' => 0.15,
            'friend_range' => [20, 80],
            'acceptance_rate' => 0.8,
            'initiation_rate' => 0.6,
        ],
        'social' => [
            'weight' => 0.30,
            'friend_range' => [10, 40],
            'acceptance_rate' => 0.9,
            'initiation_rate' => 0.7,
        ],
        'average' => [
            'weight' => 0.35,
            'friend_range' => [5, 20],
            'acceptance_rate' => 0.85,
            'initiation_rate' => 0.5,
        ],
        'reserved' => [
            'weight' => 0.15,
            'friend_range' => [1, 10],
            'acceptance_rate' => 0.6,
            'initiation_rate' => 0.2,
        ],
    ];

    /**
     * Generate friends connections
     *
     * @since 1.0.0
     * @param array $args Generation arguments
     * @return array|WP_Error Generation results
     */
    public function generate($args = []) {
        if (!bp_is_active('friends')) {
            return new WP_Error('friends_disabled', __('Friends component is not active.', BP_PLAYGROUND_TEXT_DOMAIN));
        }

        $defaults = [
            'network_density' => 0.1, // 10% connection rate
            'clustering' => true, // Create realistic clustering
            'pending_requests' => true, // Include pending friend requests
            'pending_rate' => 0.15, // 15% of requests remain pending
            'mutual_connections' => true, // Ensure mutual connections
            'batch_size' => 50,
        ];

        $args = wp_parse_args($args, $defaults);

        // Validate arguments
        $validation_rules = [
            'network_density' => ['type' => 'float', 'min' => 0.01, 'max' => 0.5],
            'pending_rate' => ['type' => 'float', 'min' => 0.0, 'max' => 0.5],
            'batch_size' => ['type' => 'int', 'min' => 1, 'max' => 200],
            'clustering' => ['type' => 'bool'],
            'pending_requests' => ['type' => 'bool'],
            'mutual_connections' => ['type' => 'bool'],
        ];

        $validated_args = $this->validate_args($args, $validation_rules);
        if (is_wp_error($validated_args)) {
            return $validated_args;
        }

        $this->start_generation('Friend Network Creation');

        $results = [
            'friendships_created' => 0,
            'pending_requests' => 0,
            'mutual_connections' => 0,
            'errors' => [],
        ];

        try {
            // Get available users
            $user_ids = $this->get_available_user_ids();
            if (count($user_ids) < 2) {
                throw new Exception('At least 2 users are required for friendship generation. Please create users first.');
            }

            // Generate social network patterns
            $data_model = bp_playground_get_module('data_model');
            $friend_patterns = $data_model->generate_friend_patterns($user_ids);

            // Create friendship connections
            $friendship_results = $this->create_friendships($friend_patterns, $validated_args);
            $results = array_merge($results, $friendship_results);

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            $this->log_error('Friends generation failed: ' . $e->getMessage());
        }

        $this->end_generation();
        return $results;
    }

    /**
     * Create friendships based on patterns
     *
     * @since 1.0.0
     * @param array $friend_patterns Friend patterns
     * @param array $options Generation options
     * @return array Creation results
     */
    private function create_friendships($friend_patterns, $options) {
        $results = [
            'friendships_created' => 0,
            'pending_requests' => 0,
            'mutual_connections' => 0,
            'errors' => [],
        ];

        $processed_pairs = []; // Track processed user pairs to avoid duplicates

        foreach ($friend_patterns as $user_id => $friend_list) {
            foreach ($friend_list as $friend_id) {
                // Create unique pair key to avoid duplicates
                $pair_key = $user_id < $friend_id ? "{$user_id}-{$friend_id}" : "{$friend_id}-{$user_id}";
                
                if (isset($processed_pairs[$pair_key])) {
                    continue; // Skip if already processed
                }
                
                $processed_pairs[$pair_key] = true;

                try {
                    $friendship_result = $this->create_single_friendship($user_id, $friend_id, $options);
                    
                    if (!is_wp_error($friendship_result)) {
                        $results['friendships_created']++;
                        
                        if ($friendship_result['is_pending']) {
                            $results['pending_requests']++;
                        }
                        
                        if ($friendship_result['is_mutual']) {
                            $results['mutual_connections']++;
                        }

                        // Update core stats
                        $core = bp_playground_get_module('core');
                        if ($core) {
                            $core->increment_stat('friends_created');
                        }
                    } else {
                        $results['errors'][] = $friendship_result->get_error_message();
                    }

                } catch (Exception $e) {
                    $results['errors'][] = "Error creating friendship {$user_id}-{$friend_id}: " . $e->getMessage();
                }
            }

            // Progress update for every 50 users
            if (array_search($user_id, array_keys($friend_patterns)) % 50 === 0) {
                $processed = array_search($user_id, array_keys($friend_patterns)) + 1;
                $total = count($friend_patterns);
                $this->show_progress($processed, $total, 'Creating friendships');
            }
        }

        return $results;
    }

    /**
     * Create a single friendship
     *
     * @since 1.0.0
     * @param int $initiator_id Initiator user ID
     * @param int $friend_id Friend user ID
     * @param array $options Creation options
     * @return array|WP_Error Friendship creation result
     */
    private function create_single_friendship($initiator_id, $friend_id, $options) {
        // Check if friendship already exists
        if (friends_check_friendship($initiator_id, $friend_id)) {
            return new WP_Error('friendship_exists', 'Friendship already exists');
        }

        // Determine if this should be a pending request
        $is_pending = $options['pending_requests'] && (mt_rand(1, 100) <= ($options['pending_rate'] * 100));
        
        // Create friendship
        if ($is_pending) {
            $friendship_id = friends_add_friend($initiator_id, $friend_id, false);
        } else {
            $friendship_id = friends_add_friend($initiator_id, $friend_id, true);
        }

        if (!$friendship_id) {
            return new WP_Error('friendship_creation_failed', 'Failed to create friendship');
        }

        // Add friendship metadata
        $this->add_friendship_meta($friendship_id, $initiator_id, $friend_id);

        // Set realistic timestamps
        $this->set_friendship_timestamps($friendship_id, $is_pending);

        return [
            'friendship_id' => $friendship_id,
            'is_pending' => $is_pending,
            'is_mutual' => !$is_pending,
        ];
    }

    /**
     * Add friendship metadata
     *
     * @since 1.0.0
     * @param int $friendship_id Friendship ID
     * @param int $initiator_id Initiator user ID
     * @param int $friend_id Friend user ID
     * @return void
     */
    private function add_friendship_meta($friendship_id, $initiator_id, $friend_id) {
        // Add playground identification
        bp_friends_update_meta($friendship_id, 'bp_playground_created', time());
        
        // Add connection context (how they might have met)
        $connection_contexts = [
            'mutual_friends', 'shared_group', 'similar_interests', 
            'professional_network', 'community_event', 'online_interaction'
        ];
        $context = $connection_contexts[array_rand($connection_contexts)];
        bp_friends_update_meta($friendship_id, 'bp_playground_connection_context', $context);

        // Add interaction score (simulated)
        $interaction_score = mt_rand(1, 100);
        bp_friends_update_meta($friendship_id, 'bp_playground_interaction_score', $interaction_score);
    }

    /**
     * Set realistic friendship timestamps
     *
     * @since 1.0.0
     * @param int $friendship_id Friendship ID
     * @param bool $is_pending Whether friendship is pending
     * @return void
     */
    private function set_friendship_timestamps($friendship_id, $is_pending) {
        global $wpdb;

        // Set date_created to random time in the past 6 months
        $date_created = date('Y-m-d H:i:s', time() - mt_rand(0, 6 * 30 * 24 * 3600));

        $wpdb->update(
            $wpdb->base_prefix . 'bp_friends',
            ['date_created' => $date_created],
            ['id' => $friendship_id],
            ['%s'],
            ['%d']
        );

        // If not pending, set is_confirmed and date_created appropriately
        if (!$is_pending) {
            $wpdb->update(
                $wpdb->base_prefix . 'bp_friends',
                [
                    'is_confirmed' => 1,
                    'date_created' => $date_created,
                ],
                ['id' => $friendship_id],
                ['%d', '%s'],
                ['%d']
            );
        }
    }

    /**
     * Create friend request notifications
     *
     * @since 1.0.0
     * @param array $pending_friendships Array of pending friendship data
     * @return int Number of notifications created
     */
    private function create_friend_notifications($pending_friendships) {
        if (!bp_is_active('notifications')) {
            return 0;
        }

        $notifications_created = 0;

        foreach ($pending_friendships as $friendship) {
            $notification_id = bp_notifications_add_notification([
                'user_id' => $friendship['friend_id'],
                'item_id' => $friendship['initiator_id'],
                'secondary_item_id' => $friendship['friendship_id'],
                'component_name' => 'friends',
                'component_action' => 'friendship_request',
                'date_notified' => $friendship['date_created'],
                'is_new' => mt_rand(0, 1), // Random read status
            ]);

            if ($notification_id) {
                $notifications_created++;
            }
        }

        return $notifications_created;
    }

    /**
     * Analyze and improve network connectivity
     *
     * @since 1.0.0
     * @param array $user_ids Available user IDs
     * @param float $target_density Target network density
     * @return array Analysis results
     */
    private function analyze_network_connectivity($user_ids, $target_density = 0.1) {
        $total_possible_connections = count($user_ids) * (count($user_ids) - 1) / 2;
        $target_connections = round($total_possible_connections * $target_density);

        // Count existing friendships
        $existing_connections = $this->count_existing_friendships($user_ids);

        $analysis = [
            'total_users' => count($user_ids),
            'total_possible_connections' => $total_possible_connections,
            'target_connections' => $target_connections,
            'existing_connections' => $existing_connections,
            'density' => $total_possible_connections > 0 ? $existing_connections / $total_possible_connections : 0,
            'target_density' => $target_density,
            'connections_needed' => max(0, $target_connections - $existing_connections),
        ];

        return $analysis;
    }

    /**
     * Count existing friendships for users
     *
     * @since 1.0.0
     * @param array $user_ids User IDs to count friendships for
     * @return int Number of existing friendships
     */
    private function count_existing_friendships($user_ids) {
        if (empty($user_ids)) {
            return 0;
        }

        global $wpdb;

        $user_ids_list = implode(',', array_map('intval', $user_ids));
        
        $count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_friends 
             WHERE (initiator_user_id IN ({$user_ids_list}) OR friend_user_id IN ({$user_ids_list}))
             AND is_confirmed = 1"
        );

        return intval($count);
    }

    /**
     * Get user friendship statistics
     *
     * @since 1.0.0
     * @param int $user_id User ID
     * @return array User friendship statistics
     */
    private function get_user_friendship_stats($user_id) {
        global $wpdb;

        $stats = [
            'total_friends' => 0,
            'pending_sent' => 0,
            'pending_received' => 0,
            'friendship_ratio' => 0,
        ];

        // Total confirmed friends
        $stats['total_friends'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_friends 
             WHERE (initiator_user_id = %d OR friend_user_id = %d) AND is_confirmed = 1",
            $user_id, $user_id
        ));

        // Pending requests sent by user
        $stats['pending_sent'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_friends 
             WHERE initiator_user_id = %d AND is_confirmed = 0",
            $user_id
        ));

        // Pending requests received by user
        $stats['pending_received'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_friends 
             WHERE friend_user_id = %d AND is_confirmed = 0",
            $user_id
        ));

        // Calculate friendship ratio (friends / (friends + pending))
        $total_connections = $stats['total_friends'] + $stats['pending_sent'] + $stats['pending_received'];
        $stats['friendship_ratio'] = $total_connections > 0 ? $stats['total_friends'] / $total_connections : 0;

        return $stats;
    }

    /**
     * Create clustered friendships (friends of friends)
     *
     * @since 1.0.0
     * @param array $user_ids Available user IDs
     * @param int $cluster_count Number of clusters to create
     * @return int Number of clustered friendships created
     */
    private function create_clustered_friendships($user_ids, $cluster_count = 5) {
        $clustered_friendships = 0;
        
        // Divide users into clusters
        $cluster_size = ceil(count($user_ids) / $cluster_count);
        $clusters = array_chunk($user_ids, $cluster_size);

        foreach ($clusters as $cluster) {
            if (count($cluster) < 3) {
                continue; // Skip small clusters
            }

            // Create more connections within cluster
            $cluster_density = 0.3; // Higher density within clusters
            $cluster_connections = $this->create_cluster_connections($cluster, $cluster_density);
            $clustered_friendships += $cluster_connections;
        }

        return $clustered_friendships;
    }

    /**
     * Create connections within a cluster
     *
     * @since 1.0.0
     * @param array $cluster_users Users in the cluster
     * @param float $density Connection density within cluster
     * @return int Number of connections created
     */
    private function create_cluster_connections($cluster_users, $density = 0.3) {
        $connections_created = 0;
        $cluster_size = count($cluster_users);
        $max_connections = $cluster_size * ($cluster_size - 1) / 2;
        $target_connections = round($max_connections * $density);

        $attempts = 0;
        while ($connections_created < $target_connections && $attempts < $target_connections * 3) {
            $user1 = $cluster_users[array_rand($cluster_users)];
            $user2 = $cluster_users[array_rand($cluster_users)];

            if ($user1 !== $user2 && !friends_check_friendship($user1, $user2)) {
                $friendship_id = friends_add_friend($user1, $user2, true);
                if ($friendship_id) {
                    $this->add_friendship_meta($friendship_id, $user1, $user2);
                    $this->set_friendship_timestamps($friendship_id, false);
                    $connections_created++;
                }
            }
            $attempts++;
        }

        return $connections_created;
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
        if (!bp_is_active('friends')) {
            return [];
        }

        global $wpdb;

        $stats = [
            'total_friendships' => 0,
            'confirmed_friendships' => 0,
            'pending_friendships' => 0,
            'playground_friendships' => 0,
            'network_density' => 0,
            'average_friends_per_user' => 0,
            'most_connected_user' => 0,
        ];

        // Total friendships
        $stats['total_friendships'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_friends");
        
        // Confirmed friendships
        $stats['confirmed_friendships'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_friends WHERE is_confirmed = 1");
        
        // Pending friendships
        $stats['pending_friendships'] = $stats['total_friendships'] - $stats['confirmed_friendships'];

        // Playground friendships
        $stats['playground_friendships'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_friends_meta WHERE meta_key = 'bp_playground_created'"
        );

        // Calculate network density and average friends per user
        $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users} WHERE ID > 1");
        if ($total_users > 1) {
            $max_possible_friendships = $total_users * ($total_users - 1) / 2;
            $stats['network_density'] = $max_possible_friendships > 0 ? 
                round(($stats['confirmed_friendships'] / $max_possible_friendships) * 100, 2) : 0;
            
            $stats['average_friends_per_user'] = $total_users > 0 ? 
                round(($stats['confirmed_friendships'] * 2) / $total_users, 1) : 0;
        }

        // Most connected user
        $most_connected = $wpdb->get_var(
            "SELECT CASE 
                WHEN initiator_count > friend_count THEN initiator_user_id 
                ELSE friend_user_id 
             END as user_id
             FROM (
                SELECT 
                    initiator_user_id,
                    friend_user_id,
                    COUNT(*) as initiator_count,
                    (SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_friends f2 
                     WHERE f2.friend_user_id = f1.initiator_user_id AND f2.is_confirmed = 1) as friend_count
                FROM {$wpdb->base_prefix}bp_friends f1 
                WHERE is_confirmed = 1 
                GROUP BY initiator_user_id, friend_user_id
                ORDER BY (initiator_count + friend_count) DESC 
                LIMIT 1
             ) as friend_counts"
        );
        
        $stats['most_connected_user'] = intval($most_connected);

        return $stats;
    }

    /**
     * Clean up friends data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function cleanup($options = []) {
        if (!bp_is_active('friends')) {
            return ['friendships_removed' => 0];
        }

        global $wpdb;

        $defaults = [
            'remove_friendships' => true,
            'remove_pending_only' => false,
            'older_than_days' => 0,
            'dry_run' => false,
        ];

        $options = wp_parse_args($options, $defaults);
        
        $results = [
            'friendships_removed' => 0,
            'pending_removed' => 0,
            'confirmed_removed' => 0,
            'meta_cleaned' => 0,
        ];

        if (!$options['remove_friendships']) {
            return $results;
        }

        // Build query for playground friendships
        $friendship_query = "
            SELECT f.id, f.is_confirmed 
            FROM {$wpdb->base_prefix}bp_friends f 
            INNER JOIN {$wpdb->base_prefix}bp_friends_meta fm ON f.id = fm.friend_id 
            WHERE fm.meta_key = 'bp_playground_created'
        ";

        // Add filters
        if ($options['remove_pending_only']) {
            $friendship_query .= " AND f.is_confirmed = 0";
        }

        if ($options['older_than_days'] > 0) {
            $timestamp = time() - ($options['older_than_days'] * 24 * 3600);
            $friendship_query .= $wpdb->prepare(" AND fm.meta_value < %d", $timestamp);
        }

        $friendships_to_remove = $wpdb->get_results($friendship_query);

        if (!$options['dry_run']) {
            foreach ($friendships_to_remove as $friendship) {
                // Delete friendship
                if (friends_delete_friendship($friendship->id)) {
                    $results['friendships_removed']++;
                    
                    if ($friendship->is_confirmed) {
                        $results['confirmed_removed']++;
                    } else {
                        $results['pending_removed']++;
                    }
                }
            }

            // Clean up any remaining meta
            $meta_cleaned = $wpdb->query(
                "DELETE FROM {$wpdb->base_prefix}bp_friends_meta WHERE meta_key LIKE 'bp_playground_%'"
            );
            $results['meta_cleaned'] = $meta_cleaned;
        } else {
            foreach ($friendships_to_remove as $friendship) {
                $results['friendships_removed']++;
                
                if ($friendship->is_confirmed) {
                    $results['confirmed_removed']++;
                } else {
                    $results['pending_removed']++;
                }
            }
        }

        return $results;
    }

    /**
     * Export friendship network data
     *
     * @since 1.0.0
     * @param array $options Export options
     * @return string|WP_Error CSV data or error
     */
    public function export_network_data($options = []) {
        if (!bp_is_active('friends')) {
            return new WP_Error('friends_disabled', 'Friends component is not active');
        }

        global $wpdb;

        $defaults = [
            'format' => 'csv',
            'include_pending' => false,
            'playground_only' => true,
        ];

        $options = wp_parse_args($options, $defaults);

        $query = "
            SELECT f.initiator_user_id, f.friend_user_id, f.is_confirmed, f.date_created,
                   u1.user_login as initiator_login, u2.user_login as friend_login
            FROM {$wpdb->base_prefix}bp_friends f
            LEFT JOIN {$wpdb->users} u1 ON f.initiator_user_id = u1.ID
            LEFT JOIN {$wpdb->users} u2 ON f.friend_user_id = u2.ID
        ";

        $where_clauses = [];

        if ($options['playground_only']) {
            $query .= " INNER JOIN {$wpdb->base_prefix}bp_friends_meta fm ON f.id = fm.friend_id";
            $where_clauses[] = "fm.meta_key = 'bp_playground_created'";
        }

        if (!$options['include_pending']) {
            $where_clauses[] = "f.is_confirmed = 1";
        }

        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }

        $query .= " ORDER BY f.date_created DESC";

        $friendships = $wpdb->get_results($query);

        if (empty($friendships)) {
            return new WP_Error('no_data', 'No friendship data found');
        }

        if ($options['format'] === 'csv') {
            $csv_data = "Initiator ID,Initiator Login,Friend ID,Friend Login,Status,Date Created\n";
            
            foreach ($friendships as $friendship) {
                $status = $friendship->is_confirmed ? 'Confirmed' : 'Pending';
                $csv_data .= sprintf('"%d","%s","%d","%s","%s","%s"' . "\n",
                    $friendship->initiator_user_id,
                    $friendship->initiator_login,
                    $friendship->friend_user_id,
                    $friendship->friend_login,
                    $status,
                    $friendship->date_created
                );
            }
            
            return $csv_data;
        }

        return new WP_Error('invalid_format', 'Invalid export format');
    }
}