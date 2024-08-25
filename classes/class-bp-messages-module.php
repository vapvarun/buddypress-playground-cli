<?php

class BP_Messages_Module {

    public function create_random_messages( $count = 200 ) {
        if ( ! bp_is_active( 'messages' ) ) {
            return;
        }

        // Array of 50+ historical facts
        $historical_facts = array(
            "The Great Wall of China is over 13,000 miles long.",
            "The first Olympics were held in 776 BC.",
            "Julius Caesar was assassinated on the Ides of March in 44 BC.",
            "The Magna Carta was signed in 1215.",
            "Christopher Columbus discovered the Americas in 1492.",
            "The printing press was invented by Johannes Gutenberg in 1440.",
            "Leonardo da Vinci painted the Mona Lisa between 1503 and 1506.",
            "The French Revolution began in 1789.",
            "The Wright brothers made their first flight in 1903.",
            "World War I began in 1914 and ended in 1918.",
            "The Titanic sank on its maiden voyage in 1912.",
            "The United Nations was established in 1945.",
            "The Berlin Wall fell in 1989.",
            "Neil Armstrong was the first person to walk on the moon in 1969.",
            "The Internet became widely accessible in the 1990s.",
            "The first atomic bomb was dropped on Hiroshima in 1945.",
            "The Roman Empire fell in 476 AD.",
            "The Black Death killed millions in Europe during the 14th century.",
            "The Renaissance began in Italy in the 14th century.",
            "Alexander the Great created one of the largest empires in history.",
            "The Declaration of Independence was signed in 1776.",
            "The first manned spaceflight was launched by Yuri Gagarin in 1961.",
            "The United States Civil War lasted from 1861 to 1865.",
            "The fall of Constantinople occurred in 1453.",
            "Albert Einstein published his theory of relativity in 1905.",
            "The Industrial Revolution began in Britain in the 18th century.",
            "Mahatma Gandhi led India to independence in 1947.",
            "The Great Depression started in 1929.",
            "The first human heart transplant was performed in 1967.",
            "The Vietnam War lasted from 1955 to 1975.",
            "The pyramids of Egypt were built over 4,000 years ago.",
            "The Cuban Missile Crisis occurred in 1962.",
            "The Battle of Hastings took place in 1066.",
            "The Eiffel Tower was completed in 1889.",
            "Nelson Mandela was released from prison in 1990.",
            "The Cold War lasted from 1947 to 1991.",
            "Martin Luther King Jr. delivered his 'I Have a Dream' speech in 1963.",
            "The Russian Revolution took place in 1917.",
            "The first computer, ENIAC, was developed in 1945.",
            "Charles Darwin published 'On the Origin of Species' in 1859.",
            "The Suez Canal was opened in 1869.",
            "The Euro became the official currency of the EU in 2002.",
            "The first artificial satellite, Sputnik, was launched by the USSR in 1957.",
            "The Taj Mahal was completed in 1653.",
            "The Spanish Armada was defeated in 1588.",
            "The Mayflower landed at Plymouth in 1620.",
            "The Statue of Liberty was a gift from France in 1886.",
            "The Battle of Gettysburg was a turning point in the US Civil War in 1863.",
            "The Hubble Space Telescope was launched in 1990.",
            "The English Civil War began in 1642."
        );

        // Get users for sending messages
        $users = get_users( array( 'number' => $count ) );

        if ( empty( $users ) ) {
            return;
        }

        for ( $i = 1; $i <= $count; $i++ ) {
            $sender = $users[ array_rand( $users ) ];
            $recipient = $users[ array_rand( $users ) ];

            if ( $sender->ID == $recipient->ID ) {
                continue;
            }

            $subject = "Historical Fact " . $i;
            $content = $historical_facts[ array_rand( $historical_facts ) ];

            // Send message
            $message_id = messages_new_message( array(
                'sender_id' => $sender->ID,
                'recipients' => array( $recipient->ID ),
                'subject' => $subject,
                'content' => $content,
            ) );

            // Randomly decide if the recipient should reply
            if ( rand( 0, 1 ) && $message_id ) {
                // Random reply from a random user
                $reply_sender = $users[ array_rand( $users ) ];
                messages_new_message( array(
                    'sender_id' => $reply_sender->ID,
                    'thread_id' => $message_id,
                    'content' => "Replying to your message: " . $content,
                ));
            }
        }
    }
}
