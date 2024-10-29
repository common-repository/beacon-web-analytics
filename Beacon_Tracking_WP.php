<?php

/**
 * Plugin Name: Veracity Bot Detection Tag
 * Plugin URI: https://www.thisisbeacon.com/
 * Description: This plugin enables the Veracity Trust Network Bot Detection Tag to run on your site, this information will not be saved unless you have a Veracity account.
 * Version: 3.0.2
 * Author: BeaconSoft Limited
 * Author URI: https://www.thisisbeacon.com
 * License: BSD New
 */

defined('ABSPATH') or die('This file is secured');

// add the action button to the menu
function test_plugin_setup_menu()
{
	add_menu_page('Veracity Insights', 'Veracity Insights', 'manage_options', 'bwai-plugin', 'renderAdminPage');
}

// handle drawing the admin page
function renderAdminPage()
{
	$beta_nonce = wp_create_nonce("handle_bwai_beta_tracking_checkbox_nonce");
	$beta_ajax_url = admin_url('admin-ajax.php?action=handle_bwai_beta_tracking_checkbox&nonce=' . $beta_nonce);

	$admin_nonce = wp_create_nonce("handle_bwai_admin_tracking_checkbox_nonce");
	$admin_ajax_url = admin_url('admin-ajax.php?action=handle_bwai_admin_tracking_checkbox&nonce=' . $admin_nonce);

	$dir = dirname(__FILE__);
	include("{$dir}/views/admin.php");
}

// update the beta option and return the stat we have updated it to
function bwai_beta_tracking_checkbox($state)
{
	update_option("bwai_beta", $state);
	return $state;
}

// update the admin tracker option and return the state we have updated it to
function bwai_admin_tracking_checkbox($state)
{
	update_option("bwai_admin_tracking", $state);
	return $state;
}

// ajax handler for enabling/disabling the beta mode
function handle_bwai_beta_tracking_checkbox()
{
	$updateState = $_REQUEST['newState'];
	$newState = bwai_beta_tracking_checkbox($updateState);
	return '{"newState":' . ($newState ? "true" : "false") . '}';
}

// ajax handler for enabling/disabling the admin user tracking mode
function handle_bwai_admin_tracking_checkbox()
{
	$updateState = $_REQUEST['newState'];
	$newState = bwai_admin_tracking_checkbox($updateState);
	return '{"newState":' . ($newState ? "true" : "false") . '}';
}

// create the action that adds the menu button to the wordpress admin section
add_action('admin_menu', 'test_plugin_setup_menu');
// create the action for updating the beta mode
add_action("wp_ajax_handle_bwai_beta_tracking_checkbox", "handle_bwai_beta_tracking_checkbox");
// create the action for updating the admin tracking mode
add_action("wp_ajax_handle_bwai_admin_tracking_checkbox", "handle_bwai_admin_tracking_checkbox");

// create the action for working out and displaying if it should the tracking code
add_action('wp_footer', function () {
	$display = true;
	$allowAdminTracking = get_option('bwai_admin_tracking', false);
	$beta = get_option('bwai_beta', false);

	// are we allowing admin users to trigger tracking 
	// if we note check the user is an admin if so disable the output
	if ($allowAdminTracking == false && current_user_can('administrator')) {
		$display = false;
	}

	// are we on a wordpress backend page if so disable the tracker
	if (is_admin()) {
		$display = false;
	}

	// are we still good to output the code to enable the tracker
	if ($display) { ?>
		<script type="text/javascript">
			window.veracity = window.bwai = {
				"formIntegration": false
			};
			(function(a, d, w) {
				var h = d.getElementsByTagName(a[0])[0];
				for (let i = 0; i < a[3].length; i++) {
					var s = d.createElement(a[1]);
					s.type = a[2];
					s.src = a[3][i].c;
					s.setAttribute("integrity", a[3][i].i);
					s.setAttribute("crossorigin", "anonymous");
					h.appendChild(s);
				}
			})(['head', 'script', 'text/javascript', [{
				"i": 'sha512-RZaUPcYG+YVW5gmPp0sxoO+dxSsQeNkwMxGlRIkEbZZhPgYqqzCIW6HD8j7SGFwUgPkzVB42NhAlYUdkx9Y6XQ==',
				"c": '//script.platform.veracitytrustnetwork.com/loader/index.js'
			}, {
				"i": 'sha512-13GUu5vadQ2mQwPDwj2jREx1SnYNp0dg5UoI3RN+8Vo+AGFMnmmibbqC4XKnkxHpyK45GtYA2tPzuQJQQHvGgg==',
				"c": '//script.thisisbeacon.com/BLoader/1.1/index.js'
			}]], document, window)
		</script>
<?php }
});
