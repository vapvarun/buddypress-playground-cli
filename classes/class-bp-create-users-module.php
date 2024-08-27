<?php

class BP_Create_Users_Module {

    public function create_users( $count = 500 ) {
        $first_names = array('Liam', 'Noah', 'Oliver', 'Elijah', 'William', 'James', 'Benjamin', 'Lucas', 'Henry', 'Alexander', 'Mason', 'Michael', 'Ethan', 'Daniel', 'Jacob', 'Logan', 'Jackson', 'Levi', 'Sebastian', 'Mateo', 'Jack', 'Owen', 'Theodore', 'Aiden', 'Samuel', 'Joseph', 'John', 'David', 'Wyatt', 'Matthew', 'Luke', 'Asher', 'Carter', 'Julian', 'Grayson', 'Leo', 'Jayden', 'Gabriel', 'Isaac', 'Lincoln', 'Anthony', 'Hudson', 'Dylan', 'Ezra', 'Thomas', 'Charles', 'Christopher', 'Jaxon', 'Maverick', 'Josiah', 'Isaiah', 'Andrew', 'Elias', 'Joshua', 'Nathan', 'Caleb', 'Ryan', 'Adrian', 'Miles', 'Eli', 'Nolan', 'Christian', 'Aaron', 'Cameron', 'Ezekiel', 'Colton', 'Luca', 'Landon', 'Hunter', 'Jonathan', 'Santiago', 'Axel', 'Easton', 'Cooper', 'Jeremiah', 'Angel', 'Roman', 'Connor', 'Jameson', 'Robert', 'Greyson', 'Jordan', 'Ian', 'Carson', 'Jace', 'Leonardo', 'Nicholas', 'Dominic', 'Austin', 'Everett', 'Brooks', 'Xavier', 'Kai', 'Jose', 'Parker', 'Adam', 'Jaxson', 'Wesley', 'Kayden', 'Silas', 'Bennett', 'Declan', 'Weston');
        $last_names = array('Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts', 'Gomez', 'Phillips', 'Evans', 'Turner', 'Diaz', 'Parker', 'Cruz', 'Edwards', 'Collins', 'Reyes', 'Stewart', 'Morris', 'Morales', 'Murphy', 'Cook', 'Rogers', 'Gutierrez', 'Ortiz', 'Morgan', 'Cooper', 'Peterson', 'Bailey', 'Reed', 'Kelly', 'Howard', 'Ramos', 'Kim', 'Cox', 'Ward', 'Richardson', 'Watson', 'Brooks', 'Chavez', 'Wood', 'James', 'Bennett', 'Gray', 'Mendoza', 'Ruiz', 'Hughes', 'Price', 'Alvarez', 'Castillo', 'Sanders', 'Patel', 'Myers', 'Long', 'Ross', 'Foster', 'Jimenez', 'Powell');
        $bios = array(
              "Passionate about technology and innovation. Always eager to learn something new.",
              "Avid traveler and foodie. Loves exploring new cultures and cuisines.",
              "Dedicated to fitness and healthy living. Enjoys outdoor activities.",
              "A creative thinker with a love for art and design.",
              "An enthusiastic reader and writer with a love for storytelling.",
              "Tech-savvy professional with a passion for coding and software development.",
              "A music lover with a penchant for playing the guitar and composing songs.",
              "An environmental advocate who enjoys gardening and sustainability practices.",
              "A family-oriented individual who values quality time with loved ones.",
              "A sports enthusiast who loves playing soccer and watching football.",
              "A photography enthusiast who enjoys capturing the beauty of nature.",
              "Passionate about helping others and making a positive impact in the community.",
              "An aspiring chef who loves experimenting with new recipes in the kitchen.",
              "A fitness trainer dedicated to helping people achieve their health goals.",
              "A bookworm who can spend hours lost in a good novel.",
              "An entrepreneur with a drive to create and innovate in the business world.",
              "A lover of the outdoors, always up for a hiking or camping adventure.",
              "An artist at heart, with a passion for painting and drawing.",
              "A tech enthusiast who enjoys staying up-to-date with the latest gadgets.",
              "A teacher dedicated to inspiring and educating the next generation.",
              "A gamer who loves diving into new video games and exploring virtual worlds.",
              "A history buff with a deep interest in learning about the past.",
              "A film enthusiast who enjoys watching and analyzing movies of all genres.",
              "A social butterfly who loves meeting new people and making connections.",
              "A writer with a passion for crafting compelling stories and articles.",
              "A DIY enthusiast who enjoys taking on home improvement projects.",
              "A yoga practitioner who believes in the power of mindfulness and relaxation.",
              "A foodie who loves trying out new restaurants and cuisines.",
              "A marathon runner who enjoys pushing physical limits and staying active.",
              "A pet lover with a special bond with animals, especially dogs and cats.",
              "A lifelong learner who believes that education never stops.",
              "A gardener who finds joy in growing and nurturing plants.",
              "A musician who enjoys playing instruments and composing music.",
              "An environmentalist who advocates for sustainability and green living.",
              "A mentor who enjoys guiding and supporting others in their careers.",
              "A volunteer who dedicates time to charitable causes and helping those in need.",
              "A tech blogger who shares insights and tips on the latest technology trends.",
              "A minimalist who believes in living a simple, clutter-free life.",
              "A sports coach dedicated to helping athletes reach their full potential.",
              "A coffee lover who enjoys exploring different types of brews and roasts.",
              "A wine enthusiast who enjoys tasting and learning about different wines.",
              "A fashionista with a keen eye for style and trends.",
              "A nature lover who enjoys spending time in parks and forests.",
              "A tech entrepreneur who loves building and scaling startups.",
              "A travel blogger who shares experiences and tips from around the world.",
              "A health-conscious individual who enjoys cooking nutritious meals.",
              "A creative writer who enjoys crafting fiction and poetry.",
              "An avid cyclist who loves exploring new trails and roads.",
              "A documentary filmmaker with a passion for telling real-life stories.",
              "A web developer who enjoys coding and creating websites.",
              "A fitness coach who helps others achieve their workout goals.",
              "An interior designer who loves creating beautiful and functional spaces.",
              "A language enthusiast who enjoys learning and speaking new languages.",
              "A sustainability advocate who promotes eco-friendly practices.",
              "A podcast host who enjoys discussing a wide range of topics.",
              "A financial advisor who helps people manage their money and investments.",
              "A foodie who enjoys baking and sharing delicious treats with others.",
              "An artist who enjoys expressing creativity through various mediums.",
              "A teacher who is passionate about making a difference in students' lives.",
              "A digital nomad who enjoys working while traveling the world.",
              "A marathoner who thrives on the challenge of long-distance running.",
              "A tech writer who enjoys breaking down complex topics for readers.",
              "A chef who is passionate about creating culinary masterpieces.",
              "A mental health advocate who supports awareness and education.",
              "A wildlife photographer with a passion for capturing animals in their natural habitats.",
              "A musician who loves composing and performing original songs.",
              "A family man who values spending quality time with loved ones.",
              "A coding enthusiast who enjoys building apps and learning new programming languages.",
              "A public speaker who enjoys inspiring and motivating audiences.",
              "A culinary artist who loves experimenting with flavors and ingredients.",
              "A dog lover who enjoys taking long walks with furry friends.",
              "A hiking enthusiast who enjoys exploring nature and challenging trails.",
              "A digital marketer who loves creating and optimizing online campaigns.",
              "A tech geek who enjoys testing and reviewing the latest gadgets.",
              "A meditation practitioner who believes in the power of mindfulness.",
              "A creative designer with a passion for visual arts and aesthetics.",
              "A wellness coach dedicated to promoting physical and mental health.",
              "A literature lover who enjoys analyzing and discussing classic works.",
              "A filmmaker who enjoys telling stories through the lens.",
              "A social media strategist who helps brands connect with their audience.",
              "A gym enthusiast who enjoys weightlifting and strength training.",
              "A writer who enjoys crafting compelling stories and blog posts.",
              "A creative entrepreneur who loves turning ideas into reality.",
              "A beach lover who enjoys relaxing by the ocean and soaking up the sun.",
              "A fitness enthusiast who enjoys a balanced and active lifestyle.",
              "A community organizer who enjoys bringing people together for a cause.",
              "A basketball player who enjoys the thrill of the game and teamwork.",
              "A tech startup founder who is passionate about innovation and growth.",
              "A travel enthusiast who loves discovering new places and cultures.",
              "A public relations expert who enjoys managing communications and media.",
              "A home chef who enjoys experimenting with new recipes and flavors.",
              "A movie buff who enjoys watching and critiquing films.",
              "A creative director with a passion for visual storytelling.",
              "A fitness blogger who shares workout routines and healthy living tips.",
              "A language teacher who enjoys helping others learn and grow.",
              "A sustainability advocate who is passionate about eco-friendly living."
          );

      $usernames = array(
            'techwizard', 'travelfreak', 'fitnessguru', 'artlover', 'storyteller', 'codejunkie', 'musicmaniac',
            'greenwarrior', 'familyguy', 'soccerfanatic', 'photogenius', 'helpinghand', 'culinaryking',
            'fitnesstrainer', 'bookworm', 'startupmaven', 'outdoorlover', 'artisticmind', 'gadgetgeek',
            'inspiredteacher', 'gameaddict', 'historybuff', 'moviecritic', 'socialbutterfly', 'wordsmith',
            'diyenthusiast', 'yogamaster', 'foodadventurer', 'marathonrunner', 'petlover', 'lifelonglearner',
            'plantparent', 'musicmaker', 'ecoactivist', 'careermentor', 'charitychamp', 'techblogger',
            'minimalistliving', 'sportycoach', 'coffeeconnoisseur', 'wineenthusiast', 'fashionista',
            'natureseeker', 'techfounder', 'worldexplorer', 'healthnut', 'creativewriter', 'bikerider',
            'documentaryfilm', 'webcreator', 'fitnesstrainer', 'homedesigner', 'languagelearner', 'greenliving',
            'podcastpro', 'moneywise', 'bakeoffboss', 'artistatwork', 'teachermaker', 'digitalnomad',
            'runnertowin', 'techreviewer', 'culinarycreator', 'mentalhealthmatters', 'wildlifephotog',
            'musicmixer', 'familyfirst', 'codebreaker', 'publicspeaker', 'culinarymaster', 'dogwalker',
            'hikinghero', 'digitalmarketer', 'gadgettester', 'mindfulnessmentor', 'creativedesigner',
            'wellnesswarrior', 'litlover', 'filmmaker', 'socialmediaguru', 'gymrat', 'blogwriter',
            'ideaengineer', 'beachbum', 'fitlife', 'communityleader', 'hoopdreams', 'innovator', 'wanderlust',
            'prpro', 'homecook', 'moviemaniac', 'visualstory', 'fitnessblogger', 'languagecoach', 'ecowarrior'
        );

        for ( $i = 1; $i <= $count; $i++ ) {
            $first_name = $first_names[ array_rand( $first_names ) ];
            $last_name = $last_names[ array_rand( $last_names ) ];
            $bio = $bios[ array_rand( $bios ) ];
            $username_base = $usernames[ array_rand( $usernames ) ];

            // Combine the username base with the first name to create a unique username
            $username = strtolower( $username_base . '_' . $first_name . $i );
            $email = $username . '@example.com';

            $user_id = wp_insert_user( array(
                'user_login' => $username,
                'user_pass'  => wp_generate_password(),
                'user_email' => $email,
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'description'=> $bio,
            ));

            if ( ! is_wp_error( $user_id ) ) {
                // Optionally, you can update the last activity to make sure the user shows up in directories
                bp_update_user_last_activity( $user_id );
            }
        }

        echo "$count users have been created successfully.";
    }
}
