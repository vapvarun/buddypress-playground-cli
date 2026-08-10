<?php
/**
 * BuddyPress Playground CLI Media Command
 *
 * Generates photo content for whichever media layer the site runs: rtMedia on
 * plain BuddyPress, or BuddyBoss Platform's own. BuddyPress core has no media
 * feature at all, so without one of those installed there is nothing to make.
 *
 * @package BuddyPress_Playground
 * @subpackage CLI
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CLI command for media, albums and their activities
 *
 * @since 1.1.0
 */
class BP_Playground_CLI_Media extends WP_CLI_Command {

    /**
     * Detected media layer: 'buddyboss', 'rtmedia' or ''
     *
     * @var string
     */
    private $layer = '';

    /**
     * Directory holding generated image files
     *
     * @var string
     */
    private $image_dir = '';

    /**
     * Generate media, albums and their activities.
     *
     * Produces every shape a media layer can hold, because a generator that
     * only makes the easy one leaves consumers testing the easy one:
     *
     *   - a profile album, and a group album
     *   - a photo in an album that ALSO has an activity (the common upload)
     *   - a photo in an album with no activity
     *   - a standalone photo in no album at all, never posted
     *
     * ## OPTIONS
     *
     * [--count=<number>]
     * : Photos to create per album. Default 2.
     *
     * [--albums=<number>]
     * : Profile albums to create. Default 1.
     *
     * [--group-albums=<number>]
     * : Group albums to create, spread over existing groups. Default 1.
     *
     * [--standalone=<number>]
     * : Photos belonging to no album and no activity. Default 1.
     *
     * [--user=<id>]
     * : Owner for profile media. Defaults to the first administrator.
     *
     * ## EXAMPLES
     *
     *     wp bp playground media
     *     wp bp playground media --count=5 --albums=3 --group-albums=2
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     */
    public function __invoke($args, $assoc_args) {
        $this->layer = $this->detect_layer();

        if ('' === $this->layer) {
            WP_CLI::error(
                'No media layer found. Install rtMedia (wp.org slug buddypress-media) ' .
                'on BuddyPress, or run BuddyBoss Platform. BuddyPress core has no media feature.'
            );
        }

        WP_CLI::log(sprintf('Media layer: %s', $this->layer));

        $count        = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'count', 2);
        $albums       = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'albums', 1);
        $group_albums = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'group-albums', 1);
        $standalone   = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'standalone', 1);
        $user         = (int) WP_CLI\Utils\get_flag_value($assoc_args, 'user', 0);

        if ($user <= 0) {
            $admins = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']);
            $user   = !empty($admins) ? (int) $admins[0] : 1;
        }

        if ('rtmedia' === $this->layer) {
            $this->ensure_rtmedia_tables();
        }

        $this->image_dir = trailingslashit(wp_upload_dir()['basedir']) . 'bp-playground-media';
        wp_mkdir_p($this->image_dir);

        $made = [
            'albums'       => 0,
            'group_albums' => 0,
            'photos'       => 0,
            'standalone'   => 0,
            'activities'   => 0,
        ];

        // Profile albums, each with photos - the first of each album carries an
        // activity, the rest do not, so both routing cases exist side by side.
        for ($i = 1; $i <= $albums; $i++) {
            $album_id = $this->create_album(sprintf('Wall Posts %d', $i), $user, 'profile', $user);
            if ($album_id <= 0) {
                WP_CLI::warning(sprintf('profile album %d could not be created', $i));
                continue;
            }
            $made['albums']++;

            for ($p = 1; $p <= $count; $p++) {
                $with_activity = (1 === $p);
                $ok            = $this->create_photo($user, $album_id, 'profile', $user, $with_activity);
                if ($ok) {
                    $made['photos']++;
                    if ($with_activity) {
                        $made['activities']++;
                    }
                }
            }
        }

        // Group albums, so the group/space routing path has data. A media row
        // whose context is a group is the only way that path is exercised.
        $groups = $this->group_ids($group_albums);
        foreach ($groups as $index => $group_id) {
            $album_id = $this->create_album(sprintf('Group Album %d', $index + 1), $user, 'group', $group_id);
            if ($album_id <= 0) {
                WP_CLI::warning(sprintf('group album for group %d could not be created', $group_id));
                continue;
            }
            $made['group_albums']++;

            for ($p = 1; $p <= $count; $p++) {
                if ($this->create_photo($user, $album_id, 'group', $group_id, false)) {
                    $made['photos']++;
                }
            }
        }

        // Never posted, in no album. Easy to forget, and the shape most likely
        // to be silently dropped by anything reading media through activities.
        for ($s = 1; $s <= $standalone; $s++) {
            if ($this->create_photo($user, 0, 'profile', $user, false)) {
                $made['standalone']++;
            }
        }

        WP_CLI::success(
            sprintf(
                '%d profile album(s), %d group album(s), %d photo(s), %d standalone, %d activity-attached',
                $made['albums'],
                $made['group_albums'],
                $made['photos'],
                $made['standalone'],
                $made['activities']
            )
        );

        // A generator that reports success while writing nothing is worse than
        // one that fails: the consumer tests an empty source and calls it a pass.
        if (0 === $made['photos'] && 0 === $made['standalone']) {
            WP_CLI::error('no media rows were created - the source would prove nothing');
        }
    }

    /**
     * Which media layer this site runs.
     *
     * @return string 'buddyboss', 'rtmedia' or ''
     */
    private function detect_layer() {
        global $wpdb;

        if (defined('BP_PLATFORM_VERSION') || $this->table_exists($wpdb->prefix . 'bp_media')) {
            return 'buddyboss';
        }

        if (class_exists('RTMediaModel') || $this->table_exists($wpdb->prefix . 'rt_rtm_media')) {
            return 'rtmedia';
        }

        return '';
    }

    /**
     * Whether a table exists.
     *
     * @param string $table Full table name.
     * @return bool
     */
    private function table_exists($table) {
        global $wpdb;

        return (string) $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
    }

    /**
     * Build rtMedia's tables when its own installer has not.
     *
     * On a fresh install driven by WP-CLI they are frequently absent, and
     * rtMedia does not notice: RTDBUpdate::do_upgrade() is gated on
     * version_compare(db_version, install_db_version, '>'), and the install
     * option is already stamped at the current version by the time the schema
     * would be built. So the UPGRADE path runs - ALTERing tables that were
     * never created, which is what fills the log with "Table wp_rt_rtm_media
     * doesn't exist" - while the CREATE path never does. rtMedia's own retry
     * lands on dbDelta, which silently creates nothing and reports no error.
     *
     * Running rtMedia's own generated CREATE TABLE statements works, so that is
     * what this does. Verified against rtMedia 4.7.11.
     */
    private function ensure_rtmedia_tables() {
        global $wpdb;

        $table = $wpdb->prefix . 'rt_rtm_media';

        if ($this->table_exists($table)) {
            return;
        }

        if (!class_exists('RTDBUpdate') || !defined('RTMEDIA_PATH')) {
            WP_CLI::error('rtMedia tables are missing and RTDBUpdate is unavailable to build them.');
        }

        WP_CLI::log('rtMedia tables missing - building them from its own schema files');

        $updater = new RTDBUpdate(false, RTMEDIA_PATH . 'index.php', RTMEDIA_PATH . 'app/schema/', true);
        foreach ((array) glob(RTMEDIA_PATH . 'app/schema/*.schema') as $schema) {
            $sql = $updater->genrate_sql(basename($schema), (string) file_get_contents($schema));
            $wpdb->query($sql); // phpcs:ignore
        }

        if (!$this->table_exists($table)) {
            WP_CLI::error('rt_rtm_media still does not exist after building the schema.');
        }
    }

    /**
     * Group ids to hang group albums off.
     *
     * @param int $limit How many.
     * @return array
     */
    private function group_ids($limit) {
        if ($limit <= 0 || !function_exists('groups_get_groups')) {
            return [];
        }

        $groups = groups_get_groups(['per_page' => $limit, 'show_hidden' => true]);

        return isset($groups['groups']) ? wp_list_pluck($groups['groups'], 'id') : [];
    }

    /**
     * Create an album on whichever layer is present.
     *
     * @param string $title      Album title.
     * @param int    $user       Owner.
     * @param string $context    'profile' or 'group'.
     * @param int    $context_id User id or group id to match the context.
     * @return int Album id in the layer's own terms, or 0.
     */
    private function create_album($title, $user, $context, $context_id) {
        if ('buddyboss' === $this->layer) {
            if (!function_exists('bp_album_add')) {
                return 0;
            }

            $album = bp_album_add([
                'user_id'  => $user,
                'group_id' => 'group' === $context ? (int) $context_id : 0,
                'title'    => $title,
                'privacy'  => 'group' === $context ? 'grouponly' : 'public',
            ]);

            return is_wp_error($album) ? 0 : (int) $album;
        }

        if (!class_exists('RTMediaAlbum')) {
            return 0;
        }

        $album = new RTMediaAlbum();

        // rtMedia returns the rt_rtm_media row id, which is what its own
        // album_id column references - NOT the album post id.
        return (int) $album->add($title, $user, true, false, $context, (int) $context_id);
    }

    /**
     * Create one photo, optionally with the activity a real upload would post.
     *
     * @param int    $user          Owner.
     * @param int    $album_id      Album id, or 0 for none.
     * @param string $context       'profile' or 'group'.
     * @param int    $context_id    User id or group id.
     * @param bool   $with_activity Whether to post an activity for it.
     * @return bool
     */
    private function create_photo($user, $album_id, $context, $context_id, $with_activity) {
        $file = $this->make_image();
        if ('' === $file) {
            return false;
        }

        $attachment = wp_insert_attachment(
            [
                'post_mime_type' => 'image/png',
                'post_title'     => basename($file, '.png'),
                'post_status'    => 'inherit',
                'post_author'    => $user,
            ],
            $file
        );

        if (is_wp_error($attachment) || !$attachment) {
            return false;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        wp_update_attachment_metadata($attachment, wp_generate_attachment_metadata($attachment, $file));

        if ('buddyboss' === $this->layer) {
            return $this->create_photo_buddyboss($attachment, $user, $album_id, $context, $context_id, $with_activity);
        }

        return $this->create_photo_rtmedia($attachment, $user, $album_id, $context, $context_id, $with_activity, $file);
    }

    /**
     * BuddyBoss photo row, plus the bp_media_ids activity meta a real upload writes.
     *
     * @param int    $attachment    Attachment id.
     * @param int    $user          Owner.
     * @param int    $album_id      Album id or 0.
     * @param string $context       Context.
     * @param int    $context_id    Context id.
     * @param bool   $with_activity Whether to attach an activity.
     * @return bool
     */
    private function create_photo_buddyboss($attachment, $user, $album_id, $context, $context_id, $with_activity) {
        if (!function_exists('bp_media_add')) {
            return false;
        }

        $activity_id = 0;
        if ($with_activity && function_exists('bp_activity_add')) {
            $activity_id = (int) bp_activity_add([
                'user_id'   => $user,
                'component' => 'activity',
                'type'      => 'activity_update',
                'content'   => 'A photo posted through BuddyBoss',
            ]);
        }

        $media = bp_media_add([
            'attachment_id' => $attachment,
            'user_id'       => $user,
            'album_id'      => (int) $album_id,
            'group_id'      => 'group' === $context ? (int) $context_id : 0,
            'activity_id'   => $activity_id,
            'privacy'       => 'group' === $context ? 'grouponly' : 'public',
            'title'         => get_the_title($attachment),
        ]);

        if (is_wp_error($media) || !$media) {
            return false;
        }

        // The activity meta is what readers actually resolve media through -
        // the bp_media.activity_id column alone is not enough.
        if ($activity_id > 0 && function_exists('bp_activity_update_meta')) {
            bp_activity_update_meta($activity_id, 'bp_media_ids', (string) $media);
        }

        return true;
    }

    /**
     * rtMedia photo row, plus the rtmedia_update activity a real upload posts.
     *
     * @param int    $attachment    Attachment id.
     * @param int    $user          Owner.
     * @param int    $album_id      rt_rtm_media row id of the album, or 0.
     * @param string $context       Context.
     * @param int    $context_id    Context id.
     * @param bool   $with_activity Whether to attach an activity.
     * @param string $file          Absolute file path.
     * @return bool
     */
    private function create_photo_rtmedia($attachment, $user, $album_id, $context, $context_id, $with_activity, $file) {
        if (!class_exists('RTMediaModel')) {
            return false;
        }

        $activity_id = 0;
        if ($with_activity && function_exists('bp_activity_add')) {
            $activity_id = (int) bp_activity_add([
                'user_id'   => $user,
                'component' => 'activity',
                'type'      => 'rtmedia_update',
                'content'   => $this->rtmedia_activity_markup($attachment),
            ]);
        }

        $model = new RTMediaModel();

        $row = $model->insert([
            'blog_id'      => get_current_blog_id(),
            'media_id'     => $attachment,
            'album_id'     => (int) $album_id,
            'media_author' => $user,
            'media_title'  => get_the_title($attachment),
            'media_type'   => 'photo',
            'context'      => $context,
            'context_id'   => (int) $context_id,
            'activity_id'  => $activity_id,
            'privacy'      => 0,
            'upload_date'  => current_time('mysql'),
            'file_size'    => file_exists($file) ? filesize($file) : 0,
        ]);

        return (int) $row > 0;
    }

    /**
     * The wrapper markup rtMedia stores for a photo activity.
     *
     * Reproduced faithfully, indentation included, because consumers have to
     * cope with it: the caption sits in .rtmedia-activity-text while the media
     * list carries the FILE NAME as visible text, and anything stripping the
     * tags naively ends up with the filename and a block of whitespace in the
     * post body. A tidied sample would quietly stop testing that.
     *
     * @param int $attachment Attachment id.
     * @return string
     */
    private function rtmedia_activity_markup($attachment) {
        $title = get_the_title($attachment);
        $url   = (string) wp_get_attachment_url($attachment);

        return '<div class="rtmedia-activity-container"><div class="rtmedia-activity-text">' . "\n\t\t\t\t\t"
            . '<span>A photo posted through rtMedia</span>' . "\n\t\t\t\t"
            . '</div><ul class="rtmedia-list rtm-activity-media-list rtmedia-activity-media-length-1 rtm-activity-photo-list">'
            . '<li class="rtmedia-list-item media-type-photo"><a href="' . esc_url(home_url('/')) . '">' . "\n\t\t\t\t\t\t"
            . '<div class="rtmedia-item-thumbnail">' . "\n\t\t\t\t\t\t\t"
            . '<img alt="' . esc_attr($title) . '" src="' . esc_url($url) . '" />' . "\n\t\t\t\t\t\t"
            . '</div>' . "\n\t\t\t\t\t\t"
            . '<div class="rtmedia-item-title">' . "\n\t\t\t\t\t\t\t"
            . '<h4 title="' . esc_attr($title) . '">' . "\n\t\t\t\t\t\t\t\t"
            . esc_html($title) . "\n\t\t\t\t\t\t\t"
            . '</h4>' . "\n\t\t\t\t\t\t"
            . '</div>' . "\n\t\t\t\t\t"
            . '</a></li></ul></div>';
    }

    /**
     * Write a small real PNG, so consumers have an actual file to read.
     *
     * Generated rather than shipped: a binary fixture in the repo is dead weight
     * and goes stale, and every consumer of this generator needs a file that
     * genuinely exists on disk with real dimensions.
     *
     * @return string Absolute path, or '' on failure.
     */
    private function make_image() {
        static $n = 0;
        $n++;

        if (!function_exists('imagecreatetruecolor')) {
            return '';
        }

        $path = sprintf('%s/playground-media-%d-%d.png', $this->image_dir, time(), $n);

        $width  = 480;
        $height = 320;
        $image  = imagecreatetruecolor($width, $height);

        // A different flat colour per image, so they are told apart on sight in
        // a feed or a gallery without opening them.
        $colour = imagecolorallocate(
            $image,
            (int) (60 + (($n * 47) % 180)),
            (int) (60 + (($n * 83) % 180)),
            (int) (60 + (($n * 131) % 180))
        );
        imagefill($image, 0, 0, $colour);
        imagepng($image, $path);
        imagedestroy($image);

        return file_exists($path) ? $path : '';
    }
}
