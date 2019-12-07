<?php
/*
Plugin Name: Vanillicon
Version: 1.0.0
Description: Set Vanillicon v1 as the default avatar. Vanillicon is an identicon generator by Vanilla Forums.
Plugin URI: https://github.com/austins/wp-vanillicon
Requires PHP: 7.1
License: GPL2
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Author: Austin S.
Author URI: https://github.com/austins
*/
class Vanillicon
{
    private const VANILLICON_BASE_URL = 'https://vanillicon.com';
    private const VANILLICON_TRUNCATED_HASH_LENGTH = 32;
    private const DEFAULT_ID = 100;
    private const DEFAULT_SIZE = 50;
    private $defaultUrl;

    /**
     * Initialize the plugin.
     */
    public function __construct()
    {
        // Set default Vanillicon URL.
        $this->defaultUrl = self::VANILLICON_BASE_URL . "/{$this->getIdHash(self::DEFAULT_ID)}_{$this->getSize(self::DEFAULT_SIZE)}.png";

        // Register hooks.
        register_activation_hook(__FILE__, [&$this, 'hookActivation']);
        register_deactivation_hook(__FILE__, [&$this, 'hookDeactivation']);
        add_filter('avatar_defaults', [&$this, 'filterAvatarDefaults']);
        add_filter('get_avatar', [&$this, 'filterGetAvatar'], 1, 5);
    }

    /**
     * Plugin activation hook.
     */
    public function hookActivation()
    {
        update_option('avatar_default', $this->defaultUrl);
    }

    /**
     * Plugin deactivation hook.
     */
    public function hookDeactivation()
    {
        if (substr(get_option('avatar_default'), 0, strlen(self::VANILLICON_BASE_URL)) === self::VANILLICON_BASE_URL)
            update_option('avatar_default', 'mystery');
    }

    /**
     * Get truncated hash of an ID or email address.
     *
     * @param int|string $id_or_email
     * @return string
     */
    private function getIdHash($id_or_email)
    {
        return substr(sha1($id_or_email), 0, self::VANILLICON_TRUNCATED_HASH_LENGTH);
    }

    /**
     * Get size within set of supported sizes.
     *
     * @param int $size
     * @return int
     */
    private function getSize($size)
    {
        // Vanillicon v1 only supports three sizes.
        if ($size <= 50)
            return 50;
        else if ($size <= 100)
            return 100;

        return 200;
    }

    /**
     * Filter hook for avatar_defaults.
     * Creates a default avatar setting.
     *
     * @param array $avatar_defaults
     * @return array
     */
    public function filterAvatarDefaults($avatar_defaults)
    {
        $avatar_defaults[$this->defaultUrl] = 'Vanillicon';

        return $avatar_defaults;
    }

    /**
     * Filter hook for get_avatar.
     * Replaces default hash and size with passed email and size.
     *
     * @param $avatar
     * @param int|WP_User|WP_Comment|string $id_or_email User ID, object, or email address.
     * @param $size
     * @param $default
     * @param $alt
     * @return string
     */
    public function filterGetAvatar($avatar, $id_or_email, $size, $default, $alt)
    {
        global $pagenow;
        if (is_admin() && $pagenow === 'options-discussion.php')
            return $avatar; // Only show initial default on Discussion Settings page.

        // Only filter if default avatar is set to Vanillicon.
        if (preg_match('/' . preg_quote(self::VANILLICON_BASE_URL, '/') . '\/(.*?)_([0-9]+)\.png/', $default, $matches) === 1) {
            $identifier = $id_or_email; // Identifier assumed to be an email address by default.
            if (is_numeric($id_or_email)) {
                // Identifier by user ID.
                $identifier = get_user_by('id', $id_or_email)->user_email;
            } else if ($id_or_email instanceof WP_User) {
                // Identifier by user object.
                $identifier = $id_or_email->user_email;
            } else if ($id_or_email instanceof WP_Comment) {
                // Identifier by comment object.
                if (!empty($id_or_email->comment_author_email))
                    $identifier = $id_or_email->comment_author_email; // Use comment author email address.
                else if (!empty($id_or_email->comment_author))
                    $identifier = $id_or_email->comment_author; // If no email address, use comment author name.
                else
                    $identifier = self::DEFAULT_ID; // If no comment author name, use default ID.
            }

            // Replace hash and size.
            $avatar = str_replace($matches[1], $this->getIdHash($identifier), $avatar);
            $avatar = str_replace($matches[2] . '.png', $this->getSize($size) . '.png', $avatar);
        }

        return $avatar;
    }
}

new Vanillicon();
