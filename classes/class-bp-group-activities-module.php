<?php

class BP_Group_Activities_Module {

    public function create_group_activities( $percent_groups = 50, $by_admin = true ) {
        global $wpdb;

        if ( ! bp_is_active( 'groups' ) || ! bp_is_active( 'activity' ) ) {
            return;
        }

        // Array of 50+ random quotes from famous English poems
        $poem_quotes = array(
            "To be, or not to be, that is the question. - William Shakespeare",
            "The woods are lovely, dark and deep, But I have promises to keep. - Robert Frost",
            "I wandered lonely as a cloud. - William Wordsworth",
            "Do not go gentle into that good night. - Dylan Thomas",
            "Because I could not stop for Death, He kindly stopped for me. - Emily Dickinson",
            "Tread softly because you tread on my dreams. - W.B. Yeats",
            "Hope is the thing with feathers. - Emily Dickinson",
            "The mind is its own place, and in itself can make a heaven of hell, a hell of heaven. - John Milton",
            "Shall I compare thee to a summer's day? Thou art more lovely and more temperate. - William Shakespeare",
            "And miles to go before I sleep. - Robert Frost",
            "The best laid schemes o' mice an' men gang aft agley. - Robert Burns",
            "I am the master of my fate, I am the captain of my soul. - William Ernest Henley",
            "A thing of beauty is a joy forever. - John Keats",
            "O Captain! My Captain! Our fearful trip is done. - Walt Whitman",
            "Water, water, everywhere, Nor any drop to drink. - Samuel Taylor Coleridge",
            "Two roads diverged in a wood, and I— I took the one less traveled by. - Robert Frost",
            "My heart aches, and a drowsy numbness pains my sense. - John Keats",
            "O Romeo, Romeo! wherefore art thou Romeo? - William Shakespeare",
            "I will arise and go now, and go to Innisfree. - W.B. Yeats",
            "But we loved with a love that was more than love. - Edgar Allan Poe",
            "Tis better to have loved and lost than never to have loved at all. - Alfred Lord Tennyson",
            "The only emperor is the emperor of ice-cream. - Wallace Stevens",
            "I sing of arms and the man. - Virgil",
            "April is the cruellest month. - T.S. Eliot",
            "Let us go then, you and I. - T.S. Eliot",
            "Rage, rage against the dying of the light. - Dylan Thomas",
            "If you can keep your head when all about you are losing theirs. - Rudyard Kipling",
            "They also serve who only stand and wait. - John Milton",
            "I think that I shall never see a poem as lovely as a tree. - Joyce Kilmer",
            "In Xanadu did Kubla Khan a stately pleasure-dome decree. - Samuel Taylor Coleridge",
            "Do not stand at my grave and weep. - Mary Elizabeth Frye",
            "She walks in beauty, like the night. - Lord Byron",
            "O wild West Wind, thou breath of Autumn's being. - Percy Bysshe Shelley",
            "How do I love thee? Let me count the ways. - Elizabeth Barrett Browning",
            "Under the spreading chestnut tree, the village smithy stands. - Henry Wadsworth Longfellow",
            "This is the way the world ends, not with a bang but a whimper. - T.S. Eliot",
            "I have measured out my life with coffee spoons. - T.S. Eliot",
            "Tyger Tyger, burning bright, In the forests of the night. - William Blake",
            "Death, be not proud, though some have called thee mighty and dreadful. - John Donne",
            "The lady doth protest too much, methinks. - William Shakespeare",
            "Once upon a midnight dreary, while I pondered, weak and weary. - Edgar Allan Poe",
            "A little learning is a dangerous thing. - Alexander Pope",
            "For whom the bell tolls. - John Donne",
            "Gather ye rosebuds while ye may. - Robert Herrick",
            "Beauty is truth, truth beauty,—that is all ye know on earth, and all ye need to know. - John Keats",
            "To strive, to seek, to find, and not to yield. - Alfred Lord Tennyson",
            "The Child is father of the Man. - William Wordsworth",
            "A friend in need is a friend indeed. - Robert Burton",
            "I have promises to keep, and miles to go before I sleep. - Robert Frost"
        );

        // Get all groups using SQL query
        $groups = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}bp_groups" );

        if ( empty( $groups ) ) {
            return;
        }

        // Calculate the number of groups to process based on the percentage
        $total_groups = count( $groups );
        $groups_to_process = round( ( $percent_groups / 100 ) * $total_groups );

        // Shuffle the groups to randomly pick the percentage of groups
        shuffle( $groups );

        foreach ( array_slice( $groups, 0, $groups_to_process ) as $group ) {
            // Get members or admins based on the $by_admin flag
            if ( $by_admin ) {
                // Get group admins using a SQL query
                $members = $wpdb->get_results( 
                    $wpdb->prepare( 
                        "
                        SELECT user_id
                        FROM {$wpdb->prefix}bp_groups_members
                        WHERE group_id = %d AND is_admin = 1 AND is_confirmed = 1 AND is_banned = 0
                        ", 
                        $group->id 
                    )
                );
            } else {
                // Get all confirmed, non-banned group members using a SQL query
                $members = $wpdb->get_results( 
                    $wpdb->prepare( 
                        "
                        SELECT user_id
                        FROM {$wpdb->prefix}bp_groups_members
                        WHERE group_id = %d AND is_confirmed = 1 AND is_banned = 0
                        ", 
                        $group->id 
                    )
                );
            }

            if ( empty( $members ) ) {
                continue;
            }

            foreach ( $members as $member ) {
                $user_id = $member->user_id;

                // Validate user_id before creating activities
                if ( empty( $user_id ) || ! is_numeric( $user_id ) || $user_id <= 0 ) {
                    continue; // Skip invalid members
                }

                // Generate a random number of activities (0 to 10)
                $num_activities = rand( 0, 10 );

                // Create the random number of activities for each member or admin
                for ( $i = 1; $i <= $num_activities; $i++ ) {
                    $quote = $poem_quotes[ array_rand( $poem_quotes ) ];

                    // Generate a random date between today and 30 days ago
                    $random_timestamp = rand( strtotime( '-30 days' ), time() );
                    $random_date = date( 'Y-m-d H:i:s', $random_timestamp );

                    // Add the activity with the random date
                    bp_activity_add( array(
                        'user_id' => $user_id,
                        'content' => $quote,
                        'component' => 'groups',
                        'type' => 'activity_update',
                        'item_id' => $group->id,
                        'secondary_item_id' => $group->id,
                        'date_recorded' => $random_date, // Set the random date
                    ));
                }
            }
        }
    }
}
