<?php // Simple File List Script: ee-functions.php | Author: Mitchell Bennis | support@simplefilelist.com

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! wp_verify_nonce( $eeSFL_Nonce, 'eeSFL_Functions' ) ) exit('ERROR 98'); // Exit if nonce fails



// NEW - WP FileSystem Interface
/**
 * WordPress Filesystem Interface Function
 *
 * This function provides a unified interface to WordPress filesystem operations,
 * replacing native PHP filesystem functions with WordPress-compliant alternatives.
 * Uses WP_Filesystem API for proper security, permissions, and hosting compatibility.
 *
 * @param string $mode The filesystem operation to perform
 * @param array $params Parameters for the operation (varies by mode)
 * @return array Returns array with 'success' boolean and 'data' or 'error'
 *
 * Supported operations:
 *
 * copy - Copy a file
 *   eeSFL_BASE_FileSystem('copy', array('from' => '/source/path', 'to' => '/dest/path', 'overwrite' => true))
 *
 * move - Move/rename a file
 *   eeSFL_BASE_FileSystem('move', array('from' => '/source/path', 'to' => '/dest/path', 'overwrite' => true))
 *
 * delete - Delete a file or directory
 *   eeSFL_BASE_FileSystem('delete', array('file' => '/path/to/file'))
 *
 * exists - Check if file or directory exists
 *   eeSFL_BASE_FileSystem('exists', array('file' => '/path/to/check'))
 *
 * is_file - Check if path is a file
 *   eeSFL_BASE_FileSystem('is_file', array('file' => '/path/to/check'))
 *
 * is_dir - Check if path is a directory
 *   eeSFL_BASE_FileSystem('is_dir', array('path' => '/path/to/check'))
 *
 * mkdir - Create directory
 *   eeSFL_BASE_FileSystem('mkdir', array('path' => '/path/dir/', 'chmod' => 0755))
 *
 * get_contents - Read file contents
 *   eeSFL_BASE_FileSystem('get_contents', array('file' => '/path/to/file'))
 *
 * put_contents - Write file contents
 *   eeSFL_BASE_FileSystem('put_contents', array('file' => '/path/to/file', 'data' => 'content', 'mode' => 0644))
 *
 * dirlist - List directory contents
 *   eeSFL_BASE_FileSystem('dirlist', array('path' => '/path/dir/', 'include_hidden' => false, 'recursive' => false))
 *
 * filesize - Get file size in bytes
 *   eeSFL_BASE_FileSystem('filesize', array('file' => '/path/to/file'))
 *
 * filemtime - Get file modification time (Unix timestamp)
 *   eeSFL_BASE_FileSystem('filemtime', array('file' => '/path/to/file'))
 *
 * touch - Update file access and modification times (creates file if it doesn't exist)
 *   eeSFL_BASE_FileSystem('touch', array('file' => '/path/to/file', 'time' => 1234567890, 'atime' => 1234567890))
 */
function eeSFL_BASE_FileSystem($mode, $params = array()) {

	// Initialize WP_Filesystem
    if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if ( ! WP_Filesystem() ) {
        return array('success' => false, 'error' => 'Cannot initialize WordPress filesystem');
    }

    global $wp_filesystem;

    switch($mode) {
        case 'copy':
            // Params: 'from' (string), 'to' (string), 'overwrite' (bool, optional, default: true)
            return array(
                'success' => $wp_filesystem->copy($params['from'], $params['to'], $params['overwrite'] ?? true),
                'data' => null
            );

        case 'move':
            // Params: 'from' (string), 'to' (string), 'overwrite' (bool, optional, default: true)
            return array(
                'success' => $wp_filesystem->move($params['from'], $params['to'], $params['overwrite'] ?? true),
                'data' => null
            );

        case 'delete':
            // Params: 'file' (string), 'recursive' (bool, optional, default: false)
            // WordPress delete() handles both files and directories
            $recursive = isset($params['recursive']) ? $params['recursive'] : true; // Default to true for directories
            return array(
                'success' => $wp_filesystem->delete($params['file'], $recursive),
                'data' => null
            );

        case 'exists':
            // Params: 'file' (string) - can be file or directory path
            return array(
                'success' => true,
                'data' => $wp_filesystem->exists($params['file'])
            );

        case 'is_file':
            // Params: 'file' (string)
            return array(
                'success' => true,
                'data' => $wp_filesystem->is_file($params['file'])
            );

        case 'is_dir':
            // Params: 'path' (string)
            return array(
                'success' => true,
                'data' => $wp_filesystem->is_dir($params['path'])
            );

        case 'mkdir':
            // Params: 'path' (string), 'chmod' (octal, optional, default: FS_CHMOD_DIR)
            return array(
                'success' => $wp_filesystem->mkdir($params['path'], $params['chmod'] ?? FS_CHMOD_DIR),
                'data' => null
            );

        case 'get_contents':
            // Params: 'file' (string)
            return array(
                'success' => true,
                'data' => $wp_filesystem->get_contents($params['file'])
            );

        case 'put_contents':
            // Params: 'file' (string), 'data' (string), 'mode' (octal, optional, default: FS_CHMOD_FILE)
            return array(
                'success' => $wp_filesystem->put_contents($params['file'], $params['data'], $params['mode'] ?? FS_CHMOD_FILE),
                'data' => null
            );

        case 'dirlist':
            // Params: 'path' (string), 'include_hidden' (bool, optional, default: false), 'recursive' (bool, optional, default: false)
            return array(
                'success' => true,
                'data' => $wp_filesystem->dirlist($params['path'], $params['include_hidden'] ?? false, $params['recursive'] ?? false)
            );

        case 'filesize':
            // Params: 'file' (string)
            return array(
                'success' => true,
                'data' => $wp_filesystem->size($params['file'])
            );

        case 'filemtime':
            // Params: 'file' (string)
            return array(
                'success' => true,
                'data' => $wp_filesystem->mtime($params['file'])
            );

        case 'touch':
            // Params: 'file' (string), 'time' (int, optional Unix timestamp), 'atime' (int, optional Unix timestamp)
            // WordPress filesystem doesn't have a native touch() method, so we implement it
            if (!$wp_filesystem->exists($params['file'])) {
                // Create empty file if it doesn't exist
                $result = $wp_filesystem->put_contents($params['file'], '', FS_CHMOD_FILE);
                if (!$result) {
                    return array('success' => false, 'error' => 'Failed to create file for touch operation');
                }
            }

            // For setting timestamps, we need to fall back to native PHP since WordPress filesystem
            // doesn't provide timestamp modification methods
            if (isset($params['time'])) {
                $atime = isset($params['atime']) ? $params['atime'] : $params['time'];
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_touch -- WordPress doesn't provide a touch() alternative
                $result = touch($params['file'], $params['time'], $atime);
                return array(
                    'success' => $result,
                    'data' => null,
                    'error' => $result ? null : 'Failed to update file timestamps'
                );
            }

            // If no time specified, just touching for current time
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_touch -- WordPress doesn't provide a touch() alternative
            $result = touch($params['file']);
            return array(
                'success' => $result,
                'data' => null,
                'error' => $result ? null : 'Failed to touch file'
            );

        default:
            return array('success' => false, 'error' => 'Unknown filesystem operation: ' . $mode);
    }
}






// Get Elapsed Time
function eeSFL_BASE_noticeTimer() {

	global $eeSFL_BASE, $eeSFL_BASE_StartTime, $eeSFL_BASE_MemoryUsedStart; // Time SFL got going

	$eeTime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]; // Time Right Now

	$eeTime = $eeTime - $eeSFL_BASE_StartTime; // Actual Time Elapsed

	$eeTime = number_format($eeTime, 3); // Format to 0.000

	$eeMemory = $eeSFL_BASE->eeSFL_FormatFileSize(memory_get_usage() - $eeSFL_BASE_MemoryUsedStart);

	return $eeTime . ' S | ' . $eeMemory;
}



function eeSFL_BASE_CheckSupported() {

	global $eeSFL_BASE;

	$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['notice'][] = eeSFL_BASE_noticeTimer() . ' - Checking Supported ...';


	// Check for supported technologies
	$eeSupported = array();

    // Check for ffMpeg
    if(function_exists('shell_exec')) {

		$eeSupported[] = 'Shell';

		if(shell_exec('ffmpeg -version')) {
			$eeSupported[] = 'ffMpeg';
			$eeSFL_Log[eeSFL_BASE_Go]['Supported'][] = 'Supported: ffMpeg';
		} else {
			$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['notice'][] = '---> shell_exec("ffMpeg") FAILED';
		}
    } else {
	    $eeSFL_BASE->eeLog[eeSFL_BASE_Go]['notice'][] = '---> shell_exec() NOT SUPPORTED';
    }

    if($eeSFL_BASE->eeEnvironment['eeOS'] != 'WINDOWS') {

		// Check for ImageMagick
		$phpExt = 'imagick';
		if(extension_loaded($phpExt)) {
			$eeSupported[] = 'ImageMagick';
			$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['Supported'][] = 'Supported: ImageMagick';
		}

		// Check for GhostScript
		if($eeSFL_BASE->eeEnvironment['eeOS'] == 'LINUX') { // TO DO - Make it work for IIS

			if(function_exists('exec')) {

				$phpExt = 'gs'; // <<<---- This will be different for Windows
				if(exec($phpExt . ' --version') >= 1.0) { // <<<---- This will be different for Windows too
					$eeSupported[] = 'GhostScript';
					$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['Supported'][] = 'Supported: GhostScript';
				}
			} else {
				$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['notice'][] = '---> exec() NOT SUPPORTED';
			}
		}
	}

	// echo '<pre>'; print_r($eeSupported); echo '</pre>'; exit;

	if(count($eeSupported)) {
		update_option('eeSFL_Supported', $eeSupported);
	} else {
		update_option('eeSFL_Supported', array('None'));
	}

	return TRUE;


}




// LEGACY - Convert hyphens to spaces for display only
function eeSFL_BASE_PreserveSpaces($eeFileName) {

	$eeFileName = str_replace('-', ' ', $eeFileName);

	return $eeFileName;
}





// Add the correct URL argument operator, ? or &
function eeSFL_BASE_AppendProperUrlOp($eeURL) {

	if ( strpos($eeURL, '?') ) {
		$eeURL .= '&';
	} else {
		$eeURL .= '?';
	}

	return $eeURL;
}



// Check for the Upload Directory, Create if Needed
function eeSFL_BASE_FileListDirCheck($eeFileListDir) {

	global $eeSFL_BASE;
	$eeCopyMaunalFile = FALSE;

	if(!$eeFileListDir OR substr($eeFileListDir, 0, 1) == '/' OR strpos($eeFileListDir, '../') ) {

		$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['errors'][] = __('Bad Directory Given', 'simple-file-list') . ': ' . $eeFileListDir;

		return FALSE;
	}

	$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['notice'][] = eeSFL_BASE_noticeTimer() . ' - Checking: ' . $eeFileListDir;

	$dirCheck = eeSFL_BASE_FileSystem('is_dir', array('path' => ABSPATH . $eeFileListDir));
	if( !$dirCheck['data'] ) { // Directory Changed or New Install

		$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['notice'][] = eeSFL_BASE_noticeTimer() . ' - New Install or Directory Change...';

		// Check if directory exists and is writable
		$existsCheck = eeSFL_BASE_FileSystem('exists', array('file' => ABSPATH . $eeFileListDir));
		if(!$existsCheck['data']) {

			$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['notice'][] = eeSFL_BASE_noticeTimer() . ' - No Directory Found. Creating ...';

			if ($eeSFL_BASE->eeEnvironment['eeOS'] == 'WINDOWS') {

				$mkdirResult = eeSFL_BASE_FileSystem('mkdir', array('path' => ABSPATH . $eeFileListDir));
			    if( !$mkdirResult['success'] ) {

				    $eeSFL_BASE->eeLog[eeSFL_BASE_Go]['errors'][] = __('Cannot Create Windows Directory:', 'simple-file-list') . ': ' . $eeFileListDir;
				}

			} elseif($eeSFL_BASE->eeEnvironment['eeOS'] == 'LINUX') {

				$mkdirResult = eeSFL_BASE_FileSystem('mkdir', array('path' => ABSPATH . $eeFileListDir, 'chmod' => 0755));
			    if( !$mkdirResult['success'] ) { // Linux - Need to set permissions

				    $eeSFL_BASE->eeLog[eeSFL_BASE_Go]['errors'][] = __('Cannot Create Linux Directory:', 'simple-file-list') . ': ' . $eeFileListDir;
				}
			} else {

				$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['errors'][] = __('ERROR: Could not detect operating system', 'simple-file-list');
				return FALSE;
			}

			// Check if directory was created successfully
			$existsCheck = eeSFL_BASE_FileSystem('exists', array('file' => ABSPATH . $eeFileListDir));
			if(!$existsCheck['data']) {
				$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['errors'][] = __('Cannot create the upload directory', 'simple-file-list') . ': ' . $eeFileListDir;
				$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['errors'][] = __('Please check directory permissions', 'simple-file-list');

				return FALSE;

			} else {

				$eeCopyMaunalFile = TRUE;

				$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['notice'][] = eeSFL_BASE_noticeTimer() . ' - The File List Dir Has Been Created!';
				$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['notice'][] = $eeFileListDir;
			}

		} else {
			$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['notice'][] = eeSFL_BASE_noticeTimer() . ' - FileListDir Looks Good';
		}

	}

	// Check index.html, create if needed.
	if( strlen($eeFileListDir) >= 2 ) {

		$eeFile = ABSPATH . $eeFileListDir . 'index.html'; // Disallow direct file indexing.

		$fileCheck = eeSFL_BASE_FileSystem('is_file', array('file' => $eeFile));
		if(!$fileCheck['data']) {

			// Get template content
			$templateContent = eeSFL_BASE_FileSystem('get_contents', array('file' => $eeSFL_BASE->eeEnvironment['pluginDir'] . 'includes/ee-index-template.html'));

			if($templateContent['success']) {
				// Write the content to index.html
				$writeResult = eeSFL_BASE_FileSystem('put_contents', array('file' => $eeFile, 'data' => $templateContent['data']));

				if(!$writeResult['success']) {
					$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['warnings'][] = __('WARNING! Could not write file', 'simple-file-list') . ': index.html';
					$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['warnings'][] = __('Please upload a blank index file to this location to prevent unauthorized access.', 'simple-file-list');
					$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['warnings'][] = ABSPATH . '/' . $eeFileListDir;
				}
			} else {
				$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['warnings'][] = __('WARNING! Could not read template file for index.html', 'simple-file-list');
			}
		}

		if($eeCopyMaunalFile === TRUE) {

			// Copy the Manual to the new directory, so there's at least one file.
			$eeCopyFrom = $eeSFL_BASE->eeEnvironment['pluginDir'] . 'Simple-File-List.pdf';
			$eeCopyTo = ABSPATH . '/' . $eeFileListDir . 'Simple-File-List.pdf';
			$copyResult = eeSFL_BASE_FileSystem('copy', array('from' => $eeCopyFrom, 'to' => $eeCopyTo));

			if(!$copyResult['success']) {
				$eeSFL_BASE->eeLog[eeSFL_BASE_Go]['warnings'][] = __('Could not copy manual file to directory', 'simple-file-list');
			}
		}
	}

	return TRUE; // Looks Good

}





// Return the size of a file in a nice format.
// Accepts a path or filesize in bytes
function eeSFL_BASE_GetFileSize($eeSFL_File) {

    if( is_numeric($eeSFL_File) ) {
		$bytes = $eeSFL_File;
	} elseif(is_file(ABSPATH . $eeSFL_File)) {
		$bytes = filesize(ABSPATH . $eeSFL_File);
	} else {
		return FALSE;
	}

    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;
    $precision = 2;

    if (($bytes >= 0) && ($bytes < $kilobyte)) {
        return $bytes . ' B';

    } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
        return round($bytes / $kilobyte, $precision) . ' KB';

    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
        return round($bytes / $megabyte, $precision) . ' MB';

    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
        return round($bytes / $gigabyte, $precision) . ' GB';

    } elseif ($bytes >= $terabyte) {
        return round($bytes / $terabyte, $precision) . ' TB';
    } else {
        return $bytes . ' B';
    }
}







// Yes or No Settings Checkboxes
function eeSFL_BASE_ProcessCheckboxInput($eeTerm) {

	$eeValue = sanitize_text_field(@$_POST['ee' . $eeTerm]);

	if($eeValue == 'YES') { return 'YES'; } else { return 'NO'; }
}



// Sanitize Form Text Inputs
function eeSFL_BASE_ProcessTextInput($eeTerm, $eeType = 'text') {

	$eeValue = '';

	if($eeType == 'email') {

		$eeValue = filter_var(sanitize_email(@$_POST['ee' . $eeTerm]), FILTER_VALIDATE_EMAIL);

	} elseif($eeType == 'textarea') {

		$eeValue = esc_textarea(sanitize_textarea_field( @$_POST['ee' . $eeTerm] ));

	} else {

		$eeValue = wp_strip_all_tags(@$_POST['ee' . $eeTerm]);
		$eeValue = esc_textarea(sanitize_text_field($eeValue));
	}

	return $eeValue;
}




// Return a formatted header string
function eeSFL_BASE_ReturnHeaderString($eeFrom, $eeCc = FALSE, $eeBcc = FALSE) {

	$eeAdminEmail = get_option('admin_email');

	$eeHeaders = 'From: ' . get_option('blogname') . ' < ' . $eeAdminEmail . ' >'  . PHP_EOL;

	if($eeCc) { $eeHeaders .= "CC: " . $eeCc . PHP_EOL; }

	if($eeBcc) { $eeHeaders .= "BCC: " . $eeBcc . PHP_EOL; }

	if( !filter_var($eeFrom, FILTER_VALIDATE_EMAIL) ) {
		$eeFrom = $eeAdminEmail;
	}

	$eeHeaders .= "Return-Path: " . $eeAdminEmail . PHP_EOL .
		"Reply-To: " . $eeFrom . PHP_EOL;

	return $eeHeaders;

}




// Process a raw input of email addresses
// Can be a single address or a comma sep list
function eeSFL_BASE_ProcessEmailString($eeString) {

	$eeString = sanitize_text_field($eeString);

	if( strpos($eeString, ',') ) { // More than one address?

		$eeArray = explode(',', $eeString);

		$eeAddresses = ''; // Reset

		foreach( $eeArray as $eeEmail) {

			$eeEmail = filter_var(sanitize_email($eeEmail), FILTER_VALIDATE_EMAIL);

			if($eeEmail) {

				$eeAddresses .= $eeEmail . ','; // Reassemble validated addresses
			}
		}

		$eeAddresses = substr($eeAddresses, 0, -1); // Strip the last comma

	} else {

		$eeAddresses = filter_var(sanitize_email($eeString), FILTER_VALIDATE_EMAIL);
	}

	if( strpos($eeAddresses, '@') ) {

		return $eeAddresses;

	} else {

		return FALSE;
	}
}

?>