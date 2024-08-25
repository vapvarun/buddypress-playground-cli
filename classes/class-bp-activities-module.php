<?php

class BP_Activities_Module {

    public function create_random_activities( $count = 50 ) {
        if ( ! bp_is_active( 'activity' ) ) {
            return;
        }

        $users = get_users( array( 'number' => $count ) );

        if ( empty( $users ) ) {
            return;
        }

        // Array of 50+ random content samples
        $content_samples = array(
            "Just had a great day!",
            "Loving this new feature in BuddyPress.",
            "Anyone up for a meetup?",
            "This is awesome!",
            "Can someone help me with a question?",
            "Feeling grateful today.",
            "Exploring new opportunities!",
            "Learning something new every day.",
            "Excited about the upcoming event!",
            "Looking forward to the weekend.",
            "What’s everyone working on?",
            "Any tips for productivity?",
            "Just finished a great book!",
            "Can’t wait to try this out.",
            "Happy to be part of this community.",
            "Seeking advice on a project.",
            "Anyone interested in a collaboration?",
            "Feeling motivated!",
            "Sharing some good news!",
            "Who’s up for a challenge?",
            "Working on some personal goals.",
            "This is a game-changer!",
            "Finally mastered this skill.",
            "Taking things one step at a time.",
            "Enjoying the little things.",
            "What a productive day!",
            "Grateful for all the support.",
            "Trying out some new tools.",
            "Any recommendations for learning resources?",
            "Reflecting on the journey so far.",
            "Let’s make today amazing!",
            "Overcoming obstacles one by one.",
            "Excited to start a new project.",
            "Anyone have experience with this?",
            "Learning from past experiences.",
            "Taking a break to recharge.",
            "Who’s up for a brainstorming session?",
            "Celebrating small victories!",
            "Challenging myself to be better.",
            "Focusing on the positives.",
            "Ready to tackle the next task!",
            "Feeling accomplished today.",
            "Looking for feedback on this idea.",
            "Anyone else working on something similar?",
            "Appreciating the journey.",
            "Making steady progress.",
            "Any tips for staying organized?",
            "Inspired by the community.",
            "Taking it one day at a time.",
            "Learning is a lifelong journey."
        );

        for ( $i = 1; $i <= $count; $i++ ) {
            $user = $users[ array_rand( $users ) ];
            $content = $content_samples[ array_rand( $content_samples ) ];

            // Generate a random timestamp within the last 3 months
            $three_months_ago = strtotime( '-3 months' );
            $now = time();
            $random_timestamp = mt_rand( $three_months_ago, $now );

            bp_activity_add( array(
                'user_id' => $user->ID,
                'content' => $content,
                'component' => 'activity',
                'type' => 'activity_update',
                'date_recorded' => date( 'Y-m-d H:i:s', $random_timestamp ),
            ) );
        }
    }
}
