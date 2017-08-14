=== Plugin Name ===
Contributors: Fran Diéguez
Donate link: http://mabishu.com/blog/contact/
Tags: Lareta.net, status, post, widget, tweet, sidebar, MeuLareta, SimplePie
Requires at least: 2.3
Tested up to: 2.5
Stable tag: 1.6beta

MeuLareta allows users to display their recent Lareta.net updates (tweets) on their blog and update their status through the Options page.

== Description ==

MeuLareta allows users to display their recent Lareta.net status updates (tweets) on their Wordpress site and update their status through the Options page. Includes customization options including number of recent twitters to display, formatting options, and stylesheets.  It can be called as a function or used as a widget.

== Installation ==
Extract the contents of the archive. Upload the meulareta folder to your Wordpress plugins folder (e.g. http://yoursitename.com/wp-content/plugins/).  Set your preferences in the Wordpress Options panel for "MeuLareta" (including username, password, and formatting options).

Establish the Cache Life to set the length of time for the Lareta.net feed to be cached before checking for updates.

If it doesn't already exist, create a folder named "cache" in the plugin directory on your webserver (and give it write permission - chmod to 755) or alternatively edit the CACHELOC value in the plugin to point to a different location (if you do this you may need to reupload the plugin) -- the cache location will be added to the Options panel in a future version.

== Frequently Asked Questions ==

= How do I use this plugin? =

Configure the options for your Lareta.net profile using the MeuLareta panel in the Options pane of Wordpress.

= How do I display my Lareta.net updates? =

Add a Widget in your layout.
Call the meulareta() function from your sidebar template or wherever else you want to include your recent Lareta.net status updates (tweets).

= How do I style my tweets using CSS? =

Example CSS code is included in example.css, but this plugin load it. If you wanto to customize it just modify this file.