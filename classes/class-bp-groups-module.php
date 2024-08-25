<?php

class BP_Groups_Module {

    public function create_groups_with_random_admins( $total_count = 200, $public_percentage = 70, $private_percentage = 25, $hidden_percentage = 5 ) {
        if ( ! bp_is_active( 'groups' ) ) {
            return;
        }

        // Calculate the number of groups for each type based on the percentages
        $public_count = round( $total_count * ($public_percentage / 100) );
        $private_count = round( $total_count * ($private_percentage / 100) );
        $hidden_count = $total_count - $public_count - $private_count;

        // Get all users to use as owners and admins
        $users = get_users( array( 'number' => $total_count ) ); // Adjust the number as needed

        if ( empty( $users ) ) {
            return;
        }

        // Helper function to create a group
        function create_group( $group_name, $group_slug, $group_status, $owner_user, $group_id ) {
            $group_id = groups_create_group( array(
                'creator_id'  => $owner_user->ID,
                'name'        => $group_name,
                'slug'        => $group_slug,
                'description' => 'This is the description for ' . $group_name,
                'status'      => $group_status,
            ) );

            if ( $group_id ) {
                groups_accept_invite( $owner_user->ID, $group_id ); // Add the user to the group
                groups_promote_member( $owner_user->ID, $group_id, 'admin' ); // Promote the user to admin
            }

            return $group_id;
        }

        // Loop to create public groups
        for ( $i = 1; $i <= $public_count; $i++ ) {
            $group_name = 'Public Group ' . $i;
            $group_slug = 'public-group-' . $i;
            $owner_user = $users[ array_rand( $users ) ];

            create_group( $group_name, $group_slug, 'public', $owner_user, $group_id );
        }

        // Loop to create private groups
        for ( $i = 1; $i <= $private_count; $i++ ) {
            $group_name = 'Private Group ' . $i;
            $group_slug = 'private-group-' . $i;
            $owner_user = $users[ array_rand( $users ) ];

            create_group( $group_name, $group_slug, 'private', $owner_user, $group_id );
        }

        // Loop to create hidden groups
        for ( $i = 1; $i <= $hidden_count; $i++ ) {
            $group_name = 'Hidden Group ' . $i;
            $group_slug = 'hidden-group-' . $i;
            $owner_user = $users[ array_rand( $users ) ];

            create_group( $group_name, $group_slug, 'hidden', $owner_user, $group_id );
        }
    }
}
