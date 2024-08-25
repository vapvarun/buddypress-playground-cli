<?php

class BP_Update_Last_Activity_Module {

    public function update_all_users_last_activity_in_batches( $batch_size = 1000 ) {
        if ( ! function_exists( 'bp_update_user_last_activity' ) ) {
            return; // Ensure BuddyPress is active and the function is available
        }

        $offset = 0;
        $total_users = count_users();
        $total_users_count = $total_users['total_users'];

        while ( $offset < $total_users_count ) {
            // Get a batch of users
            $users = get_users( array(
                'number' => $batch_size,
                'offset' => $offset,
            ) );

            foreach ( $users as $user ) {
                // Update the last activity timestamp for each user
                bp_update_user_last_activity( $user->ID );
            }

            // Increment the offset
            $offset += $batch_size;

            // Sleep for a few seconds to avoid overwhelming the server
            sleep( 2 ); // Adjust the sleep time as needed
        }

        // Indicate that the process has completed
        update_option( 'bp_last_activity_updated', true );
        echo "All users' last activity timestamps have been updated.";
    }
}
