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
    }

    WP_CLI::add_command( 'bp', 'BP_CLI' );
}
