<?php

class BP_Activity_Comments_Module {

    public function create_random_activity_comments( $count = 100 ) {
        if ( ! bp_is_active( 'activity' ) ) {
            return;
        }

        // Fetch activities to comment on
        $activities = bp_activity_get( array(
            'max' => $count,
            'display_comments' => false,
        ) );

        if ( empty( $activities['activities'] ) ) {
            return;
        }

        // Get a list of users to assign as comment authors
        $users = get_users( array( 'number' => $count ) );

        // Array of 50 random quotes from Roman history
        $comment_samples = array(
            "Veni, vidi, vici. - Julius Caesar",
            "Alea iacta est. - Julius Caesar",
            "Carpe diem. - Horace",
            "Civis Romanus sum. - Cicero",
            "Et tu, Brute? - Julius Caesar",
            "Fortes fortuna adiuvat. - Terence",
            "In vino veritas. - Pliny the Elder",
            "Quo vadis? - Nero",
            "Sic semper tyrannis. - Brutus",
            "Vox populi, vox Dei. - Alcuin",
            "Audentes fortuna iuvat. - Virgil",
            "Tempus fugit. - Virgil",
            "Morituri te salutamus. - Gladiators",
            "Mens sana in corpore sano. - Juvenal",
            "Amor vincit omnia. - Virgil",
            "Divide et impera. - Julius Caesar",
            "Dulce et decorum est pro patria mori. - Horace",
            "Faber est suae quisque fortunae. - Appius Claudius Caecus",
            "Panem et circenses. - Juvenal",
            "Per aspera ad astra. - Seneca",
            "Senatus Populusque Romanus. - Roman Senate",
            "Semper fidelis. - Roman Army",
            "Virtus et veritas. - Cicero",
            "Hannibal ad portas. - Roman saying",
            "Homo sum, humani nihil a me alienum puto. - Terence",
            "Imperium in imperio. - Cicero",
            "Nemo me impune lacessit. - Roman Army",
            "Non scholae sed vitae discimus. - Seneca",
            "Pax Romana. - Augustus",
            "Sic transit gloria mundi. - Roman saying",
            "Tu quoque, fili mi? - Caesar",
            "Vae victis. - Brennus",
            "Amicus certus in re incerta cernitur. - Ennius",
            "Cogito, ergo sum. - Descartes (though not Roman, often associated)",
            "Ex nihilo nihil fit. - Lucretius",
            "Fortuna caeca est. - Cicero",
            "Gloria in excelsis Deo. - Christian hymn",
            "In pace leones, in proelio cervi. - Cicero",
            "Magna est veritas et praevalet. - Cicero",
            "Nunc est bibendum. - Horace",
            "Omnia vincit amor. - Virgil",
            "Pecunia non olet. - Vespasian",
            "Quis custodiet ipsos custodes? - Juvenal",
            "Si vis pacem, para bellum. - Vegetius",
            "Veni, vidi, vici. - Julius Caesar",
            "Arma virumque cano. - Virgil",
            "Caesar non supra grammaticos. - Roman saying",
            "Labor omnia vincit. - Virgil"
        );

        for ( $i = 1; $i <= $count; $i++ ) {
            $activity = $activities['activities'][ array_rand( $activities['activities'] ) ];
            $user = $users[ array_rand( $users ) ];
            $comment_content = $comment_samples[ array_rand( $comment_samples ) ];

            // Ensure the comment date is between the activity's posting date and now
            $activity_timestamp = strtotime( $activity->date_recorded );
            $now = time();
            $random_timestamp = mt_rand( $activity_timestamp, $now );

            bp_activity_new_comment( array(
                'activity_id'    => $activity->id,
                'user_id'        => $user->ID,
                'content'        => $comment_content,
                'date_recorded'  => date( 'Y-m-d H:i:s', $random_timestamp ),
            ) );
        }
    }
}
