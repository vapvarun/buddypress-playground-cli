# BuddyPress Playground CLI

BuddyPress Playground CLI is a WordPress plugin that helps you generate test data for your BuddyPress installation. This plugin includes several modules that can create random activities, group activities, messages, connections, group members, and more. It is designed to help developers test the BuddyPress functionality thoroughly.

## Installation

1. **Download or Clone the Repository:** Place the `buddypress-playground-cli` directory in the `wp-content/mu-plugins/` directory of your WordPress installation.
2. **Activate BuddyPress:** Ensure that the BuddyPress plugin is active on your WordPress site.

## Modules

This plugin includes several modules, each designed to generate specific types of data within BuddyPress. Below are the available modules and how to use them:

### 1. BP_Groups_Module

**Description:** Generates random BuddyPress groups with a specified number of groups and assigns random users as group admins. The groups can be public, private, or hidden based on the specified percentages.

**Usage:**

    wp bp create_groups --count=200 --public=70 --private=25 --hidden=5

`--count`: (Optional) Total number of groups to create. Defaults to 200.

`--public`: (Optional) Percentage of groups to be public. Defaults to 70%.

`--private`: (Optional) Percentage of groups to be private. Defaults to 25%.

`--hidden`: (Optional) Percentage of groups to be hidden. Defaults to 5%.

**Example:**

To create 200 groups with 70% public, 25% private, and 5% hidden:

    wp bp create_groups --count=200 --public=70 --private=25 --hidden=5

### 2. BP_Activities_Module

**Description:** Creates random activities posted by random users. The content of the activities is based on random quotes from famous English poems.

**Usage:**

    wp bp create_activities --count=50

`--count`: (Optional) Number of activities to create. Defaults to 50.

### 3. BP_Group_Activities_Module

**Description:** Creates group activities, ensuring that each group has a minimum of 5 activities posted by the group admin or owner. The content is based on random quotes from famous English poems.

**Usage:**

    wp bp create_group_activities --min_activities=5 --by_admin=true

`--min_activities`: (Optional) Specifies the minimum number of activities to be created per member or admin in each group. Defaults to 5 if not provided.

`--by_admin`: (Optional) Determines whether activities should be created by group admins or random group members. Set to true to use admins, or false to use members. Defaults to true.

### 4. BP_Messages_Module

**Description:** Generates random messages between users. The content of the messages is based on random historical facts. The module also randomly decides whether the recipient should reply, simulating multi-user threads.

**Usage:**

    wp bp create_messages --count=200

`--count`: (Optional) Number of messages to create. Defaults to 200.

### 5. BP_Connections_Module

**Description:** Creates random connections (friendships) between users. The module ensures a 70:30 ratio of confirmed friendships to pending requests.

**Usage:**

    wp bp create_connections --count=200

`--count`: (Optional) Number of connections to create. Defaults to 200.

### 6. BP_Activity_Comments_Module

**Description:** Creates random comments on activities, using historical quotes. Each comment’s date is randomly selected to be between the activity’s posting date and now. The comment authors are random users.

**Usage:**

    wp bp create_activity_comments --count=100

`--count`: (Optional) Number of comments to create. Defaults to 100.

### 7. BP_Group_Members_Module

**Description:** Adds random users to BuddyPress groups, ensuring that each group has between 5 and 40 members.

**Usage:**

    wp bp add_group_members

- No additional parameters are needed.

### 8. BP_Update_Last_Activity_Module

**Description:** Updates the last activity timestamp for all users in batches. This ensures that all users appear in the BuddyPress member directory based on their last activity. The process is done in batches to prevent server overload.

**Usage:**

    wp bp update_last_activity --batch_size=1000

`--batch_size`: (Optional) The number of users to process in each batch. Defaults to 1000.

**Example:**

To update the last activity timestamp for all users, processing them in batches of 1000:

    wp bp update_last_activity --batch_size=1000

### 9. BP_Create_Users_Module

**Description:** Generates a specified number of users with random first names, last names, and unique usernames. Each user also has a randomly assigned 2-liner bio.

**Usage:**

    wp bp create_users --count=500

- `--count`: (Optional) The number of users to create. Defaults to 500.

**Example:**

To create 500 users with random names, usernames, and bios:

    wp bp create_users --count=500

This command will create a set of users with diverse profiles, useful for populating a BuddyPress site for testing or demo purposes.

### 10. BP_BBPress_Module

**Description:** Creates bbPress forum topics and replies using random historical and war facts. Topics are created with historical facts as the title, and replies are created using war facts.

**Usage:**

    wp bp create_forums_with_topics_replies --forum_count=10 --min_topics=10 --max_topics=15 --min_replies=3 --max_replies=7

`--forum_count`: (Optional) Number of forums to create. Defaults to 10.

`--min_topics`: (Optional) Minimum number of topics to create per forum. Defaults to 10.

`--max_topics`: (Optional) Maximum number of topics to create per forum. Defaults to 15.

`--min_replies`: (Optional) Minimum number of replies per topic. Defaults to 3.

`--max_replies`: (Optional) Maximum number of replies per topic. Defaults to 7.

## Running Commands

To use the above commands, please ensure you have WP-CLI installed and running on your WordPress installation. Please go ahead and execute the commands from your terminal in the root directory of your WordPress site.

## Contributing

Contributions are welcome! Please submit pull requests or issues on GitHub if you have suggestions or improvements.

## License

This plugin is licensed under the GPL-2.0+ License. See the LICENSE file for more information.
