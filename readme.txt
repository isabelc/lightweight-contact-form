=== Lightweight Contact Form ===
Contributors: isabel104
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R7BHLMCQ437SS
Tags: contact form, lightweight contact form, light contact form, fast contact form, minimalistic
Requires at least: 4.6
Tested up to: 5.1.1
Stable tag: 2.0
Requires PHP: 5.6
License: GNU Version 2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The most lightweight Contact Form plugin for WordPress. No CSS files, no overhead, no SPAM. The goal is fastest page speed.

== Description ==

This is the absolute lightest contact form for WordPress. This plugin is designed for the fastest pagespeed. So, there is no CSS stylesheet, no extra files, and no overhead. And no spam. It uses Google reCAPTCHA v3 so as not to annoy your users with any extra step, while keeping your contact form spam-free.

If all you need is a simple contact form to take messages from site visitors, and fast page speed is your priority, this minimal plugin is for you.

Although this form is lightweight and simple, it blocks SPAM, and has both client-side and server-side validation. This is a solid and dependable contact form. 

**NOTE: To make use of the extra spam protection from Google reCAPTCHA v3, you must [get your reCAPTCHA v3 API keys](https://isabelcastillo.com/recaptcha-v3-keys) from Google. This is a free and easy step, and is strongly recommended.**

**Features**

- Emails are sent to the admin email which is set in your WordPress General Settings.
- Add the contact form to any page with a shortcode.
- Both JavaScript client-side and PHP server-side validation.
- It’s just one file. No extra CSS or JavaScript files. This is for the most lightweight footprint.

You must add any CSS styling yourself. This is easy and is explained in the [installation guide](https://isabelcastillo.com/lightweight-wordpress-contact-form#jl-css).

(In addition, if you need extra fields or different forms for different pages, see the [Advanced: Add Custom Form Fields](https://isabelcastillo.com/lightweight-wordpress-contact-form#jl-advanced) section.)

== Installation ==

**Install and Activate**

1. Install and activate the plugin in your WordPress dashboard by going to Plugins –> Add New. 
2. Search for "Lightweight Contact Form" to find the plugin.
3. When you see "Lightweight Contact Form," click "Install Now" to install the plugin.
4. Click "Activate" to activate the plugin.

**Quick Setup**

Add the contact form to any page with this shortcode:

`[lcf_contact_form]`

Optionally, add [these CSS styles](https://isabelcastillo.com/lightweight-wordpress-contact-form#jl-css) to align the form fields.

This step is strongly recommended: To stop SPAM on your contact form, get your free [Google reCAPTCHA keys](https://isabelcastillo.com/recaptcha-v3-keys). Then, enter those keys in your WordPress admin settings by going to **Settings** > **Discussion**. On the Discussion Settings page, scroll all the way down to "Lightweight Contact Form" where you'll find the place to enter your **reCAPTCHA v3 Site key** and **reCAPTCHA v3 Secret key**.

That is all. Now you have a lightweight, working contact form on your WordPress site. When someone fills out and submits your contact form, it will be sent to the admin email which is set in your WordPress General Settings.

To try the contact form, visit that page on your site.

(If you need extra fields or different forms for different pages, see the [Advanced: Add Custom Form Fields](https://isabelcastillo.com/lightweight-wordpress-contact-form#jl-advanced) section.)

== Frequently Asked Questions ==

= How do I stop SPAM on the contact form? =

To stop SPAM on your contact form, get your free [Google reCAPTCHA keys](https://isabelcastillo.com/recaptcha-v3-keys). Then, enter those keys in your WordPress admin settings by going to **Settings** > **Discussion**. On the Discussion Settings page, scroll all the way down to "Lightweight Contact Form" where you'll find the place to enter your **reCAPTCHA v3 Site key** and **reCAPTCHA v3 Secret key**.

== Screenshots ==

1. This is how the contact form appears using the default WordPress theme.

== Changelog ==

= 2.0, 2019-01-09 =
* New - Added the ability to use Google reCAPTCHA v3 to stop spam on your contact form. To use this new protection, get your free [Google reCAPTCHA keys](https://isabelcastillo.com/recaptcha-v3-keys). Then, enter those keys in your WordPress admin settings by going to **Settings** > **Discussion**. On the Discussion Settings page, scroll all the way down to "Lightweight Contact Form" where you'll find the place to enter your **reCAPTCHA v3 Site key** and **reCAPTCHA v3 Secret key**.
* New - Removed the math captcha box, again.

= 1.5.2, 2018-08-06 =
* Tweak - Add back the math captcha box which was previously removed in version 1.5. It is added again because the honeypot field is not working well enough to eliminate spam.

= 1.5.1, 2018-03-07 =
* Tweak - Added 2 (optional) semi-colons.

= 1.5, 2018-03-07 =
* New - Removed the math captcha box in favor of better spam-blocking.

= 1.4.1, 2017-10-02 =
* Fixed - Running submission checks outside a function.
* Fixed - Proper escaping.

= 1.4, 2017-09-28 =
* New - Internationalize all strings to add support for translations.
* New - Remove jQuery dependency.

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
= 2.0 =
Ability to use Google reCAPTCHA v3 to stop spam on your contact form.

= 1.5.2 =
Add back the match captcha box to better eliminate spam.

= 1.5 =
Removed the math captcha box in favor of better spam-blocking.

= 1.3 =
New filters allow insertion of custom fields.
