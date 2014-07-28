=== dm4extensions ===
Contributors: honza.skypala
Donate link: http://www.honza.info
Tags: download manager, download monitor, WordPress, plugin, theme
Requires at least: 2.8
Tested up to: 3.9.1
Stable tag: 1.2

This is add-on for two most favorite download manager plug-ins for WordPress, i.e. <a href="http://wordpress.org/extend/plugins/wp-downloadmanager/" target="_blank">WP-DownloadManager</a> and <a href="http://wordpress.org/extend/plugins/download-monitor/" target="_blank">WordPress Download Monitor</a>. If you have your own WP plug-in or theme and you host it on wordpress.org site and also list it in your blog in your favorite download manager, then this plug-in automatically updates hits on these files based on information provided by statistics retrieved from wordpress.org site.


== Description ==

Let's make sure that these are the starting conditions:

1.	You have developed your own WordPress plugin or theme and you host it at <a href="http://wordpress.org/extend/" target="_blank">WordPress.org</a>
2.	You have your own WordPress blog and in there you use either <a href="http://wordpress.org/extend/plugins/wp-downloadmanager/" target="_blank">WP-DownloadManager</a> plugin or <a href="http://wordpress.org/extend/plugins/download-monitor/" target="_blank">WordPress Download Monitor</a> plugin
3.	You list your own plug-in or theme in one of the download managers mentioned in point 2 and it links to URL at WordPress.org

OK, these are the conditions. All is fine, everything works, visitors of your web can click on the download link and they are served the file from wordpress.org and your download manager counts the hits on the files. But maybe you would like it not to show only the hits that people do on your own blog, but all the hits that your plugin or theme gets, including the ones when people find the download directly on WordPress.org site, not just through your blog.

This plugin solves it. It runs in the background, it checks if you are using one of the supported download managers, it goes through the downloads registered, and if it finds a download that has URL pointing to plugins or themes at WordPress.org, it will automatically update the hits from there to your download manager.

All is done automatically in the background. The functionality is run in the background by WordPress built-in cron. The plugin has no configuration. If you don't have any download manager installed or you do not list any download pointing to plugin or theme at WordPress.org, this plugin will simply "do nothing", but it will not damage your site.


== Installation ==

English:

1.	Well, you should have either <a href="http://wordpress.org/extend/plugins/wp-downloadmanager/" target="_blank">WP-DownloadManager</a> or <a href="http://wordpress.org/extend/plugins/download-monitor/" target="_blank">WordPress Download Monitor</a> running in your WordPress installation and you should list at least one WordPress.org hosted plugin or theme in your downloads. My plugin dm4extensions will not hurt your WP site if this is not true, but it would just not do anything in such case.
2.	Upload the full plugin directory into your wp-content/plugins directory.
3.	Activate the plugin in plugins administration.
4.	That's it, the plugin runs automatically in the background via cron. There is no configuration of the plugin.


== Screenshots ==

1. WP-DownloadManager
2. WordPress Download Monitor


== Changelog ==

= 1.2 =
* rewritten as class, additional code cleaning
* updated to the current structure of wordpress.org web pages, so the valid info is extracted again
* updated to the new structure of Download Monitor plugin
= 1.1 =
* bugfix - did not work with plugins containing "-" in its URL
= 1.0 =
* Initial release.


== Licence ==

WTFPL License 2.0 applies

<code>           DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
                   Version 2, December 2004

Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>

Everyone is permitted to copy and distribute verbatim or modified
copies of this license document, and changing it is allowed as long
as the name is changed.

           DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
  TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

 0. You just DO WHAT THE FUCK YOU WANT TO.</code>