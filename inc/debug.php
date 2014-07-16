<?php # -*- coding: utf-8 -*-
if (! defined('ABSPATH')) exit;


/**
 * Class TFSLDebug.
 *
 * Handles listing of registry variables and plugin settings.
 * Requires registry variable `DEBUG` set to true.
 *
 * @since 1.0.0
 */
class TFSLDebug {


	/**
	 * Print the Debug page for the plugin admin menu.
	 *
	 * @since 1.0.0
	 */
	function print_page() {
?>
<div class="wrap debug">
	<h2>Song List &rsaquo; <?php _e("Debug", 'tf-song-list'); ?></h2>
	<h3><?php _e("Constants", 'tf-song-list'); ?></h3>
	<table>
		<?php
			// Print registered variables
			foreach (TFSongList::get_all() as $key => $value) {
				echo "<tr><td><code>$key</code></td><td><pre>";
				print_r($value);
				echo "</pre></td></tr>".PHP_EOL;
				if ('DEFAULT_OPTIONS' === $key)
					$defaults = $value;
			}
		?>
	</table>
	<h3><?php _e("Settings", 'tf-song-list'); ?></h3>
	<table class="options">
		<?php
			// Print options
			foreach (TFSongList::get_options() as $key => $value) {
				$class = (($defaults[$key] === $value) ? '' : ' class="custom"');
				echo "<tr><td><code>$key</code></td><td><pre$class>";
				print_r($value);
				echo "</pre></td></tr>".PHP_EOL;
			}
		?>
	</table>
</div><!-- .wrap -->
<?php
	} // function print_page
} // class TFSLDebug
