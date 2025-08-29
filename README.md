# BuddyPress Playground CLI

Generate comprehensive BuddyPress test data for development and testing.

**Author:** vapvarun  
**Website:** [Wbcom Designs](https://wbcomdesigns.com)  
**GitHub:** [vapvarun/buddypress-playground-cli](https://github.com/vapvarun/buddypress-playground-cli)

## Quick Start

```bash
# Generate a complete test community with one command
wp bp playground scenario generate small_community --clean

# That's it! You now have:
# ✓ 50 users with complete profiles and member types
# ✓ XProfile fields and data
# ✓ Friend connections
# ✓ 10 groups with members
# ✓ Private message threads
# ✓ 200+ activities with comments and favorites
# ✓ BBPress forums, topics, and replies (if BBPress is active)
```

## Features

- **7 Complete Modules**: Users, XProfile, Friends, Groups, Messages, Activities, BBPress
- **Smart Data Generation**: Realistic names, profiles, and content
- **Member Types**: Students, Professionals, Instructors, Alumni
- **Proper Sequencing**: Data created in correct dependency order
- **Predefined Scenarios**: From minimal (10 users) to large (5000 users)
- **Batch Processing**: Efficient handling of large datasets
- **Full WP-CLI Integration**: Complete command-line control

## Requirements

- WordPress 5.0+
- BuddyPress 12.0+
- PHP 7.4+
- WP-CLI
- Optional: BBPress for forum generation

## Installation

```bash
# Clone or download to plugins directory
cd wp-content/plugins/
git clone https://github.com/vapvarun/buddypress-playground-cli.git

# Activate the plugin
wp plugin activate buddypress-playground-cli
```

## Available Scenarios

| Scenario | Users | Groups | Activities | Description |
|----------|-------|--------|------------|-------------|
| `minimal` | 10 | 3 | 50 | Quick testing setup |
| `small_community` | 50 | 10 | 200 | Development environment |
| `development` | 100 | 15 | 500 | Balanced dataset |
| `educational` | 300 | 30 | 1000 | LMS/Educational platform |
| `professional` | 200 | 25 | 800 | Professional networking |
| `medium_community` | 500 | 50 | 2000 | Staging environment |
| `large_community` | 5000 | 200 | 10000 | Performance testing |

## Scenario Usage

### List and Info Commands

```bash
# List all available scenarios
wp bp playground scenario list

# View details about a specific scenario
wp bp playground scenario info small_community

# Preview what will be generated (dry run)
wp bp playground scenario generate medium_community --dry-run
```

### Generate Complete Communities

```bash
# Generate with default settings
wp bp playground scenario generate small_community

# Clean existing data first (recommended)
wp bp playground scenario generate development --clean

# Skip specific phases if needed
wp bp playground scenario generate professional --skip-phases=forums,messages
```

## Individual Module Commands

### Users Module

```bash
# Generate users with specific settings
wp bp playground users generate 100 \
  --role-distribution=natural \
  --name-style=realistic \
  --batch-size=10

# Options:
# --role-distribution: equal, natural, custom
# --name-style: realistic, simple, mixed
# --set-avatars: true/false
```

### XProfile Module

```bash
# Create XProfile structure and populate data
wp eval "
  require_once WP_PLUGIN_DIR . '/buddypress-playground-cli/includes/functions-bp-playground-xprofile.php';
  
  // Register member types
  bp_playground_register_member_types();
  
  // Create field groups and fields
  bp_playground_create_xprofile_structure();
  
  // Populate for all users
  $users = get_users(['fields' => 'ID']);
  bp_playground_populate_xprofile($users, true);
"
```

### Friends Module

```bash
# Generate friendships between users
wp eval "
  $friends = bp_playground_get_module('friends');
  $friends->generate([
    'friendship_patterns' => true,
    'mutual_friendships' => true,
    'friendship_distribution' => 'natural',
    'acceptance_rate' => 85,
    'batch_size' => 50
  ]);
"
```

### Groups Module

```bash
# Create groups with members
wp bp playground groups generate 20 \
  --types=mixed \
  --membership-patterns=true \
  --batch-size=5

# Or via PHP
wp eval "
  $groups = bp_playground_get_module('groups');
  $groups->generate([
    'count' => 15,
    'types' => 'mixed',
    'membership_patterns' => true
  ]);
"
```

### Messages Module

```bash
# Generate private message threads
wp eval "
  $messages = bp_playground_get_module('messages');
  $messages->generate([
    'count' => 100,
    'thread_variations' => true,
    'conversation_depth' => 'mixed',
    'timeframe_days' => 30
  ]);
"
```

### Activities Module

```bash
# Generate activity stream with engagement
wp bp playground activities generate 500 \
  --with-comments=true \
  --with-favorites=true \
  --engagement-patterns=true

# Or via PHP
wp eval "
  $activities = bp_playground_get_module('activities');
  $activities->generate([
    'count' => 200,
    'with_comments' => true,
    'with_favorites' => true,
    'engagement_patterns' => true,
    'realistic_timing' => true,
    'timeframe_days' => 30
  ]);
"
```

### BBPress Module

```bash
# Generate forums, topics, and replies
wp eval "
  $bbpress = bp_playground_get_module('bbpress');
  $bbpress->generate([
    'forums' => 5,
    'topics_per_forum' => 10,
    'replies_per_topic' => 5,
    'with_tags' => true
  ]);
"
```

## Data Generation Sequence

The plugin follows this sequence to ensure proper data relationships:

1. **Users** - Create user accounts with proper roles
2. **XProfile Structure** - Set up field groups and fields
3. **XProfile Data** - Populate user profiles and assign member types
4. **Friends** - Create friendship connections
5. **Groups** - Create groups and add members
6. **Messages** - Generate private message threads
7. **Activities** - Create activity stream (uses existing groups/friends)
8. **BBPress** - Generate forums, topics, and replies

## XProfile Fields Created

The plugin creates comprehensive XProfile field groups:

- **Base Group**: Name (auto-populated from user data)
- **Personal Info**: Birthday, Gender, About Me, Location
- **Education & Work**: Company, Job Title, Education Level, Field of Study
- **Interests**: Hobbies, Favorite Books, Movies, Music
- **Contact**: Phone, Website, Address
- **Social Media**: Twitter, LinkedIn, Facebook, Instagram
- **Preferences**: Newsletter subscription, Privacy settings

## Member Types

Four member types are automatically created and distributed:

- **Students** (40%)
- **Professionals** (30%)
- **Alumni** (20%)
- **Teachers/Instructors** (10%)

## Performance Tips

### Memory Management

```bash
# The plugin auto-sets memory to 1024M, but for very large datasets:
WP_CLI_PHP_ARGS="-d memory_limit=2048M" wp bp playground scenario generate large_community
```

### Batch Processing

```bash
# For large communities, run in background
nohup wp bp playground scenario generate large_community --clean &

# Monitor progress
tail -f nohup.out
```

### Progressive Generation

```bash
# Start small and build up
wp bp playground scenario generate minimal
wp bp playground users generate 50
wp bp playground groups generate 10
wp bp playground activities generate 200
```

## Cleanup

```bash
# Remove all BuddyPress data (keeps WordPress users)
wp bp playground scenario generate minimal --clean

# Or clean specific components
wp eval "
  global \$wpdb;
  // Clean activities
  BP_Activity_Activity::delete(['type' => null]);
  // Clean messages
  \$wpdb->query('TRUNCATE TABLE ' . \$wpdb->base_prefix . 'bp_messages_messages');
  // Clean groups
  \$wpdb->query('DELETE FROM ' . \$wpdb->base_prefix . 'bp_groups');
"
```

## Troubleshooting

### Common Issues

1. **Memory errors**: Increase PHP memory limit
2. **Timeout errors**: Use smaller batches or run in background
3. **Missing data**: Ensure BuddyPress components are active
4. **BBPress warnings**: Non-critical, related to textdomain loading

### Debug Mode

```bash
# Enable WordPress debug
wp config set WP_DEBUG true
wp config set WP_DEBUG_LOG true

# Check logs
tail -f wp-content/debug.log
```

## Module Status

All modules have been tested and optimized:

- ✅ **Users Module** - Fully functional
- ✅ **XProfile Module** - Includes member types
- ✅ **Friends Module** - Fixed array boundary issues
- ✅ **Groups Module** - Fixed table name issues
- ✅ **Messages Module** - Fixed timestamp generation
- ✅ **Activities Module** - Fixed deprecated functions
- ✅ **BBPress Module** - Fixed undefined keys

## Contributing

Report issues or contribute at: https://github.com/vapvarun/buddypress-playground-cli

## License

GPL v2 or later

## Credits

Developed by [Wbcom Designs](https://wbcomdesigns.com) for the BuddyPress community.