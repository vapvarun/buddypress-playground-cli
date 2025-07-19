# BuddyPress Playground CLI

A comprehensive WordPress plugin for generating realistic BuddyPress and bbPress test data for addon development and testing.

**Author:** vapvarun  
**Website:** [Wbcom Designs](https://wbcomdesigns.com)  
**GitHub:** [wbcomdesigns/buddypress-playground](https://github.com/wbcomdesigns/buddypress-playground)

## TL;DR - Just Get Me Started!

```bash
# Install plugin, activate it, then run:
wp bp playground scenario small-community

# That's it! You now have a complete BuddyPress community with:
# 500 users, profiles, groups, activities, messages, and friendships
```

## Features

- **Comprehensive Data Generation**: Create users, groups, activities, messages, forums, and more
- **Realistic Data Models**: Generate data that mimics real-world usage patterns
- **XProfile System**: Complete extended profile field generation with all field types
- **Batch Processing**: Efficient handling of large datasets with memory management
- **Multiple Scenarios**: Pre-configured scenarios for different testing needs
- **CLI Integration**: Full WP-CLI support for automation and scripting
- **Data Integrity**: Maintains proper relationships between all components
- **Cleanup Tools**: Comprehensive cleanup options with safety checks
- **Performance Optimized**: Handles large datasets efficiently
- **Detailed Logging**: Comprehensive logging system for debugging and monitoring

## Requirements

- WordPress 5.0+
- PHP 7.4+
- BuddyPress (latest version recommended)
- WP-CLI (for command-line functionality)
- bbPress (optional, for forum data generation)

## Installation

1. Download or clone the plugin files
2. Upload to your WordPress plugins directory
3. Activate the plugin through WordPress admin
4. Ensure BuddyPress is installed and activated
5. Install WP-CLI if not already available

## Quick Start

### Most Common Command - Generate Small Community

```bash
# Generate a complete small community with all components (Recommended)
wp bp playground scenario small-community
```

This creates:
- 500 users with complete XProfile data
- 25 groups with realistic membership
- 10,000 activities with comments and favorites
- Friend connections
- Private messages
- All profile field types

### Other Useful Scenarios

```bash
# Perfect for addon development and testing
wp bp playground scenario addon-testing

# Medium community with balanced data (2K users, 100 groups, 50K activities)
wp bp playground scenario medium-community

# Large community for stress testing (10K users, 500 groups, 200K activities)
wp bp playground scenario large-community
```

### Custom Generation Options

```bash
# Generate with specific parameters
wp bp playground generate_all --scale=small

# Generate with custom counts
wp bp playground generate_all --users=1000 --groups=50 --activities=25000

# Preview what will be generated
wp bp playground generate_all --scale=small --dry-run
```

## Common Usage Patterns

### For BuddyPress Addon Development

```bash
# 1. Start with a small community that has everything
wp bp playground scenario small-community

# 2. Add more specific data as needed
wp bp playground activities --count=1000 --with-mentions --with-comments
wp bp playground groups --count=10 --types=private
```

### For Testing XProfile Fields

```bash
# Create just XProfile fields and a few users
wp bp playground users --count=20 --with-xprofile

# The above command will:
# - Create 6 field groups with various field types if they don't exist
# - Create 20 users with populated profiles
# - Skip existing profile data (won't overwrite)
```

### For Performance Testing

```bash
# Start with medium scale
wp bp playground scenario medium-community

# Then add more activities for stress testing
wp bp playground activities --count=50000 --with-comments
```

### Quick Cleanup After Testing

```bash
# See what would be cleaned
wp bp playground cleanup --dry-run

# Clean everything
wp bp playground cleanup --force

# Clean only old data
wp bp playground cleanup --older-than=7
```

## Command Reference

### Main Commands

#### `wp bp playground generate_all`

Generate comprehensive BuddyPress test data.

**Options:**
- `--scale=<scale>`: Scale of generation (small, medium, large, enterprise)
- `--users=<count>`: Number of users to generate
- `--groups=<count>`: Number of groups to generate
- `--activities=<count>`: Number of activities to generate
- `--messages=<count>`: Number of messages to generate
- `--forums=<count>`: Number of forums to generate
- `--dry-run`: Preview what would be generated without creating data
- `--skip-components=<list>`: Comma-separated list of components to skip

**Examples:**
```bash
# Basic medium-scale generation
wp bp playground generate_all

# Large-scale with custom parameters
wp bp playground generate_all --scale=large --users=15000 --groups=750

# Preview generation plan
wp bp playground generate_all --dry-run
```

#### `wp bp playground scenario <scenario-name>`

Generate data using predefined scenarios.

**Available Scenarios:**
- `small-community`: 500 users, 25 groups, 10K activities
- `medium-community`: 2K users, 100 groups, 50K activities  
- `large-community`: 10K users, 500 groups, 200K activities
- `addon-testing`: Optimized for comprehensive addon testing

**Options:**
- `--dry-run`: Preview scenario without generating data
- `--customize=<json>`: JSON string of custom parameters

**Examples:**
```bash
# Generate addon testing scenario
wp bp playground scenario addon-testing

# Customize scenario parameters
wp bp playground scenario medium-community --customize='{"users":3000,"groups":150}'
```

### Component-Specific Commands

#### `wp bp playground users`

Generate users with realistic profiles.

**Options:**
- `--count=<number>`: Number of users to create (default: 1000)
- `--with-xprofile`: Generate extended profile fields and populate them
- `--member-types`: Create member types
- `--activation-rate=<rate>`: Percentage of users to activate (0.0-1.0)
- `--admin-rate=<rate>`: Percentage of users to make admins (0.0-0.1)

```bash
# Generate users with complete profiles (creates XProfile fields if needed)
wp bp playground users --count=100 --with-xprofile

# Generate users with 90% activation rate
wp bp playground users --count=1000 --activation-rate=0.9
```

**Note on XProfile:** When using `--with-xprofile`:
- If XProfile fields don't exist, they will be created automatically
- Existing profile data is preserved (only empty fields are populated)
- All field types are demonstrated (text, date, select, radio, checkbox, etc.)

#### `wp bp playground groups`

Generate groups with realistic membership patterns.

**Options:**
- `--count=<number>`: Number of groups to create
- `--types=<types>`: Group types (public, private, hidden, mixed)
- `--with-hierarchy`: Create parent-child group relationships
- `--membership-patterns`: Use realistic membership distribution

```bash
# Generate 100 mixed-type groups
wp bp playground groups --count=100 --types=mixed

# Generate groups with hierarchical structure
wp bp playground groups --count=50 --with-hierarchy
```

#### `wp bp playground activities`

Generate activity stream data.

**Options:**
- `--count=<number>`: Number of activities to create
- `--with-mentions`: Include @mentions in activities
- `--with-comments`: Generate comments on activities
- `--favorite-rate=<rate>`: Percentage of activities to favorite (0.0-1.0)
- `--comment-rate=<rate>`: Percentage of activities to comment on (0.0-1.0)

```bash
# Generate 25,000 activities with mentions
wp bp playground activities --count=25000 --with-mentions

# Generate activities with comments
wp bp playground activities --count=10000 --with-comments

# Generate activities with high engagement
wp bp playground activities --count=10000 --with-comments --favorite-rate=0.2 --comment-rate=0.3
```

### Information Commands

#### `wp bp playground info`

Display system information and plugin status.

**Options:**
- `--format=<format>`: Output format (table, json, yaml)

```bash
# Display system info
wp bp playground info

# Export system info as JSON
wp bp playground info --format=json
```

#### `wp bp playground stats`

Display comprehensive statistics about generated data.

**Options:**
- `--format=<format>`: Output format (table, json, yaml)
- `--detailed`: Show detailed statistics for each component

```bash
# Basic statistics
wp bp playground stats

# Detailed component statistics
wp bp playground stats --detailed

# Export stats as JSON
wp bp playground stats --format=json
```

#### `wp bp playground list_scenarios`

List all available scenarios with descriptions.

```bash
# View available scenarios
wp bp playground list_scenarios
```

### Maintenance Commands

#### `wp bp playground cleanup`

Clean up generated test data.

**Options:**
- `<component>`: Specific component to clean (users, groups, activities, etc.)
- `--older-than=<days>`: Only clean data older than specified days
- `--dry-run`: Show what would be cleaned without actually doing it
- `--force`: Skip confirmation prompts

**Examples:**
```bash
# Clean all generated data (with confirmation)
wp bp playground cleanup

# Clean only activities older than 30 days
wp bp playground cleanup activities --older-than=30

# Preview cleanup operation
wp bp playground cleanup --dry-run

# Force cleanup without prompts
wp bp playground cleanup --force
```

#### `wp bp playground verify`

Verify data integrity and relationships.

**Options:**
- `--fix`: Attempt to fix found issues automatically
- `--relationships`: Check relationship integrity between components

```bash
# Basic integrity check
wp bp playground verify

# Check and fix relationship issues
wp bp playground verify --relationships --fix
```

### Import/Export Commands

#### `wp bp playground export`

Export scenario configuration to JSON file.

**Options:**
- `<scenario>`: Scenario name to export
- `--file=<file>`: Output file path
- `--customize=<params>`: Custom parameters to include

```bash
# Export addon testing scenario
wp bp playground export addon-testing

# Export with custom file name
wp bp playground export medium-community --file=my-scenario.json
```

## Plugin Architecture

### Core Components

1. **Core Module** (`BP_Playground_Core`): Central coordination and settings management
2. **Batch Processor** (`BP_Playground_Batch_Processor`): Efficient large dataset processing
3. **Logger** (`BP_Playground_Logger`): Comprehensive logging system
4. **Data Model** (`BP_Playground_Data_Model`): Realistic data generation patterns

### Generation Modules

1. **Users Module**: Create users with realistic personas and profiles
2. **XProfile Module**: Generate complete extended profile systems
3. **Groups Module**: Create groups with realistic membership patterns
4. **Activities Module**: Generate activity streams with proper relationships
5. **Messages Module**: Create private message threads and conversations
6. **Friends Module**: Generate social network connections
7. **bbPress Module**: Create forums, topics, and replies
8. **Social Network Module**: Model realistic user interaction patterns

### CLI Commands

Organized command structure with comprehensive help and validation:
- Main commands for common operations
- Component-specific commands for targeted generation
- Maintenance commands for cleanup and verification
- Information commands for monitoring and statistics

## Configuration

### Settings

Plugin settings can be configured through the core module:

```php
$core = bp_playground_get_module('core');
$core->update_settings([
    'batch_size' => 100,
    'memory_limit' => '1024M',
    'enable_logging' => true,
    'log_level' => 'info',
]);
```

### Scenarios

Scenarios are predefined configurations optimized for different use cases:

- **Small Community**: Quick testing with minimal data
- **Medium Community**: Balanced dataset for most testing needs
- **Large Community**: Performance testing with substantial data
- **Addon Testing**: Comprehensive coverage for addon development

## Data Generation Patterns

### User Personas

The plugin generates users based on realistic personas:

- **Tech Professionals** (25%): Developers, engineers, technical roles
- **Creative Professionals** (20%): Designers, artists, creative roles
- **Business Professionals** (20%): Managers, consultants, business roles
- **Educators** (15%): Teachers, instructors, academic roles
- **Students** (20%): Various educational levels and interests

### Relationship Modeling

- **Social Networks**: Realistic friend connection patterns
- **Group Membership**: Natural distribution across group types and sizes
- **Activity Patterns**: Authentic engagement and interaction flows
- **Content Distribution**: 80/20 rule for content popularity

### Data Integrity

- **Referential Integrity**: All relationships properly maintained
- **Cascade Operations**: Proper cleanup of dependent data
- **Validation**: Input validation and error handling
- **Performance**: Optimized queries and batch processing
- **Profile Data Protection**: Existing XProfile data is preserved - only empty fields are populated

## Performance Considerations

### Memory Management

- Configurable memory limits
- Automatic garbage collection
- Batch processing for large datasets
- Progress tracking and reporting

### Database Optimization

- Efficient batch inserts
- Proper indexing strategies
- Transaction management
- Connection pooling

### Scalability

- Support for datasets up to 25,000+ users
- Configurable batch sizes
- Memory usage monitoring
- Time limit management

## Logging and Monitoring

### Log Levels

- **Debug**: Detailed execution information
- **Info**: General operation information
- **Warning**: Potential issues or concerns
- **Error**: Operation failures
- **Critical**: System-level problems

### Log Storage

- Database logging with automatic rotation
- File-based logging option
- Export capabilities (CSV, JSON)
- Automatic cleanup of old logs

### Monitoring

- Real-time progress tracking
- Memory usage monitoring
- Performance metrics
- Error rate tracking

## Security Considerations

### Data Protection

- No real user data exposure
- Placeholder avatars and images
- Safe email domain usage
- IP address logging controls

### Access Control

- WP-CLI requirement for operations
- Admin capability checks
- Confirmation prompts for destructive operations
- Dry-run options for testing

## Troubleshooting

### Common Issues

1. **Memory Limit Errors**
   - Increase PHP memory limit
   - Reduce batch size
   - Use smaller dataset scales

2. **Time Limit Errors**
   - Increase PHP max execution time
   - Use WP-CLI instead of web interface
   - Process in smaller batches

3. **Database Connection Errors**
   - Check database connectivity
   - Verify MySQL timeout settings
   - Reduce concurrent operations

### Debug Mode

Enable debug logging for detailed troubleshooting:

```bash
wp bp playground info --format=json
wp bp playground stats --detailed
```

### Log Analysis

Review logs for issues:

```bash
# View recent logs
wp option get bp_playground_settings

# Check plugin settings
wp option get bp_playground_settings
```

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Follow WordPress coding standards
2. Add comprehensive PHPDoc comments
3. Include unit tests for new features
4. Update documentation for changes
5. Test with multiple BuddyPress configurations

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, please:

1. Check the documentation
2. Review the logs for errors
3. Use the verify command to check data integrity
4. Submit issues with detailed error information

## Changelog

### Version 1.0.0
- Initial release
- Complete BuddyPress data generation
- XProfile system support with data preservation
- Batch processing implementation
- CLI command structure
- Logging system
- Cleanup utilities
- Performance optimizations
- Security hardening for SQL queries
- Fixed array handling issues
- Production-ready error handling