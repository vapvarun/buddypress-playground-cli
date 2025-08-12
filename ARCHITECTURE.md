# BuddyPress Playground CLI - Architecture & File Organization

## Overview
This document provides a clear overview of the plugin's file structure and execution flow to avoid confusion and ensure maintainability.

## Core Plugin Structure

### Main Entry Point
- `buddypress-playground-cli.php` - Main plugin file with minimal initialization

### Autoloader System  
- `includes/class-bp-playground-autoloader.php` - Memory-optimized class autoloader

## File Categories

### 1. Core Infrastructure (Essential)
These files provide the fundamental plugin functionality:

- `includes/class-bp-playground-core.php` - Core plugin functionality
- `includes/class-bp-playground-utils.php` - Utility functions
- `includes/class-bp-playground-logger.php` - Logging system
- `includes/class-bp-playground-batch-processor.php` - Batch processing for large datasets
- `includes/class-bp-playground-validator.php` - Data validation
- `includes/class-bp-playground-cleanup.php` - Cleanup utilities

### 2. Data Management (Essential)
- `includes/class-bp-playground-data-model.php` - Data model definitions
- `includes/class-bp-playground-sample-data.php` - Sample data generation
- `includes/data/xprofile-structure.php` - Predefined XProfile field structure
- `includes/data/scenario-configs.php` - Predefined scenario configurations

### 3. Component Modules (Modular)
Each module handles a specific BuddyPress component:

- `includes/modules/class-bp-playground-users-module.php` - User generation
- `includes/modules/class-bp-playground-xprofile-module.php` - XProfile field management
- `includes/modules/class-bp-playground-groups-module.php` - Group creation
- `includes/modules/class-bp-playground-activities-module.php` - Activity stream
- `includes/modules/class-bp-playground-messages-module.php` - Private messages
- `includes/modules/class-bp-playground-friends-module.php` - Friendships
- `includes/modules/class-bp-playground-bbpress-module.php` - bbPress forums

### 4. Specialized Handlers (Essential)
- `includes/class-bp-playground-name-handler.php` - Automatic name generation for users
- `includes/class-bp-playground-xprofile-generator.php` - XProfile data population
- `includes/class-bp-playground-sequence-manager.php` - Scenario execution sequencing

### 5. CLI Commands (User Interface)
- `includes/cli/class-bp-playground-cli-main.php` - Main CLI command
- `includes/cli/class-bp-playground-cli-users.php` - User-specific commands
- `includes/cli/class-bp-playground-cli-groups.php` - Group-specific commands
- `includes/cli/class-bp-playground-cli-activities.php` - Activity-specific commands
- `includes/cli/class-bp-playground-cli-names.php` - Name update commands
- `includes/cli/class-bp-playground-cli-scenario-enhanced.php` - **PRIMARY** scenario command (USE THIS)
- ~~`includes/cli/class-bp-playground-cli-scenario.php`~~ - **DEPRECATED** (to be removed)

### 6. Abstracts & Interfaces (Architecture)
- `includes/interfaces/interface-bp-playground-module.php` - Module interface
- `includes/abstracts/abstract-bp-playground-module.php` - Base module class

## Execution Flow

### 1. Plugin Initialization
```
buddypress-playground-cli.php
  ↓
BuddyPress_Playground singleton
  ↓
Autoloader registration
  ↓
Hook registration (plugins_loaded, cli_init)
```

### 2. CLI Command Registration (WP-CLI only)
```
cli_init action
  ↓
Register all CLI commands
  ↓
Commands use autoloader for lazy loading
```

### 3. Scenario Execution Flow
```
User runs: wp bp playground scenario generate <scenario>
  ↓
BP_Playground_CLI_Scenario_Enhanced::generate()
  ↓
Load scenario config from scenario-configs.php
  ↓
Initialize BP_Playground_Sequence_Manager
  ↓
Execute phases in order:
  1. prepare - Clean existing data if requested
  2. xprofile - Create XProfile structure from xprofile-structure.php
  3. users - Generate users with BP_Playground_Name_Handler auto-naming
  4. profile_data - Populate profiles with BP_Playground_XProfile_Generator
  5. groups - Create groups
  6. friendships - Create friend connections
  7. messages - Generate private messages
  8. activities - Create activity stream items
  9. forums - Create forum content (if enabled)
  10. finalize - Clear caches and optimize
```

### 4. Automatic Name Generation
```
User registration/creation
  ↓
BP_Playground_Name_Handler hooks
  ↓
Generate First Name, Last Name, Nickname
  ↓
Update display_name and user_nicename
```

## Key Design Patterns

### 1. Lazy Loading
- Classes are only loaded when needed via autoloader
- Modules are instantiated on-demand

### 2. Sequence Management
- All scenarios follow a defined execution sequence
- Dependencies are resolved automatically
- Critical phases must succeed for execution to continue

### 3. Predefined Data
- XProfile structure is predefined for consistency
- Scenario configurations are centralized
- Sample data is template-based

### 4. Memory Optimization
- Batch processing for large datasets
- Minimal class loading
- Efficient autoloader with simple array maps

## Recommended Usage

### For Testing/Development
```bash
# Quick test with minimal data
wp bp playground scenario generate minimal

# Development environment
wp bp playground scenario generate development --clean
```

### For Staging
```bash
# Medium-sized community
wp bp playground scenario generate medium_community
```

### For Performance Testing
```bash
# Large dataset
wp bp playground scenario generate large_community
```

## Files to Remove/Deprecate

1. **Remove**: `includes/cli/class-bp-playground-cli-scenario.php` - Replaced by enhanced version
2. **Consider merging**: Multiple small data files could be consolidated

## Maintenance Notes

1. **Always use** `BP_Playground_CLI_Scenario_Enhanced` for scenario generation
2. **XProfile structure** is defined in `xprofile-structure.php` - modify there for changes
3. **Scenario configs** are in `scenario-configs.php` - add new scenarios there
4. **Name generation** is automatic via hooks - no manual intervention needed
5. **Sequence order** is critical - defined in `BP_Playground_Sequence_Manager::PHASES`

## Testing

To verify the plugin is working correctly:

```bash
# List available scenarios
wp bp playground scenario list

# Show scenario details
wp bp playground scenario info small_community

# Dry run to see what would be created
wp bp playground scenario generate minimal --dry-run

# Generate actual data
wp bp playground scenario generate minimal
```

## Support

For issues or questions:
- Check error logs in wp-content/debug.log
- Use --debug flag with WP-CLI commands
- Review the sequence manager output for phase-specific errors