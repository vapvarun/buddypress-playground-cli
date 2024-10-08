<?php

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    class BP_CLI {

        public function create_groups( $args, $assoc_args ) {
            $count = isset( $assoc_args['count'] ) ? (int) $assoc_args['count'] : 200;
            $public = isset( $assoc_args['public'] ) ? (int) $assoc_args['public'] : 70;
            $private = isset( $assoc_args['private'] ) ? (int) $assoc_args['private'] : 25;
            $hidden = isset( $assoc_args['hidden'] ) ? (int) $assoc_args['hidden'] : 5;

            $groups_module = new BP_Groups_Module();
            $groups_module->create_groups_with_random_admins( $count, $public, $private, $hidden );
            WP_CLI::success( "Created $count BuddyPress groups with random admins." );
        }

        public function create_activities( $args, $assoc_args ) {
            $count = isset( $assoc_args['count'] ) ? (int) $assoc_args['count'] : 50;
            $activities_module = new BP_Activities_Module();
            $activities_module->create_random_activities( $count );
            WP_CLI::success( "Created $count BuddyPress activities." );
        }

        public function create_messages( $args, $assoc_args ) {
            $count = isset( $assoc_args['count'] ) ? (int) $assoc_args['count'] : 200;
            $messages_module = new BP_Messages_Module();
            $messages_module->create_random_messages( $count );
            WP_CLI::success( "Created $count BuddyPress messages." );
        }

        public function create_connections( $args, $assoc_args ) {
            $count = isset( $assoc_args['count'] ) ? (int) $assoc_args['count'] : 200;
            $connections_module = new BP_Connections_Module();
            $connections_module->create_random_connections( $count );
            WP_CLI::success( "Created $count BuddyPress connections." );
        }

        public function create_activity_comments( $args, $assoc_args ) {
            $count = isset( $assoc_args['count'] ) ? (int) $assoc_args['count'] : 100;
            $activity_comments_module = new BP_Activity_Comments_Module();
            $activity_comments_module->create_random_activity_comments( $count );
            WP_CLI::success( "Created $count BuddyPress activity comments." );
        }

        public function add_group_members( $args, $assoc_args ) {
            $group_members_module = new BP_Group_Members_Module();
            $group_members_module->add_random_members_to_groups();
            WP_CLI::success( "Random users have been added to groups." );
        }
        
        public function update_last_activity( $args, $assoc_args ) {
            $batch_size = isset( $assoc_args['batch_size'] ) ? (int) $assoc_args['batch_size'] : 1000;
            $activity_module = new BP_Update_Last_Activity_Module();
            $activity_module->update_all_users_last_activity_in_batches( $batch_size );
            WP_CLI::success( "User last activity timestamps have been updated." );
        }

        public function create_users( $args, $assoc_args ) {
            $count = isset( $assoc_args['count'] ) ? (int) $assoc_args['count'] : 500;
            $user_module = new BP_Create_Users_Module();
            $user_module->create_users( $count );
            WP_CLI::success( "$count users have been created." );
        }

        public function create_group_activities( $args, $assoc_args ) {
            // Get the percentage of groups to process (default is 50%)
            $percent_groups = isset( $assoc_args['percent_groups'] ) ? (int) $assoc_args['percent_groups'] : 50;
        
            // Check if the activities should be created by admins (default is true)
            $by_admin = isset( $assoc_args['by_admin'] ) ? filter_var( $assoc_args['by_admin'], FILTER_VALIDATE_BOOLEAN ) : true;
        
            // Create an instance of the BP_Group_Activities_Module class
            $group_activities_module = new BP_Group_Activities_Module();
        
            // Call the updated method with the percentage of groups and by_admin parameter
            $group_activities_module->create_group_activities( $percent_groups, $by_admin );
        
            // Output a success message based on whether activities were created by admins or random members
            $creator = $by_admin ? 'group admin' : 'random group member';
            WP_CLI::success( "Activities were created for $percent_groups% of groups, with each $creator." );
        
            // Additional feedback if needed
            if ( empty( $group_activities_module ) ) {
                WP_CLI::error( "No activities were created. Please check your group configuration or try again." );
            }
        }                   
        
        public function create_forums_with_topics_replies( $args, $assoc_args ) {
            $forum_count  = isset( $assoc_args['forum_count'] ) ? (int) $assoc_args['forum_count'] : 10;
            $min_topics   = isset( $assoc_args['min_topics'] ) ? (int) $assoc_args['min_topics'] : 10;
            $max_topics   = isset( $assoc_args['max_topics'] ) ? (int) $assoc_args['max_topics'] : 15;
            $min_replies  = isset( $assoc_args['min_replies'] ) ? (int) $assoc_args['min_replies'] : 3;
            $max_replies  = isset( $assoc_args['max_replies'] ) ? (int) $assoc_args['max_replies'] : 7;
        
            $bbpress_module = new BP_BBPress_Module();
            $bbpress_module->create_forums_with_topics_and_replies( $forum_count, $min_topics, $max_topics, $min_replies, $max_replies );
            WP_CLI::success( "Created forums with historical facts as topics and war facts as replies." );
        }
        
    }

    WP_CLI::add_command( 'bp', 'BP_CLI' );
}
