<?php
/**
 * BuddyPress Playground Sample Data Loader
 *
 * @package BuddyPress_Playground
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sample Data Loader Class
 *
 * @since 1.0.0
 */
class BP_Playground_Sample_Data {

    /**
     * Cached data arrays
     *
     * @since 1.0.0
     * @var array
     */
    private static $cached_data = [];

    /**
     * Get first names
     *
     * @since 1.0.0
     * @param string $type Type of names (male, female, neutral, all)
     * @return array Array of first names
     */
    public static function get_first_names($type = 'all') {
        if (!isset(self::$cached_data['first_names'])) {
            self::load_data('first-names.json', 'first_names');
        }

        $data = self::$cached_data['first_names'];
        
        if ($type === 'all') {
            return array_merge(
                $data['male'] ?? [],
                $data['female'] ?? [],
                $data['neutral'] ?? []
            );
        }

        return $data[$type] ?? [];
    }

    /**
     * Get last names
     *
     * @since 1.0.0
     * @param string $type Type of names (common, professional, all)
     * @return array Array of last names
     */
    public static function get_last_names($type = 'all') {
        if (!isset(self::$cached_data['last_names'])) {
            self::load_data('last-names.json', 'last_names');
        }

        $data = self::$cached_data['last_names'];
        
        if ($type === 'all') {
            return array_merge(
                $data['common'] ?? [],
                $data['professional'] ?? []
            );
        }

        return $data[$type] ?? [];
    }

    /**
     * Get group names data
     *
     * @since 1.0.0
     * @return array Group names data
     */
    public static function get_group_names() {
        if (!isset(self::$cached_data['group_names'])) {
            self::load_data('group-names.json', 'group_names');
        }

        return self::$cached_data['group_names'];
    }

    /**
     * Generate a random group name
     *
     * @since 1.0.0
     * @param string $category Optional category to limit to
     * @return string Generated group name
     */
    public static function generate_group_name($category = null) {
        $data = self::get_group_names();
        
        // Select category
        if ($category && isset($data['categories'][$category])) {
            $base_names = $data['categories'][$category];
        } else {
            // Random category
            $categories = array_keys($data['categories']);
            $category = $categories[array_rand($categories)];
            $base_names = $data['categories'][$category];
        }

        // Get random base name
        $base_name = $base_names[array_rand($base_names)];

        // Sometimes add a prefix (30% chance)
        if (mt_rand(1, 100) <= 30 && !empty($data['prefixes'])) {
            $prefix = $data['prefixes'][array_rand($data['prefixes'])];
            $base_name = $prefix . ' ' . $base_name;
        }

        // Sometimes replace the suffix (20% chance)
        if (mt_rand(1, 100) <= 20 && !empty($data['suffixes'])) {
            // Remove existing suffix words
            $suffix_pattern = '/\s+(Club|Group|Society|Association|Network|Community|Collective|Circle|Team|League)$/i';
            $base_name = preg_replace($suffix_pattern, '', $base_name);
            
            // Add new suffix
            $suffix = $data['suffixes'][array_rand($data['suffixes'])];
            $base_name = $base_name . ' ' . $suffix;
        }

        return $base_name;
    }

    /**
     * Get bio templates data
     *
     * @since 1.0.0
     * @return array Bio templates data
     */
    public static function get_bio_templates() {
        if (!isset(self::$cached_data['bio_templates'])) {
            self::load_data('bio-templates.json', 'bio_templates');
        }

        return self::$cached_data['bio_templates'];
    }

    /**
     * Generate a bio based on persona
     *
     * @since 1.0.0
     * @param string $persona Persona type
     * @return string Generated bio
     */
    public static function generate_bio($persona = 'community_member') {
        $data = self::get_bio_templates();
        
        // Get templates for persona
        $templates = $data['templates'][$persona] ?? $data['templates']['community_member'];
        $template = $templates[array_rand($templates)];
        
        // Get variables
        $variables = $data['variables'];
        
        // Replace variables in template
        preg_match_all('/\{(\w+)\}/', $template, $matches);
        
        foreach ($matches[1] as $var_name) {
            if (isset($variables[$var_name])) {
                $value = $variables[$var_name][array_rand($variables[$var_name])];
                $template = str_replace('{' . $var_name . '}', $value, $template);
            }
        }
        
        return $template;
    }

    /**
     * Load data from JSON file
     *
     * @since 1.0.0
     * @param string $filename Filename to load
     * @param string $cache_key Cache key to store data
     * @return array|bool Loaded data or false on failure
     */
    private static function load_data($filename, $cache_key) {
        $file_path = BP_PLAYGROUND_PLUGIN_DIR . 'data/' . $filename;
        
        if (!file_exists($file_path)) {
            return false;
        }

        $json_data = file_get_contents($file_path);
        if ($json_data === false) {
            return false;
        }

        $data = json_decode($json_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        self::$cached_data[$cache_key] = $data;
        return $data;
    }

    /**
     * Clear cached data
     *
     * @since 1.0.0
     * @return void
     */
    public static function clear_cache() {
        self::$cached_data = [];
    }

    /**
     * Get random item from array
     *
     * @since 1.0.0
     * @param array $array Array to get random item from
     * @return mixed Random item or null if array is empty
     */
    public static function get_random($array) {
        if (empty($array)) {
            return null;
        }

        return $array[array_rand($array)];
    }

    /**
     * Get multiple random items from array
     *
     * @since 1.0.0
     * @param array $array Array to get random items from
     * @param int $count Number of items to get
     * @return array Random items
     */
    public static function get_random_multiple($array, $count) {
        if (empty($array) || $count <= 0) {
            return [];
        }

        $count = min($count, count($array));
        $keys = array_rand($array, $count);
        
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $result = [];
        foreach ($keys as $key) {
            $result[] = $array[$key];
        }

        return $result;
    }

    /**
     * Get activity content data
     *
     * @since 1.0.0
     * @return array Activity content data
     */
    public static function get_activity_content() {
        if (!isset(self::$cached_data['activity_content'])) {
            self::load_data('activity-content.json', 'activity_content');
        }

        return self::$cached_data['activity_content'];
    }

    /**
     * Generate activity content
     *
     * @since 1.0.0
     * @param string $type Type of activity content
     * @return string Generated activity content
     */
    public static function generate_activity_content($type = 'status_update') {
        $data = self::get_activity_content();
        
        // Map activity types to content types
        $type_mapping = [
            'activity_update' => 'status_updates',
            'status_update' => 'status_updates',
            'question' => 'questions',
            'achievement' => 'achievements',
            'group_activity_update' => 'group_updates',
        ];
        
        $content_type = $type_mapping[$type] ?? 'status_updates';
        $templates = $data[$content_type] ?? $data['status_updates'];
        
        // Get random template
        $template = $templates[array_rand($templates)];
        
        // Replace variables if present
        if (strpos($template, '{') !== false && !empty($data['variables'])) {
            preg_match_all('/\{(\w+)\}/', $template, $matches);
            
            foreach ($matches[1] as $var_name) {
                if (isset($data['variables'][$var_name])) {
                    $value = $data['variables'][$var_name][array_rand($data['variables'][$var_name])];
                    $template = str_replace('{' . $var_name . '}', $value, $template);
                }
            }
        }
        
        return $template;
    }

    /**
     * Generate comment content
     *
     * @since 1.0.0
     * @return string Generated comment content
     */
    public static function generate_comment_content() {
        // Simple comments for now - can be expanded with comment templates
        $comments = [
            'Great post! Thanks for sharing.',
            'I completely agree with this perspective.',
            'This is really helpful, thank you!',
            'Interesting point of view.',
            'Thanks for the inspiration!',
            'Love this! Keep up the great work.',
            'Very insightful, learned something new.',
            'Couldn\'t agree more!',
            'This resonates with me.',
            'Well said! 👍',
            'Thanks for posting this.',
            'Exactly what I needed to hear today.',
            'This is why I love this community.',
            'Great insights, appreciate you sharing.',
            'Totally relate to this!',
            'Thanks for the valuable information.',
            'This made my day!',
            'So true! Thanks for the reminder.',
            'Bookmarking this for later.',
            'Really appreciate your perspective on this.',
        ];
        
        return $comments[array_rand($comments)];
    }

    /**
     * Get message content data
     *
     * @since 1.0.0
     * @return array Message content data
     */
    public static function get_message_content() {
        if (!isset(self::$cached_data['message_content'])) {
            self::load_data('message-content.json', 'message_content');
        }

        return self::$cached_data['message_content'];
    }

    /**
     * Generate message thread
     *
     * @since 1.0.0
     * @param string $type Type of conversation
     * @param int $min_messages Minimum messages in thread
     * @param int $max_messages Maximum messages in thread
     * @return array Array of messages
     */
    public static function generate_message_thread($type = 'random', $min_messages = 2, $max_messages = 6) {
        $data = self::get_message_content();
        
        // If specific template type requested
        if ($type !== 'random' && !empty($data['thread_templates'])) {
            foreach ($data['thread_templates'] as $template) {
                if ($template['type'] === $type) {
                    return self::process_message_templates($template['messages']);
                }
            }
        }
        
        // Generate random conversation
        $num_messages = mt_rand($min_messages, $max_messages);
        $messages = [];
        
        // Start with a conversation starter
        $starter = $data['conversation_starters'][array_rand($data['conversation_starters'])];
        $messages[] = self::replace_message_variables($starter, $data['variables']);
        
        // Add response
        if ($num_messages > 1) {
            $response = $data['responses'][array_rand($data['responses'])];
            $messages[] = self::replace_message_variables($response, $data['variables']);
        }
        
        // Add follow-ups
        $follow_ups_needed = $num_messages - 2 - 1; // -2 for starter/response, -1 for closing
        for ($i = 0; $i < $follow_ups_needed && $i < count($data['follow_ups']); $i++) {
            $follow_up = $data['follow_ups'][array_rand($data['follow_ups'])];
            $messages[] = self::replace_message_variables($follow_up, $data['variables']);
        }
        
        // Add closing if we have enough messages
        if ($num_messages > 2) {
            $closing = $data['closings'][array_rand($data['closings'])];
            $messages[] = self::replace_message_variables($closing, $data['variables']);
        }
        
        return $messages;
    }

    /**
     * Process message templates
     *
     * @since 1.0.0
     * @param array $templates Message templates
     * @return array Processed messages
     */
    private static function process_message_templates($templates) {
        $data = self::get_message_content();
        $messages = [];
        
        foreach ($templates as $template) {
            $messages[] = self::replace_message_variables($template, $data['variables']);
        }
        
        return $messages;
    }

    /**
     * Replace variables in message
     *
     * @since 1.0.0
     * @param string $message Message with variables
     * @param array $variables Available variables
     * @return string Message with replaced variables
     */
    private static function replace_message_variables($message, $variables) {
        preg_match_all('/\{(\w+)\}/', $message, $matches);
        
        foreach ($matches[1] as $var_name) {
            if (isset($variables[$var_name])) {
                $value = $variables[$var_name][array_rand($variables[$var_name])];
                $message = str_replace('{' . $var_name . '}', $value, $message);
            }
        }
        
        return $message;
    }

    /**
     * Generate single message
     *
     * @since 1.0.0
     * @param string $type Type of message (starter, response, follow_up, closing)
     * @return string Generated message
     */
    public static function generate_single_message($type = 'follow_up') {
        $data = self::get_message_content();
        
        $type_mapping = [
            'starter' => 'conversation_starters',
            'response' => 'responses',
            'follow_up' => 'follow_ups',
            'closing' => 'closings',
        ];
        
        $message_type = $type_mapping[$type] ?? 'follow_ups';
        
        if (!isset($data[$message_type])) {
            return 'Thanks for your message!';
        }
        
        $message = $data[$message_type][array_rand($data[$message_type])];
        return self::replace_message_variables($message, $data['variables']);
    }

    /**
     * Get profile data
     *
     * @since 1.0.0
     * @return array Profile data
     */
    public static function get_profile_data() {
        if (!isset(self::$cached_data['profile_data'])) {
            self::load_data('profile-data.json', 'profile_data');
        }

        return self::$cached_data['profile_data'];
    }

    /**
     * Generate profile field value
     *
     * @since 1.0.0
     * @param string $field_type Type of field
     * @param array $options Field options
     * @return mixed Generated field value
     */
    public static function generate_profile_field_value($field_type, $options = []) {
        $data = self::get_profile_data();
        
        // Map field types to data keys
        $type_mapping = [
            'about' => 'about_me',
            'bio' => 'about_me',
            'interests' => 'interests',
            'skills' => 'skills',
            'location' => 'location',
            'website' => 'website',
            'languages' => 'languages',
            'education' => 'education',
            'experience' => 'experience_level',
            'availability' => 'availability',
        ];
        
        $data_key = $type_mapping[$field_type] ?? null;
        
        // Handle special field types
        if ($field_type === 'social_links' && isset($options['platform'])) {
            if (isset($data['field_values']['social_links'][$options['platform']])) {
                $links = $data['field_values']['social_links'][$options['platform']];
                return $links[array_rand($links)];
            }
        }
        
        // Handle array selection fields
        if (isset($options['type']) && $options['type'] === 'multiselect' && $data_key) {
            if (isset($data['field_values'][$data_key])) {
                $available = $data['field_values'][$data_key];
                $count = min(3, count($available)); // Select up to 3 items
                return self::get_random_multiple($available, mt_rand(1, $count));
            }
        }
        
        // Handle single value fields
        if ($data_key && isset($data['field_values'][$data_key])) {
            $values = $data['field_values'][$data_key];
            $value = $values[array_rand($values)];
            
            // Replace variables if present
            if (is_string($value) && strpos($value, '{') !== false && !empty($data['variables'])) {
                preg_match_all('/\{(\w+)\}/', $value, $matches);
                
                foreach ($matches[1] as $var_name) {
                    if (isset($data['variables'][$var_name])) {
                        $replacement = $data['variables'][$var_name][array_rand($data['variables'][$var_name])];
                        $value = str_replace('{' . $var_name . '}', $replacement, $value);
                    }
                }
            }
            
            return $value;
        }
        
        // Default values for unknown field types
        return self::generate_default_field_value($field_type, $options);
    }

    /**
     * Generate default field value
     *
     * @since 1.0.0
     * @param string $field_type Field type
     * @param array $options Field options
     * @return mixed Default value
     */
    private static function generate_default_field_value($field_type, $options) {
        switch ($field_type) {
            case 'textbox':
            case 'textarea':
                return 'Sample text for ' . ($options['name'] ?? 'field');
                
            case 'number':
                return mt_rand(1, 100);
                
            case 'url':
                return 'https://example.com/' . uniqid();
                
            case 'datebox':
                return date('Y-m-d', strtotime('-' . mt_rand(365, 7300) . ' days'));
                
            case 'selectbox':
            case 'radio':
                if (!empty($options['options'])) {
                    return self::get_random($options['options']);
                }
                return 'Option ' . mt_rand(1, 5);
                
            case 'multiselectbox':
            case 'checkbox':
                if (!empty($options['options'])) {
                    return self::get_random_multiple($options['options'], mt_rand(1, 3));
                }
                return ['Option 1', 'Option 2'];
                
            default:
                return 'Sample value';
        }
    }
}