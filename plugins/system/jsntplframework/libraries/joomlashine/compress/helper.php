<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  TPLFramework
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * CSS Compression engine
 * 
 * @package     TPLFramework
 * @subpackage  Plugin
 * @since       1.0.0
 */
abstract class JSNTplCompressHelper
{
	/**
	 * Retrieve path to file in hard disk based from file URL
	 * 
	 * @param   string  $file  URL to the file
	 * @return  string
	 */
	public static function getFilePath ($file)
	{
		// Located file from root
		if (strpos($file, '/') === 0) {
			return realpath($_SERVER['DOCUMENT_ROOT'] . '/' . $file);
		}

		if (strpos($file, '://') !== false && JURI::isInternal($file)) {
			$path = parse_url($file, PHP_URL_PATH);
			return realpath($_SERVER['DOCUMENT_ROOT'] . '/' . $path);
		}
		
		$rootURL = JUri::root();
		$currentURL = JUri::current();

		$currentPath = JPATH_ROOT . '/' . substr($currentURL, strlen($rootURL));
		$currentPath = str_replace(DIRECTORY_SEPARATOR, '/', $currentPath);
		$currentPath = dirname($currentPath);

		return JPath::clean($currentPath . '/' . $file);
	}

	/**
	 * Retrieve absolute path from the current path
	 * 
	 * @param   string  $currentPath  Current path
	 * @param   string  $filePath     File path
	 * @return  string
	 */
	public static function getRelativeFilePath ($currentPath, $filePath)
	{
		$currentPath = str_replace('\\', '/', $currentPath);
		$realPath = realpath(realpath($currentPath) . '/' . $filePath);
		$rootPath = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);

		return substr($realPath, strlen($rootPath));
	}

	/**
	 * Parse attributes from the html tag
	 * 
	 * @param   string  $markup  HTML Markup of the tag
	 * @return  array
	 */
	public static function parseAttributes ($markup)
	{
		$attributes = array();
		// Parse attributes by using regular expression
		if (preg_match_all('/\s*([a-z]+)\s*=(["|\']([^"|\']+)["|\'])/i', $markup, $matches))
			$attributes = array_combine(
				array_map('strtolower',
					array_map('trim',
						$matches[1]
					)
				), $matches[3]);
		// Return the parsed attibutes
		return $attributes;
	}
}