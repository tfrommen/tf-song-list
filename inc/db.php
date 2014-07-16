<?php # -*- coding: utf-8 -*-
if (! defined('ABSPATH')) exit;


/**
 * Class TFSLDB.
 *
 * Handles database-specific functions.
 *
 * @since 1.0.0
 */
class TFSLDB {


	/**
	 * Automatically upgrade the song list database to the current version.
	 *
	 * @since 1.0.0
	 */
	static function autoupgrade() {
		// Prepare required data
		$O = TFSongList::get_options();
		$installed_version = $O['db_version'];
		$current_version = TFSongList::get('DB_VERSION');
		$table = TFSongList::get('TABLE');

		// Adapt the song list table, if necessary
		if (
			($GLOBALS['wpdb']->get_var("SHOW TABLES LIKE '$table'") !== $table)
			|| ($current_version > $installed_version)
		)
			self::dbDelta($table);

		// Upgrade the database to the current version
		if ($current_version > $installed_version) {
			switch($installed_version) {
				case '1.0':
					#self::upgrade_11();
					#self::upgrade_12();
					break;

				case '1.1':
					#self::upgrade_12();
					break;
			}
			$O['db_version'] = $current_version;
			update_option('tfsl_options', $O);
		}
	} // static function autoupgrade


	/**
	 * Use the dbDelta function to adapt the table.
	 *
	 * @since 1.0.0
	 *
	 * @param	string	$table		Table name
	 */
	private function dbDelta($table) {
		global $wpdb;
		$charset_collate = '';
		if (empty($wpdb->charset))
			$charset_collate .= " DEFAULT CHARACTER SET $wpdb->charset";
		if (empty($wpdb->collate))
			$charset_collate .= " COLLATE $wpdb->collate";

		require_once ABSPATH.'wp-admin/includes/upgrade.php';
		dbDelta("CREATE TABLE $table (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			artist TEXT NOT NULL,
			title TEXT NOT NULL,
			public TINYINT(1) NOT NULL DEFAULT 1,
			UNIQUE KEY id (id)
		)$charset_collate;");
	} // private function dbDelta


	/**
	 * Prepare (and sanitize) data that is to be written to the database.
	 *
	 * @since 1.0.0
	 *
	 * @param	string	$data		Various data
	 * @param	bool	$sanitize	Sanitize data (default) or not?
	 * @return	string
	 */
	static function db_in($data, $sanitize = true) {
		$data = addslashes(trim($data));
		return (($sanitize) ? sanitize_text_field($data, true) : wp_kses_post($data));
	} // static function db_in


	/**
	 * Prepare data that was queried from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param	string	$data	Various data
	 * @return	string
	 */
	static function db_out($data) {
		return htmlentities(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
	} // static function db_out
} // class TFSLDB
