<?php
/*
Plugin Name: dm4extensions
Plugin URI: http://wordpress.org/plugins/dm4extensions/
Description: Automatically updates hits for WP Plugin downloads and themes in download managers WP-DownloadManager and WordPress Download Monitor, based on hits reported by <a href="http://wordpress.org/" target="_blank">wordpress.org</a> website. Needs one of these two download managers to be installed in your WP site.
Version: 1.2
Author: Honza Skýpala
Author URI: http://www.honza.info
*/

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

class dm4extensions {
  const version = "1.2";
  
  const plugin_preg_pattern = '@http://downloads\.wordpress\.org/plugin/([a-zA-Z0-9_\-]+)(\.([0-9\.]+)|)\.zip@i';
  const theme_preg_pattern  = '@http://wordpress\.org/themes/download/([a-zA-Z0-9_\-]+)(\.([0-9\.]+)|)\.zip@i';
  
  const plugin_like_pattern = 'http://downloads.wordpress.org/plugin/%.zip';
  const theme_like_pattern  = 'http://wordpress.org/themes/download/%.zip';

  public function __construct() {
    register_activation_hook(__FILE__, array($this, 'activate'));
    register_deactivation_hook(__FILE__, array($this, 'deactivate'));

    add_action('dm4extensions_hourly_event', array($this, 'do_all'));
//  add_action('admin_init', array($this, 'do_all'));  // for debugging-only, not waiting for the scheduled event
  }

  function do_all() {
    $current_plugins = get_option('active_plugins');
    foreach ($current_plugins as $plugin) {
    	if ($plugin == 'download-monitor/download-monitor.php') {
    		$this->wordpress_download_monitor();
    	} elseif ($plugin == 'wp-downloadmanager/wp-downloadmanager.php') {
    		$this->wp_downloadmanager();
    	}
    }
  }

  function wordpress_download_monitor() {
    $downloads = get_posts(array('post_type' => 'dlm_download_version'));
    foreach ($downloads as $download) {
      $files = json_decode(get_post_meta($download->ID, '_files', true));
      foreach ($files as $file) {
        if (preg_match($this::plugin_preg_pattern, $file, $matches)) {
          $this->download_monitor_update_single($matches[1], "plugins", $download);
  			} elseif (preg_match($this::theme_preg_pattern, $file, $matches)) {
          $this->download_monitor_update_single($matches[1], "themes", $download);
        }
      }
    }
  }
  
  function download_monitor_update_single($extension, $types, $download) {
		$details = $this->get_plugin_details($extension, $types);
		if (isset($details['hits']))
		  update_post_meta($download->ID, '_download_count', $details['hits']);
		if (isset($details['version']))
		  update_post_meta($download->ID, '_version', $details['version']);
		$parents = get_post_ancestors($download->ID);
		foreach ($parents as $parent) {
		  $total_hits = 0;
		  $children = get_children(array('post_parent' => $parent, 'post_type' => 'dlm_download_version'));
		  foreach ($children as $child) {
		    $total_hits += get_post_meta($child->ID, '_download_count', true);
		  }
		  update_post_meta($parent, '_download_count', $total_hits);
      if (isset($details['version']))
		    update_post_meta($parent, '_version', $details['version']);		}
  }

  function wp_downloadmanager() {
  	$this->update("plugins", "downloads", "file_id", "file", "file_hits");
  	$this->update("themes",  "downloads", "file_id", "file", "file_hits");
  }

  function update($types, $table_name, $id, $filename, $hits, $version='') {
  	if ($types == 'plugins') {
  		$like_pattern = $this::plugin_like_pattern;
  		$preg_pattern = $this::plugin_preg_pattern;
  	} elseif ($types == 'themes') {
  		$like_pattern = $this::theme_like_pattern;
  		$preg_pattern = $this::theme_preg_pattern;
  	} else {
  		return;
  	}
  	global $wpdb,  $table_prefix;
  	$wp_table = $table_prefix . $table_name;
  	$extensions = $wpdb->get_results("SELECT $id as id, $filename as filename, $hits as hits" . ($version == "" ? "" : ", $version as version") . " FROM $wp_table WHERE $filename LIKE '$like_pattern'");
  	if ($extensions) {
  		foreach ($extensions as $extension) {
  			if (preg_match($preg_pattern, $extension->filename, $matches)) {
  				$extension_details = $this->get_plugin_details($matches[1], $types);
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

  function get_plugin_details($name, $types) {
    $result = wp_remote_fopen("http://wordpress.org/$types/$name/stats/");
  	$details = array();
  	if (preg_match('|<th scope="row">All Time</th>.*\n.*<td>([0-9,]+)</td>|i', $result, $matches)) {
  		$details['hits'] = intval(str_replace(',', '', $matches[1]));
  	}
  	if (preg_match('|Download Version ([0-9\.]+)|i', $result, $matches)) {
  		$details['version'] = $matches[1];
  	}
  	return $details;
  }

  function activate() {
  	wp_schedule_event(time(), 'hourly', 'dm4extensions_hourly_event');
  }

  function deactivate() {
  	wp_clear_scheduled_hook('dm4extensions_hourly_event');
  }

}

$wp_dm4extensions = new dm4extensions();
?>