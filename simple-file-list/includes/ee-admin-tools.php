<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// Simple File List - Copyright 2026
// Author: Mitchell Bennis | support@simplefilelist.com | https://simplefilelist.com
// License: GPLv2 or later | https://www.gnu.org/licenses/gpl-2.0.html

eeSFL_Debug_Log("Loading Admin Tools ...", 'Tools', $eeSFL->eeListID);

// Check if we're being loaded as a main tab (not subtab)
$is_main_tab = (isset($_GET['tab']) && $_GET['tab'] === 'tools'); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation param, not form submission

// If Tools tab is requested and Tools plugin is active, redirect to Tools
if ($is_main_tab && defined('eeSFLu_Version')) {
	wp_safe_redirect(admin_url('admin.php?page=ee-simple-file-list-tools'));
	exit;
}

if ($is_main_tab) {
     $eeOutput .= '<section class="eeSFL_Settings">';
}

// User Messaging
 $eeOutput .= $eeSFL->eeSFL_ResultsNotification();

// Build download URL with tracking parameters
$admin_email = get_option('admin_email');
$site_url = get_site_url();
$download_url = 'https://downloads.simplefilelist.com/?file=ee-simple-file-list-tools&pin=x79syg';
$download_url .= '&email=' . urlencode($admin_email);
$download_url .= '&site=' . urlencode($site_url);

 $eeOutput .= '

<div class="eeColInline eeSettingsTile">
	<h1>' . __('Admin Tools', 'simple-file-list') . '</h1>
</div>

<div class="eeColumns">

<div class="eeColLeft">

<div class="eeSettingsTile">
<h2>' . __('Simple File List Tools Plugin', 'simple-file-list') . '</h2>

<fieldset>

<legend>' . __('A free admin toolkit for Simple File List', 'simple-file-list') . '</legend>

<p>' . __('The Simple File List Tools plugin provides professional debugging tools, custom tweaks system, and administrative utilities to enhance your development experience.', 'simple-file-list') . '</p>

<p><strong>' . __('Key Features:', 'simple-file-list') . '</strong></p>
<ul style="margin-left: 20px; line-height: 1.8;">
	<li>Professional debug console with frontend/backend controls</li>
	<li>Custom tweaks system - add PHP, CSS & JS without editing core files</li>
	<li>List management tools - reset arrays, settings, delete orphaned thumbnails</li>
	<li>Auto-delete old files by type and age</li>
	<li>WordPress & SFL debug log file viewers</li>
	<li>Browser console logging and error type filtering</li>
</ul>

<div class="eeNote">' . __('Completely free for all Simple File List admins.', 'simple-file-list') . '</div>

<p style="margin-top: 20px;">
	<a href="' . esc_url($download_url) . '" class="button button-primary button-large">
		' . __('Download Free Tools Plugin', 'simple-file-list') . '
	</a>
</p>

</fieldset>

</div>

</div>

<div class="eeColRight">

<div class="eeSettingsTile">
<h2>' . __('Installation', 'simple-file-list') . '</h2>

<fieldset>

<legend>' . __('Easy setup in just a few steps', 'simple-file-list') . '</legend>

<ol style="line-height: 2;">
	<li>Download the plugin ZIP file using the button on the left</li>
	<li>Go to <strong>Plugins &rarr; Add New &rarr; Upload Plugin</strong></li>
	<li>Choose the downloaded ZIP file and click Install Now</li>
	<li>Activate the plugin</li>
	<li>Access the tools at <strong>File List &rarr; File List Tools</strong></li>
</ol>

<div class="eeNote">' . __('The plugin integrates seamlessly using WordPress hooks - no modifications to core files required.', 'simple-file-list') . '</div>

</fieldset>

</div>

<div class="eeSettingsTile">
<h2>' . __('What\'s Included', 'simple-file-list') . '</h2>

<fieldset>

<legend>' . __('Six comprehensive tabs with powerful features', 'simple-file-list') . '</legend>

<ul style="line-height: 1.8;">
	<li><strong>Debugging</strong> - Console controls, error filtering, log file access</li>
	<li><strong>Plugin Information</strong> - Complete system overview</li>
	<li><strong>List Tools</strong> - Reset arrays, manage settings, clean thumbnails</li>
	<li><strong>Tweaks</strong> - Custom code management (3 examples included)</li>
	<li><strong>Auto-Delete</strong> - Automated file cleanup</li>
	<li><strong>Clean Install</strong> - Reset SFL to fresh state</li>
</ul>

</fieldset>

</div>

</div>

</div>

';

// Close the wrapper if we opened it for main tab
if ($is_main_tab) {
     $eeOutput .= '</section>';
}


?>
