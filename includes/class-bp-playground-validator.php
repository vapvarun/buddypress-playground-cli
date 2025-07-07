<?php
/**
 * BuddyPress Playground Validator
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
 * Data integrity validation and verification system
 *
 * @since 1.0.0
 */
class BP_Playground_Validator {

    /**
     * Validation results
     *
     * @since 1.0.0
     * @var array
     */
    private $validation_results = [];

    /**
     * Issues found during validation
     *
     * @since 1.0.0
     * @var array
     */
    private $issues_found = [];

    /**
     * Issues fixed during validation
     *
     * @since 1.0.0
     * @var array
     */
    private $issues_fixed = [];

    /**
     * Verify data integrity across all components
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return array Verification results
     */
    public function verify_data_integrity($options = []) {
        $defaults = [
            'fix_issues' => false,
            'check_relationships' => true,
            'check_orphaned_data' => true,
            'check_corrupted_data' => true,
            'components' => ['users', 'xprofile', 'groups', 'activities', 'messages', 'friends'],
        ];

        $options = wp_parse_args($options, $defaults);

        $this->validation_results = [];
        $this->issues_found = [];
        $this->issues_fixed = [];

        // Verify each component
        foreach ($options['components'] as $component) {
            $this->verify_component($component, $options);
        }

        // Check relationships if requested
        if ($options['check_relationships']) {
            $this->verify_relationships($options);
        }

        // Check for orphaned data
        if ($options['check_orphaned_data']) {
            $this->check_orphaned_data($options);
        }

        // Check for corrupted data
        if ($options['check_corrupted_data']) {
            $this->check_corrupted_data($options);
        }

        return [
            'issues_found' => count($this->issues_found),
            'issues_fixed' => count($this->issues_fixed),
            'details' => $this->validation_results,
            'summary' => $this->generate_summary(),
            'performance_notes' => $this->generate_performance_notes(),
        ];
    }

    /**
     * Verify a specific component
     *
     * @since 1.0.0
     * @param string $component Component name
     * @param array $options Verification options
     * @return void
     */
    private function verify_component($component, $options) {
        $this->validation_results[$component] = [];

        switch ($component) {
            case 'users':
                $this->verify_users($options);
                break;
            case 'xprofile':
                $this->verify_xprofile($options);
                break;
            case 'groups':
                $this->verify_groups($options);
                break;
            case 'activities':
                $this->verify_activities($options);
                break;
            case 'messages':
                $this->verify_messages($options);
                break;
            case 'friends':
                $this->verify_friends($options);
                break;
            case 'bbpress':
                $this->verify_bbpress($options);
                break;
        }
    }

    /**
     * Verify users data integrity
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function verify_users($options) {
        global $wpdb;

        // Check for users without required meta
        $users_without_playground_meta = $wpdb->get_results(
            "SELECT u.ID, u.user_login 
             FROM {$wpdb->users} u 
             LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'bp_playground_created'
             WHERE um.user_id IS NULL AND u.ID > 1"
        );

        if (!empty($users_without_playground_meta)) {
            $issue = "Found " . count($users_without_playground_meta) . " users without playground metadata";
            $this->add_issue('users', $issue);

            if ($options['fix_issues']) {
                // Add missing meta for existing users
                foreach ($users_without_playground_meta as $user) {
                    update_user_meta($user->ID, 'bp_playground_created', time());
                    $this->add_fix('users', "Added playground metadata for user {$user->user_login}");
                }
            }
        }

        // Check for duplicate emails
        $duplicate_emails = $wpdb->get_results(
            "SELECT user_email, COUNT(*) as count 
             FROM {$wpdb->users} 
             GROUP BY user_email 
             HAVING count > 1"
        );

        if (!empty($duplicate_emails)) {
            foreach ($duplicate_emails as $duplicate) {
                $issue = "Duplicate email found: {$duplicate->user_email} ({$duplicate->count} users)";
                $this->add_issue('users', $issue);
            }
        }

        // Check for users with invalid data
        $invalid_users = $wpdb->get_results(
            "SELECT ID, user_login, user_email 
             FROM {$wpdb->users} 
             WHERE user_email = '' OR user_email IS NULL OR user_login = '' OR user_login IS NULL"
        );

        if (!empty($invalid_users)) {
            $issue = "Found " . count($invalid_users) . " users with invalid data";
            $this->add_issue('users', $issue);

            if ($options['fix_issues']) {
                foreach ($invalid_users as $user) {
                    wp_delete_user($user->ID);
                    $this->add_fix('users', "Removed invalid user: {$user->user_login}");
                }
            }
        }
    }

    /**
     * Verify XProfile data integrity
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function verify_xprofile($options) {
        if (!bp_is_active('xprofile')) {
            return;
        }

        global $wpdb;

        // Check for orphaned field data
        $orphaned_data = $wpdb->get_results(
            "SELECT xd.id, xd.field_id, xd.user_id 
             FROM {$wpdb->base_prefix}bp_xprofile_data xd 
             LEFT JOIN {$wpdb->base_prefix}bp_xprofile_fields xf ON xd.field_id = xf.id 
             LEFT JOIN {$wpdb->users} u ON xd.user_id = u.ID 
             WHERE xf.id IS NULL OR u.ID IS NULL"
        );

        if (!empty($orphaned_data)) {
            $issue = "Found " . count($orphaned_data) . " orphaned XProfile data entries";
            $this->add_issue('xprofile', $issue);

            if ($options['fix_issues']) {
                foreach ($orphaned_data as $data) {
                    $wpdb->delete(
                        $wpdb->base_prefix . 'bp_xprofile_data',
                        ['id' => $data->id],
                        ['%d']
                    );
                    $this->add_fix('xprofile', "Removed orphaned XProfile data ID: {$data->id}");
                }
            }
        }

        // Check for fields without parent groups
        $orphaned_fields = $wpdb->get_results(
            "SELECT xf.id, xf.name 
             FROM {$wpdb->base_prefix}bp_xprofile_fields xf 
             LEFT JOIN {$wpdb->base_prefix}bp_xprofile_groups xg ON xf.group_id = xg.id 
             WHERE xg.id IS NULL AND xf.type != 'option'"
        );

        if (!empty($orphaned_fields)) {
            $issue = "Found " . count($orphaned_fields) . " XProfile fields without parent groups";
            $this->add_issue('xprofile', $issue);

            if ($options['fix_issues']) {
                // Move orphaned fields to default group (ID: 1)
                foreach ($orphaned_fields as $field) {
                    $wpdb->update(
                        $wpdb->base_prefix . 'bp_xprofile_fields',
                        ['group_id' => 1],
                        ['id' => $field->id],
                        ['%d'],
                        ['%d']
                    );
                    $this->add_fix('xprofile', "Moved orphaned field '{$field->name}' to default group");
                }
            }
        }

        // Check for duplicate field names within groups
        $duplicate_fields = $wpdb->get_results(
            "SELECT group_id, name, COUNT(*) as count 
             FROM {$wpdb->base_prefix}bp_xprofile_fields 
             WHERE type != 'option'
             GROUP BY group_id, name 
             HAVING count > 1"
        );

        if (!empty($duplicate_fields)) {
            foreach ($duplicate_fields as $duplicate) {
                $issue = "Duplicate field name in group {$duplicate->group_id}: {$duplicate->name}";
                $this->add_issue('xprofile', $issue);
            }
        }
    }

    /**
     * Verify groups data integrity
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function verify_groups($options) {
        if (!bp_is_active('groups')) {
            return;
        }

        global $wpdb;

        // Check for orphaned group members
        $orphaned_members = $wpdb->get_results(
            "SELECT gm.id, gm.group_id, gm.user_id 
             FROM {$wpdb->base_prefix}bp_groups_members gm 
             LEFT JOIN {$wpdb->base_prefix}bp_groups g ON gm.group_id = g.id 
             LEFT JOIN {$wpdb->users} u ON gm.user_id = u.ID 
             WHERE g.id IS NULL OR u.ID IS NULL"
        );

        if (!empty($orphaned_members)) {
            $issue = "Found " . count($orphaned_members) . " orphaned group memberships";
            $this->add_issue('groups', $issue);

            if ($options['fix_issues']) {
                foreach ($orphaned_members as $member) {
                    $wpdb->delete(
                        $wpdb->base_prefix . 'bp_groups_members',
                        ['id' => $member->id],
                        ['%d']
                    );
                    $this->add_fix('groups', "Removed orphaned group membership ID: {$member->id}");
                }
            }
        }

        // Check for groups without creators
        $groups_without_creators = $wpdb->get_results(
            "SELECT g.id, g.name 
             FROM {$wpdb->base_prefix}bp_groups g 
             LEFT JOIN {$wpdb->users} u ON g.creator_id = u.ID 
             WHERE u.ID IS NULL"
        );

        if (!empty($groups_without_creators)) {
            $issue = "Found " . count($groups_without_creators) . " groups without valid creators";
            $this->add_issue('groups', $issue);

            if ($options['fix_issues']) {
                // Assign to admin user or delete if no admin available
                $admin_user = get_users(['role' => 'administrator', 'number' => 1]);
                if (!empty($admin_user)) {
                    $admin_id = $admin_user[0]->ID;
                    foreach ($groups_without_creators as $group) {
                        $wpdb->update(
                            $wpdb->base_prefix . 'bp_groups',
                            ['creator_id' => $admin_id],
                            ['id' => $group->id],
                            ['%d'],
                            ['%d']
                        );
                        $this->add_fix('groups', "Assigned admin as creator for group: {$group->name}");
                    }
                } else {
                    foreach ($groups_without_creators as $group) {
                        groups_delete_group($group->id);
                        $this->add_fix('groups', "Deleted group without creator: {$group->name}");
                    }
                }
            }
        }

        // Check for inconsistent group member counts
        $inconsistent_counts = $wpdb->get_results(
            "SELECT g.id, g.name, g.total_member_count, 
                    COUNT(gm.user_id) as actual_count
             FROM {$wpdb->base_prefix}bp_groups g 
             LEFT JOIN {$wpdb->base_prefix}bp_groups_members gm ON g.id = gm.group_id 
             GROUP BY g.id 
             HAVING g.total_member_count != actual_count"
        );

        if (!empty($inconsistent_counts)) {
            $issue = "Found " . count($inconsistent_counts) . " groups with inconsistent member counts";
            $this->add_issue('groups', $issue);

            if ($options['fix_issues']) {
                foreach ($inconsistent_counts as $group) {
                    groups_update_groupmeta($group->id, 'total_member_count', $group->actual_count);
                    $this->add_fix('groups', "Fixed member count for group: {$group->name}");
                }
            }
        }
    }

    /**
     * Verify activities data integrity
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function verify_activities($options) {
        if (!bp_is_active('activity')) {
            return;
        }

        global $wpdb;

        // Check for activities with invalid user IDs
        $invalid_activities = $wpdb->get_results(
            "SELECT a.id, a.user_id 
             FROM {$wpdb->base_prefix}bp_activity a 
             LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID 
             WHERE u.ID IS NULL AND a.user_id != 0"
        );

        if (!empty($invalid_activities)) {
            $issue = "Found " . count($invalid_activities) . " activities with invalid user IDs";
            $this->add_issue('activities', $issue);

            if ($options['fix_issues']) {
                foreach ($invalid_activities as $activity) {
                    bp_activity_delete(['id' => $activity->id]);
                    $this->add_fix('activities', "Deleted activity with invalid user ID: {$activity->id}");
                }
            }
        }

        // Check for orphaned activity comments
        $orphaned_comments = $wpdb->get_results(
            "SELECT ac.id, ac.activity_id 
             FROM {$wpdb->base_prefix}bp_activity ac 
             LEFT JOIN {$wpdb->base_prefix}bp_activity ap ON ac.item_id = ap.id 
             WHERE ac.type = 'activity_comment' AND ap.id IS NULL"
        );

        if (!empty($orphaned_comments)) {
            $issue = "Found " . count($orphaned_comments) . " orphaned activity comments";
            $this->add_issue('activities', $issue);

            if ($options['fix_issues']) {
                foreach ($orphaned_comments as $comment) {
                    bp_activity_delete(['id' => $comment->id]);
                    $this->add_fix('activities', "Deleted orphaned activity comment: {$comment->id}");
                }
            }
        }

        // Check for activities with invalid timestamps
        $invalid_timestamps = $wpdb->get_results(
            "SELECT id, date_recorded 
             FROM {$wpdb->base_prefix}bp_activity 
             WHERE date_recorded > NOW() OR date_recorded < '2000-01-01'"
        );

        if (!empty($invalid_timestamps)) {
            $issue = "Found " . count($invalid_timestamps) . " activities with invalid timestamps";
            $this->add_issue('activities', $issue);

            if ($options['fix_issues']) {
                foreach ($invalid_timestamps as $activity) {
                    $wpdb->update(
                        $wpdb->base_prefix . 'bp_activity',
                        ['date_recorded' => current_time('mysql')],
                        ['id' => $activity->id],
                        ['%s'],
                        ['%d']
                    );
                    $this->add_fix('activities', "Fixed timestamp for activity: {$activity->id}");
                }
            }
        }
    }

    /**
     * Verify messages data integrity
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function verify_messages($options) {
        if (!bp_is_active('messages')) {
            return;
        }

        global $wpdb;

        // Check for orphaned message recipients
        $orphaned_recipients = $wpdb->get_results(
            "SELECT mr.id, mr.thread_id, mr.user_id 
             FROM {$wpdb->base_prefix}bp_messages_recipients mr 
             LEFT JOIN {$wpdb->base_prefix}bp_messages_messages mm ON mr.thread_id = mm.thread_id 
             LEFT JOIN {$wpdb->users} u ON mr.user_id = u.ID 
             WHERE mm.id IS NULL OR u.ID IS NULL"
        );

        if (!empty($orphaned_recipients)) {
            $issue = "Found " . count($orphaned_recipients) . " orphaned message recipients";
            $this->add_issue('messages', $issue);

            if ($options['fix_issues']) {
                foreach ($orphaned_recipients as $recipient) {
                    $wpdb->delete(
                        $wpdb->base_prefix . 'bp_messages_recipients',
                        ['id' => $recipient->id],
                        ['%d']
                    );
                    $this->add_fix('messages', "Removed orphaned message recipient: {$recipient->id}");
                }
            }
        }

        // Check for messages without valid senders
        $invalid_messages = $wpdb->get_results(
            "SELECT mm.id, mm.sender_id 
             FROM {$wpdb->base_prefix}bp_messages_messages mm 
             LEFT JOIN {$wpdb->users} u ON mm.sender_id = u.ID 
             WHERE u.ID IS NULL"
        );

        if (!empty($invalid_messages)) {
            $issue = "Found " . count($invalid_messages) . " messages with invalid senders";
            $this->add_issue('messages', $issue);

            if ($options['fix_issues']) {
                foreach ($invalid_messages as $message) {
                    bp_messages_delete_thread($message->id);
                    $this->add_fix('messages', "Deleted message with invalid sender: {$message->id}");
                }
            }
        }
    }

    /**
     * Verify friends data integrity
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function verify_friends($options) {
        if (!bp_is_active('friends')) {
            return;
        }

        global $wpdb;

        // Check for friend connections with invalid user IDs
        $invalid_friendships = $wpdb->get_results(
            "SELECT f.id, f.initiator_user_id, f.friend_user_id 
             FROM {$wpdb->base_prefix}bp_friends f 
             LEFT JOIN {$wpdb->users} u1 ON f.initiator_user_id = u1.ID 
             LEFT JOIN {$wpdb->users} u2 ON f.friend_user_id = u2.ID 
             WHERE u1.ID IS NULL OR u2.ID IS NULL"
        );

        if (!empty($invalid_friendships)) {
            $issue = "Found " . count($invalid_friendships) . " friendships with invalid user IDs";
            $this->add_issue('friends', $issue);

            if ($options['fix_issues']) {
                foreach ($invalid_friendships as $friendship) {
                    $wpdb->delete(
                        $wpdb->base_prefix . 'bp_friends',
                        ['id' => $friendship->id],
                        ['%d']
                    );
                    $this->add_fix('friends', "Removed invalid friendship: {$friendship->id}");
                }
            }
        }

        // Check for self-friendships
        $self_friendships = $wpdb->get_results(
            "SELECT id, initiator_user_id 
             FROM {$wpdb->base_prefix}bp_friends 
             WHERE initiator_user_id = friend_user_id"
        );

        if (!empty($self_friendships)) {
            $issue = "Found " . count($self_friendships) . " self-friendships";
            $this->add_issue('friends', $issue);

            if ($options['fix_issues']) {
                foreach ($self_friendships as $friendship) {
                    $wpdb->delete(
                        $wpdb->base_prefix . 'bp_friends',
                        ['id' => $friendship->id],
                        ['%d']
                    );
                    $this->add_fix('friends', "Removed self-friendship: {$friendship->id}");
                }
            }
        }

        // Check for duplicate friendships
        $duplicate_friendships = $wpdb->get_results(
            "SELECT initiator_user_id, friend_user_id, COUNT(*) as count 
             FROM {$wpdb->base_prefix}bp_friends 
             GROUP BY LEAST(initiator_user_id, friend_user_id), GREATEST(initiator_user_id, friend_user_id) 
             HAVING count > 1"
        );

        if (!empty($duplicate_friendships)) {
            $issue = "Found " . count($duplicate_friendships) . " duplicate friendships";
            $this->add_issue('friends', $issue);
        }
    }

    /**
     * Verify bbPress data integrity
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function verify_bbpress($options) {
        if (!class_exists('bbPress')) {
            return;
        }

        global $wpdb;

        // Check for orphaned topics
        $orphaned_topics = $wpdb->get_results(
            "SELECT p.ID, p.post_title 
             FROM {$wpdb->posts} p 
             LEFT JOIN {$wpdb->posts} f ON p.post_parent = f.ID 
             WHERE p.post_type = 'topic' AND p.post_parent != 0 AND f.ID IS NULL"
        );

        if (!empty($orphaned_topics)) {
            $issue = "Found " . count($orphaned_topics) . " orphaned bbPress topics";
            $this->add_issue('bbpress', $issue);

            if ($options['fix_issues']) {
                foreach ($orphaned_topics as $topic) {
                    wp_delete_post($topic->ID, true);
                    $this->add_fix('bbpress', "Deleted orphaned topic: {$topic->post_title}");
                }
            }
        }

        // Check for orphaned replies
        $orphaned_replies = $wpdb->get_results(
            "SELECT p.ID, p.post_title 
             FROM {$wpdb->posts} p 
             LEFT JOIN {$wpdb->posts} t ON p.post_parent = t.ID 
             WHERE p.post_type = 'reply' AND p.post_parent != 0 AND t.ID IS NULL"
        );

        if (!empty($orphaned_replies)) {
            $issue = "Found " . count($orphaned_replies) . " orphaned bbPress replies";
            $this->add_issue('bbpress', $issue);

            if ($options['fix_issues']) {
                foreach ($orphaned_replies as $reply) {
                    wp_delete_post($reply->ID, true);
                    $this->add_fix('bbpress', "Deleted orphaned reply: {$reply->ID}");
                }
            }
        }
    }

    /**
     * Verify relationships between components
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function verify_relationships($options) {
        // This would check cross-component relationships
        // For example: activities referencing groups, messages between friends, etc.
        
        $this->validation_results['relationships'] = [];
        
        // Check activity-group relationships
        if (bp_is_active('activity') && bp_is_active('groups')) {
            $this->verify_activity_group_relationships($options);
        }
        
        // Check activity-user relationships
        if (bp_is_active('activity')) {
            $this->verify_activity_user_relationships($options);
        }
    }

    /**
     * Verify activity-group relationships
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function verify_activity_group_relationships($options) {
        global $wpdb;

        $invalid_group_activities = $wpdb->get_results(
            "SELECT a.id, a.item_id 
             FROM {$wpdb->base_prefix}bp_activity a 
             LEFT JOIN {$wpdb->base_prefix}bp_groups g ON a.item_id = g.id 
             WHERE a.component = 'groups' AND a.item_id != 0 AND g.id IS NULL"
        );

        if (!empty($invalid_group_activities)) {
            $issue = "Found " . count($invalid_group_activities) . " activities referencing non-existent groups";
            $this->add_issue('relationships', $issue);

            if ($options['fix_issues']) {
                foreach ($invalid_group_activities as $activity) {
                    bp_activity_delete(['id' => $activity->id]);
                    $this->add_fix('relationships', "Deleted activity referencing non-existent group: {$activity->id}");
                }
            }
        }
    }

    /**
     * Verify activity-user relationships
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function verify_activity_user_relationships($options) {
        global $wpdb;

        $invalid_user_activities = $wpdb->get_results(
            "SELECT a.id, a.item_id 
             FROM {$wpdb->base_prefix}bp_activity a 
             LEFT JOIN {$wpdb->users} u ON a.item_id = u.ID 
             WHERE a.component = 'members' AND a.item_id != 0 AND u.ID IS NULL"
        );

        if (!empty($invalid_user_activities)) {
            $issue = "Found " . count($invalid_user_activities) . " activities referencing non-existent users";
            $this->add_issue('relationships', $issue);

            if ($options['fix_issues']) {
                foreach ($invalid_user_activities as $activity) {
                    bp_activity_delete(['id' => $activity->id]);
                    $this->add_fix('relationships', "Deleted activity referencing non-existent user: {$activity->id}");
                }
            }
        }
    }

    /**
     * Check for orphaned data across components
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function check_orphaned_data($options) {
        $this->validation_results['orphaned_data'] = [];

        // Check for orphaned user meta
        $this->check_orphaned_user_meta($options);

        // Check for orphaned post meta
        $this->check_orphaned_post_meta($options);

        // Check for orphaned comment meta
        $this->check_orphaned_comment_meta($options);
    }

    /**
     * Check for orphaned user meta
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function check_orphaned_user_meta($options) {
        global $wpdb;

        $orphaned_user_meta = $wpdb->get_var(
            "SELECT COUNT(*) 
             FROM {$wpdb->usermeta} um 
             LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID 
             WHERE u.ID IS NULL"
        );

        if ($orphaned_user_meta > 0) {
            $issue = "Found {$orphaned_user_meta} orphaned user meta entries";
            $this->add_issue('orphaned_data', $issue);

            if ($options['fix_issues']) {
                $deleted = $wpdb->query(
                    "DELETE um FROM {$wpdb->usermeta} um 
                     LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID 
                     WHERE u.ID IS NULL"
                );
                $this->add_fix('orphaned_data', "Deleted {$deleted} orphaned user meta entries");
            }
        }
    }

    /**
     * Check for orphaned post meta
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function check_orphaned_post_meta($options) {
        global $wpdb;

        $orphaned_post_meta = $wpdb->get_var(
            "SELECT COUNT(*) 
             FROM {$wpdb->postmeta} pm 
             LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
             WHERE p.ID IS NULL"
        );

        if ($orphaned_post_meta > 0) {
            $issue = "Found {$orphaned_post_meta} orphaned post meta entries";
            $this->add_issue('orphaned_data', $issue);

            if ($options['fix_issues']) {
                $deleted = $wpdb->query(
                    "DELETE pm FROM {$wpdb->postmeta} pm 
                     LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
                     WHERE p.ID IS NULL"
                );
                $this->add_fix('orphaned_data', "Deleted {$deleted} orphaned post meta entries");
            }
        }
    }

    /**
     * Check for orphaned comment meta
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function check_orphaned_comment_meta($options) {
        global $wpdb;

        $orphaned_comment_meta = $wpdb->get_var(
            "SELECT COUNT(*) 
             FROM {$wpdb->commentmeta} cm 
             LEFT JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID 
             WHERE c.comment_ID IS NULL"
        );

        if ($orphaned_comment_meta > 0) {
            $issue = "Found {$orphaned_comment_meta} orphaned comment meta entries";
            $this->add_issue('orphaned_data', $issue);

            if ($options['fix_issues']) {
                $deleted = $wpdb->query(
                    "DELETE cm FROM {$wpdb->commentmeta} cm 
                     LEFT JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID 
                     WHERE c.comment_ID IS NULL"
                );
                $this->add_fix('orphaned_data', "Deleted {$deleted} orphaned comment meta entries");
            }
        }
    }

    /**
     * Check for corrupted data
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function check_corrupted_data($options) {
        $this->validation_results['corrupted_data'] = [];

        // Check for corrupted serialized data
        $this->check_corrupted_serialized_data($options);

        // Check for invalid JSON data
        $this->check_invalid_json_data($options);
    }

    /**
     * Check for corrupted serialized data
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function check_corrupted_serialized_data($options) {
        global $wpdb;

        // Check user meta for corrupted serialized data
        $corrupted_user_meta = $wpdb->get_results(
            "SELECT umeta_id, user_id, meta_key, meta_value 
             FROM {$wpdb->usermeta} 
             WHERE meta_value LIKE 'a:%' OR meta_value LIKE 'O:%' OR meta_value LIKE 's:%'"
        );

        $corrupted_count = 0;
        foreach ($corrupted_user_meta as $meta) {
            if (@unserialize($meta->meta_value) === false && $meta->meta_value !== 'b:0;') {
                $corrupted_count++;
                if ($options['fix_issues']) {
                    delete_user_meta($meta->user_id, $meta->meta_key);
                    $this->add_fix('corrupted_data', "Fixed corrupted user meta: {$meta->meta_key} for user {$meta->user_id}");
                }
            }
        }

        if ($corrupted_count > 0) {
            $issue = "Found {$corrupted_count} corrupted serialized user meta entries";
            $this->add_issue('corrupted_data', $issue);
        }
    }

    /**
     * Check for invalid JSON data
     *
     * @since 1.0.0
     * @param array $options Verification options
     * @return void
     */
    private function check_invalid_json_data($options) {
        global $wpdb;

        // Check for invalid JSON in options table
        $json_options = $wpdb->get_results(
            "SELECT option_id, option_name, option_value 
             FROM {$wpdb->options} 
             WHERE option_value LIKE '{%' OR option_value LIKE '[%'"
        );

        $invalid_count = 0;
        foreach ($json_options as $option) {
            json_decode($option->option_value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $invalid_count++;
                if ($options['fix_issues']) {
                    delete_option($option->option_name);
                    $this->add_fix('corrupted_data', "Removed invalid JSON option: {$option->option_name}");
                }
            }
        }

        if ($invalid_count > 0) {
            $issue = "Found {$invalid_count} options with invalid JSON data";
            $this->add_issue('corrupted_data', $issue);
        }
    }

    /**
     * Add an issue to the validation results
     *
     * @since 1.0.0
     * @param string $component Component name
     * @param string $issue Issue description
     * @return void
     */
    private function add_issue($component, $issue) {
        if (!isset($this->validation_results[$component])) {
            $this->validation_results[$component] = [];
        }
        
        $this->validation_results[$component][] = $issue;
        $this->issues_found[] = $issue;
    }

    /**
     * Add a fix to the validation results
     *
     * @since 1.0.0
     * @param string $component Component name
     * @param string $fix Fix description
     * @return void
     */
    private function add_fix($component, $fix) {
        $this->issues_fixed[] = $fix;
    }

    /**
     * Generate validation summary
     *
     * @since 1.0.0
     * @return array Validation summary
     */
    private function generate_summary() {
        $total_issues = count($this->issues_found);
        $total_fixes = count($this->issues_fixed);
        
        return [
            'total_issues' => $total_issues,
            'total_fixes' => $total_fixes,
            'success_rate' => $total_issues > 0 ? round(($total_fixes / $total_issues) * 100, 2) : 100,
            'components_checked' => count($this->validation_results),
        ];
    }

    /**
     * Generate performance notes
     *
     * @since 1.0.0
     * @return array Performance recommendations
     */
    private function generate_performance_notes() {
        global $wpdb;
        
        $notes = [];
        
        // Check table sizes
        $large_tables = $wpdb->get_results(
            "SELECT table_name, 
                    round(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb'
             FROM information_schema.TABLES 
             WHERE table_schema = '{$wpdb->dbname}' 
             AND table_name LIKE '{$wpdb->prefix}%' 
             HAVING size_mb > 100 
             ORDER BY size_mb DESC"
        );
        
        if (!empty($large_tables)) {
            $notes[] = "Large database tables detected that may affect performance";
            foreach ($large_tables as $table) {
                $notes[] = "  - {$table->table_name}: {$table->size_mb} MB";
            }
        }
        
        // Check for missing indexes
        if (bp_is_active('activity')) {
            $activity_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_activity");
            if ($activity_count > 10000) {
                $notes[] = "Large activity table ({$activity_count} entries) - ensure proper indexing";
            }
        }
        
        if (bp_is_active('xprofile')) {
            $xprofile_data_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->base_prefix}bp_xprofile_data");
            if ($xprofile_data_count > 50000) {
                $notes[] = "Large XProfile data table ({$xprofile_data_count} entries) - consider data archiving";
            }
        }
        
        // Check memory usage
        $memory_limit = ini_get('memory_limit');
        $memory_usage = memory_get_peak_usage(true);
        $memory_limit_bytes = $this->parse_memory_limit($memory_limit);
        $memory_usage_percent = ($memory_usage / $memory_limit_bytes) * 100;
        
        if ($memory_usage_percent > 80) {
            $notes[] = "High memory usage detected ({$memory_usage_percent}%) - consider increasing memory limit";
        }
        
        return $notes;
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

    /**
     * Get validation statistics
     *
     * @since 1.0.0
     * @return array Validation statistics
     */
    public function get_validation_stats() {
        return [
            'total_issues_found' => count($this->issues_found),
            'total_issues_fixed' => count($this->issues_fixed),
            'components_validated' => array_keys($this->validation_results),
            'validation_results' => $this->validation_results,
        ];
    }

    /**
     * Export validation report
     *
     * @since 1.0.0
     * @param string $format Export format (json, csv)
     * @return string|WP_Error Export data or error
     */
    public function export_validation_report($format = 'json') {
        $report_data = [
            'timestamp' => current_time('mysql'),
            'plugin_version' => BP_PLAYGROUND_VERSION,
            'issues_found' => $this->issues_found,
            'issues_fixed' => $this->issues_fixed,
            'validation_results' => $this->validation_results,
            'summary' => $this->generate_summary(),
        ];

        switch ($format) {
            case 'json':
                return json_encode($report_data, JSON_PRETTY_PRINT);
            case 'csv':
                return $this->convert_report_to_csv($report_data);
            default:
                return new WP_Error('invalid_format', 'Invalid export format');
        }
    }

    /**
     * Convert validation report to CSV format
     *
     * @since 1.0.0
     * @param array $report_data Report data
     * @return string CSV data
     */
    private function convert_report_to_csv($report_data) {
        $csv_data = "Component,Issue Type,Description,Status\n";

        foreach ($report_data['validation_results'] as $component => $issues) {
            foreach ($issues as $issue) {
                $status = in_array($issue, $report_data['issues_fixed']) ? 'Fixed' : 'Found';
                $csv_data .= sprintf('"%s","%s","%s","%s"' . "\n", $component, 'Issue', $issue, $status);
            }
        }

        return $csv_data;
    }
}