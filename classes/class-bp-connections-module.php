<?php

class BP_Connections_Module {

    public function create_random_connections( $count = 200 ) {
        if ( ! bp_is_active( 'friends' ) ) {
            return;
        }

        // Calculate the number of confirmed and pending connections
        $confirmed_count = (int) ( $count * 0.7 );
        $pending_count = $count - $confirmed_count;

        // Get a list of users to create connections between
        $users = get_users( array( 'number' => $count * 2 ) ); // Get double the count to avoid duplicates

        if ( empty( $users ) ) {
            return;
        }

        // Create confirmed friendships
        for ( $i = 1; $i <= $confirmed_count; $i++ ) {
            $user1 = $users[ array_rand( $users ) ];
            $user2 = $users[ array_rand( $users ) ];

            if ( $user1->ID == $user2->ID || friends_check_friendship( $user1->ID, $user2->ID ) ) {
                $i--; // Retry if the same user or already friends
                continue;
            }

            friends_add_friend( $user1->ID, $user2->ID, true ); // Directly confirm the friendship
        }

        // Create pending friendship requests
        for ( $i = 1; $i <= $pending_count; $i++ ) {
            $user1 = $users[ array_rand( $users ) ];
            $user2 = $users[ array_rand( $users ) ];

            if ( $user1->ID == $user2->ID || friends_check_friendship( $user1->ID, $user2->ID ) ) {
                $i--; // Retry if the same user or already friends
                continue;
            }

            friends_add_friend( $user1->ID, $user2->ID, false ); // Create a pending friendship request
        }
    }
}
