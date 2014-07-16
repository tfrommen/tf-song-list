<?php # -*- coding: utf-8 -*-
if (! defined('ABSPATH')) exit;


/**
 * Class TFSLSongs.
 *
 * Handles song list data.
 * Serves as read/write interface.
 *
 * @since 1.0.0
 */
class TFSLSongs {


	/**
	 * Print the Songs page for the plugin admin menu.
	 *
	 * @since 1.0.0
	 */
	function print_page() {
		global $wpdb;
		$action = (isset($_GET['tfsl_action']) ? $_GET['tfsl_action'] : 'add');
		$edit = false;
		$id = (isset($_GET['song_id']) ? $_GET['song_id'] : -1);
		$notice = '';
		$scope = (isset($_GET['scope']) ? $_GET['scope'] : 'all');
		$table = TFSongList::get('TABLE');

		switch ($action) {
			case 'add':
				if (isset($_POST['submit']) || isset($_POST['update'])) {
					// Check authorization
					check_admin_referer('tfsl-action');

					if ((! empty($_POST['artist'])) && (! empty($_POST['title']))) {
						// Set up song data and ...
						$data = array(
							// strip slashes that WordPress automatically adds to $_POST data
							'artist' => TFSLDB::db_in(stripslashes_deep($_POST['artist'])),
							'title' => TFSLDB::db_in(stripslashes_deep($_POST['title'])),
							'public' => TFSLDB::db_in($_POST['public']),
						);
						if (isset($_POST['submit']) && $_POST['submit']) {
							// ... either insert
							$return = $wpdb->insert($table, $data);
							if (! $return)
								$notice = '<div class="settings-error error"><p><strong>'
									.__("Song not added to the song list!", 'tf-song-list')
									.'</strong></p></div>';
							else
								$notice = '<div class="settings-error updated"><p><strong>'
									.__("Song added to the song list!", 'tf-song-list')
									.'</strong></p></div>';
						} elseif (isset($_POST['update']) && $_POST['update']
							&& isset($_POST['id']) && $_POST['id']
						) {
							// ... or update
							$return = $wpdb->update(
								$table,
								$data,
								array('id' => TFSLDB::db_in($_POST['id']))
							);
							if (false === $return)
								$notice = '<div class="settings-error error"><p><strong>'
									.__("Song not updated!", 'tf-song-list')
									.'</strong></p></div>';
							else
								$notice = '<div class="settings-error updated"><p><strong>'
									.__("Song updated!", 'tf-song-list')
									.'</strong></p></div>';
						}
					} else
						$notice = '<div class="settings-error error"><p><strong>'
							.__("Song not added to the song list!", 'tf-song-list')
							.'</strong> '
							.__("Both the artist and the title have to be entered.", 'tf-song-list')
							.'</p></div>';
				}
				break; // case 'add'

			case 'edit':
				// Check authorization
				check_admin_referer('tfsl-action');

				// Query song
				if (-1 !== $id)
					$edit = $wpdb->get_row(
						"SELECT *
						FROM $table
						WHERE id='".TFSLDB::db_in($id)."'"
					);
				if (empty($edit)) $edit = false;
				break; // case 'edit'

			case 'delete':
				// Check authorization
				check_admin_referer('tfsl-action');

				// Delete song
				if (-1 !== $id)
					$return = $wpdb->query(
						"DELETE
						FROM $table
						WHERE id='".TFSLDB::db_in($id)."'"
					);
				if (! $return)
					$notice = '<div class="settings-error error"><p><strong>'
						.__("Song not deleted!", 'tf-song-list')
						.'</strong></p></div>';
				else
					$notice = '<div class="settings-error updated"><p><strong>'
						.__("Song deleted!", 'tf-song-list')
						.'</strong></p></div>';
				break; // case 'delete'

			case 'truncate':
				// Check authorization
				check_admin_referer('tfsl-action');

				// Truncate table
				$return = $wpdb->query("TRUNCATE TABLE $table");
				if (! $return)
					$notice = '<div class="settings-error error"><p><strong>'
						.__("Song list not deleted!", 'tf-song-list')
						.'</strong></p></div>';
				else
					$notice = '<div class="settings-error updated"><p><strong>'
						.__("Song list deleted!", 'tf-song-list')
						.'</strong></p></div>';
				break; // case 'truncate'
		} // switch ($action)

		// Enable WP-intern settings message handling
		settings_errors();

		// Print notices
		if ('' !== $notice) echo $notice;
?>
<div class="wrap">
	<h2>Song List &rsaquo; <?php _e("Songs", 'tf-song-list'); ?></h2>
	<form action="<?php bloginfo('url'); ?>/wp-admin/admin.php?page=tfsl-songs" method="post">
		<?php
			$O = TFSongList::get_options();
			$col1 = $O['first_column'];
			$col2 = (('artist' == $col1) ? 'title' : 'artist');
			$order = $O['order_by'];
			$suborder = (($order == $col1) ? $col2 : $col1);
		?>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="<?php echo $col1; ?>"><?php echo $O[$col1.'_name']; ?></label></th>
			<td><input name="<?php echo $col1; ?>" type="text" id="<?php echo $col1; ?>" value="<?php if ($edit) echo TFSLDB::db_out($edit->$col1); ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="<?php echo $col2; ?>"><?php echo $O[$col2.'_name']; ?></label></th>
			<td><input name="<?php echo $col2; ?>" type="text" id="<?php echo $col2; ?>" value="<?php if ($edit) echo TFSLDB::db_out($edit->$col2); ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="public"><?php _e("Visibility", 'tf-song-list'); ?></label></th>
			<td>
				<select name="public" id="public">
					<option value="1"<?php if ((! $edit) || $edit->public) echo ' selected="selected"'; ?>><?php _e("Public", 'tf-song-list'); ?></option>
					<option value="0"<?php if ($edit && (! $edit->public)) echo ' selected="selected"'; ?>><?php _e("Private", 'tf-song-list'); ?></option>
				</select>
				<p class="description"><?php _e("Private songs are listed in here only &ndash; not on the frontend.", 'tf-song-list'); ?></p>
			</td>
		</tr>
		</table><!-- .form-table -->
		<?php wp_nonce_field('tfsl-action') ?>
		<input type="hidden" name="id" id="id" value="<?php if ($edit) echo TFSLDB::db_out($edit->id); ?>" />
		<?php
			if ($edit) submit_button(__("Update Song", 'tf-song-list'), 'primary', 'update');
			else submit_button(__("Add Song", 'tf-song-list'));
		?>
	</form>
	<p><?php
		// Set up query according to given scope
		if ('public' == $scope) $where = "WHERE public=1";
		elseif ('private' == $scope) $where = "WHERE public=0";
		else $where = "";

		// Query songs
		$songs = $wpdb->get_results(
			"SELECT *
			FROM $table
			$where
			ORDER BY $order ASC, $suborder ASC"
		);
		$allsongs = $wpdb->get_results("SELECT public FROM $table");

		// Delete button
		$delete_confirm = __("Delete all songs?", 'tf-song-list');
		if (! empty($allsongs)) {
			$link = get_bloginfo('url')
				.'/wp-admin/admin.php?page=tfsl-songs&tfsl_action=truncate';
			$link = ((function_exists('wp_nonce_url'))
				? wp_nonce_url($link, 'tfsl-action')
				: $link
			);
			echo '<a href="'.$link.'">'
				.'<input type="button" name="delete" id="delete" '
				.'class="button button-secondary" value="'
				.__("Delete Song List", 'tf-song-list')
				.'" onclick="return confirm(\''.$delete_confirm.'\');" /></a>';
		} else
			echo '<input type="button" name="delete" id="delete" '
				.'class="button button-secondary" value="'
				.__("Delete Song List", 'tf-song-list')
				.'" disabled="disabled" />';
	?></p>
	<p><?php
		// Determine number of songs for each status
		$num_all = count($allsongs);
		$num_public = 0;
		foreach ($allsongs as $song) if ($song->public) $num_public++;
		$num_private = $num_all - $num_public;

		// Filter mechanism
		echo '<div id="tfsl-filter">'.__("Filter", 'tf-song-list').': ';
		if ('all' == $scope)
			echo '<strong>'.__("All Songs", 'tf-song-list').'</strong>';
		else
			echo '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=tfsl-songs">'
				.__("All Songs", 'tf-song-list')
				.'</a>';
		echo ' ('.$num_all.') | ';
		if ('public' == $scope)
			echo '<strong>'.__("Public Songs", 'tf-song-list').'</strong>';
		else
			echo '<a href="'.get_bloginfo('url')
				.'/wp-admin/admin.php?page=tfsl-songs&amp;scope=public">'
				.__("Public Songs", 'tf-song-list')
				.'</a>';
		echo ' ('.$num_public.') | ';
		if ('private' == $scope)
			echo '<strong>'.__("Private Songs", 'tf-song-list').'</strong>';
		else
			echo '<a href="'.get_bloginfo('url')
				.'/wp-admin/admin.php?page=tfsl-songs&amp;scope=private">'
				.__("Private Songs", 'tf-song-list')
				.'</a>';
		echo ' ('.$num_private.')';
		echo '</div><!-- #tfsl-filter -->';
	?></p>
	<table id="tf-song-list" class="widefat fixed" cellspacing="0">
	<thead>
	<tr>
		<th scope="col" id="col-1"><?php echo esc_html($O[$col1.'_name']); ?></th>
		<th scope="col" id="col-2"><?php echo esc_html($O[$col2.'_name']); ?></th>
		<th scope="col" id="col-edit"><?php _e("Edit", 'tf-song-list'); ?></th>
		<th scope="col" id="col-delete"><?php _e("Delete", 'tf-song-list'); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="col"><?php echo esc_html($O[$col1.'_name']); ?></th>
		<th scope="col"><?php echo esc_html($O[$col2.'_name']); ?></th>
		<th scope="col"><?php _e("Edit", 'tf-song-list'); ?></th>
		<th scope="col"><?php _e("Delete", 'tf-song-list'); ?></th>
	</tr>
	</tfoot>
	<tbody id="the-song-list">
	<?php
		if (! empty($songs)) {
			foreach ($songs as $song) {
	?>
	<tr>
		<td class="col1<?php if (! $song->public) echo ' private'; ?>"><?php echo TFSLDB::db_out($song->$col1); ?></td>
		<td<?php if (! $song->public) echo ' class="private"'; ?>><?php echo TFSLDB::db_out($song->$col2); ?></td>
		<?php
			$link = get_bloginfo('url')
				.'/wp-admin/admin.php?page=tfsl-songs&tfsl_action=edit&song_id='
				.TFSLDB::db_out($song->id);
			$link = ((function_exists('wp_nonce_url'))
				? wp_nonce_url($link, 'tfsl-action')
				: $link
			);
		?>
		<td><a href="<?php echo $link; ?>"><?php _e("Edit", 'tf-song-list'); ?></a></td>
		<?php
			$link = get_bloginfo('url')
				.'/wp-admin/admin.php?page=tfsl-songs&tfsl_action=delete&song_id='
				.TFSLDB::db_out($song->id);
			$link = ((function_exists('wp_nonce_url'))
				? wp_nonce_url($link, 'tfsl-action')
				: $link
			);
		?>
		<td><a href="<?php echo $link; ?>"><?php _e("Delete", 'tf-song-list'); ?></a></td>
	</tr>
	<?php
			}
		} else {
	?>
	<tr>
		<td id="no-songs" colspan="4"><?php _e("There are no songs in the song list.", 'tf-song-list'); ?></td>
	</tr>
	<?php
		}
	?>
	</tbody><!-- #the-song-list -->
</table>
</div><!-- .wrap -->
<?php
	} // function print_page
} // class TFSLSongs
