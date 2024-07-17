=== Adobe Fonts (formerly Typekit) for WordPress ===
Contributors: jamescollins, glenn-om4
Donate link: https://om4.com.au/plugins/#donate
Tags: adobe, typekit, fonts, font, design, wp, multisite, wpmu, css, snippet
Requires at least: 6.0
Tested up to: 6.6
Stable tag: 1.10.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate the Adobe Fonts service into your WordPress website or blog to use a range of over 25,000 high-quality fonts.

== Description ==

Embed and use Adobe Fonts (https://fonts.adobe.com) in your WordPress website without having to edit your theme!

Adobe Fonts offers a service that allows you to select from a range of over 25,000 high-quality fonts for your WordPress website. The fonts are applied using the font-face standard, so they are standards compliant, fully licensed, and accessible.

To use this plugin, you need to sign up with Adobe Fonts, install this plugin, and then either configure some Adobe Fonts selectors or define your own CSS rules. Adobe Fonts selectors provide a quick and easy way to get fonts enabled on your site. Using your own CSS rules (as explained in Adobe Fonts' Advanced tips) gives you more control and lets you access additional attributes such as font-weight. This plugin allows you to create your own CSS rules that use Adobe Fonts without the need to edit/upload CSS style sheets.

Detailed instructions are available on the plugin's settings page.

This plugin by default uses Adobe Fonts' CSS embed code (https://blog.typekit.com/2017/11/16/new-on-typekit-load-web-fonts-with-css/). However, if you prefer, you can use the asynchronous JavaScript embed.

Compatible with WordPress Multisite.

**Available Languages**

* Japanese – 日本語 (ja)

**Other Languages**

If you would like to translate this plugin into another language, please visit the translate.wordpress.org site (https://translate.wordpress.org/projects/wp-plugins/typekit-fonts-for-wordpress). Thank you!

== Installation ==

Installation of this plugin is simple:

1. Download the plugin files and copy them to your Plugins directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the WordPress Dashboard, and use "Settings", "Adobe Fonts" to enter your Web Projects ID and embed method.
4. If you want to set up some CSS selectors like the examples shown in the Advanced link, enter your CSS rules in the plugin settings as well.

== Frequently Asked Questions ==

= Where can I get help? =

There are detailed instructions on the plugin's settings page. See screenshot #2 for more information.

= Is this plugin secure? =

Yes, see the plugin's description for more information.

= Which web browser(s) does Adobe Fonts support? =

Please see this page (https://helpx.adobe.com/fonts/using/browser-os-support.html) for information on web browser support.

== Screenshots ==
1. Settings/configuration page
2. Detailed inline help

== Changelog ==

= 1.10.0 =
* Marked as compatible with WordPress 6.6.

= 1.10.0 =
* Renamed to "Adobe Fonts (formerly Typekit) for WordPress."
* Updated the help/description to reflect the differences between Adobe Fonts and Typekit.
* Marked as compatible with WordPress 6.5.

= 1.9.0 =
* Added support for Typekit's new CSS embed method.
* Added support for Typekit's improved Advanced JavaScript embed code.
* Simplified settings screen (just enter your Kit ID rather than your full embed code).
* WordPress 4.9 compatibility.

= 1.8.4 =
* Added support for Typekit's synchronous tracking code by setting async to false. Useful for avoiding FOUT.
* WordPress 4.8 compatibility.

= 1.8.3 =
* WordPress 4.7 compatibility.
* Added "Settings" link on plugins screen.

= 1.8.2 =
* WordPress 4.6 compatibility.
* Improved handling of the HTTP response when verifying a Typekit Kit URL.

= 1.8.1 =
* PHP7 compatibility (no more deprecated constructor warning).

= 1.8 =
* Use WordPress.org language packs for plugin translations.
* Improved compatibility with older PHP versions (no more pass by reference).
* Screenshot updates.
* Readme updates.

= 1.7.2 =
* Use Typekit's latest recommended embed code (which uses a https:// Typekit embed code URL for all sites).
* WordPress 4.3 compatibility.
* Changed plugin's textdomain to match the plugin's folder name in preparation for translate.wordpress.org translations.

= 1.7 =
* Japanese language - thanks to ThemeBoy.
* Improved translation support.

= 1.6 =
* WordPress 3.8 compatibility.

= 1.5 =
* WordPress 3.5 compatibility.

= 1.4 =
* Used the new scheme-less typekit.net embed code format (`use.typekit.net/xyz.js`).

= 1.3.1 =
* WordPress 3.4 compatibility.
* Clarified license as GPLv2 or later.

= 1.3 =
* WordPress 3.3 compatibility.

= 1.2 =
* Fixed invalid HTML on settings page.
* Properly saved/displayed settings.
* WordPress 3.2 compatibility.
* Translation/localization improvements.
* Fixed localization deprecated notice (thanks to aradams for reporting).
* Stored translation files in a /languages subdirectory.

= 1.1 =
* WordPress 3.1 compatibility.

= 1.0.3 =
* Added support for HTTPS/SSL websites.
* WordPress 3.0.1 compatibility.

= 1.0.2 =
* Added instructions on how to use Typekit Kit Editor selectors.
* Added instructions on how to use font weights/styles.

= 1.0.1 =
* WordPress 2.9 compatibility.
* Improved FAQ.

= 1.0.0 =
* Initial release.
