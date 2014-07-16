<?php # -*- coding: utf-8 -*-
if (! defined('ABSPATH')) exit;


/**
 * Plugin Name: tf Song List
 * Plugin URI: http://wordpress.org/extend/plugins/tf-song-list/
 * Description: An easy-to-use song listing plugin for bands and solo musicians. Insert with shortcode [tf_song_list].
 * Version: 1.1.0
 * Author: Thorsten Frommen
 * Author URI: http://ipm-frommen.de
 * License: GPL2
 *
 * Copyright 2013 THORSTEN FROMMEN  (email : tf@ipm-frommen.de)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


// Require all PHP files inside the `inc` directory
foreach (glob(dirname(__FILE__).'/inc/*.php') as $file) require_once $file;


/**
 * Main class TFSongList.
 *
 * Handles construction, (de-)activation and uninstall.
 * Serves as registry for plugin-specific data needed by various classes.
 *
 * @since 1.0.0
 */
final class TFSongList extends TFSLRegistry {


	/**
	 * Construct class TFSongList.
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		// Populate registry
		self::set('VERSION', '1.1.0');
		self::set('DB_VERSION', '1.0');
		self::set('CAPABILITY', 'manage_tf_song_list');
		self::set('DEBUG', false);
		self::set('PLUGIN_FILE', plugin_basename(__FILE__));
		self::set('DIR', dirname(__FILE__));
		self::set('BASE', basename(self::get('DIR')));
		self::set('URL', WP_PLUGIN_URL.'/'.self::get('BASE'));
		self::set('OPTION_NAME', 'tfsl_options');
		self::set('TABLE', $GLOBALS['wpdb']->prefix.'tfsl_songs');

		// Define default options
		$defaults = array(
			'db_version' => self::get('DB_VERSION'),
			'introduction' => '',
			'show_introduction' => 1,
			'wrap_introduction' => 1,
			'show_headings' => 1,
			'artist_name' => __("Artist", 'tf-song-list'),
			'title_name' => __("Title", 'tf-song-list'),
			'first_column' => 'artist',
			'order_by' => 'artist',
		);
		self::set('DEFAULT_OPTIONS', $defaults);

		// Register hooks
		register_activation_hook(__FILE__, array(__CLASS__, 'activation'));
		register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivation'));
		register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

		// Add actions, ...
		add_action('init', array(__CLASS__, 'load_textdomain'));
		add_action('admin_menu', array(__CLASS__, 'admin_menu'));
		add_action('admin_init', array(__CLASS__, 'register_settings'));
		add_action('right_now_content_table_end', array(__CLASS__, 'right_now_content_songs'));
		add_action('admin_post_tfsl_export', array('TFSLImportExport', 'export'));
		add_action('admin_post_nopriv_tfsl_export', array('TFSLImportExport', 'export_nopriv'));
		add_action('template_redirect', array(__CLASS__, 'head'));

		// ... filters, ...
		add_filter('plugin_row_meta', array(__CLASS__, 'add_action_links'), 10, 2);
		add_filter('upload_mimes', array(__CLASS__, 'upload_mimes'));

		// ... and shortcodes
		add_shortcode('tf_song_list', array(__CLASS__, 'tf_song_list'));
	} // function __construct


	/**
	 * Perform tasks when the plugin is being activated.
	 *
	 * @since 1.0.0
	 */
	function activation() {
		// Add plugin-specific custom capability
		self::add_cap();

		// Register plugin settings
		self::register_settings();

		// Automatically upgrade database to current version
		TFSLDB::autoupgrade();
	} // function activation


	/**
	 * Perform tasks when the plugin is being deactivated.
	 *
	 * @since 1.0.0
	 */
	function deactivation() {
		// Remove plugin-specific custom capability
		self::remove_cap();
	} // function deactivation


	/**
	 * Perform tasks when the plugin is being uninstalled.
	 *
	 * @since 1.0.0
	 */
	static function uninstall() {
		// Drop song list table
		$GLOBALS['wpdb']->query("DROP TABLE IF EXISTS ".self::get('TABLE'));

		// Delete plugin options
		delete_option(self::get('OPTION_NAME'));
	} // static function uninstall


	/**
	 * Add the plugin-specific custom capability to all editable roles capable of editing pages.
	 *
	 * @since 1.0.0
	 */
	private function add_cap() {
		$min_cap = 'edit_pages';
		$roles = get_editable_roles();
		foreach ($GLOBALS['wp_roles']->role_objects as $key => $role)
			if (isset($roles[$key]) && $role->has_cap($min_cap))
				$role->add_cap(self::get('CAPABILITY'));
	} // private function add_cap


	/**
	 * Remove the plugin-specific custom capability.
	 *
	 * @since 1.0.0
	 */
	private function remove_cap() {
		$cap = self::get('CAPABILITY');
		$roles = get_editable_roles();
		foreach ($GLOBALS['wp_roles']->role_objects as $key => $role)
			if (isset($roles[$key]) && $role->has_cap($cap))
				$role->remove_cap($cap);
	} // private function remove_cap


	/**
	 * Register the plugin settings.
	 *
	 * If options not yet defined, store default options.
	 *
	 * @since 1.0.0
	 */
	function register_settings() {
		register_setting(
			self::get('OPTION_NAME'),
			self::get('OPTION_NAME'),
			array(__CLASS__, 'sanitize_options')
		);
		if (false === get_option(self::get('OPTION_NAME')))
			update_option(self::get('OPTION_NAME'), self::get_options());
	} // function register_settings


	/**
	 * Sanitize and return the given array of option values.
	 *
	 * Options equal to their respective default value are removed.
	 *
	 * @since 1.0.0
	 *
	 * @param	array	$options	Option values
	 * @return	array
	 */
	function sanitize_options($options = array()) {
		// Nothing to do here
		if (! is_array($options)) $options = array();
		if (empty($options)) return $options;

		// Special treatment of checkbox variables
		$checkboxes = array(
			'show_introduction',
			'wrap_introduction',
			'show_headings',
		);
		// Cast string(1) values to int
		foreach ($checkboxes as $key)
			if (isset($options[$key])) $options[$key] = (int) $options[$key];

		// Remove options equal to their respective default value
		$defaults = self::get('DEFAULT_OPTIONS');
		foreach ($options as $key => $value)
			if ($value === $defaults[$key]) unset($options[$key]);

		// Sanitize now
		$sanitized = array();
		foreach ($options as $key => $value) {
			switch ($key) {
				case 'db_version':
					// The version number is restricted to digits and period only
					$sanitized[$key] = preg_replace('/[^0-9.]/', '', $value);
					break;

				case 'introduction':
					// Sanitize string for allowed HTML tags for post content
					if ('' !== $value) $sanitized[$key] = wp_kses_post($value);
					break;

				case 'show_introduction':
				case 'wrap_introduction':
				case 'show_headings':
					// These checkbox variables are clean
					$sanitized[$key] = $value;
					break;

				case 'artist_name':
				case 'title_name':
					// Trim and sanitize strings, check for invalid UTF-8, strip all tags
					if ('' !== $value) $sanitized[$key] = sanitize_text_field($value);
					break;

				case 'first_column':
				case 'order_by':
					// The default value is `artist`, so this variable can only be `title`
					$sanitized[$key] = 'title';
					break;
			}
		}
		return $sanitized;
	} // function sanitize_options


	/**
	 * Return the sanitized plugin option values.
	 *
	 * Missing values are replaced by their defaults.
	 *
	 * @since 1.0.0
	 *
	 * @return	array
	 */
	static function get_options() {
		return array_merge(
			self::get('DEFAULT_OPTIONS'),
			self::sanitize_options(get_option(self::get('OPTION_NAME')))
		);
	} // static function get_options


	/**
	 * Load the plugin textdomain to allow for internationalization.
	 *
	 * @since 1.0.0
	 */
	function load_textdomain() {
		load_plugin_textdomain('tf-song-list', false, self::get('BASE').'/languages');
	} // function load_textdomain


	/**
	 * Add plugin menu pages to admin menu.
	 *
	 * @since 1.0.0
	 */
	function admin_menu() {
		// Add plugin-specific custom capability
		self::add_cap();
		$capability = self::get('CAPABILITY');

		// Set up plugin admin menu
		$songs = __("Songs", 'tf-song-list');
		$settings = __("Settings", 'tf-song-list');
		$import_export = __("Import/Export", 'tf-song-list');
		$menu_pages = array();
		$menu_pages[] = add_menu_page(
			"Song List &rsaquo; $songs",
			"Song List",
			$capability,
			'tfsl-songs',
			array('TFSLSongs', 'print_page'),
			'dashicons-format-audio'
		);
		$menu_pages[] = add_submenu_page(
			'tfsl-songs',
			"Song List &rsaquo; $songs",
			$songs,
			$capability,
			'tfsl-songs',
			array('TFSLSongs', 'print_page')
		);
		$menu_pages[] = add_submenu_page(
			'tfsl-songs',
			"Song List &rsaquo; $settings",
			$settings,
			$capability,
			'tfsl-settings',
			array('TFSLSettings', 'print_page')
		);
		$menu_pages[] = add_submenu_page(
			'tfsl-songs',
			"Song List &rsaquo; $import_export",
			$import_export,
			$capability,
			'tfsl-import-export',
			array('TFSLImportExport', 'print_page')
		);

		// When debugging, add according submenu page
		if (self::get('DEBUG'))
			$menu_pages[] = add_submenu_page(
				'tfsl-songs',
				"Song List &rsaquo; Debug",
				"Debug",
				'manage_options',
				'tfsl-debug',
				array('TFSLDebug', 'print_page')
			);

		// Add plugin-specific actions
		foreach ($menu_pages as $page) {
			add_action('load-'.$page, array(__CLASS__, 'admin_head'));
			add_action('load-'.$page, array(__CLASS__, 'admin_help'));
		}
	} // function admin_menu


	/**
	 * Enqueue admin styles and scripts.
	 *
	 * @since 1.0.0
	 */
	function admin_head() {
		// Enqueue stylesheets
		wp_enqueue_style(
			'tfsl-admin',
			self::get('URL').'/assets/css/admin.css',
			array(),
			self::get('VERSION')
		);
	} // function admin_head


	/**
	 * Provide admin help.
	 *
	 * @since 1.0.0
	 */
	function admin_help() {
		// Help tab
		$screen = get_current_screen();
		$screen->add_help_tab(array(
				'id' => 'tfsl_help',
				'title' => __("Help", 'tf-song-list'),
				'content' => '<p>'.__("The plugin settings should be fairly self-explanatory.<br />However, if you encounter any difficulties, please first have a look at the plugin website. If you still can't find a solution to your problem, feel free to contact me.", 'tf-song-list').'</p>',
		));

		// Help sidebar
		$sidebar = '<p><strong>'.__("More information:", 'tf-song-list').'</strong></p>'
			.'<p><a href="http://ipm-frommen.de/wordpress/tf-song-list" '
			.'title="Thorsten Frommen | Internet Print Multimedia" target="_blank">'
			.__("Plugin Website", 'tf-song-list')
			.'</a></p>';
		$screen->set_help_sidebar($sidebar);
	} // function admin_help


	/**
	 * Add number of songs to Right Now.
	 *
	 * @since 1.0.0
	 */
	function right_now_content_songs() {
		global $wpdb;

		// Query songs
		$songs = $wpdb->get_results("SELECT public FROM ".self::get('TABLE'));

		// Prepare values
		$number = '<span class="total-count">'.number_format_i18n($wpdb->num_rows).'</span>';
		$text = _n("Song", "Songs", $wpdb->num_rows, 'tf-song-list');

		// Wrap into links, if current user capable of editing song list
		if (current_user_can(self::get('CAPABILITY'))) {
			$number = '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=tfsl-songs">'
				.$number
				.'</a>';
			$text = '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=tfsl-songs">'
				.$text
				.'</a>';
		}

		// Print row
		echo '<tr>'
			.'<td class="first b b-songs">'.$number.'</td>'
			.'<td class="t songs">'.$text.'</td>'
			.'</tr>'.PHP_EOL;
	} // function right_now_content_songs


	/**
	 * Enqueue front-end styles and scripts.
	 *
	 * @since 1.0.0
	 */
	function head() {
		// Enqueue default stylesheet, ...
		wp_enqueue_style(
			'tfsl-default',
			self::get('URL').'/assets/css/tf-song-list.css',
			array(),
			self::get('VERSION')
		);

		// ... custom stylesheet from (parent) theme directory, if present, ...
		if (file_exists(get_template_directory().'/tf-song-list.css'))
			wp_enqueue_style(
				'tfsl-custom',
				get_template_directory_uri().'/tf-song-list.css'
			);

		// ... and custom stylesheets from (child) theme directory, if present
		if ((get_template_directory() !== get_stylesheet_directory())
			&& file_exists(get_stylesheet_directory().'/tf-song-list.css')
		)
			wp_enqueue_style(
				'tfsl-custom-child',
				get_stylesheet_directory_uri().'/tf-song-list.css'
			);
	} // function head

	/**
	 * Add custom links to the plugin descritpion on the Installed Plugins page.
	 *
	 * @since 1.0.6
	 *
	 * @param	array	$links	Current links
	 * @param	string	$file	Plugin file name
	 * @return	array
	 */
	function add_action_links($links, $file) {
		if (self::get('PLUGIN_FILE') === $file) {
			$links[] = '<a href="'.get_bloginfo('url').'/wp-admin/admin.php?page=tfsl-settings"'
				.'title="'.__("Settings", 'tf-song-list').'">'
				.__("Settings", 'tf-song-list')
				.'</a>';
			$links[] = '<a href="http://ipm-frommen.de/wordpress/tf-song-list#documentation" '
				.'title="'.__("Documentation", 'tf-song-list').'" target="_blank">'
				.__("Documentation", 'tf-song-list')
				.'</a>';
		}
		return $links;
	} // function add_action_links

	/**
	 * Add custom MIME types to the existing ones.
	 *
	 * @since 1.0.0
	 *
	 * @param	array	$mimes	Existing MIME types
	 * @return	array
	 */
	function upload_mimes($mimes = array()) {
		// Allow CSV files (required for import functionality)
		$mimes['csv'] = 'text/csv';
		return $mimes;
	} // function upload_mimes


	/**
	 * Wrap the given string into the given HTML element tags.
	 *
	 * Optional: provide id and/or class attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param	string	$string		Text that is to be wrapped
	 * @param	string	$element	HTML element, paragraph tag <p> by default
	 * @param	string	$id			Optional wrapper id attribute
	 * @param	string	$class		Optional wrapper class attribute
	 * @return	string
	 */
	private function wrap($string, $element = 'p', $id = false, $class = false) {
		$wrap = "<$element";
		if ($id) $wrap .= " id=\"$id\"";
		if ($class) $wrap .= " class=\"$class\"";
		$wrap .= ">$string</$element>";
		return $wrap;
	} // private function wrap


	/**
	 * Return songs as HTML table rows.
	 *
	 * @since 1.0.0
	 *
	 * @return	string
	 */
	private function get_song_rows() {
		// Configure neccessary settings
		$O = self::get_options();
		$col1 = $O['first_column'];
		$col2 = (('artist' == $col1) ? 'title' : 'artist');
		$order = $O['order_by'];
		$suborder = (($order == $col1) ? $col2 : $col1);

		// Query songs from database
		$songs = $GLOBALS['wpdb']->get_results(
			"SELECT *
			FROM ".self::get('TABLE')."
			WHERE public=1
			ORDER BY $order ASC, $suborder ASC"
		);
		$rows = '';
		if (! empty($songs))
			$current_first_letter = '';
			foreach ($songs as $song) {
				$new_first_letter = substr($song->$order, 0, 1);
				$class = '';
				if ($current_first_letter != $new_first_letter)
					$class = ' class="new-first-letter"';
				$current_first_letter = $new_first_letter;
				$rows .= '<tr'.$class.' id="$order">'
					.'<td class="col1">'.TFSLDB::db_out($song->$col1).'</td>'
					.'<td class="col2">'.TFSLDB::db_out($song->$col2).'</td>'
					.'</tr>'.PHP_EOL;
			}
		return $rows;
	} // private function get_song_rows


	/**
	 * Print the whole song list according to its settings.
	 *
	 * @since 1.0.0
	 *
	 * @return	string
	 */
	function tf_song_list() {
		// Configure neccessary settings
		$O = self::get_options();
		$intro = nl2br($O['introduction']);
		$col1 = $O['first_column'];
		$col2 = (('artist' == $col1) ? 'title' : 'artist');

		// Print song list
		$song_list = '';
		if (($O['show_introduction']) && ('' != $intro))
			$song_list .= self::wrap(
				$intro,
				(($O['wrap_introduction']) ?  'p' : 'span'),
				'tfsl-introduction',
				''
			).PHP_EOL;
		$song_list .= '<div id="tfsl-wrapper">'.PHP_EOL
			.'<table id="tf-song-list">'.PHP_EOL;
		if ($O['show_headings']) {
			$song_list .= '<thead>'.PHP_EOL
				.'<tr>'
				.'<td class="col1">'.esc_html($O[$col1.'_name']).'</td>'
				.'<td class="col2">'.esc_html($O[$col2.'_name']).'</td>'
				.'<tr>'.PHP_EOL
				.'</thead>'.PHP_EOL;
		}
		$song_list .= '<tbody id="tfsl-the-list">'.PHP_EOL
			.self::get_song_rows()
			.'</tbody><!-- #tfsl-the-list -->'.PHP_EOL
			.'</table><!-- #tf-song-list -->'.PHP_EOL
			.'</div><!-- #tfsl-wrapper -->';
		return $song_list;
	} // function tf_song_list
} // final class TFSongList

// Create TFSongList object
$TFSongList = new TFSongList();
