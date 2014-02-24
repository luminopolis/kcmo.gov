<?php

/*
Plugin Name: WPMS Site Maintenance Mode
Plugin URI: http://wordpress.org/extend/plugins/wpms-site-maintenance-mode/
Description: Provides an interface to make a WPMS network unavailable to everyone during maintenance, except the admin.
Original Author: I.T. Damager
Author: 7 Media Web Solutions, LLC
Author URI: http://www.7mediaws.org/
Version: 1.0.3
License: GPL

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
*/

class wpms_sitemaint {

	var $sitemaint;
	var $retryafter;
	var $message;

	function wpms_sitemaint() {
		add_action('init',array(&$this,'wpms_sitemaint_init'),1);
		add_action('network_admin_menu',array(&$this,'add_admin_subpanel'));
	}

	function wpms_sitemaint_init() {
		$this->apply_settings();
		if ($this->sitemaint) return $this->shutdown();
	}

	function add_admin_subpanel() {
		add_submenu_page('settings.php', __('WPMS Site Shutdown'), __('WPMS Sitedown'), 'manage_network_options', 'wpms_site_maint', array(&$this,'adminpage'));
	}

	function set_defaults() {
		// do not edit here - use the admin screen
		$this->sitemaint = 0;
		$this->retryafter = 60;
		$this->message = '<!doctype html>
			<html lang="en">
			    <head>
			        <meta charset="utf-8">
			        <title>Coming Soon - All New KCMO.gov</title>

			        <meta name="viewport" content="width=device-width, initial-scale=1">
			        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

			        <!-- Custom font from Google Web Fonts -->
			        <link href="//fonts.googleapis.com/css?family=PT+Sans:700,400&subset=cyrillic" rel="stylesheet">

			        <!-- Bootstrap stylesheets -->
			        <link href="http://luminopolis.github.io/kcmo.gov/css/bootstrap.min.css" rel="stylesheet">

			        <!-- Template stylesheet -->
			        <link href="http://luminopolis.github.io/kcmo.gov/css/sunrise.css" rel="stylesheet">
			    </head>

			    <body>
			        <div class="container">

			            <h1 class="page-heading">KCMO.gov is launching soon</h1>

			            <p class="description">Coming 01.10.14. An all new city website.<br>Built using open source. Of, by, and for the people.</p>
			            <div id="countdown" class="countdown">

			                <div class="countdown-item">
			                    <div class="countdown-number countdown-days"></div>
			                    <div class="countdown-text">days</div>
			                </div>

			                <div class="countdown-item">
			                    <div class="countdown-number countdown-hours"></div>
			                    <div class="countdown-text">hours</div>
			                </div>

			                <div class="countdown-item">
			                    <div class="countdown-number countdown-minutes"></div>
			                    <div class="countdown-text">minutes</div>
			                </div>

			                <div class="countdown-item">
			                    <div class="countdown-number countdown-seconds"></div>
			                    <div class="countdown-text">seconds</div>
			                </div>
			            </div>

			        </div>

			        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
			        <script>window.jQuery || document.write(\'<script src="js/jquery.min.js"><\/script>\')</script>
			        <script src="http://luminopolis.github.io/kcmo.gov/js/jquery.countdown.js"></script>
			        <script type="text/javascript" charset="utf-8">
			        var config = {

			            countdown: {
			                year: 2014,
			                month: 01,
			                day: 10,
			                hours: 15,
			                minutes: 00,
			                seconds: 00
			            },
			        }
			        </script>
			        <script type="text/javascript" charset="utf-8">
			        $(function() {
			            var date = new Date(config.countdown.year,
			                                config.countdown.month - 1,
			                                config.countdown.day,
			                                config.countdown.hours,
			                                config.countdown.minutes,
			                                config.countdown.seconds),
			                $body = $("body"),
			                $countdown = $("#countdown");

			            $countdown.countdown(date, function(event) {
			                if (event.type == "finished") {
			                    $countdown.fadeOut();
			                } else {
			                    $(".countdown-" + event.type, $countdown).text(event.value);
			                }
			            });

			        });
			        </script>
			    </body>
			</html>';
	}

	function apply_settings($settings = false) {
		if (!$settings) $settings = get_site_option('wpms_sitemaint_settings');
		if (is_array($settings)) foreach($settings as $setting => $value) $this->$setting = $value;
		else $this->set_defaults();
	}

	function save_settings() {
		global $wpdb, $updated, $configerror;
		
		//dbug( $_REQUEST );
		
		//check_admin_referer();
		
		//ddbug();
		
		// validate all input!
		if (preg_match('/^[0-9]+$/',$_POST['sitemaint']))
			$sitemaint = intval($_POST['sitemaint']);
		else $configerror[] = 'sitemaint must be numeric. Default: 0 (Normal site operation)';

		if ($_POST['retryafter']>0)
			$retryafter = intval($_POST['retryafter']);
		else $configerror[] = 'Retry After must be greater than zero minutes. Default: 60';
		
		//$wpdb->escape() or addslashes not needed -- string is compacted into an array then serialized before saving in db
		if (trim($_POST['message'])) $message = (get_magic_quotes_gpc()) ? stripslashes(trim($_POST['message'])) : trim($_POST['message']);
		else $configerror[] = 'Please enter a message to display to visitors when the site is down. (HTML OK!)';

		if (is_array($configerror)) return $configerror;

		$settings = compact('sitemaint','retryafter','message');
		foreach($settings as $setting => $value) if ($this->$setting != $value) $changed = true;
		if ($changed) {
			update_site_option('wpms_sitemaint_settings', $settings);
			$this->apply_settings($settings);
			return $updated = true;
		}
	}

	function delete_settings() {
		global $wpdb, $updated, $wp_object_cache;
		$settings = get_site_option('wpms_sitemaint_settings');
		if ($settings) {
			$wpdb->query("DELETE FROM $wpdb->sitemeta WHERE `meta_key` = 'wpms_sitemaint_settings'");
			if (is_object($wp_object_cache) && $wp_object_cache->cache_enabled == true) wp_cache_delete('wpms_sitemaint_settings','site-options');
			$this->set_defaults();
			return $updated = true;
		}
	}

	function urlend($end) {
		return (substr($_SERVER['REQUEST_URI'], strlen($end)*-1) == $end) ? true : false;
	}

	function shutdown() {
		global $wpdb;
		get_currentuserinfo();
		if (is_super_admin()) return; //allow admin to use site normally
		if ($wpdb->blogid == 1 && $this->urlend('wp-login.php')) return; //I told you *not* to log out, but you did anyway. duh!
		if ($this->sitemaint == 2 && $wpdb->blogid != 1) return; //user blogs on, main blog off
		if ($this->sitemaint == 1 && $wpdb->blogid == 1) return; //main blog on, user blogs off
		header('HTTP/1.1 503 Service Unavailable');
		header('Retry-After: '.$this->retryafter*60); //seconds
		if (!$this->urlend('feed/') && !$this->urlend('trackback/') && !$this->urlend('xmlrpc.php')) echo stripslashes($this->message);
		exit();
	}

	function adminpage() {
		global $updated, $configerror;
		get_currentuserinfo();
		
		if (!is_super_admin())
			die(__('<p>You do not have permission to access this page.</p>'));
		
		if ($_POST['action'] == 'update') {
			if ($_POST['reset'] != 1) $this->save_settings();
			else $this->delete_settings();
		}
		
		if ($updated) { ?>
<div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div>
<?php	} elseif (is_array($configerror)) { ?>
<div class="error"><p><?php echo implode('<br />',$configerror); ?></p></div>
<?php }
if ($this->sitemaint == 1) { ?>
  <div class="error"><p><?php _e('WARNING: YOUR USER BLOGS ARE CURRENTLY DOWN!' ); ?></p></div>
<?php }
if ($this->sitemaint == 2) { ?>
  <div class="error"><p><?php _e('WARNING: YOUR MAIN BLOG IS CURRENTLY DOWN!' ); ?></p></div>
<?php }
if ($this->sitemaint == 3) { ?>
  <div class="error"><p><?php _e('WARNING: YOUR ENTIRE SITE IS CURRENTLY DOWN!' ); ?></p></div>
<?php } ?>
<div class="wrap">
  <h2><?php _e('WPMS Site Maintenace' ); ?></h2>
  <fieldset>
  <p><?php _e('This plugin shuts down your site for maintenance by sending feed readers, bots, and browsers an http response code 503 and the Retry-After header' ); ?> (<a href="ftp://ftp.isi.edu/in-notes/rfc2616.txt" target="_blank">rfc2616</a>). <?php _e('It displays your message except when feeds, trackbacks, or other xml pages are requested.' ); ?></p>
  <p><?php _e('Choose site UP or DOWN, retry time (in minutes) and your message.' ); ?></p>
  <p><em><?php _e('The site will remain fully functional for admin users.' ); ?> <span style="color:#CC0000;"><?php _e('Do not log out while the site is down!' ); ?></span><br />
  <?php _e('If you log out (and lock yourself out of the site) visit' ); ?> <?php bloginfo_rss('home') ?>/wp-login.php <?php _e('to log back in.' ); ?></em></p>
  <form name="sitemaintform" method="post" action="">
    <p><label><input type="radio" name="sitemaint" value="0"<?php checked(0, $this->sitemaint); ?> /> <?php _e('SITE UP (Normal Operation)' ); ?></label><br />
       <label><input type="radio" name="sitemaint" value="1"<?php checked(1, $this->sitemaint); ?> /> <?php _e('USER BLOGS DOWN, MAIN BLOG UP!' ); ?></label><br />
       <label><input type="radio" name="sitemaint" value="2"<?php checked(2, $this->sitemaint); ?> /> <?php _e('MAIN BLOG DOWN, USER BLOGS UP!' ); ?></label><br />
       <label><input type="radio" name="sitemaint" value="3"<?php checked(3, $this->sitemaint); ?> /> <?php _e('ENTIRE SITE DOWN!' ); ?></label></p>
    <p><label><?php _e('Retry After' ); ?> <input name="retryafter" type="text" id="retryafter" value="<?php echo $this->retryafter; ?>" size="3" /> <?php _e('minutes.' ); ?></label></p>
    <p><label><?php _e('HTML page displayed to site visitors:' ); ?><br />
      <textarea name="message" cols="125" rows="10" id="message"><?php echo stripslashes($this->message); ?></textarea></label></p>
	<p>&nbsp;</p>
	<p><label><input name="reset" type="checkbox" value="1" /> <?php _e('Reset all settings to default' ); ?></label></p>
    <p class="submit">
      <input name="action" type="hidden" id="action" value="update" />
      <input type="submit" name="Submit" value="Update Settings" />
    </p>
  </form>
  </fieldset>
</div>
<?php
	}
}

//begin execution
if (defined('ABSPATH')) $wpms_sitemaint = new wpms_sitemaint();