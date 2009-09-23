<?php
/*
Plugin Name: dm4extensions
Plugin URI: http://www.honza.info/category/wordpress/
Description: Automatically updates hits for WP Plugin downloads and themes in download managers WP-DownloadManager and WordPress Download Monitor, based on hits reported by <a href="http://wordpress.org/extend/" target="_blank">wordpress.org</a> website. Needs one of these two download managers to be installed in your WP site.
Version: 1.0
Author: Honza Skýpala
Author URI: http://www.honza.info
*/

function dm4extensions_do_all() {
  $current_plugins = get_option('active_plugins');
  foreach ($current_plugins as $plugin) {
  	if ($plugin == 'download-monitor/wp-download_monitor.php') {
  		dm4extensions_wordpress_download_monitor();
  	} elseif ($plugin == 'wp-downloadmanager/wp-downloadmanager.php') {
  		dm4extensions_wp_downloadmanager();
  	}
  }
}

function dm4extensions_wordpress_download_monitor() {
	dm4extensions_update("plugins", "DLM_DOWNLOADS", "id", "filename", "hits", "dlversion");
	dm4extensions_update("themes",  "DLM_DOWNLOADS", "id", "filename", "hits", "dlversion");
}

function dm4extensions_wp_downloadmanager() {
	dm4extensions_update("plugins", "downloads", "file_id", "file", "file_hits");
	dm4extensions_update("themes",  "downloads", "file_id", "file", "file_hits");
}

function dm4extensions_update($types, $table_name, $id, $filename, $hits, $version='') {
	if ($types == 'plugins') {
		$like_pattern = 'http://downloads.wordpress.org/plugin/%.zip';
		$preg_pattern = 'http://downloads\.wordpress\.org/plugin/([a-zA-Z0-9_]+)(\.([0-9\.]+)|)\.zip';
	} elseif ($types == 'themes') {
		$like_pattern = 'http://wordpress.org/extend/themes/download/%.zip';
		$preg_pattern = 'http://wordpress\.org/extend/themes/download/([a-zA-Z0-9_]+)(\.([0-9\.]+)|)\.zip';
	} else {
		return;
	}
	global $wpdb,  $table_prefix;
	$wp_table = $table_prefix . $table_name;
	$extensions = $wpdb->get_results("SELECT $id as id, $filename as filename, $hits as hits" . ($version == "" ? "" : ", $version as version") . " FROM $wp_table WHERE $filename LIKE '$like_pattern'");
	if ($extensions) {
		foreach ($extensions as $extension) {
			if (preg_match("@$preg_pattern@i", $extension->filename, $matches)) {
				$extension_details = dm4extensions_get_plugin_details($matches[1], $types);
				if (isset($extension_details['hits']) && $extension_details['hits'] != $extension->hits) {
					$wpdb->query("UPDATE $wp_table SET $hits=" . $extension_details['hits'] . " WHERE $id=$extension->id");
				}
				if ($version != "" && isset($extension_details['version']) && $extension_details['version'] != $extension->version) {
					$wpdb->query("UPDATE $wp_table SET $version=" . $extension_details['version'] . " WHERE $id=$extension->id");
				}
			}
		}
	}
}

function dm4extensions_get_plugin_details($name, $types) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://wordpress.org/extend/$types/$name/stats/");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	$result = curl_exec($ch);
	curl_close($ch);
	
	$details = array();
	if (preg_match('|<th scope="row">All Time</th>.*\n.*<td>([0-9,]+)</td>|i', $result, $matches)) {
		$details['hits'] = intval(str_replace(',', '', $matches[1]));
	}
	if (preg_match('|<li><strong>Version:</strong>[ ]*([0-9\.]+)</li>|i', $result, $matches)) {
		$details['version'] = $matches[1];
	}
	return $details;
}

register_activation_hook(__FILE__, 'dm4extensions_activation');
add_action('dm4extensions_hourly_event', 'dm4extensions_do_all');

function dm4extensions_activation() {
	wp_schedule_event(time(), 'hourly', 'dm4extensions_hourly_event');
}

register_deactivation_hook(__FILE__, 'dm4extensions_deactivation');

function dm4extensions_deactivation() {
	wp_clear_scheduled_hook('dm4extensions_hourly_event');
}
?>