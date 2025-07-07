# BuddyPress Playground CLI - Installation & Quick Start Guide

**Author:** vapvarun  
**Company:** [Wbcom Designs](https://wbcomdesigns.com)  
**Repository:** [github.com/wbcomdesigns/buddypress-playground](https://github.com/wbcomdesigns/buddypress-playground)

## 🚀 Quick Installation

### Prerequisites
- WordPress 5.0+
- PHP 7.4+
- BuddyPress (latest version recommended)
- WP-CLI installed and working
- MySQL 5.6+ or MariaDB equivalent

### Step 1: Install the Plugin
```bash
# Option A: Download and install manually
cd wp-content/plugins/
git clone [your-repo-url] buddypress-playground
cd buddypress-playground

# Option B: Upload plugin files to wp-content/plugins/buddypress-playground/
```

### Step 2: Activate the Plugin
```bash
# Via WP-CLI (recommended)
wp plugin activate buddypress-playground

# Or via WordPress admin dashboard
```

### Step 3: Verify Installation
```bash
# Check system requirements and plugin status
wp bp playground info

# List available scenarios
wp bp playground list-scenarios
```

## 🎯 Quick Start - Generate Test Data

### Option 1: Use Predefined Scenarios (Recommended)
```bash
# Small community (perfect for quick testing)
wp bp playground scenario small-community

# Medium community (balanced dataset)
wp bp playground scenario medium-community

# Addon development optimized
wp bp playground scenario addon-testing
```

### Option 2: Custom Generation
```bash
# Generate custom scale data
wp bp playground generate-all --users=1000 --groups=50 --activities=10000

# Generate specific components
wp bp playground users --count=500 --with-xprofile=true
wp bp playground groups --count=25 --types=mixed
wp bp playground activities --count=5000 --with-mentions=true
```

## 📋 Available Scenarios

| Scenario | Users | Groups | Activities | Best For |
|----------|-------|--------|------------|----------|
| `small-community` | 500 | 25 | 10K | Quick testing, development |
| `medium-community` | 2K | 100 | 50K | Integration testing |
| `large-community` | 10K | 500 | 200K | Performance testing |
| `addon-testing` | 500 | 50 | 15K | Comprehensive addon development |

## 🛠️ Component-Specific Generation

### Users & Profiles
```bash
# Generate users with extended profiles
wp bp playground users --count=1000 --with-xprofile=true --member-types=true

# Create comprehensive XProfile system
wp bp playground xprofile --field-groups=8 --fields-per-group=10 --completion-rate=0.85
```

### Groups & Communities
```bash
# Generate groups with realistic membership
wp bp playground groups --count=100 --types=mixed --with-hierarchy=true

# Create group types and hierarchies
wp bp playground groups --count=50 --types=all --membership-patterns=true
```

### Activities & Engagement
```bash
# Generate activity streams with engagement
wp bp playground activities --count=25000 --with-mentions=true --favorite-rate=0.15

# Create realistic activity patterns
wp bp playground activities --count=10000 --with-comments=true --comment-rate=0.25
```

### Messages & Communication
```bash
# Generate private message conversations
wp bp playground messages --count=5000 --thread-variations=true

# Create realistic conversation patterns
wp bp playground messages --count=3000 --conversation-depth=mixed
```

### Social Networks
```bash
# Generate friend connections
wp bp playground friends --network-density=0.1 --clustering=true

# Create realistic social patterns
wp bp playground friends --pending-requests=true --mutual-connections=true
```

## 🔧 Management Commands

### View Statistics
```bash
# Basic statistics
wp bp playground stats

# Detailed component breakdown
wp bp playground stats --detailed

# Export statistics
wp bp playground stats --format=json > stats.json
```

### Data Verification
```bash
# Check data integrity
wp bp playground verify

# Fix relationship issues
wp bp playground verify --relationships --fix

# Full integrity check with repairs
wp bp playground verify --fix --relationships
```

### Cleanup Operations
```bash
# Preview what would be cleaned
wp bp playground cleanup --dry-run

# Clean specific components
wp bp playground cleanup activities --older-than=30

# Full cleanup with confirmation
wp bp playground cleanup --force

# Clean everything older than 60 days
wp bp playground cleanup --older-than=60
```

## ⚙️ Configuration & Settings

### Plugin Settings
The plugin automatically configures optimal settings, but you can customize:

```bash
# View current settings
wp option get bp_playground_settings

# Update batch size for large operations
wp option update bp_playground_settings '{"batch_size":200}'
```

### Performance Tuning
For large datasets, optimize your environment:

```bash
# Check current limits
wp bp playground info --format=table

# Recommended settings for large generation:
# PHP memory_limit: 1024M or higher
# max_execution_time: 0 (unlimited for CLI)
# WP_CLI timeout: disabled
```

## 🔍 Monitoring & Debugging

### View Logs
```bash
# Check recent activity
wp option get bp_playground_logs

# Export detailed logs
wp bp playground export-logs --since=yesterday --format=csv
```

### Debug Mode
```bash
# Enable debug logging
wp option update bp_playground_settings '{"log_level":"debug","enable_logging":true}'

# Monitor memory usage during generation
wp bp playground generate-all --scale=large 2>&1 | grep "Memory:"
```

## 📊 Data Export & Backup

### Export Configurations
```bash
# Export scenario for reuse
wp bp playground export addon-testing --file=my-setup.json

# Export with customizations
wp bp playground export medium-community --customize='{"users":3000}'
```

### Data Analysis
```bash
# Export network data for analysis
wp bp playground friends export-network --format=csv

# Generate cleanup reports
wp bp playground cleanup --dry-run --format=json > cleanup-preview.json
```

## 🚨 Troubleshooting

### Common Issues

#### Memory Limit Errors
```bash
# Check current memory usage
wp bp playground info | grep -i memory

# Increase PHP memory limit
wp eval "ini_set('memory_limit', '2048M');"

# Use smaller batch sizes
wp bp playground generate-all --scale=medium --batch-size=25
```

#### Time Limit Errors
```bash
# For CLI operations, set unlimited time
wp bp playground generate-all --scale=large
# CLI automatically sets time_limit=0

# For web operations (if any), increase limit
wp eval "set_time_limit(0);"
```

#### Database Connection Issues
```bash
# Check database connectivity
wp db check

# Optimize database before large operations
wp db optimize

# Check for sufficient disk space
df -h
```

### Getting Help
```bash
# Get command help
wp help bp playground generate-all

# Check system compatibility
wp bp playground info --detailed

# Verify all dependencies
wp plugin status buddypress
```

## 🔒 Security & Best Practices

### Safe Usage
- Always use `--dry-run` first for large operations
- Backup your database before major data generation
- Monitor memory and disk usage during operations
- Use cleanup commands to manage test data

### Production Environment
```bash
# Never run in production without backups
wp db export backup-before-playground.sql

# Use environment checks
wp eval "echo wp_get_environment_type();"

# Restrict to development environments only
```

## 🔄 Automation & CI/CD

### Scripted Setup
```bash
#!/bin/bash
# setup-test-environment.sh

# Install and activate
wp plugin activate buddypress-playground

# Generate standard test dataset
wp bp playground scenario addon-testing

# Verify data integrity
wp bp playground verify

# Display statistics
wp bp playground stats --detailed
```

### Continuous Integration
```yaml
# .github/workflows/test.yml example
- name: Setup BuddyPress Test Data
  run: |
    wp plugin activate buddypress-playground
    wp bp playground scenario small-community
    wp bp playground verify --fix
```

## 📈 Performance Benchmarks

### Expected Generation Times
| Dataset Size | Users | Estimated Time | Memory Usage |
|--------------|-------|----------------|--------------|
| Small | 500 | 2-3 minutes | 256MB |
| Medium | 2,000 | 8-12 minutes | 512MB |
| Large | 10,000 | 25-35 minutes | 1GB |
| Enterprise | 25,000 | 60-90 minutes | 2GB |

### Optimization Tips
- Use SSD storage for better I/O performance
- Increase MySQL buffer pool size for large datasets
- Consider running during off-peak hours for large generations
- Monitor system resources and adjust batch sizes accordingly

## 🆘 Support & Resources

### Documentation
- Full command reference: `wp help bp playground`
- Component documentation: Check individual module files
- API documentation: PHPDoc comments in source code

### Reporting Issues
1. Check system requirements: `wp bp playground info`
2. Verify data integrity: `wp bp playground verify`
3. Export logs: `wp bp playground export-logs`
4. Include system info and logs when reporting issues

### Contributing
- Follow WordPress coding standards
- Add PHPDoc comments for all functions
- Include unit tests for new features
- Update documentation for new commands

---

🎉 **You're ready to go!** Start with a small scenario and explore the powerful data generation capabilities of BuddyPress Playground CLI.