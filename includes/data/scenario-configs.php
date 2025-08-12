<?php
/**
 * Predefined Scenario Configurations
 * 
 * Complete scenario configurations with proper sequencing
 */

return [
    
    /**
     * Small Community Scenario
     * Perfect for development and testing
     */
    'small_community' => [
        'name' => 'Small Community',
        'description' => 'A small, active community with 50 users and complete data',
        'clean_existing' => false,
        'use_predefined_xprofile' => true,
        
        'member_types' => [
            'student' => [
                'labels' => ['name' => 'Students', 'singular_name' => 'Student']
            ],
            'professional' => [
                'labels' => ['name' => 'Professionals', 'singular_name' => 'Professional']
            ],
            'instructor' => [
                'labels' => ['name' => 'Instructors', 'singular_name' => 'Instructor']
            ]
        ],
        
        'users' => [
            'count' => 50,
            'with_avatar' => false,
            'with_cover_image' => false
        ],
        
        'groups' => [
            'count' => 10,
            'min_members' => 5,
            'max_members' => 20,
            'with_forums' => false
        ],
        
        'friendships' => [
            'per_user' => 5,
            'acceptance_rate' => 0.9
        ],
        
        'messages' => [
            'threads' => 30,
            'min_messages' => 2,
            'max_messages' => 8
        ],
        
        'activities' => [
            'count' => 200,
            'with_comments' => true,
            'with_favorites' => true
        ],
        
        'forums' => [
            'count' => 0  // Disabled by default
        ]
    ],
    
    /**
     * Medium Community Scenario
     * Good for staging environments
     */
    'medium_community' => [
        'name' => 'Medium Community',
        'description' => 'A medium-sized community with 500 users and rich content',
        'clean_existing' => false,
        'use_predefined_xprofile' => true,
        
        'member_types' => [
            'student' => [
                'labels' => ['name' => 'Students', 'singular_name' => 'Student']
            ],
            'professional' => [
                'labels' => ['name' => 'Professionals', 'singular_name' => 'Professional']
            ],
            'instructor' => [
                'labels' => ['name' => 'Instructors', 'singular_name' => 'Instructor']
            ],
            'mentor' => [
                'labels' => ['name' => 'Mentors', 'singular_name' => 'Mentor']
            ],
            'alumni' => [
                'labels' => ['name' => 'Alumni', 'singular_name' => 'Alumnus']
            ]
        ],
        
        'users' => [
            'count' => 500,
            'with_avatar' => false,
            'with_cover_image' => false
        ],
        
        'groups' => [
            'count' => 50,
            'min_members' => 10,
            'max_members' => 100,
            'with_forums' => false
        ],
        
        'friendships' => [
            'per_user' => 15,
            'acceptance_rate' => 0.85
        ],
        
        'messages' => [
            'threads' => 200,
            'min_messages' => 2,
            'max_messages' => 15
        ],
        
        'activities' => [
            'count' => 2000,
            'with_comments' => true,
            'with_favorites' => true
        ],
        
        'forums' => [
            'count' => 5,
            'topics_per_forum' => 10,
            'replies_per_topic' => 5
        ]
    ],
    
    /**
     * Large Community Scenario
     * For performance testing
     */
    'large_community' => [
        'name' => 'Large Community',
        'description' => 'A large community with 5000 users for performance testing',
        'clean_existing' => false,
        'use_predefined_xprofile' => true,
        
        'member_types' => [
            'student' => [
                'labels' => ['name' => 'Students', 'singular_name' => 'Student']
            ],
            'professional' => [
                'labels' => ['name' => 'Professionals', 'singular_name' => 'Professional']
            ],
            'instructor' => [
                'labels' => ['name' => 'Instructors', 'singular_name' => 'Instructor']
            ],
            'mentor' => [
                'labels' => ['name' => 'Mentors', 'singular_name' => 'Mentor']
            ],
            'alumni' => [
                'labels' => ['name' => 'Alumni', 'singular_name' => 'Alumnus']
            ]
        ],
        
        'users' => [
            'count' => 5000,
            'with_avatar' => false,
            'with_cover_image' => false
        ],
        
        'groups' => [
            'count' => 200,
            'min_members' => 20,
            'max_members' => 500,
            'with_forums' => true
        ],
        
        'friendships' => [
            'per_user' => 50,
            'acceptance_rate' => 0.8
        ],
        
        'messages' => [
            'threads' => 1000,
            'min_messages' => 1,
            'max_messages' => 20
        ],
        
        'activities' => [
            'count' => 10000,
            'with_comments' => true,
            'with_favorites' => true
        ],
        
        'forums' => [
            'count' => 10,
            'topics_per_forum' => 20,
            'replies_per_topic' => 10
        ]
    ],
    
    /**
     * Educational Platform Scenario
     * For LMS/Educational sites
     */
    'educational' => [
        'name' => 'Educational Platform',
        'description' => 'An educational community with courses and learning focus',
        'clean_existing' => false,
        'use_predefined_xprofile' => true,
        
        'member_types' => [
            'student' => [
                'labels' => ['name' => 'Students', 'singular_name' => 'Student']
            ],
            'instructor' => [
                'labels' => ['name' => 'Instructors', 'singular_name' => 'Instructor']
            ],
            'teaching_assistant' => [
                'labels' => ['name' => 'Teaching Assistants', 'singular_name' => 'Teaching Assistant']
            ],
            'alumni' => [
                'labels' => ['name' => 'Alumni', 'singular_name' => 'Alumnus']
            ]
        ],
        
        'users' => [
            'count' => 300,
            'with_avatar' => true,
            'with_cover_image' => false
        ],
        
        // Groups represent courses/classes
        'groups' => [
            'count' => 30,
            'min_members' => 15,
            'max_members' => 50,
            'with_forums' => true
        ],
        
        'friendships' => [
            'per_user' => 10,
            'acceptance_rate' => 0.95
        ],
        
        'messages' => [
            'threads' => 150,
            'min_messages' => 3,
            'max_messages' => 10
        ],
        
        'activities' => [
            'count' => 1000,
            'with_comments' => true,
            'with_favorites' => true
        ],
        
        'forums' => [
            'count' => 10,
            'topics_per_forum' => 15,
            'replies_per_topic' => 8
        ]
    ],
    
    /**
     * Professional Network Scenario
     * For business/professional networking sites
     */
    'professional' => [
        'name' => 'Professional Network',
        'description' => 'A professional networking community',
        'clean_existing' => false,
        'use_predefined_xprofile' => true,
        
        'member_types' => [
            'professional' => [
                'labels' => ['name' => 'Professionals', 'singular_name' => 'Professional']
            ],
            'entrepreneur' => [
                'labels' => ['name' => 'Entrepreneurs', 'singular_name' => 'Entrepreneur']
            ],
            'consultant' => [
                'labels' => ['name' => 'Consultants', 'singular_name' => 'Consultant']
            ],
            'recruiter' => [
                'labels' => ['name' => 'Recruiters', 'singular_name' => 'Recruiter']
            ]
        ],
        
        'users' => [
            'count' => 200,
            'with_avatar' => true,
            'with_cover_image' => true
        ],
        
        'groups' => [
            'count' => 25,
            'min_members' => 10,
            'max_members' => 100,
            'with_forums' => true
        ],
        
        'friendships' => [
            'per_user' => 25,
            'acceptance_rate' => 0.7
        ],
        
        'messages' => [
            'threads' => 100,
            'min_messages' => 1,
            'max_messages' => 5
        ],
        
        'activities' => [
            'count' => 500,
            'with_comments' => true,
            'with_favorites' => false
        ],
        
        'forums' => [
            'count' => 8,
            'topics_per_forum' => 12,
            'replies_per_topic' => 6
        ]
    ],
    
    /**
     * Minimal Test Scenario
     * Quick setup for basic testing
     */
    'minimal' => [
        'name' => 'Minimal Test',
        'description' => 'Minimal data for quick testing',
        'clean_existing' => true,
        'use_predefined_xprofile' => true,
        
        'users' => [
            'count' => 10,
            'with_avatar' => false,
            'with_cover_image' => false
        ],
        
        'groups' => [
            'count' => 3,
            'min_members' => 3,
            'max_members' => 5,
            'with_forums' => false
        ],
        
        'friendships' => [
            'per_user' => 2,
            'acceptance_rate' => 1.0
        ],
        
        'messages' => [
            'threads' => 5,
            'min_messages' => 1,
            'max_messages' => 3
        ],
        
        'activities' => [
            'count' => 20,
            'with_comments' => false,
            'with_favorites' => false
        ],
        
        'forums' => [
            'count' => 0
        ]
    ],
    
    /**
     * Development Scenario
     * Balanced data for development
     */
    'development' => [
        'name' => 'Development',
        'description' => 'Balanced dataset for development and testing',
        'clean_existing' => false,
        'use_predefined_xprofile' => true,
        
        'member_types' => [
            'regular' => [
                'labels' => ['name' => 'Regular Members', 'singular_name' => 'Regular Member']
            ],
            'premium' => [
                'labels' => ['name' => 'Premium Members', 'singular_name' => 'Premium Member']
            ],
            'moderator' => [
                'labels' => ['name' => 'Moderators', 'singular_name' => 'Moderator']
            ]
        ],
        
        'users' => [
            'count' => 100,
            'with_avatar' => false,
            'with_cover_image' => false
        ],
        
        'groups' => [
            'count' => 15,
            'min_members' => 5,
            'max_members' => 30,
            'with_forums' => false
        ],
        
        'friendships' => [
            'per_user' => 8,
            'acceptance_rate' => 0.9
        ],
        
        'messages' => [
            'threads' => 50,
            'min_messages' => 2,
            'max_messages' => 10
        ],
        
        'activities' => [
            'count' => 300,
            'with_comments' => true,
            'with_favorites' => true
        ],
        
        'forums' => [
            'count' => 3,
            'topics_per_forum' => 5,
            'replies_per_topic' => 3
        ]
    ]
];