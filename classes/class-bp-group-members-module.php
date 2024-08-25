<?php

class BP_Group_Members_Module {

    public function add_random_members_to_groups() {
        if ( ! bp_is_active( 'groups' ) ) {
            return;
        }

        // Get all groups
        $groups = groups_get_groups( array( 'show_hidden' => true ) );

        if ( empty( $groups['groups'] ) ) {
            return;
        }

        // Get all users
        $users = get_users( array( 'number' => 500 ) ); // Fetch a large number to have enough users

        if ( empty( $users ) ) {
            return;
        }

        foreach ( $groups['groups'] as $group ) {
            // Determine a random number of members for the group (between 5 and 40)
            $num_members = rand( 5, 40 );

            // Add random users to the group
            for ( $i = 1; $i <= $num_members; $i++ ) {
                $user = $users[ array_rand( $users ) ];

                // Check if the user is already a member of the group
                if ( groups_is_user_member( $user->ID, $group->id ) ) {
                    $i--; // Retry if the user is already a member
                    continue;
                }

                // Add the user to the group
                groups_join_group( $group->id, $user->ID );
            }
        }
    }
}
