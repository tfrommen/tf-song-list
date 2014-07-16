<?php # -*- coding: utf-8 -*-
if (! defined('ABSPATH')) exit;


/**
 * Class TFSLSettings.
 *
 * Handles plugin-specific settings.
 *
 * @since 1.0.0
 */
class TFSLSettings {


	/**
	 * Print the Settings page for the plugin admin menu.
	 *
	 * @since 1.0.0
	 */
	function print_page() {
		// Enable WP-intern settings message handling
		settings_errors();
?>
<div class="wrap">
	<h2>Song List &rsaquo; <?php _e("Settings", 'tf-song-list'); ?></h2>
	<form action="options.php" method="post">
		<?php $O = TFSongList::get_options(); ?>
		<?php settings_fields('tfsl_options'); ?>
		<input type="hidden" name="tfsl_options[db_version]" value="<?php echo $O['db_version']; ?>" />
		<h3><?php _e("Introduction", 'tf-song-list'); ?></h3>
		<table class="form-table">
		<tr valign="top">
			<th><label for="tfsl_options[introduction]"><?php _e("Introduction", 'tf-song-list'); ?></label></th>
			<td>
				<textarea name="tfsl_options[introduction]" id="tfsl_options[introduction]" rows="5" cols="111"><?php echo $O['introduction']; ?></textarea>
				<p class="description"><?php _e("Introduction before the actual song list.", 'tf-song-list'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tfsl_options[show_introduction]"><?php _e("Show Introduction", 'tf-song-list'); ?></label></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("Show Introduction", 'tf-song-list'); ?></span></legend>
					<label for="tfsl_options[show_introduction]">
						<input name="tfsl_options[show_introduction]" type="hidden" value="0" />
						<input name="tfsl_options[show_introduction]" type="checkbox" id="tfsl_options[show_introduction]" value="1" <?php checked(1, $O['show_introduction']); ?> />
						<?php _e("Show the introduction before the actual song list.", 'tf-song-list'); ?>
					</label>
				</fieldset>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tfsl_options[wrap_introduction]"><?php _e("Wrap Introduction", 'tf-song-list'); ?></label></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("Wrap Introduction", 'tf-song-list'); ?></span></legend>
					<label for="tfsl_options[wrap_introduction]">
						<input name="tfsl_options[wrap_introduction]" type="hidden" value="0" />
						<input name="tfsl_options[wrap_introduction]" type="checkbox" id="tfsl_options[wrap_introduction]" value="1" <?php checked(1, $O['wrap_introduction']); ?> />
						<?php _e("Wrap the introduction in &lt;p&gt; tags.", 'tf-song-list'); ?>
					</label>
				</fieldset>
			</td>
		</tr>
		</table><!-- .form-table -->
		<h3><?php _e("Song List", 'tf-song-list'); ?></h3>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="tfsl_options[show_headings]"><?php _e("Show Headings", 'tf-song-list'); ?></label></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e("Show Headings", 'tf-song-list'); ?></span></legend>
					<label for="tfsl_options[show_headings]">
						<input name="tfsl_options[show_headings]" type="hidden" value="0" />
						<input name="tfsl_options[show_headings]" type="checkbox" id="tfsl_options[show_headings]" value="1" <?php checked(1, $O['show_headings']); ?> />
						<?php _e("Show the song list headings.", 'tf-song-list'); ?>
					</label>
				</fieldset>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tfsl_options[artist_name]"><?php _e("Artist Name", 'tf-song-list'); ?></label></th>
			<td>
				<input name="tfsl_options[artist_name]" type="text" id="tfsl_options[artist_name]" value="<?php echo $O['artist_name']; ?>" class="regular-text" />
				<p class="description"><?php _e("What should the Artist column be named?", 'tf-song-list'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tfsl_options[title_name]"><?php _e("Title Name", 'tf-song-list'); ?></label></th>
			<td>
				<input name="tfsl_options[title_name]" type="text" id="tfsl_options[title_name]" value="<?php echo $O['title_name']; ?>" class="regular-text" />
				<p class="description"><?php _e("What should the Title column be named?", 'tf-song-list'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tfsl_options[first_column]"><?php _e("First Column", 'tf-song-list'); ?></label></th>
			<td>
				<select name="tfsl_options[first_column]" id="tfsl_options[first_column]">
					<option value="artist"<?php if ('artist' == $O['first_column']) echo ' selected="selected"'; ?>><?php echo $O['artist_name']; ?></option>
					<option value="title"<?php if ('title' == $O['first_column']) echo ' selected="selected"'; ?>><?php echo $O['title_name']; ?></option>
				</select>
				<p class="description"><?php _e("What should the first column contain?", 'tf-song-list'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="tfsl_options[order_by]"><?php _e("Order By", 'tf-song-list'); ?></label></th>
			<td>
				<select name="tfsl_options[order_by]" id="tfsl_options[order_by]">
					<option value="artist"<?php if ('artist' == $O['order_by']) echo ' selected="selected"'; ?>><?php echo $O['artist_name']; ?></option>
					<option value="title"<?php if ('title' == $O['order_by']) echo ' selected="selected"'; ?>><?php echo $O['title_name']; ?></option>
				</select>
				<p class="description"><?php _e("What should the song list be ordered by?", 'tf-song-list'); ?></p>
			</td>
		</tr>
		</table><!-- .form-table -->
		<?php submit_button(); ?>
	</form>
</div><!-- .wrap -->
<?php
	} // function print_page
} // class TFSLSettings
