<?php

class BP_BBPress_Module {

    private $historical_facts = [
        "The Great Wall of China was built to protect against invasions",
        "The Roman Empire was one of the largest empires in history",
        "The printing press revolutionized the way information was disseminated",
        "The pyramids in Egypt were built as tombs for pharaohs",
        "The first human civilizations emerged in Mesopotamia",
        "The Renaissance was a period of great cultural and artistic achievements",
        "The French Revolution led to the rise of democracy in France",
        "The discovery of penicillin revolutionized medicine",
        "The Industrial Revolution transformed economies from agrarian to industrial",
        "The American Civil War was fought over issues including slavery and state rights",
        "The first successful human flight was made by the Wright brothers",
        "The sinking of the Titanic was one of the deadliest maritime disasters",
        "The signing of the Magna Carta limited the power of English kings",
        "The Berlin Wall separated East and West Berlin during the Cold War",
        "The atomic bomb was used for the first time during World War II",
        "The Great Depression was a severe global economic downturn",
        "The Black Death was one of the most devastating pandemics in human history",
        "The Apollo program sent humans to the Moon",
        "The discovery of electricity changed the course of technological advancement",
        "The fall of Constantinople marked the end of the Byzantine Empire",
        "The abolition of slavery was a major milestone in human rights",
        "The Enlightenment was an intellectual and philosophical movement",
        "The Boston Tea Party was a key event leading up to the American Revolution",
        "The Cuban Missile Crisis was a significant event during the Cold War",
        "The assassination of Julius Caesar led to the end of the Roman Republic",
        "The discovery of the Americas by European explorers changed world history",
        "The Reformation led to the split of the Catholic Church and the formation of Protestantism",
        "The Spartans were known for their military prowess and discipline",
        "The Viking age was characterized by raids, trade, and exploration",
        "The discovery of fire was one of the most important developments in human history",
        "The fall of the Western Roman Empire marked the beginning of the Middle Ages",
        "The Crusades were a series of religious wars initiated by the Latin Church",
        "The invention of the steam engine powered the Industrial Revolution",
        "The Olympic Games originated in ancient Greece",
        "The Huns were a nomadic people who played a role in the fall of the Roman Empire",
        "The discovery of DNA's structure revolutionized biology",
        "The Suez Canal was a major engineering achievement connecting Europe and Asia",
        "The Spanish Armada was defeated by the English navy in a key battle",
        "The unification of Germany changed the political landscape of Europe",
        "The Ottoman Empire was a powerful empire that lasted for centuries",
        "The sinking of the Lusitania played a role in the United States entering World War I",
        "The Wright brothers are credited with inventing and building the first successful airplane",
        "The Mongol Empire was the largest contiguous land empire in history",
        "The discovery of the Rosetta Stone was key to understanding Egyptian hieroglyphs",
        "The Battle of Waterloo marked the end of Napoleon Bonaparte's rule",
        "The Inca civilization was known for its advanced agricultural techniques",
        "The spread of the bubonic plague drastically reduced Europe's population",
        "The discovery of the New World led to widespread colonization by European powers",
        "The Silk Road was a key trade route connecting the East and West",
        "The Treaty of Versailles officially ended World War I",
        "The fall of the Berlin Wall symbolized the end of the Cold War",
        "The invention of the telephone transformed global communication",
        "The British Empire was the largest empire in history at its height",
        "The discovery of antibiotics has saved millions of lives",
        "The Colosseum in Rome was used for gladiatorial games and public spectacles",
        "The Hundred Years' War was fought between England and France",
        "The French Revolution brought an end to the monarchy in France",
        "The assassination of Archduke Franz Ferdinand triggered World War I",
        "The Aztec Empire was known for its rich culture and advanced society",
        "The fall of the Soviet Union marked the end of the Cold War",
        "The discovery of vaccines has prevented numerous deadly diseases",
        "The Parthenon is one of the most iconic structures of ancient Greece",
        "The sinking of the Bismarck was a major naval battle in World War II",
        "The fall of the Bastille was a key event in the French Revolution",
        "The Mayans were known for their advances in mathematics and astronomy",
        "The Norman Conquest of England significantly shaped the history of the British Isles",
        "The Salem witch trials were a dark chapter in early American history",
        "The discovery of oil transformed economies around the world",
        "The Rosetta Stone was key to deciphering Egyptian hieroglyphs",
        "The Protestant Reformation led to religious upheaval across Europe",
        "The Taj Mahal is one of the most famous monuments in India, built by an emperor for his wife"
    ];    

    private $war_facts = [
        "World War II was the deadliest conflict in human history",
        "The Trojan War is said to have been fought over Helen of Troy",
        "The Civil War was fought in the United States between 1861-1865",
        "Napoleon Bonaparte led the French Army during the Napoleonic Wars",
        "The Vietnam War lasted from 1955 to 1975",
        "World War I was known as the Great War until World War II",
        "The Korean War resulted in the division of Korea into North and South",
        "The Persian Gulf War was triggered by Iraq's invasion of Kuwait",
        "The Hundred Years' War was fought between England and France",
        "The Falklands War was fought between Argentina and the United Kingdom",
        "The Peloponnesian War was a conflict between Athens and Sparta",
        "The Crimean War was one of the first wars to be covered by journalists",
        "The War of 1812 was fought between the United States and Great Britain",
        "The Russo-Japanese War was the first major military victory of an Asian power over a European power",
        "The Napoleonic Wars reshaped the political boundaries of Europe",
        "The Punic Wars were fought between Rome and Carthage",
        "The Gulf War was the first war to be broadcast live on television",
        "The Battle of Hastings in 1066 changed the course of English history",
        "The Spanish Civil War was a precursor to World War II",
        "The Thirty Years' War devastated much of Europe",
        "The Mexican-American War resulted in Mexico losing a large portion of its territory",
        "The Battle of Gettysburg was a turning point in the American Civil War",
        "The Boer Wars were fought between the British Empire and two Boer states in South Africa",
        "The War of the Roses was a series of civil wars for control of the throne of England",
        "The Blitz was the sustained bombing of the United Kingdom by Nazi Germany",
        "The Boxer Rebellion was an anti-foreign, anti-colonial uprising in China",
        "The Spanish Armada was defeated by the English navy in 1588",
        "The Battle of Waterloo marked the end of the Napoleonic Wars",
        "The Seven Years' War involved most of the great powers of the time",
        "The Battle of Stalingrad was one of the bloodiest battles in World War II",
        "The First Punic War was primarily a naval conflict between Rome and Carthage",
        "The Gulf of Tonkin incident escalated U.S. involvement in the Vietnam War",
        "The Suez Crisis was a diplomatic and military conflict involving Egypt, Israel, Britain, and France",
        "The invasion of Normandy, known as D-Day, was a key Allied victory in World War II",
        "The Battle of Midway was a turning point in the Pacific Theater of World War II",
        "The Rwandan Civil War culminated in the 1994 genocide",
        "The Battle of Britain was the first major military campaign fought entirely by air forces",
        "The Arab-Israeli wars have shaped much of the modern Middle East conflict",
        "The French and Indian War was part of the larger Seven Years' War",
        "The Zulu Wars were fought between the British Empire and the Zulu Kingdom",
        "The First Gulf War saw a coalition of forces liberate Kuwait from Iraqi occupation",
        "The American Revolutionary War secured the United States' independence from Britain",
        "The Siege of Leningrad lasted over 800 days during World War II",
        "The Vietnam War saw extensive use of guerrilla tactics by the Viet Cong",
        "The Iran-Iraq War was one of the longest conventional wars of the 20th century",
        "The Battle of the Somme was one of the largest battles of World War I",
        "The Soviet-Afghan War contributed to the fall of the Soviet Union",
        "The Ottoman Empire collapsed after its defeat in World War I",
        "The Japanese attack on Pearl Harbor brought the U.S. into World War II",
        "The Spanish-American War resulted in the U.S. acquiring territories like Puerto Rico and the Philippines",
        "The War of Spanish Succession involved most of the European powers",
        "The Battle of Thermopylae is famous for the heroic stand of 300 Spartans",
        "The Six-Day War dramatically reshaped the borders of the Middle East",
        "The Yom Kippur War was fought between Israel and a coalition of Arab states",
        "The Iran-Contra Affair was a political scandal involving U.S. covert operations",
        "The Battle of Verdun was one of the longest and most costly battles of World War I",
        "The Anglo-Zanzibar War is the shortest war in history, lasting 38 minutes",
        "The First Indochina War laid the groundwork for the Vietnam War",
        "The Balkan Wars were fought in the early 20th century over the territories of the declining Ottoman Empire",
        "The War in Afghanistan began in 2001 following the 9/11 attacks",
        "The War of Austrian Succession involved multiple European powers competing for influence",
        "The Battle of Okinawa was one of the largest amphibious assaults in the Pacific Theater",
        "The Siege of Constantinople in 1453 led to the fall of the Byzantine Empire",
        "The Gulf of Tonkin Resolution gave the U.S. President authority to assist Southeast Asian countries",
        "The Alamo became a symbol of Texan resistance during the Texas Revolution",
        "The Invasion of Iraq in 2003 was part of the War on Terror",
        "The Tet Offensive was a major turning point in the Vietnam War",
        "The Battle of Agincourt was a major English victory in the Hundred Years' War",
        "The Crusades were a series of religious wars sanctioned by the Latin Church",
        "The War of Jenkins' Ear was a conflict between Britain and Spain",
        "The Battle of Trafalgar established British naval dominance"
    ];
    

    // Variable to hold user IDs
    private $user_ids = [];

    public function __construct() {
        $this->user_ids = $this->fetch_user_ids();
    }

    public function create_forums_with_topics_and_replies( $forum_count = 10, $min_topics = 10, $max_topics = 15, $min_replies = 3, $max_replies = 7 ) {
        for ( $i = 0; $i < $forum_count; $i++ ) {
            $forum_id = $this->create_forum();

            // Create a random number of topics for this forum
            $topic_count = rand( $min_topics, $max_topics );
            WP_CLI::line( "Creating $topic_count topics for Forum ID: $forum_id" );
            for ( $j = 0; $j < $topic_count; $j++ ) {
                $topic_id = $this->create_topic( $forum_id );

                // Create a random number of replies for this topic
                $reply_count = rand( $min_replies, $max_replies );
                WP_CLI::line( "Creating $reply_count replies for Topic ID: $topic_id" );
                $this->create_replies( $topic_id, $reply_count );
            }
        }

        WP_CLI::success( "$forum_count forums with topics and replies have been created." );
    }

    private function create_forum() {
        $user_id = $this->get_random_user_id();

        // Create a forum
        $forum_id = bbp_insert_forum( array(
            'post_title'   => 'Random Forum ' . wp_generate_password( 5, false ),
            'post_content' => 'This is a randomly generated forum description.',
            'post_author'  => $user_id,
            'post_status'  => bbp_get_public_status_id(),
        ));

        if ( $forum_id ) {
            WP_CLI::line( "Created Forum ID: $forum_id by User ID: $user_id" );
        }

        return $forum_id;
    }

    private function create_topic( $forum_id ) {
        $user_id = $this->get_random_user_id();
        $topic_title = $this->get_random_historical_fact();

        // Create a topic
        $topic_id = bbp_insert_topic( array(
            'post_title'   => $topic_title,
            'post_content' => 'This is a topic discussing the historical event: ' . $topic_title,
            'post_parent'  => $forum_id,
            'post_author'  => $user_id,
            'post_status'  => bbp_get_public_status_id(),
        ));

        if ( $topic_id ) {
            WP_CLI::line( "Created Topic ID: $topic_id with title '$topic_title' in Forum ID: $forum_id by User ID: $user_id" );
        }

        return $topic_id;
    }

    private function create_replies( $topic_id, $count ) {
        for ( $i = 0; $i < $count; $i++ ) {
            $user_id = $this->get_random_user_id();
            $reply_content = $this->get_random_war_fact();

            // Create a reply
            $reply_id = bbp_insert_reply( array(
                'post_content' => $reply_content,
                'post_parent'  => $topic_id,
                'post_author'  => $user_id,
                'post_status'  => bbp_get_public_status_id(),
            ));

            if ( $reply_id ) {
                WP_CLI::line( "Created Reply ID: $reply_id with content '$reply_content' for Topic ID: $topic_id by User ID: $user_id" );
            }
        }
    }

    private function get_random_historical_fact() {
        return $this->historical_facts[array_rand( $this->historical_facts )];
    }

    private function get_random_war_fact() {
        return $this->war_facts[array_rand( $this->war_facts )];
    }

    // Fetches all user IDs and shuffles them to ensure randomness
    private function fetch_user_ids() {
        $users = get_users( array(
            'fields' => 'ID'
        ));

        if ( !empty( $users ) ) {
            shuffle( $users ); // Shuffle to ensure randomness
            return $users;
        }

        return [1]; // Default to admin if no users found
    }

    // Returns a random user ID from the fetched and shuffled list
    private function get_random_user_id() {
        if ( empty( $this->user_ids ) ) {
            $this->user_ids = $this->fetch_user_ids(); // Re-fetch if empty
        }
        
        return $this->user_ids[array_rand( $this->user_ids )];
    }
}
