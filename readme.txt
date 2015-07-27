=== Client Access ===
Contributors: ZaneMatthew
Donate link: http://zanematthew.com/
Tags: administration, authentication, registration, responsive
Requires at least: 4.2.2
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Let your client see their work in progress.

== Description ==

Have you had to make a working project publicly accessible for your client, yet still have to keep
the site "private" to the general public?

**[Demo](demo.zanematthew.com/client-access/)**

Normally you'll quickly hack in an allowed IP list, server-side password (htpasswd), etc. Why hack this in? With Client Access you can take the professional approach. Use a WordPress plugin to allow specific IPs, or those with the universal password the ability to access the work in progress.

= Allow by IP =

Once enabled via the settings tab, you can add a list of IPs to the textarea. These IP addresses are the *only* IP addresses that are allowed. Additionally you can add a comment per IP to better detail each entry.

Example:
`
// Office at 123 Main street
192.168.0.1

// My localhost
127.0.0.1

// Ana
123.456.789
`

Allow by IP allows for:

* Provide a list of IPs that are allowed
* Comments are allowed for IPs
* Display a content message
* Display a footer message
* All IPs are validated against the latest IP protocols

= Allow by Universal Password =
Once enabled via the settings tab, you can create a universal password that is *shared* with all users. No user name is required.

Anyone that visits the website while this is enabled will see the default message you've added. The site will only be accessible to users that you have shared out the password with.

* Provide a universal password
* Set an expire time

= Allow By Role =
Allow by role of choice.

* Choose any registered role

== Installation ==

= Automatic =
1. Go to Plugins > Add New.
1. Type in the name of the WordPress Plugin "Client Access"
1. From the search results find "Client Access"
1. Click Install Now to install the WordPress Plugin

= Manual =
1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Does this plugin block the WordPress admin and login pages? =
No, visitors can still access the /wp-login.php page and login.

= Does this plugin make specific content private? =
No, this plugin makes your *entire* site private, i.e., posts, pages, etc.

= Does this plugin make assets private? =
No, images, stylesheets, text files, etc. are still accessible if a user knows the direct URL.

= Does the universal password need a user name? =
No.

= Does "Allow by IP" support IP ranges? =
No, it is scheduled for a later release.

= How do I reset the universal password? =
From the settings page, enter the new password and click "save"

== Screenshots ==

1. Allow by Role
1. Allow by IP
1. Allow by universal password
1. Front-end showing all options set

== Changelog ==

= 1.0 =
* Initial release

== Upgrade Notice ==

None