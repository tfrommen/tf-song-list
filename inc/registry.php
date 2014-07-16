<?php # -*- coding: utf-8 -*-
if (! defined('ABSPATH')) exit;


/**
 * Abstract class TFSLRegistry.
 *
 * Provides basic registry functionality.
 *
 * @since 1.0.0
 */
abstract class TFSLRegistry {


	// Registry
	private static $registry = array();


	/**
	 * Check if given variable is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param	string	$key	Variable name
	 * @return	bool
	 */
	public static function has($key) {
		return isset(self::$registry[$key]);
	} // public static function has


	/**
	 * Register new variable with given value.
	 *
	 * Due to the keyword `protected` only child classes have access.
	 *
	 * @since 1.0.0
	 *
	 * @param	string	$key	Variable name
	 * @param	unknown	$value	Variable value
	 * @return
	 */
	protected static function set($key, $value) {
		if (! self::has($key)) self::$registry[$key] = $value;
		else throw new Exception(
			'Warning: Variable `'.$key.'` could not be set, as it was already set.'
		);
	} // protected static function set


	/**
	 * Get value for given variable.
	 *
	 * @since 1.0.0
	 *
	 * @param	string	$key	Variable name
	 * @return	unknown | null
	 */
	public static function get($key) {
		return (self::has($key) ? self::$registry[$key] : null);
	} // public static function get


	/**
	 * Get values of all registered variables.
	 *
	 * @since 1.0.0
	 *
	 * @return	array
	 */
	public static function get_all() {
		return self::$registry;
	} // public static function get_all


	/**
	 * Deregister given variable.
	 *
	 * @since 1.0.0
	 *
	 * @param	string	$key	Variable name
	 */
	public static function remove($key) {
		if (self::has($key)) unset(self::$registry[$key]);
	} // public static function remove


	/**
	 * Deregister all registered variables (i.e., clear the registry).
	 *
	 * @since 1.0.0
	 */
	public static function remove_all() {
		self::$registry = array();
	} // public static function remove_all
} // abstract class TFSLRegistry
