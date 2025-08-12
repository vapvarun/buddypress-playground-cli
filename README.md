# BuddyPress Playground CLI

Generate comprehensive BuddyPress test data for development and testing.

**Author:** vapvarun  
**Website:** [Wbcom Designs](https://wbcomdesigns.com)  
**GitHub:** [vapvarun/buddypress-playground-cli](https://github.com/vapvarun/buddypress-playground-cli)

## Quick Start

```bash
# Generate a small test community
wp bp playground scenario generate small_community

# That's it! You now have a complete BuddyPress community with:
# 50 users, profiles, groups, activities, messages, and friendships
```

## Features

- **Automatic Data Generation**: Users, groups, activities, messages, and more
- **Smart Name Generation**: Automatic first name, last name, nickname creation
- **Complete XProfile**: All BuddyPress field types with predefined values
- **Sequential Execution**: Proper dependency resolution and ordered creation
- **Multiple Scenarios**: 7 predefined scenarios for different needs
- **Batch Processing**: Efficient handling of large datasets
- **WP-CLI Integration**: Full command-line support

## Requirements

- WordPress 5.0+
- BuddyPress 12.0+
- PHP 7.4+
- WP-CLI

## Installation

1. Upload the plugin to `/wp-content/plugins/`
2. Activate via WP-CLI: `wp plugin activate buddypress-playground-cli`

## Available Scenarios

| Scenario | Users | Groups | Description |
|----------|-------|--------|-------------|
| minimal | 10 | 3 | Quick testing setup |
| small_community | 50 | 10 | Development environment |
| development | 100 | 15 | Balanced dataset |
| educational | 300 | 30 | LMS/Educational platform |
| professional | 200 | 25 | Professional networking |
| medium_community | 500 | 50 | Staging environment |
| large_community | 5000 | 200 | Performance testing |

## Usage Examples

### Generate Test Data

```bash
# List available scenarios
wp bp playground scenario list

# View scenario details
wp bp playground scenario info small_community

# Generate a scenario
wp bp playground scenario generate development

# Clean and start fresh
wp bp playground scenario generate minimal --clean

# Preview without creating data
wp bp playground scenario generate medium_community --dry-run
```

### Manual Generation

```bash
# Generate users
wp bp playground users generate 100

# Create groups
wp bp playground groups generate 20

# Generate activities
wp bp playground activities generate 500
```

### Utilities

```bash
# Update all user names
wp bp playground names update-all

# Clean all BuddyPress data
wp bp playground scenario generate minimal --clean
```

## Architecture

See [ARCHITECTURE.md](ARCHITECTURE.md) for detailed file structure and execution flow.

## Key Features Explained

### Automatic Name Generation
When users are created, the plugin automatically generates:
- First Name & Last Name
- Display Name
- Nickname
- SEO-friendly nicename

### Predefined XProfile Structure
The plugin creates a comprehensive XProfile structure covering all field types:
- Base Group (Primary profile)
- Education & Work
- Interests & Hobbies
- Contact Information
- Social Media
- Additional Details

### Sequential Execution
Scenarios execute in proper order:
1. Environment preparation
2. XProfile structure creation
3. User generation
4. Profile data population
5. Groups, friendships, messages
6. Activities and forums
7. Cache cleanup

## Troubleshooting

### Memory Issues
The plugin automatically increases memory to 1024M. For large datasets:
```bash
# Start with smaller scenario
wp bp playground scenario generate minimal
```

### Performance
For large datasets, run in background:
```bash
nohup wp bp playground scenario generate large_community &
```

## Support

Report issues at: https://github.com/vapvarun/buddypress-playground-cli

## License

GPL v2 or later