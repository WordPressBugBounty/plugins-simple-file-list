<?php // Simple File List Script: ee-class-uploads.php | Author: Mitchell Bennis | support@simplefilelist.com

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class eeSFL_BASE_UploadClass {

	public $eeUploadedFiles = array(); // Save the original file names for an upload job


	public function eeSFL_UploadForm() {

		global $eeSFL_BASE;
		$eeOutput = '';

		// Detect Which SFL
		if(is_object($eeSFL_BASE)) {

			$eeObject = $eeSFL_BASE;
			$eeListID = 1;
			$eeCurrentFolder = '';

		} else {

			global $eeSFL; $eeObject = $eeSFL;
			$eeListID = $eeSFL->eeListID;

			// Sanitize folder parameter once at the top
			$eeFolder = FALSE;
			if(isset($_REQUEST['eeFolder'])) {
				if(!empty($_REQUEST['eeFolder'])) {
					$eeFolder = sanitize_text_field(wp_unslash($_REQUEST['eeFolder']));
				}
			}

			// Check for a Sub-Folder
			if($eeFolder AND $eeObject->eeListRun == 1) { // Adjust the path based on REQUEST arg
				// For uploads, allow folder access if we have proper navigation nonce OR if we're displaying the upload form
				// The actual upload security is handled later by the upload form nonce
				if (wp_verify_nonce(isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '', 'ee-folder-navigation') ||
				    wp_verify_nonce(isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '', 'ee-folder-navigation') ||
				    empty($_POST)) { // Allow for GET requests (form display) - POST security handled by upload nonce
					$eeCurrentFolder = urldecode($eeFolder) . '/';
				} else {
					$eeCurrentFolder = FALSE; // Don't use folder parameter if nonce fails
				}
			} elseif( !empty($eeObject->eeShortcodeFolder) ) {
				$eeCurrentFolder = str_replace('&#34;', '', $eeObject->eeShortcodeFolder) . '/'; // Fix for uploading to draft status page
			} else {
				$eeCurrentFolder = FALSE;
			}
		}

		// User Messaging
		$eeOutput .= $eeObject->eeSFL_ResultsNotification();

		$eeOutput .= '

		<!-- Simple File List Uploader -->

		<form action="' . $eeObject->eeSFL_GetThisURL() . '" method="POST" enctype="multipart/form-data" name="eeSFL_UploadForm" id="eeSFL_UploadForm">

		<input type="hidden" name="MAX_FILE_SIZE" value="' . (($eeObject->eeListSettings['UploadMaxFileSize']*1024)*1024) . '" />
		<input type="hidden" name="ee" value="1" />
		<input type="hidden" name="eeSFL_Upload" value="TRUE" />
		<input type="hidden" name="eeListID" value="' . $eeListID . '" />
		<input type="hidden" name="eeSFL_FileCount" value="" id="eeSFL_FileCount" />
		<input type="hidden" name="eeSFL_FileList" value="" id="eeSFL_FileList" />';

		if($eeObject->eeEnvironment['wpUserID'] > 0) { $eeOutput .= '
		<input type="hidden" name="eeSFL_FileOwner" value="' . $eeObject->eeEnvironment['wpUserID'] . '" id="eeSFL_FileOwner" />'; }

		if($eeCurrentFolder) { $eeOutput .= '
		<input type="hidden" name="eeSFL_UploadFolder" value="' . urlencode($eeCurrentFolder) . '" id="eeSFL_UploadFolder" />'; }

		$eeOutput .= wp_nonce_field( 'ee-simple-file-list-upload-form', 'ee-simple-file-list-upload-form-nonce', TRUE, FALSE);

		$eeOutput .= '

		<h2 class="eeSFL_UploadFilesTitle">' . __('Upload Files', 'simple-file-list') . '</h2>

		<div class="eeClearFix" id="eeSFL_FileDropZone" ondrop="eeSFL_DropHandler(event);" ondragover="eeSFL_DragOverHandler(event);">';

		$eeName = ''; $eeEmail = '';

		$wpUserObj = wp_get_current_user();

		if(!empty($wpUserObj->user_email)) {
			$eeName = $wpUserObj->first_name . ' ' . $wpUserObj->last_name;
			$eeEmail = $wpUserObj->user_email;
		}

		$eeOutput .= '

		<div id="eeUploadInfoForm" class="eeClearFix">';

		if(!$eeEmail AND $eeObject->eeListSettings['GetUploaderInfo'] == 'YES') {

			$eeOutput .= '

			<label for="eeSFL_Name">' . __('Name', 'simple-file-list') . ':</label>
			<input type="text" name="eeSFL_Name" value="" id="eeSFL_Name" size="64" maxlength="64" />

			<label for="eeSFL_Email">' . __('Email', 'simple-file-list') . ':</label>
			<input type="text" name="eeSFL_Email" value="" id="eeSFL_Email" size="64" maxlength="128" />';

		}

		if($eeObject->eeListSettings['GetUploaderDesc'] == 'YES' OR is_admin() ) {

			$eeOutput .= '<label for="eeSFL_FileDesc">' . __('Description', 'simple-file-list') . '</label>

			<textarea placeholder="' . __('Add a description (optional)', 'simple-file-list') . '" name="eeSFL_FileDesc" id="eeSFL_FileDesc" rows="5" cols="64" maxlength="5012"></textarea>';

		}

		$eeOutput .= '</div>

		<input type="file" name="eeSFL_FileInput" id="eeSFL_FileInput" onchange="eeSFL_FileInputHandler(event)" multiple />

		<p id="eeSFL_FilesDrug"></p>

		<script>

		var eeSFL_ListID = "' . $eeListID . '";
		var eeSFL_FileUploadDir = "' . urlencode($eeCurrentFolder) . '";
		var eeSFL_FileLimit = ' . $eeObject->eeListSettings['UploadLimit'] . ';
		var eeSFL_UploadMaxFileSize = ' . (($eeObject->eeListSettings['UploadMaxFileSize']*1024)*1024) . ';
		var eeSFL_FileFormats = "' . str_replace(' ' , '', $eeObject->eeListSettings['FileFormats']) . '";
		var eeSFL_Nonce = "' . wp_create_nonce('ee-simple-file-list-upload') . '";
		var eeSFL_UploadEngineURL = "' . admin_url( 'admin-ajax.php') . '";

		</script>

		<span id="eeSFL_UploadProgress"><em class="eeHide">' . __('Processing the Upload', 'simple-file-list') . '</em></span>

		<div id="eeSFL_FileUploadQueue"></div>

		<button type="button" class="button" name="eeSFL_UploadGo" id="eeSFL_UploadGo" onclick="eeSFL_UploadProcessor(eeSFL_FileObjects);">' . __('Upload', 'simple-file-list') . '</button>';

		if($eeObject->eeListSettings['ShowUploadLimits'] == 'YES') {

			$eeOutput .= '<p class="sfl_instuctions">' . __('File Limit', 'simple-file-list') . ': ' . $eeObject->eeListSettings['UploadLimit'] . ' ' . __('files', 'simple-file-list') . '<br />

			' . __('Size Limit', 'simple-file-list') . ': ' . $eeObject->eeListSettings['UploadMaxFileSize'] . ' MB

			' . __('per file', 'simple-file-list') . '.<br />

			' . __('Types Allowed', 'simple-file-list') . ': ' . str_replace(',', ', ', $eeObject->eeListSettings['FileFormats'])  . '<br />

			' . __('Drag-and-drop files here or use the Browse button.', 'simple-file-list') . '</p>';

		}

		$eeOutput .= '

		</div>

		</form>';

		return $eeOutput;
	}





	// Check for an Upload Job
	public function eeSFL_UploadCheck($eeListRun) {

		if($eeListRun > 1 ) { return; }


		global $eeSFL_BASE;
		$eeListID = 1;
		$eeMessages = array('Upload Job Complete');

		$eeUploaded = FALSE; // Show Confirmation

		// Check for an upload job, then run notification routine.
		if(isset($_POST['eeSFL_Upload'])) {

			// Verify nonce for security
			if (!wp_verify_nonce(isset($_POST['ee-simple-file-list-upload-form-nonce']) ? sanitize_text_field(wp_unslash($_POST['ee-simple-file-list-upload-form-nonce'])) : '', 'ee-simple-file-list-upload-form')) {
				return; // Exit silently if nonce verification fails
			}

			// Detect Which SFL
			if(is_object($eeSFL_BASE)) { $eeObject = $eeSFL_BASE; } else { global $eeSFL; $eeObject = $eeSFL; }

			if(isset($_POST['eeListID'])) {
				$eeListID = absint(wp_unslash($_POST['eeListID']));
			}

			if( $eeListID >=1 ) { $this->eeSFL_ProcessUploadJob($eeListID); $eeObject->eeListID = $eeListID; }

			$eeMessages[] = 'List ID: ' . $eeListID;
			$eeMessages[] = isset($_POST['eeSFL_FileList']) ? sanitize_textarea_field(wp_unslash($_POST['eeSFL_FileList'])) : 'No file list provided'; // json string

			// Add Custom Hook
			if( is_admin() ) {
				$eeMessages[] = 'Back-End Upload Complete';
				do_action('eeSFL_Admin_Hook_Uploaded', $eeMessages);
			} else {
				$eeMessages[] = 'Front-End Upload Complete';
				do_action('eeSFL_Hook_Uploaded', $eeMessages);
			}

			// Legacy
			if(is_object($eeSFL_BASE)) {

				if( is_admin() ) { do_action('eeSFL_UploadCompletedAdmin');
				} else { do_action('eeSFL_UploadCompleted'); }

			} else {

				if( is_admin() ) { do_action('eeSFL_BASE_UploadCompletedAdmin');
					} else { do_action('eeSFL_BASE_UploadCompleted'); }
			}

			if($eeObject->eeListSettings['UploadConfirm'] == 'YES' OR is_admin() ) { $eeUploaded = TRUE; }

		}

		return $eeUploaded;
	}






	// Process an Upload Job, Update the DB as Needed and Return the Results in a Nice Message
	public function eeSFL_ProcessUploadJob($eeListID) {

		global $eeSFL_BASE;

		// Detect Which SFL
		if(is_object($eeSFL_BASE)) {
			$eeObject = $eeSFL_BASE;
			$eeGo = eeSFL_BASE_Go;
			$eeTime = eeSFL_BASE_noticeTimer();
		} else {
			global $eeSFL, $eeSFLF, $eeSFLA, $eeSFL_Tasks;
			$eeObject = $eeSFL;
			$eeGo = eeSFL_Go;
			$eeTime = eeSFL_noticeTimer();
		}

		$eeUploadFolder = FALSE;

		$eeObject->eeLog[$eeGo]['notice'][] = $eeTime . ' - Processing the Upload Job...';

		// Get a list of the original file names that were uploaded. JSON STRING
		$eeFileListString = isset($_POST['eeSFL_FileList']) ? sanitize_textarea_field(wp_unslash($_POST['eeSFL_FileList'])) : '[]'; // ["Sunset2.jpg","Sunset.jpg","Boats.jpg"]
		$eeFileListArray = json_decode($eeFileListString);

		if(!is_array($eeFileListArray)) {

			$eeObject->eeLog[$eeGo]['error'][] = 'Upload String Not a JSON Array.';
			return FALSE;
		}

		// Security: Validate all file paths in the array for directory traversal BEFORE processing
		foreach($eeFileListArray as $eeTestFile) {
			$eeTestFile = sanitize_text_field($eeTestFile);
			// Use existing security method to check for directory traversal
			$eeObject->eeSFL_DetectUpwardTraversal($eeObject->eeListSettings['FileListDir'] . $eeTestFile);
		}

		// Get the File Count
		$eeFileCount = count($eeFileListArray);


		// Sanitize upload folder parameter
		$eeUploadFolder = FALSE;
		if( isset($_POST['eeSFL_UploadFolder']) ) { // Pro
			if(!empty($_POST['eeSFL_UploadFolder'])) {
				$eeUploadFolder = urldecode(sanitize_text_field(wp_unslash($_POST['eeSFL_UploadFolder'])));
				// Security: Check upload folder for directory traversal
				$eeObject->eeSFL_DetectUpwardTraversal($eeObject->eeListSettings['FileListDir'] . $eeUploadFolder);
			}
		}


		$eeObject->eeLog[$eeGo]['notice'][] = $eeTime . ' - ' . $eeFileCount . ' Files Uploaded';

		// Check for Form Nonce
		if(check_admin_referer( 'ee-simple-file-list-upload-form', 'ee-simple-file-list-upload-form-nonce')) {

			$eeUploadJob = ''; // This will be the well-formed message we return

			// Semantics
			if($eeFileCount > 1) {
				$eeUploadJob .= $eeFileCount . ' ' . __('Files Uploaded', 'simple-file-list');
			} else {
				$eeUploadJob .= __('File Uploaded', 'simple-file-list');
			}
			$eeUploadJob .= ":" . PHP_EOL . PHP_EOL;

			// Get the existing array
			if(empty($eeObject->eeAllFiles)) {
				$eeObject->eeAllFiles = get_option('eeSFL_FileList_' . $eeListID);
			}

			// Loop through the uploaded files, original names.
			if(count($eeFileListArray)) {

				foreach($eeFileListArray as $eeKey => $eeFile) {

					$eeFile = sanitize_text_field($eeFile);
					$eeFile = urlencode($eeUploadFolder . $eeFile); // Tack on any sub-folder of FileListDir

					// Check if Name was Sanitized using hashed transient key
					$eeFileOriginal = FALSE; // Transient is named using hash of original file path

					// Create the same hash key used during upload
					// Note: eeFile already includes the folder path, so we don't add eeUploadFolder
					$eeOriginalPath = $eeObject->eeListSettings['FileListDir'] . urldecode($eeFile);
					$eeHashKey = 'eeSFL-Renamed-' . md5($eeOriginalPath . $eeListID);
					$eeFileSanitized = get_transient($eeHashKey);

					if($eeFileSanitized) {

						$eeFileOriginal = $eeFile;
						$eeFileSanitized = urldecode($eeFileSanitized); // The sanitized name
						delete_transient($eeHashKey); // Clean up the transient
					$eeFile = $eeFileSanitized;

				} else {
					$eeFile = urldecode($eeFile);
				}

				// Security: Final check for directory traversal after all decoding
				$eeObject->eeSFL_DetectUpwardTraversal($eeObject->eeListSettings['FileListDir'] . $eeFile);

				// Check to be sure the file is there
				$eeFileCheck = eeSFL_BASE_FileSystem('is_file', array('file' => ABSPATH . $eeObject->eeListSettings['FileListDir'] . $eeFile));
				if($eeFileCheck['success'] && $eeFileCheck['data']) {

					$eeObject->eeLog[$eeGo]['notice'][] = $eeTime . ' - Creating File Array: ' . $eeFile;					$eeFound = FALSE;

					// Only look for existing file arrays if we're allowing overwrites
					// If overwrites are disabled, files are renamed and we should always create new entries
					if($eeObject->eeListSettings['AllowOverwrite'] == 'YES') { // Look for existing file array

						foreach( $eeObject->eeAllFiles as $eeKey => $eeThisFileArray ) {
							$eeFound = FALSE;
							if($eeThisFileArray['FilePath'] == $eeFile) { $eeFound = TRUE; break; }
						}

						if($eeFound) {
							$eeNewFileArray = $eeObject->eeSFL_BuildFileArray($eeFile, $eeThisFileArray);
						} else {
							$eeNewFileArray = $eeObject->eeSFL_BuildFileArray($eeFile); // Path relative to FileListDir
						}
					} else {
						// When overwrites are disabled, always build a new file array since file was renamed
						$eeNewFileArray = $eeObject->eeSFL_BuildFileArray($eeFile); // Path relative to FileListDir
					}
						// Use Original as the Nice Name
						if($eeFileOriginal AND $eeObject->eeListSettings['PreserveName'] == 'YES') {
							$eeNewFileArray['FileNiceName'] = basename(urldecode($eeFileOriginal)); // The original name
						}


						// Save Owner Info
						$eeID = get_current_user_id();

						if( !is_admin() ) { // Front-end only

							if($eeID === 0) {

								$eeNewFileArray['FileOwner'] = '0'; // Public

								if( isset($_POST['eeSFL_Name'])) {

									$eeString = esc_textarea(sanitize_text_field(wp_unslash($_POST['eeSFL_Name'])));

									if($eeString) {

										$eeNewFileArray['SubmitterName'] = $eeString; // Who uploaded the file
									}
								}

								if( isset($_POST['eeSFL_Email'])) {

									$eeString = filter_var( sanitize_email(wp_unslash($_POST['eeSFL_Email'])), FILTER_VALIDATE_EMAIL);

									if($eeString) {

										$eeNewFileArray['SubmitterEmail'] = $eeString; // Their email
									}
								}

							} else {
								$eeNewFileArray['FileOwner'] = $eeID;
							}
						} else {
							$eeNewFileArray['FileOwner'] = $eeID;
						}



						if( isset($_POST['eeSFL_FileDesc'])) {

							$eeString = esc_textarea(sanitize_text_field(wp_unslash($_POST['eeSFL_FileDesc'])));

							if($eeString) {

								$eeNewFileArray['FileDescription'] = $eeString; // A short description of the file
								$eeNewFileArray['SubmitterComments'] = $eeString; // What they said
							}
						}

						$eeObject->eeLog[$eeGo]['notice'][] = $eeTime . ' ——> Done';

						$eeNewFileArray = array_filter($eeNewFileArray); // Remove empty elements

						// To add or modify
						if($eeFound) {
							$eeObject->eeAllFiles[$eeKey] = $eeNewFileArray; // Updating current file array
						} else {
							$eeObject->eeAllFiles[] = $eeNewFileArray; // Append this file array to the big one
						}

						// If in a folder, update the folder dates
						if($eeUploadFolder) {

							$eePathPieces = explode('/', $eeUploadFolder);
							$eePartPaths = '';
							if(is_array($eePathPieces)) {
								foreach( $eePathPieces as $eePart ) {
									if($eePart) {
										$eePartPaths .= $eePart . '/';
										$eeObject->eeSFL_UpdateFileDetail($eePartPaths, 'FileDateChanged', current_time('Y-m-d H:i:s') );
									}
								}
							}
						}


						// If in a folder, update the folder dates
						if(isset($eeSFLF) AND $eeUploadFolder) {

							$eePathPieces = explode('/', $eeUploadFolder);
							$eePartPaths = '';
							if(is_array($eePathPieces)) {
								foreach( $eePathPieces as $eePart ) {
									if($eePart) {
										$eePartPaths .= $eePart . '/';
										$eeObject->eeSFL_UpdateFileDetail($eePartPaths, 'FileDateChanged', current_time('Y-m-d H:i:s') );
									}
								}
							}
						}


						// Create thumbnail if needed
						if(isset($eeSFL_Tasks) AND $eeObject->eeListSettings['ShowFileThumb'] == 'YES') {

							if(( $eeObject->eeListSettings['GeneratePDFThumbs'] == 'YES' AND $eeNewFileArray['FileExt'] == 'pdf' )

							OR ( $eeObject->eeListSettings['GenerateVideoThumbs'] == 'YES' AND in_array($eeNewFileArray['FileExt'], $eeObject->eeDynamicVideoThumbFormats) )

							) {

								// Start the background function: eeSFL_Background_GenerateThumbs()
								if(is_array($eeSFL_Tasks)) {
									$eeSFL_Tasks[$eeObject->eeListID]['GenerateThumbs'] = 'YES';
									update_option('eeSFL_Tasks', $eeSFL_Tasks);
								}
							}
						}


						// Notification Info
						if(isset($eeSFLA)) {
							$eeFileURL = $eeObject->eeEnvironment['wpSiteURL'] . 'ee-get-file/?list=' . $eeSFL->eeListID . '&file=' . $eeFile;
						} else {
							$eeFileURL = $eeObject->eeListSettings['FileListURL'] . $eeFile;
						}

						$eeUploadJob .=  $eeFile . " (" . $eeObject->eeSFL_FormatFileSize($eeNewFileArray['FileSize']) . ")" . PHP_EOL;
						$eeUploadJob .=  $eeFileURL . PHP_EOL . PHP_EOL;
					}

					// Add to our Upload Results Array
					$this->eeUploadedFiles[] = $eeFile;
				}

				// Add the Description
				if(!empty($eeNewFileArray['FileDescription'])) {
					$eeUploadJob .= $eeNewFileArray['FileDescription'] . PHP_EOL . PHP_EOL;
				}

				$eeObject->eeSFL_SortFiles($eeObject->eeListSettings['SortBy'], $eeObject->eeListSettings['SortOrder']);

				// If uploading into a folder, increment the counts and sizes.
				if(isset($eeSFLF) AND $eeUploadFolder) { $eeSFLF->eeSFLF_UpdateFolderSizes(); }

				// Save the new array
				update_option('eeSFL_FileList_' . $eeListID, $eeObject->eeAllFiles);

				$eeObject->eeLog[$eeGo]['messages'][] = __('File Upload Complete', 'simple-file-list');

				if( is_admin() ) {

					return TRUE;

				} else  {

					// Upload Email Notice
					if($eeObject->eeListSettings['Notify'] == 'YES') {

						// Send the Email Notification
						$eeObject->eeSFL_NotificationEmail($eeUploadJob);
						$_POST = array();
						return TRUE;

					} else {
						$_POST = array();
						return TRUE; // No notice wanted
					}
				}


			} else {
				$_POST = array();
				wp_die('ERROR 98 - ProcessUpload');
			}

		} else {
			$eeObject->eeLog[$eeGo]['errors'][] = 'No Files to Process';
			return FALSE;
		}
	}


	// --------------------------------------------------------------------------



	// File Upload Engine
	public function eeSFL_FileUploader() {

		global $eeSFL_BASE;

		// return print_r($_POST, FALSE);

		// Detect Which SFL
		if(is_object($eeSFL_BASE)) {
			$eeObject = $eeSFL_BASE;
			$eeListID = 1;
			$eeGo = eeSFL_BASE_Go;
			$eeTime = eeSFL_BASE_noticeTimer();
		} else {
			global $eeSFL, $eeSFLF, $eeSFLA, $eeSFL_Tasks;
			$eeObject = $eeSFL;
			if(isset($_POST['eeSFL_ID'])) { $eeListID = absint(wp_unslash($_POST['eeSFL_ID'])); } else { $eeListID = 1; };
			$eeGo = eeSFL_Go;
			$eeTime = eeSFL_noticeTimer();
		}

		// The FILE object
		if(empty($_FILES)) { return 'The File Object is Empty'; }

		if( !is_admin() ) { // Front-side protections

			// Who should be uploading?
			switch ($eeObject->eeListSettings['AllowUploads']) {
				case 'YES':
					break; // Allow it, even if it's dangerous.
				case 'USER':
					// Allow it if logged in at all
					if( get_current_user_id() ) { break; } else { return 'ERROR 97'; }
				case 'ADMIN':
					// Allow it if admin only.
					if(current_user_can('manage_options')) { break; } else { return 'ERROR 97'; }
					break;
				default: // Don't allow at all
					return 'ERROR 97';
			}
		}

		// Get this List's Settings
		$eeObject->eeSFL_GetSettings($eeListID);
		$eeFileUploadDir = $eeObject->eeListSettings['FileListDir'];


		// Sanitize file upload directory parameter
		if(isset($_POST['eeSFL_FileUploadDir'])) {
			if(!empty($_POST['eeSFL_FileUploadDir'])) {
				$eeFileUploadDir .= urldecode(sanitize_text_field(wp_unslash($_POST['eeSFL_FileUploadDir'])));
			}
		}


		// Check size
		$eeFileSize = isset($_FILES['file']['size']) ? filter_var($_FILES['file']['size'], FILTER_VALIDATE_INT) : 0;
		$eeUploadMaxFileSize = $eeObject->eeListSettings['UploadMaxFileSize']*1024*1024; // Convert MB to B

		if($eeFileSize > $eeUploadMaxFileSize) {
			return __('File size is too large.', 'simple-file-list');
		}

		// Go...
		$eeDirCheck = eeSFL_BASE_FileSystem('is_dir', array('path' => eeSFL_ABSPATH . $eeFileUploadDir));
		if($eeDirCheck['success'] && $eeDirCheck['data']) {

			if(wp_verify_nonce(isset($_POST['ee-simple-file-list-upload']) ? sanitize_text_field(wp_unslash($_POST['ee-simple-file-list-upload'])) : '', 'ee-simple-file-list-upload')) {

				// Temp file
				$eeTempFile = isset($_FILES['file']['tmp_name']) ? sanitize_text_field($_FILES['file']['tmp_name']) : '';

				// Sanitize file name parameter
				$eeFileName = FALSE;
				if(isset($_FILES['file']['name'])) {
					if(!empty($_FILES['file']['name'])) {
						$eeFileName = $eeObject->eeSFL_SanitizeFileName(sanitize_file_name($_FILES['file']['name']));
					}
				}

				// Check if it already exists and get the renamed filename
				if($eeObject->eeListSettings['AllowOverwrite'] == 'NO') {
					$eeRenamedFileName = $eeObject->eeSFL_CheckForDuplicateFile($eeFileUploadDir . $eeFileName);
					if($eeRenamedFileName != $eeFileName) {
						$eeFileName = $eeRenamedFileName; // Use the renamed filename
					}
				}

				$eeObject->eeSFL_DetectUpwardTraversal($eeFileUploadDir . $eeFileName); // Die if foolishness

				$eePathParts = pathinfo($eeFileName);
				$eeFileNameAlone = $eePathParts['filename'];
				$eeExtension = strtolower($eePathParts['extension']); // We need to do this here and in eeSFL_ProcessUpload()

				// Format Check
				$eeFileFormatsArray = array_map('trim', explode(',', $eeObject->eeListSettings['FileFormats']));

				if(!in_array($eeExtension, $eeFileFormatsArray) OR in_array($eeExtension, $eeObject->eeForbiddenTypes)) {
					return __('File type not allowed', 'simple-file-list') . ': (' . $eeExtension . ')';
				}

				// MIME type validation
				$eeFiletype = wp_check_filetype($eeFileName);
				if (!$eeFiletype['type']) {
					return __('File type could not be determined', 'simple-file-list') . ': (' . $eeExtension . ')';
				}

				// Verify MIME type matches file extension
				$eeMimeCheck = wp_check_filetype_and_ext($eeTempFile, $eeFileName);
				if ($eeMimeCheck['ext'] === false || $eeMimeCheck['type'] === false) {
					return __('File failed security validation', 'simple-file-list') . ': (' . $eeExtension . ')';
				}

				// Assemble FilePath - use the already processed $eeFileName which includes duplicate numbering
				$eeTargetFile = $eeFileUploadDir . $eeFileName;

				// Check if the name has changed
				if($eeFileNameRaw && sanitize_text_field($eeFileNameRaw) != $eeFileName) {

					// Create a consistent hash key for the transient using original file path and list ID
					$eeOriginalPath = $eeFileUploadDir . ($eeFileNameRaw ? sanitize_text_field($eeFileNameRaw) : '');
					$eeHashKey = 'eeSFL-Renamed-' . md5($eeOriginalPath . $eeListID);
					$eeNewFilePath = str_replace($eeObject->eeListSettings['FileListDir'], '', $eeTargetFile); // Strip the FileListDir

					// Set transient with hashed key
					set_transient($eeHashKey, $eeNewFilePath, 900); // Expires in 15 minutes
				}

				$eeTarget = ABSPATH . $eeTargetFile;

				// Save the file
				// phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- move_uploaded_file() required for file uploads, will be replaced with wp_handle_upload() in SFL 7
				if( move_uploaded_file($eeTempFile, $eeTarget) ) {

					$eeTargetCheck = eeSFL_BASE_FileSystem('is_file', array('file' => $eeTarget));
					if(!($eeTargetCheck['success'] && $eeTargetCheck['data'])) {
						return 'Error - File System Error.'; // No good.
					} else {

						// Check for corrupt images
						if( in_array($eeExtension, $eeObject->eeDynamicImageThumbFormats) ) {

							$eeString = implode('...', getimagesize($eeTarget) );

							if(!strpos($eeString, 'width=') OR !strpos($eeString, 'height=')) { // Make sure it's really an image


								eeSFL_BASE_FileSystem('delete', array('file' => $eeTarget));

								return 'ERROR 99';
							}
						}

						// Update the File Date
						$eeDate = isset($_POST['eeSFL_FileDate']) ? esc_textarea(sanitize_text_field(wp_unslash($_POST['eeSFL_FileDate']))) : '';
						$eeDate = strtotime($eeDate);
						if($eeDate) {
							// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_touch -- Direct file timestamp modification required, will use WP_Filesystem in SFL 7
							touch($eeTarget, $eeDate);  // Do nothing if bad date
						}

						// Build Image thumbs right away right away. We'll set other types to use the background job within eeSFL_ProcessUpload()
						if($eeObject->eeListSettings['ShowFileThumb'] == 'YES') {
							if( in_array($eeExtension, $eeObject->eeDynamicImageThumbFormats) ) {

								$eeTargetFile = str_replace($eeObject->eeListSettings['FileListDir'], '', $eeTargetFile); // Strip the FileListDir
								$eeObject->eeSFL_CheckThumbnail($eeTargetFile, $eeObject->eeListSettings);
							}
						}

						return 'SUCCESS';
					}

				} else {
					return 'Cannot save the uploaded file: ' . $eeTargetFile;
				}

			} else {

				return 'ERROR 98 - FileUploader';
			}

		} else {
			return 'Upload Path Not Found: ' . $eeFileUploadDir;
		}
	}




	// Clean up orphaned rename transients (called during plugin maintenance)
	public function eeSFL_CleanupRenameTransients() {

		// Clean up any orphaned eeSFL rename transients using WordPress functions
		// Get all transients that match our pattern
		$transients = wp_cache_get('eeSFL_rename_transients', 'eeSFL');
		if (false === $transients) {
			// If not cached, we'll let them expire naturally (15 minutes)
			// This avoids direct database queries while maintaining functionality
			wp_cache_set('eeSFL_rename_transients', array(), 'eeSFL', 900); // Cache for 15 minutes
		}

		return true;
	}




	// Get Actual Max Upload Size
	public function eeSFL_ActualUploadMax() {

		$eeEnv = array();

		$eeEnv['upload_max_filesize'] = substr(ini_get('upload_max_filesize'), 0, -1); // PHP Limit (Strip off the "M")
		$eeEnv['post_max_size'] = substr(ini_get('post_max_size'), 0, -1); // PHP Limit (Strip off the "M")

		// Check which is smaller, upload size or post size.
		if ($eeEnv['upload_max_filesize'] <= $eeEnv['post_max_size']) {
			return $eeEnv['upload_max_filesize'];
		} else {
			return $eeEnv['post_max_size'];
		}
	}

}
?>