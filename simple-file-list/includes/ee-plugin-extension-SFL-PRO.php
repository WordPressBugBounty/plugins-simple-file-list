<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeSFL' )) exit('ERROR 98 - SFLM Info'); // Exit if nonce fails

$eeOutput .= '<div class="eeSettingsTile">

<h2>' . __('Add More Features', 'simple-file-list') . '</h2>

<p><strong>' . __('Upgrade to Simple File List Pro and enjoy more features and functionality.', 'simple-file-list') . ' ' . __('Pro is also extendable in more ways.', 'simple-file-list') . '</strong></p>

<img src="' . $eeSFL_BASE->eeEnvironment['pluginURL'] . 'images/SFL-Pro-Admin-List.jpg" width="400" height="510" class="eeFloatRight" alt="Screen Shot" />

<p>' . __('The Pro version adds new features and is further extendable.', 'simple-file-list') . '</p>
<p>' . __('Cost is just once per domain. This includes all first-level sub-domains, plus a separate staging domain. There are no recurring fees.', 'simple-file-list') . '</p>

<h2>Pro Features</h2>

<ul>
	<li>' . __('Create folders and unlimited levels of sub-folders.', 'simple-file-list') . '</li>
	<li>' . __('Use the shortcode to display specific folders:', 'simple-file-list') . '<br />
	<code>[eeSFL folder="Folder-A"]</code><br />
	<code>[eeSFL folder="Folder-A/Folder-B"]</code></li>
	<li>' . __('Display different folders in different places on your site.', 'simple-file-list') . '
	<li>' . __('Breadcrumb navigation indicates where you are.', 'simple-file-list') . '</li>
	<li>' . __('You can even show multiple folders on the same page', 'simple-file-list') . '<a target="_blank" href="https://demo.simple-file-list.com/show-multiple-folders-on-a-single-page/">*</a></li>
	<li>' . __('Front-side users cannot navigate above the folder you specify.', 'simple-file-list') . '</li>
	<li>' . __('Sort folders first or sort along with the files.', 'simple-file-list') . '</li>
	<li>' . __('Display folder sizes and the count of items within.', 'simple-file-list') . '</li>
	<li>' . __('Optionally define a custom directory for your file list.', 'simple-file-list') . '*</li>
	<li>' . __('Bulk file editing allows you to download, move, delete or add descriptions to many files or folders at once.', 'simple-file-list') . '</li>
	<li>' . __('Edit file dates. Change the date added or the modification date of any file or folder.', 'simple-file-list') . '</li>
	<li>' . __('Allow front-end users to download entire folders, or multiple files or folders at once as a zip file.', 'simple-file-list') . '</li>
	<li>' . __('Use the Shortcode Builder to create custom snippets for secondary file list location.', 'simple-file-list') . '</li>
	<li>' . __('A Tools Tab allows you to reset settings, the file list array and delete orphaned thumbnails.', 'simple-file-list') . '</li>
	<li>' . __('Updating Pro to newer versions works just like the free plugin.', 'simple-file-list') . '</li>
</ul>

<h2>' . __('Pro is More Extendable', 'simple-file-list') . '</h2>

<p>' . __('Pro extensions to give you even more features:', 'simple-file-list') . '</p>

<ul>
	<li><a href="https://simplefilelist.com/file-access-manager/?pr=free" target="_blank">' . __('File Access Manager', 'simple-file-list') . '</a><br />' .
	__('Create additional file lists, each with its own directory, settings and access restrictions.', 'simple-file-list') . ' ' .
	__( 'Limit list or file access by WordPress user or role.', 'simple-file-list') . '</li>
	<li><a href="https://simplefilelist.com/add-search-pagination/?pr=free" target="_blank">' . __('Search and Pagination', 'simple-file-list') . '</a><br />' .
	__('Search for files by name, description, date range or file owner.', 'simple-file-list') . ' ' .
	__('Add pagination to break up large file lists into smaller sections.', 'simple-file-list') . '</li>
	<li><a href="https://simplefilelist.com/send-files-by-email/?pr=free" target="_blank">' . __('Send Files by Email', 'simple-file-list') . '</a><br />' .
	__('Send an email with links to your files. Send to multiple recipients and CC more.', 'simple-file-list') . '</li>
</ul>


<br class="eeClear" />

<p class="eeCentered"><a class="button" target="_blank" href="https://get.simplefilelist.com/index.php?eeDomain=' . $eeSFL_BASE->eeEnvironment['wpSiteURL'] . '&eeProduct=ee-simple-file-list-pro">' . __('Upgrade to Pro', 'simple-file-list') . '</a>
<a class="button" target="_blank" href="https://simplefilelist.com/">' . __('More Information', 'simple-file-list') . '</a></p>

</div>';

?>