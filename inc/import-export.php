<?php # -*- coding: utf-8 -*-
if (! defined('ABSPATH')) exit;


/**
 * Class TFSLImportExport.
 *
 * Provides import/export functionality for CSV files.
 *
 * @since 1.0.0
 */
class TFSLImportExport {


	/**
	 * Import data of a CSV file into the song list database.
	 *
	 * @since 1.0.0
	 */
	private function import() {
		// Check authorization
		check_admin_referer('tfsl-action');

		global $notice;
		if ($_FILES['tfsl_import']['name']) {
			$upload = wp_upload_bits(
				$_FILES['tfsl_import']['name'],
				null,
				file_get_contents($_FILES['tfsl_import']['tmp_name'])
			);

			if (! $upload['error']) {
				require_once TFSongList::get('DIR').'/lib/parsecsv.lib.min.php';
				$import = new parseCSV();
				$import->encoding('UTF-8', 'UTF-8');
				$import->parse($upload['file']);
				if ($import->data) {
					global $wpdb;
					$replace_notice = '';
					$table = TFSongList::get('TABLE');

					if ('replace' === $_POST['tfsl_import_type']) {
						// Clear current song list
						$return = $wpdb->query("TRUNCATE TABLE $table");
						if (! $return)
							$replace_notice = '<div class="settings-error error"><p><strong>'
								.__("Song list not deleted!", 'tf-song-list')
								.'</strong></p></div>';
					}

					$duplicates = 0;
					$inserted = 0;
					$skipped = 0;
					foreach ($import->data as $song) {
						// Check if song already in song list
						$song_in_db = $wpdb->get_var($wpdb->prepare(
							"SELECT id FROM $table WHERE artist=%s AND title=%s",
							$song['artist'],
							$song['title']
						));
						if ($song_in_db) $duplicates++;
						else {
							// Create song ...
							$new_song = array(
								'artist' => TFSLDB::db_in($song['artist']),
								'title' => TFSLDB::db_in($song['title']),
								'public' => TFSLDB::db_in($song['public']),
							);
							// ... and insert into database
							$return = $wpdb->insert($table, $new_song);
							if ($return)
								$inserted++;
							else
								$skipped++;
						}
					}

					// Prepare notice for skipped songs
					$skipped_notice = '';
					if ($skipped) {
						$skipped_notice = '<div class="settings-error error"><p><strong>';
						$skipped_notice .= sprintf(
							_n(
								"%d song was skipped due to errors.",
								"%d songs were skipped due to errors.",
								$skipped,
								'tf-song-list'
							),
							$skipped
						);
						$skipped_notice .= '</strong></p></div>';
					}

					// Prepare notice for inserted and duplicate songs
					$inserted_duplicates_notice = '';
					if (($inserted) || ($duplicates)) {
						$inserted_duplicates_notice = '<div class="settings-error updated"><p><strong>';
						if ($inserted) {
							$inserted_duplicates_notice .= sprintf(
								_n(
									"%d song was successfully added to the song list.",
									"%d songs were successfully added to the song list.",
									$inserted,
									'tf-song-list'
								),
								$inserted
							);
						}
						if ($duplicates) {
							if ($inserted) $inserted_duplicates_notice .= '<br />';
							$inserted_duplicates_notice .= sprintf(
								_n(
									"%d song was not added to the song list as it was already present.",
									"%d songs were not added to the song list as they were already present.",
									$duplicates,
									'tf-song-list'
								),
								$duplicates
							);
						}
						$inserted_duplicates_notice .= '</strong></p>';
						$inserted_duplicates_notice .= '<p><a href="'
							.get_bloginfo('url').'/wp-admin/admin.php?page=tfsl-songs">'
							.__("Show song list", 'tf-song-list')
							.'</a></p>';
						$inserted_duplicates_notice .= '</div>';
					}
					$notice = $replace_notice.$skipped_notice.$inserted_duplicates_notice;
				} else
					$notice = '<div class="settings-error error"><p><strong>'
						.__("File not processed!", 'tf-song-list')
						.'</strong> '
						.__("Maybe double-check the file formatting?", 'tf-song-list')
						.'</p></div>';
			} else
				$notice = '<div class="settings-error error"><p><strong>'
					.__("File not uploaded!", 'tf-song-list')
					.'</strong> '
					.__("Error", 'tf-song-list').': '.$upload['error']
					.'</p></div>';
			unlink($upload['file']);
		} else
			$notice = '<div class="settings-error error"><p><strong>'
				.__("No file name entered!", 'tf-song-list')
				.'</strong> '
				.__("Forgot something?", 'tf-song-list')
				.'</p></div>';
	} // private function import


	/**
	 * Export the song list database as CSV file.
	 *
	 * @since 1.0.0
	 */
	function export() {
		// Check authorization
		check_admin_referer('tfsl-action');

		// Export all songs or public only?
		$where = ('public' == $_POST['content']) ? " WHERE public=1" : "";

		// Query songs
		$songs = $GLOBALS['wpdb']->get_results(
			"SELECT artist, title, public
			FROM ".TFSongList::get('TABLE')
			.$where
		);

		if($songs) {
			// Prepare data ...
			$name = 'song-list-'.date('Y-m-d').'.csv';
			$export_songs = array();
			foreach ($songs as $song) $export_songs[] = $song;
			$fields = array(
				'artist',
				'title',
				'public',
			);

			// ... and export
			require_once TFSongList::get('DIR').'/lib/parsecsv.lib.min.php';
			$export = new parseCSV();
			$export->encoding('UTF-8', 'UTF-8');
			$export->output($name, stripslashes_deep($export_songs), $fields);
		} else
			echo '<p>'
				.__("Nothing to export.", 'tf-song-list')
				.'</p><p><a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=tfsl-import-export">'
				.__("Back", 'tf-song-list')
				.'</a></p>';
	} // function export


	/**
	 * Echo warning due to missing authorization for export.
	 *
	 * @since 1.0.0
	 */
	function export_nopriv() {
		echo '<p>'.__("You are not authorized to do that. Please log in first.", 'tf-song-list').'</p>';
	} // function export_nopriv


	/**
	 * Print the Import/Export page for the plugin admin menu.
	 *
	 * @since 1.0.0
	 */
	function print_page() {
		global $notice;

		// Enable WP-intern settings message handling
		settings_errors();

		// Check for import request
		if ((isset($_POST['action'])) && ('tfsl_import' === $_POST['action']))
			self::import();

		// Print notices
		if ('' !== $notice) echo $notice;
?>
<div class="wrap">
	<h2>Song List &rsaquo; <?php _e("Import/Export", 'tf-song-list'); ?></h2>
	<h3><?php _e("Import", 'tf-song-list'); ?></h3>
	<p><?php _e("Upload and import a comma-separated values (CSV) file into your database.", 'tf-song-list'); ?><br />
	<strong><a href="http://ipm-frommen.de/wordpress/tf-song-list#csv" target="_blank"><?php _e("Please have a read here to learn about the proper file format specifications.", 'tf-song-list'); ?></a></strong></p>
	<form action="" method="post" enctype="multipart/form-data">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="tfsl_import"><?php _e("CSV File", 'tf-song-list'); ?></label></th>
				<td><input type="file" name="tfsl_import" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="tfsl_import_type"><?php _e("Import Type", 'tf-song-list'); ?></label></th>
				<td>
					<select name="tfsl_import_type">
						<option value="add"><?php _e("Add to current song list", 'tf-song-list'); ?></option>
						<option value="replace"><?php _e("Replace current song list", 'tf-song-list'); ?></option>
					</select>
					<p class="description"><?php _e("When replacing the song list, all existing songs will be deleted!", 'tf-song-list'); ?></p>
				</td>
			</tr>
		</table><!-- .form-table -->
		<?php wp_nonce_field('tfsl-action') ?>
		<input type="hidden" name="action" value="tfsl_import" />
		<?php submit_button(__("Upload CSV File", 'tf-song-list')); ?>
	</form>
	<h3><?php _e("Export", 'tf-song-list'); ?></h3>
	<p><?php _e("Download a comma-separated values (CSV) file containing all the information of the song list database.", 'tf-song-list'); ?><br />
	<?php _e("The file is compatible with almost any spreadsheet software, and it can be imported into another tf Song List installation.", 'tf-song-list'); ?></p>
	<form action="admin-post.php" method="post">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="content"><?php _e("File Content", 'tf-song-list'); ?></label></th>
				<td>
					<select name="content">
						<option value="all"><?php _e("All songs", 'tf-song-list'); ?></option>
						<option value="public"><?php _e("Public songs", 'tf-song-list'); ?></option>
					</select>
				</td>
			</tr>
		</table><!-- .form-table -->
		<?php wp_nonce_field('tfsl-action') ?>
		<input type="hidden" name="action" value="tfsl_export" />
		<?php submit_button(__("Download CSV File", 'tf-song-list')); ?>
	</form>
</div><!-- .wrap -->
<?php
	} // function print_page
} // class TFSLImportExport
