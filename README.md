# Vanillicon for WordPress

This is a WordPress plugin that lets you set Vanillicon v1 as the default avatar. [Vanillicon](http://vanillicon.com) is an identicon generator by Vanilla Forums.

This does not interfere with Gravatar. If a user has a image set via Gravatar, their Gravatar image will be shown instead.

Tested with WordPress v5.3.

## Installation

Place `vanillicon.php` in your `/wp-content/plugins` directory and activate the plugin from the admin page.

## Developer Notes

The hash parameters used for Vanillicon v1 match [the Gravatar plugin's for Vanilla](https://github.com/vanilla/vanilla/commit/9028a6058e28ded6bcef1e6f3c99af649c64bd43#diff-10ea97896ab4e2d2d322a54e2f88dc87).

[Vanillicon v2](https://open.vanillaforums.com/discussion/28162/vanillicon-2-is-coming/p1) can't be used because it outputs an svg image, which Gravatar doesn't support for the default avatar fallback.
