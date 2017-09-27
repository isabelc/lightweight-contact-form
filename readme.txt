=== Lightweight Contact Form ===
Contributors: isabel104
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R7BHLMCQ437SS
Tags: contact form, lightweight contact form, minimal contact form, fast contact form
Requires at least: 3.8
Tested up to: 4.9-alpha-41604
Stable tag: 1.3
License: GNU Version 2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The most lightweight Contact Form plugin for WordPress. No CSS, no settings, no overhead. The goal is fastest page speed.

== Description ==

This is the absolute lightest contact form for WordPress. This plugin is designed for the fastest pagespeed. So, there is no settings page, no CSS stylesheet, no extra files, and no overhead.

If all you need is a simple contact form to take messages from site visitors, and fast page speed is your priority, this minimal plugin is for you.

Although this form is lightweight and simple, it blocks SPAM, and has both client-side and server-side validation. This is a solid and dependable contact form.

**Features**

- No settings. Emails are sent to the admin email which is set in your WordPress General Settings.
- Add the contact form to any page with a shortcode.
- Both JavaScript client-side and PHP server-side validation.
- No CSS included. This is for the most lightweight footprint.
- It’s just one file. No extra CSS or JavaScript files.

You must add any CSS styling yourself. This is easy and is explained in the [installation guide](https://isabelcastillo.com/lightweight-wordpress-contact-form#jl-css).

(In addition, if you need extra fields or different forms for different pages, see the [Advanced: Add Custom Form Fields](https://isabelcastillo.com/lightweight-wordpress-contact-form#jl-advanced) section.)

== Installation ==

**Install and Activate**

1. Log in to your WordPress dashboard.
2. Go to "Plugins -> Add New".
3. Click "Upload Plugin".
4. Click "Browse” to locate the plugin `.zip` file on your computer.
5. Click "Install Now".
6. Click "Activate Plugin".

**Quick Setup**

Add the contact form to any page with this shortcode:

`[lcf_contact_form]`

Optionally, add [these CSS styles](https://isabelcastillo.com/lightweight-wordpress-contact-form#jl-css) to align the form fields.

(In addition, if you need extra fields or different forms for different pages, see the [Advanced: Add Custom Form Fields](https://isabelcastillo.com/lightweight-wordpress-contact-form#jl-advanced) section.)

== Frequently Asked Questions ==

None yet.

== Screenshots ==

1. This is the subscription checkbox that is added beneath your comment form.

== Changelog ==

= 1.3, 2017-09-26 =
* New - New filters allow insertion of custom fields.
* New - Add shortcode atts for message_label.
* New - Move form url to top in email message.
* Fix - Fixed HTML typo in email and math fields.

= 1.2, 2017-03-23 =
* New - Eliminated the jQuery Validation plugin in favor of some smaller jQuery validation for the form fields. This eliminates the need for an extra file.

= 1.1, 2017-03-22 =
* New - Removed the mandatory step.

= 1.0, 2017-02-11 =
* New - Removed fieldset elements for better mobile style.

= 0.9, 2016-11-20 =
* New - Remove max-length from textarea.

= 0.8.1, 2015-08-08 =
* New - Remove redundant date from email.

= 0.8, 2015-08-08 =
* Initial public release.

== Upgrade Notice ==

= 1.3 =
New filters allow insertion of custom fields.
