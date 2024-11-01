=== Update customer ===
Contributors: wpexpertgr
Donate link: 
Tags: email, email notifications, update notifications
Requires at least: 3.9
Tested up to: 5.1.1
Requires PHP: 5.6
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
Useful plugin for dev freelancers that need to inform their customers automatically when maintenance is being carried out to their websites.

== Description ==

Useful plugin for dev freelancers that have disabled automated core updates to the websites of their customers, but need to inform them automatically, when maintenance is being carried out.

WordPress sends email to admin anyway, when automated update is enabled but in most times developers have disabled this feature. 

Your customer needs to know when you work for his website in order to pay for a maintenance contract.

You can add multiple recipients, add your subject, compose your message with a wysiwug editor and when a WordPress core update performed the email you composed will be sent to the recipients you entered.
 

== Installation ==

1. Upload the entire `update-customer` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

You will find 'Update customer' menu under WordPress admin panel Settings.

 
== Frequently Asked Questions ==
 
= What 'From' address is used when the email is sent =
 
The default address that is used is wordpress@mydomain.com (where 'mydomain.com' is your website`s domain name).
 
 
== Screenshots ==
 
1. Plugin settings
 
== Changelog ==
 
= 1.0.1 =

* Bugfix: Send email when plugins are updated
* Bugfix: Redirection caused errors when plugins are updated

= 1.0.0 =

* Init upload

== Upgrade Notice ==