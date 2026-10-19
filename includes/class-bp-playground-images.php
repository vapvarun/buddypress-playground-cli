<?php
/**
 * BuddyPress Playground Images
 *
 * Real avatars and cover images for generated members and groups, so a seeded
 * site looks like a community and not a wall of grey silhouettes.
 *
 * Portraits come from randomuser.me (100 per gender), landscapes from
 * picsum.photos (Unsplash photos). randomuser.me portraits are 128 px, the
 * largest it serves, so avatars stay 128 px: sharp at list and card sizes,
 * slightly soft in a large profile header. Swap the portrait source if a
 * site needs larger faces. Both are free stock sets meant for test
 * data. URLs are derived from the item ID, so a rerun picks the same image.
 *
 * Files are written where BuddyPress itself looks for them, the same layout
 * its own uploader produces, so no BuddyPress option or meta is needed.
 *
 * @package BuddyPress_Playground
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class BP_Playground_Images {

    /**
     * Give a member a stock portrait matching their gender.
     *
     * @param int    $user_id User ID.
     * @param string $gender  'male', 'female' or 'neutral'.
     * @return bool
     */
    public static function member_avatar($user_id, $gender = 'neutral') {
        if ('male' === $gender) {
            $set = 'men';
        } elseif ('female' === $gender) {
            $set = 'women';
        } else {
            $set = ($user_id % 2) ? 'women' : 'men';
        }

        $url = sprintf('https://randomuser.me/api/portraits/%s/%d.jpg', $set, $user_id % 100);

        return self::save_avatar($url, bp_core_avatar_upload_path() . '/avatars/' . (int) $user_id);
    }

    /**
     * Give a member a stock landscape cover image.
     *
     * @param int $user_id User ID.
     * @return bool
     */
    public static function member_cover($user_id) {
        return self::save_cover('members', $user_id);
    }

    /**
     * Give a group a stock square avatar.
     *
     * @param int $group_id Group ID.
     * @return bool
     */
    public static function group_avatar($group_id) {
        $url = sprintf('https://picsum.photos/seed/bpp-group-%d/400/400', $group_id);

        return self::save_avatar($url, bp_core_avatar_upload_path() . '/group-avatars/' . (int) $group_id);
    }

    /**
     * Give a group a stock landscape cover image.
     *
     * @param int $group_id Group ID.
     * @return bool
     */
    public static function group_cover($group_id) {
        return self::save_cover('groups', $group_id);
    }

    /**
     * Download an image and write BuddyPress's full + thumb avatar pair.
     *
     * Saved at twice BuddyPress's avatar size, as its own cropper does for
     * high-density screens.
     *
     * @param string $url Image URL.
     * @param string $dir Avatar directory for the item.
     * @return bool
     */
    private static function save_avatar($url, $dir) {
        $tmp = self::download($url);
        if (!$tmp) {
            return false;
        }

        wp_mkdir_p($dir);
        array_map('unlink', glob($dir . '/*') ?: []);

        $saved = true;
        $stamp = time();
        foreach (['bpfull' => bp_core_avatar_full_width(), 'bpthumb' => bp_core_avatar_thumb_width()] as $type => $width) {
            $editor = wp_get_image_editor($tmp);
            if (is_wp_error($editor)) {
                $saved = false;
                break;
            }
            $editor->resize($width * 2, $width * 2, true);
            $saved = $saved && !is_wp_error($editor->save("{$dir}/{$stamp}-{$type}.jpg", 'image/jpeg'));
        }

        @unlink($tmp);
        return $saved;
    }

    /**
     * Download a landscape into the item's cover-image folder.
     *
     * BuddyPress picks the first file whose name contains "cover-image".
     *
     * @param string $object_dir 'members' or 'groups'.
     * @param int    $item_id    User or group ID.
     * @return bool
     */
    private static function save_cover($object_dir, $item_id) {
        $uploads = bp_attachments_uploads_dir_get();
        if (empty($uploads['basedir'])) {
            return false;
        }

        $tmp = self::download(sprintf('https://picsum.photos/seed/bpp-%s-%d/1500/500', $object_dir, $item_id));
        if (!$tmp) {
            return false;
        }

        $dir = $uploads['basedir'] . '/' . $object_dir . '/' . (int) $item_id . '/cover-image';
        wp_mkdir_p($dir);
        array_map('unlink', glob($dir . '/*') ?: []);

        $saved = copy($tmp, $dir . '/' . time() . '-bp-cover-image.jpg');
        @unlink($tmp);
        return $saved;
    }

    /**
     * Download a URL to a temp file.
     *
     * @param string $url URL.
     * @return string Temp file path, or '' on failure.
     */
    private static function download($url) {
        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $tmp = download_url($url, 30);
        return is_wp_error($tmp) ? '' : $tmp;
    }
}
