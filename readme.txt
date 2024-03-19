=== Temporary Login ===
Contributors: elemntor
Tags: temporary login, passwordless login, temporary access, login
Requires at least: 6.2
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Create a secure, temporary URL for easy access to your WP admin.

== Description ==

Temporary Login creates a secure, temporary URL for easy access to your WP admin with no username and password. Share this URL with trusted support agents and colleagues in order to resolve issues quickly, and shut down access as soon as you're done.

**FEATURES**

* Grant access to your site with a single click; a temporary URL will be created that you can share for admin-level access to your site and it will automatically expire 7 days from creation.
* Extend access - need more time? No problem. Just click to extend access so that users don’t get locked out.
* All done? Revoke access and the link becomes inaccessible.
* Auto disable access - whether you forget to revoke access or lose track of the timing, there’s no need to worry. We will automatically disable the access URL at the expiration, within 7 days.

= CONTRIBUTION =

Would you like to contribute to this plugin? You’re more than welcome to submit your pull requests on the [GitHub repo](https://github.com/elementor/temporary-login/). Also, if you have any notes about the code, please open a ticket on the issue tracker.

== Installation ==

1. Install using the WordPress built-in Plugin installer, or Extract the zip file and drop the contents in the wp-content/plugins/ directory of your WordPress installation.
2. Activate the plugin through the ‘Plugins’ menu in WordPress.
3. Go to the Temporary Login tab within the Users menu.
4. Click "Grant Access", and you’re all set.

== Frequently Asked Questions ==

= Why do I need to use this plugin? =

Temporary Login is a useful plugin that allows you to create an easy login option for temporary access to your website. If you need a support agent to help troubleshoot a problem or a colleague to make a simple change, just grant access and provide them with the URL. As soon as the work is done, you can disable access immediately.

= Can I extend the access period? =

Access can be extended up to 1 week from the original expiration date by simply clicking the ‘Extend Access’ button and confirming the action.

= How secure is the URL? =

The URL is very secure. In fact, it would be easier for a hacker to determine your username and password than to figure out the encryption we use here.

= What if I forget to revoke access? =

Not to worry. The URL is automatically disabled at the expiration time with no action needed on your part.

= I deleted the plugin but forgot to revoke access first; what should I do? =

Nothing! We disable the access as soon as you delete the plugin, so there’s nothing for you to worry about.

== Changelog ==

= 1.1.0 - 2024-03-18 =
* Tweak: Added site token to mitigate security risk

= 1.0.0 - 2024-03-12  =
* Initial release
