<?php
/**
 * Predefined XProfile Structure with All Field Types
 * 
 * Complete structure covering all BuddyPress field types with
 * predefined value sets for consistent data generation
 */

return [
    'field_groups' => [
        
        // Group 1: Base (Primary/Personal) - This is the default BuddyPress group (ID: 1)
        // We'll add fields to the existing Base group instead of creating a new one
        'base' => [
            'name' => 'Base', // This will match the existing Base group
            'description' => 'Primary profile information',
            'can_delete' => false, // Base group cannot be deleted
            'use_existing' => true, // Flag to indicate we should use the existing Base group
            'fields' => [
                // Note: The "Name" field (ID: 1) already exists in Base group by default
                // We'll add additional essential personal fields
                
                // TEXTBOX Fields - Core personal identifiers
                'first_name' => [
                    'type' => 'textbox',
                    'name' => 'First Name',
                    'description' => 'Your first name',
                    'is_required' => true,
                    'can_delete' => false, // Core field in Base group
                    'field_order' => 2, // After the default Name field
                    'values' => [
                        'male' => ['James', 'John', 'Robert', 'Michael', 'William', 'David', 'Richard', 'Joseph', 'Thomas', 'Charles'],
                        'female' => ['Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth', 'Barbara', 'Susan', 'Jessica', 'Sarah', 'Karen']
                    ]
                ],
                
                'last_name' => [
                    'type' => 'textbox',
                    'name' => 'Last Name',
                    'description' => 'Your last name',
                    'is_required' => true,
                    'can_delete' => false, // Core field in Base group
                    'field_order' => 3,
                    'values' => ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin']
                ],
                
                // TEXTAREA Field - Primary bio in Base group
                'bio' => [
                    'type' => 'textarea',
                    'name' => 'Bio',
                    'description' => 'Brief description about yourself',
                    'is_required' => false,
                    'can_delete' => false, // Core field in Base group
                    'field_order' => 4,
                    'values' => [
                        'templates' => [
                            'Passionate {professional} with over {years} years of experience in {industry}. Love {hobby1} and {hobby2} in my free time.',
                            'Dedicated to {cause} and making a positive impact through {method}. When not working, enjoy {hobby1} and {hobby2}.',
                            '{years} years in the {industry} industry. Excited about {trend} and always looking to learn new things.',
                            'Professional {role} with a love for {hobby}. Always up for a good conversation about {topic}.',
                            'Lifelong learner with interests in {interest1}, {interest2}, and {interest3}. Currently exploring {new_skill}.',
                            'Building my skills in {skill} while working as a {role}. Open to new opportunities and connections.',
                            'Creative {professional} exploring the intersection of {field1} and {field2}. Let\'s connect and collaborate!',
                            'Enthusiastic about {topic} and its impact on {industry}. Always happy to share knowledge and learn from others.'
                        ],
                        'variables' => [
                            'professional' => ['developer', 'designer', 'manager', 'consultant', 'entrepreneur', 'educator', 'engineer', 'artist', 'writer', 'marketer'],
                            'years' => ['5', '10', '15', '3', '7', '12', '20'],
                            'industry' => ['technology', 'healthcare', 'education', 'finance', 'marketing', 'design', 'consulting', 'media', 'retail', 'manufacturing'],
                            'hobby' => ['photography', 'traveling', 'reading', 'cooking', 'hiking', 'gaming', 'music', 'sports', 'art', 'writing'],
                            'hobby1' => ['photography', 'traveling', 'reading', 'cooking', 'hiking'],
                            'hobby2' => ['gaming', 'music', 'sports', 'art', 'writing'],
                            'cause' => ['education', 'sustainability', 'innovation', 'community building', 'social justice', 'health', 'technology', 'creativity'],
                            'method' => ['technology', 'education', 'community engagement', 'creative solutions', 'collaboration', 'innovation'],
                            'trend' => ['AI', 'blockchain', 'sustainability', 'remote work', 'digital transformation', 'Web3', 'automation'],
                            'role' => ['developer', 'designer', 'manager', 'consultant', 'analyst', 'coordinator', 'specialist', 'director'],
                            'topic' => ['technology', 'design', 'business', 'education', 'science', 'culture', 'philosophy', 'economics'],
                            'interest1' => ['technology', 'art', 'science', 'history', 'philosophy'],
                            'interest2' => ['music', 'literature', 'sports', 'nature', 'culture'],
                            'interest3' => ['travel', 'food', 'fitness', 'meditation', 'photography'],
                            'new_skill' => ['machine learning', 'blockchain', 'UX design', 'data science', 'digital marketing', 'project management'],
                            'field1' => ['art', 'technology', 'science', 'business', 'education'],
                            'field2' => ['design', 'innovation', 'sustainability', 'community', 'creativity']
                        ]
                    ]
                ],
                
                // TEXTBOX Field - Location in Base group
                'location' => [
                    'type' => 'textbox',
                    'name' => 'Location',
                    'description' => 'City, Country',
                    'is_required' => false,
                    'can_delete' => false,
                    'field_order' => 5,
                    'values' => [
                        'New York, USA', 'Los Angeles, USA', 'London, UK', 'Paris, France',
                        'Tokyo, Japan', 'Sydney, Australia', 'Toronto, Canada', 'Berlin, Germany',
                        'Amsterdam, Netherlands', 'Singapore', 'Dubai, UAE', 'Mumbai, India'
                    ]
                ],
                
                // URL Field - Primary website in Base group
                'website' => [
                    'type' => 'url',
                    'name' => 'Website',
                    'description' => 'Your personal website or blog',
                    'is_required' => false,
                    'can_delete' => false,
                    'field_order' => 6,
                    'values' => [
                        'patterns' => [
                            'https://www.{username}.com',
                            'https://{username}.github.io',
                            'https://portfolio.{username}.dev'
                        ]
                    ]
                ]
            ]
        ],
        
        // Group 2: Additional Personal Details (previously personal_info, now moved here)
        'personal_details' => [
            'name' => 'Personal Details',
            'description' => 'Additional personal information',
            'can_delete' => true,
            'fields' => [
                
                'nickname' => [
                    'type' => 'textbox',
                    'name' => 'Nickname',
                    'description' => 'Preferred nickname',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 1,
                    'values' => ['generate_from_name'] // Special flag to generate from first name
                ],
                
                // DATEBOX Field
                'date_of_birth' => [
                    'type' => 'datebox',
                    'name' => 'Date of Birth',
                    'description' => 'Your birth date',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 2,
                    'values' => [
                        'min_year' => 1950,
                        'max_year' => 2005
                    ]
                ],
                
                // RADIO Field
                'gender' => [
                    'type' => 'radio',
                    'name' => 'Gender',
                    'description' => 'Your gender',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 3,
                    'options' => [
                        'Male',
                        'Female',
                        'Non-binary',
                        'Prefer not to say'
                    ],
                    'default_option' => 'Prefer not to say'
                ],
                
                // SELECTBOX Field
                'marital_status' => [
                    'type' => 'selectbox',
                    'name' => 'Marital Status',
                    'description' => 'Your marital status',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 4,
                    'options' => [
                        'Single',
                        'Married',
                        'Divorced',
                        'Widowed',
                        'In a relationship',
                        'It\'s complicated',
                        'Prefer not to say'
                    ]
                ]
            ]
        ],
        
        // Group 3: Contact Information (renamed from Contact & Location)
        'contact_location' => [
            'name' => 'Contact & Location',
            'description' => 'Contact information and location details',
            'can_delete' => true,
            'fields' => [
                
                // URL Fields
                'website' => [
                    'type' => 'url',
                    'name' => 'Website',
                    'description' => 'Your personal or professional website',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 1,
                    'values' => [
                        'patterns' => [
                            'https://www.{username}.com',
                            'https://{username}.github.io',
                            'https://portfolio.{username}.dev',
                            'https://blog.{username}.net',
                            'https://www.{username}-portfolio.com'
                        ]
                    ]
                ],
                
                'linkedin' => [
                    'type' => 'url',
                    'name' => 'LinkedIn Profile',
                    'description' => 'Your LinkedIn profile URL',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 2,
                    'values' => [
                        'patterns' => [
                            'https://www.linkedin.com/in/{username}',
                            'https://www.linkedin.com/in/{firstname}-{lastname}',
                            'https://linkedin.com/in/{username}-{random}'
                        ]
                    ]
                ],
                
                // TELEPHONE Field
                'phone' => [
                    'type' => 'telephone',
                    'name' => 'Phone Number',
                    'description' => 'Your contact number',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 3,
                    'values' => [
                        'patterns' => [
                            '+1 (555) {area}-{exchange}',
                            '+44 20 {num1} {num2}',
                            '+61 2 {num1} {num2}'
                        ],
                        'area' => ['212', '415', '312', '213', '305', '404', '503', '206'],
                        'exchange' => ['0100-9999'] // Range
                    ]
                ],
                
                // TEXTBOX Fields for location
                'city' => [
                    'type' => 'textbox',
                    'name' => 'City',
                    'description' => 'City where you live',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 4,
                    'values' => [
                        'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia',
                        'San Antonio', 'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville',
                        'San Francisco', 'Columbus', 'Indianapolis', 'Fort Worth', 'Charlotte',
                        'Seattle', 'Denver', 'Washington', 'Boston', 'Nashville', 'Detroit',
                        'Portland', 'Memphis', 'Louisville', 'Baltimore', 'Milwaukee', 'Albuquerque'
                    ]
                ],
                
                // SELECTBOX for country
                'country' => [
                    'type' => 'selectbox',
                    'name' => 'Country',
                    'description' => 'Country where you live',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 5,
                    'options' => [
                        'United States',
                        'Canada',
                        'United Kingdom',
                        'Australia',
                        'Germany',
                        'France',
                        'Spain',
                        'Italy',
                        'Netherlands',
                        'Sweden',
                        'Switzerland',
                        'Japan',
                        'South Korea',
                        'Singapore',
                        'India',
                        'Brazil',
                        'Mexico',
                        'Argentina'
                    ]
                ]
            ]
        ],
        
        // Group 3: Professional Information
        'professional' => [
            'name' => 'Professional Information',
            'description' => 'Work experience and professional details',
            'can_delete' => true,
            'fields' => [
                
                // TEXTBOX Fields
                'job_title' => [
                    'type' => 'textbox',
                    'name' => 'Job Title',
                    'description' => 'Your current job title',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 1,
                    'values' => [
                        'Software Engineer', 'Product Manager', 'UX Designer', 'Data Scientist',
                        'Marketing Manager', 'Sales Representative', 'Business Analyst',
                        'Project Manager', 'Graphic Designer', 'Content Writer', 'HR Manager',
                        'Financial Analyst', 'Operations Manager', 'Customer Success Manager',
                        'DevOps Engineer', 'Quality Assurance Engineer', 'Technical Writer',
                        'Account Executive', 'Social Media Manager', 'SEO Specialist'
                    ]
                ],
                
                'company' => [
                    'type' => 'textbox',
                    'name' => 'Company',
                    'description' => 'Your current employer',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 2,
                    'values' => [
                        'TechCorp Solutions', 'Global Innovations Inc', 'Digital Dynamics',
                        'Future Systems Ltd', 'Creative Studios', 'Data Insights Corp',
                        'Cloud Services Pro', 'Marketing Genius Agency', 'Consulting Partners',
                        'StartUp Ventures', 'Enterprise Solutions', 'Mobile First Inc',
                        'AI Research Labs', 'Green Energy Co', 'Health Tech Solutions',
                        'Finance Pro Services', 'Education Platform', 'Media Productions'
                    ]
                ],
                
                // NUMBER Field
                'years_experience' => [
                    'type' => 'number',
                    'name' => 'Years of Experience',
                    'description' => 'Total years of professional experience',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 3,
                    'values' => [
                        'min' => 0,
                        'max' => 40,
                        'distribution' => 'weighted', // More people with 2-10 years
                        'weights' => [
                            '0-2' => 0.15,
                            '3-5' => 0.25,
                            '6-10' => 0.30,
                            '11-15' => 0.15,
                            '16-20' => 0.10,
                            '21+' => 0.05
                        ]
                    ]
                ],
                
                // SELECTBOX for industry
                'industry' => [
                    'type' => 'selectbox',
                    'name' => 'Industry',
                    'description' => 'Your industry sector',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 4,
                    'options' => [
                        'Technology',
                        'Healthcare',
                        'Finance',
                        'Education',
                        'Retail',
                        'Manufacturing',
                        'Consulting',
                        'Marketing & Advertising',
                        'Real Estate',
                        'Entertainment',
                        'Non-Profit',
                        'Government',
                        'Transportation',
                        'Energy',
                        'Agriculture',
                        'Hospitality',
                        'Telecommunications',
                        'Legal Services'
                    ]
                ],
                
                // RADIO for employment status
                'employment_status' => [
                    'type' => 'radio',
                    'name' => 'Employment Status',
                    'description' => 'Current employment status',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 5,
                    'options' => [
                        'Full-time',
                        'Part-time',
                        'Freelance',
                        'Self-employed',
                        'Unemployed',
                        'Student',
                        'Retired'
                    ],
                    'default_option' => 'Full-time'
                ],
                
                // TEXTAREA for skills
                'skills' => [
                    'type' => 'textarea',
                    'name' => 'Skills',
                    'description' => 'Your professional skills',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 6,
                    'values' => [
                        'skill_sets' => [
                            'technical' => ['JavaScript', 'Python', 'React', 'Node.js', 'SQL', 'AWS', 'Docker', 'Git', 'TypeScript', 'MongoDB'],
                            'design' => ['Photoshop', 'Illustrator', 'Figma', 'Sketch', 'InDesign', 'After Effects', 'UI/UX Design', 'Wireframing'],
                            'business' => ['Project Management', 'Strategic Planning', 'Business Analysis', 'Financial Modeling', 'Risk Management'],
                            'marketing' => ['SEO', 'Content Marketing', 'Social Media', 'Google Analytics', 'Email Marketing', 'PPC', 'Brand Strategy'],
                            'soft' => ['Leadership', 'Communication', 'Problem Solving', 'Team Work', 'Time Management', 'Critical Thinking'],
                            'data' => ['Data Analysis', 'Machine Learning', 'Statistics', 'R', 'Tableau', 'Power BI', 'Excel', 'Data Visualization']
                        ],
                        'format' => 'comma_separated', // Will pick 3-7 skills and format as comma-separated
                        'count' => [3, 7]
                    ]
                ]
            ]
        ],
        
        // Group 4: Interests & Preferences
        'interests' => [
            'name' => 'Interests & Preferences',
            'description' => 'Personal interests, hobbies, and preferences',
            'can_delete' => true,
            'fields' => [
                
                // CHECKBOX Field (multiple selection)
                'hobbies' => [
                    'type' => 'checkbox',
                    'name' => 'Hobbies',
                    'description' => 'Select your hobbies (multiple)',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 1,
                    'options' => [
                        'Reading',
                        'Traveling',
                        'Photography',
                        'Cooking',
                        'Gaming',
                        'Sports',
                        'Music',
                        'Movies',
                        'Hiking',
                        'Art & Crafts',
                        'Gardening',
                        'Dancing',
                        'Writing',
                        'Yoga',
                        'Fitness'
                    ],
                    'values' => [
                        'min_selections' => 2,
                        'max_selections' => 6
                    ]
                ],
                
                // MULTISELECTBOX Field
                'languages' => [
                    'type' => 'multiselectbox',
                    'name' => 'Languages Spoken',
                    'description' => 'Languages you speak',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 2,
                    'options' => [
                        'English',
                        'Spanish',
                        'French',
                        'German',
                        'Italian',
                        'Portuguese',
                        'Russian',
                        'Chinese (Mandarin)',
                        'Japanese',
                        'Korean',
                        'Arabic',
                        'Hindi',
                        'Dutch',
                        'Swedish',
                        'Polish'
                    ],
                    'values' => [
                        'min_selections' => 1,
                        'max_selections' => 4,
                        'always_include' => ['English'] // Always include English
                    ]
                ],
                
                // SELECTBOX for favorite category
                'favorite_music' => [
                    'type' => 'selectbox',
                    'name' => 'Favorite Music Genre',
                    'description' => 'Your favorite music genre',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 3,
                    'options' => [
                        'Rock',
                        'Pop',
                        'Jazz',
                        'Classical',
                        'Hip Hop',
                        'Electronic',
                        'Country',
                        'R&B',
                        'Blues',
                        'Metal',
                        'Folk',
                        'Reggae',
                        'Latin',
                        'Indie'
                    ]
                ],
                
                // RADIO for dietary preference
                'dietary_preference' => [
                    'type' => 'radio',
                    'name' => 'Dietary Preference',
                    'description' => 'Your dietary preference',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 4,
                    'options' => [
                        'No restrictions',
                        'Vegetarian',
                        'Vegan',
                        'Pescatarian',
                        'Gluten-free',
                        'Keto',
                        'Paleo',
                        'Other'
                    ],
                    'default_option' => 'No restrictions'
                ],
                
                // TEXTAREA for interests
                'interests_description' => [
                    'type' => 'textarea',
                    'name' => 'Interests & Goals',
                    'description' => 'Describe your interests and goals',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 5,
                    'values' => [
                        'templates' => [
                            'Currently interested in {interest1} and {interest2}. My goal is to {goal} within the next {timeframe}.',
                            'Passionate about {passion1} and {passion2}. Working towards {achievement} while maintaining {balance}.',
                            'Exploring {field} with a focus on {specialty}. Hope to {aspiration} in the coming years.',
                            'Love learning about {topic1} and {topic2}. My current project involves {project}.'
                        ],
                        'variables' => [
                            'interest1' => ['blockchain technology', 'sustainable living', 'digital art', 'machine learning', 'creative writing'],
                            'interest2' => ['community building', 'personal development', 'outdoor adventures', 'culinary arts', 'music production'],
                            'goal' => ['master a new skill', 'start a business', 'complete a certification', 'travel to new places', 'write a book'],
                            'timeframe' => ['year', 'six months', 'two years', 'few months'],
                            'passion1' => ['education', 'technology', 'arts', 'health', 'environment'],
                            'passion2' => ['innovation', 'creativity', 'wellness', 'culture', 'science'],
                            'achievement' => ['professional growth', 'personal milestones', 'creative projects', 'academic goals'],
                            'balance' => ['work-life balance', 'health and fitness', 'family time', 'personal interests'],
                            'field' => ['data science', 'UX design', 'digital marketing', 'software development', 'content creation'],
                            'specialty' => ['AI applications', 'user research', 'growth strategies', 'cloud architecture', 'visual storytelling'],
                            'aspiration' => ['lead a team', 'launch a product', 'publish research', 'speak at conferences', 'mentor others'],
                            'topic1' => ['psychology', 'history', 'technology trends', 'philosophy', 'economics'],
                            'topic2' => ['science', 'art history', 'world cultures', 'innovation', 'sustainability'],
                            'project' => ['building a mobile app', 'writing a blog', 'creating a course', 'developing a framework', 'organizing events']
                        ]
                    ]
                ]
            ]
        ],
        
        // Group 5: Settings & Privacy (with special field types)
        'settings_privacy' => [
            'name' => 'Settings & Privacy',
            'description' => 'Account settings and privacy preferences',
            'can_delete' => true,
            'fields' => [
                
                // CHECKBOX ACCEPTANCE Field (Terms acceptance)
                'terms_accepted' => [
                    'type' => 'checkbox_acceptance',
                    'name' => 'Terms & Conditions',
                    'description' => 'I accept the terms and conditions',
                    'is_required' => true,
                    'can_delete' => false,
                    'field_order' => 1,
                    'values' => [
                        'always_true' => true // For data generation, always accepted
                    ]
                ],
                
                // CHECKBOX for notifications
                'notification_preferences' => [
                    'type' => 'checkbox',
                    'name' => 'Notification Preferences',
                    'description' => 'Select your notification preferences',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 2,
                    'options' => [
                        'Email notifications',
                        'Push notifications',
                        'SMS notifications',
                        'Weekly digest',
                        'Event reminders',
                        'Friend requests',
                        'Group invitations',
                        'Message alerts'
                    ],
                    'values' => [
                        'min_selections' => 1,
                        'max_selections' => 5
                    ]
                ],
                
                // RADIO for profile visibility
                'profile_visibility' => [
                    'type' => 'radio',
                    'name' => 'Profile Visibility',
                    'description' => 'Who can see your profile',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 3,
                    'options' => [
                        'Public',
                        'Members only',
                        'Friends only',
                        'Private'
                    ],
                    'default_option' => 'Members only'
                ],
                
                // SELECTBOX for timezone
                'timezone' => [
                    'type' => 'selectbox',
                    'name' => 'Timezone',
                    'description' => 'Your timezone',
                    'is_required' => false,
                    'can_delete' => true,
                    'field_order' => 4,
                    'options' => [
                        'UTC-12:00',
                        'UTC-11:00',
                        'UTC-10:00 (Hawaii)',
                        'UTC-09:00 (Alaska)',
                        'UTC-08:00 (Pacific)',
                        'UTC-07:00 (Mountain)',
                        'UTC-06:00 (Central)',
                        'UTC-05:00 (Eastern)',
                        'UTC-04:00 (Atlantic)',
                        'UTC-03:00',
                        'UTC-02:00',
                        'UTC-01:00',
                        'UTC+00:00 (London)',
                        'UTC+01:00 (Paris)',
                        'UTC+02:00 (Cairo)',
                        'UTC+03:00 (Moscow)',
                        'UTC+04:00 (Dubai)',
                        'UTC+05:00 (Karachi)',
                        'UTC+05:30 (Mumbai)',
                        'UTC+06:00 (Dhaka)',
                        'UTC+07:00 (Bangkok)',
                        'UTC+08:00 (Singapore)',
                        'UTC+09:00 (Tokyo)',
                        'UTC+10:00 (Sydney)',
                        'UTC+11:00',
                        'UTC+12:00 (Auckland)'
                    ]
                ]
            ]
        ]
    ],
    
    // Member Types Configuration
    'member_types' => [
        'student' => [
            'labels' => [
                'name' => 'Students',
                'singular_name' => 'Student'
            ],
            'has_directory' => true,
            'directory_slug' => 'students',
            'show_in_list' => true
        ],
        'professional' => [
            'labels' => [
                'name' => 'Professionals',
                'singular_name' => 'Professional'
            ],
            'has_directory' => true,
            'directory_slug' => 'professionals',
            'show_in_list' => true
        ],
        'instructor' => [
            'labels' => [
                'name' => 'Instructors',
                'singular_name' => 'Instructor'
            ],
            'has_directory' => true,
            'directory_slug' => 'instructors',
            'show_in_list' => true
        ],
        'mentor' => [
            'labels' => [
                'name' => 'Mentors',
                'singular_name' => 'Mentor'
            ],
            'has_directory' => true,
            'directory_slug' => 'mentors',
            'show_in_list' => true
        ],
        'alumni' => [
            'labels' => [
                'name' => 'Alumni',
                'singular_name' => 'Alumnus'
            ],
            'has_directory' => true,
            'directory_slug' => 'alumni',
            'show_in_list' => true
        ]
    ],
    
    // Data Generation Rules
    'generation_rules' => [
        // Rules for correlating field values
        'correlations' => [
            // If years_experience < 3, employment_status more likely to be 'Entry Level'
            'experience_level' => [
                'based_on' => 'years_experience',
                'rules' => [
                    '0-2' => ['Entry Level' => 0.7, 'Mid Level' => 0.3],
                    '3-5' => ['Entry Level' => 0.2, 'Mid Level' => 0.6, 'Senior Level' => 0.2],
                    '6-10' => ['Mid Level' => 0.3, 'Senior Level' => 0.5, 'Executive' => 0.2],
                    '11+' => ['Senior Level' => 0.3, 'Executive' => 0.5, 'Consultant' => 0.2]
                ]
            ],
            
            // Member type based on employment status
            'member_type' => [
                'based_on' => 'employment_status',
                'rules' => [
                    'Student' => 'student',
                    'Full-time' => 'professional',
                    'Part-time' => 'professional',
                    'Freelance' => 'professional',
                    'Self-employed' => 'mentor',
                    'Retired' => 'alumni'
                ]
            ]
        ],
        
        // Profile completeness weights (some users have incomplete profiles)
        'completeness' => [
            'complete' => 0.60,      // 60% have all fields filled
            'mostly_complete' => 0.25, // 25% missing 1-3 fields
            'partial' => 0.10,        // 10% missing 4-6 fields
            'minimal' => 0.05         // 5% only have required fields
        ]
    ]
];