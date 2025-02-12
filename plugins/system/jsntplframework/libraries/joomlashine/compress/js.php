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
 * Javascript Compression engine
 *
 * @package     TPLFramework
 * @subpackage  Plugin
 * @since       1.0.0
 */
abstract class JSNTplCompressJs
{
	/**
	 * Method to parse all link to css files from the html markup
	 * and compress it
	 *
	 * @param   string  $htmlMarkup  HTML Content to response to browser
	 * @return  void
	 */
	public static function compress ($htmlMarkup)
	{
		// Get object for working with URI
		$uri = JUri::getInstance();

		// Generate link prefix if current scheme is HTTPS
		$prefix = '';

		if ($uri->getScheme() == 'https')
		{
			$prefix = $uri->toString(array('scheme', 'host', 'port'));
		}

		// Initialize variables
		$groupIndex	= 0;
		$groupType	= 'default';
		$groupFiles	= array();
		$compress	= array();

		// Sometime, script file need to be stored in the original file name
		$document = JFactory::getDocument();
		$leaveAlone = preg_split('/[\r\n]+/', $document->params->get('compressionExclude'));

		// Goto each link tag to parse attributes and add parsed file
		// to compress list
		foreach (explode('>', $htmlMarkup[0]) as $line)
		{
			$attributes = JSNTplCompressHelper::parseAttributes($line);

			// Set default group
			$attributes['group'] = 'default';

			// Skip if not have attibute src
			if (!isset($attributes['src']))
				continue;

			// Add to result list if is external file
			if ( ! JURI::isInternal($attributes['src']) OR strpos($attributes['src'], '//') === 0) {
				// Add collected files to compress list
				if (!empty($groupFiles)) {
					$compress[] = array(
						'files' => $groupFiles[$groupIndex],
						'group' => $groupType
					);
					$groupFiles = array();
				}
				$compress[] = array('src' => $attributes['src']);
				continue;
			}

			// Set a special media attribute
			$scriptName = basename(
				($pos = strpos($attributes['src'], '?')) !== false ? substr($attributes['src'], 0, $pos) : $attributes['src']
			);

			if (in_array($scriptName, $leaveAlone))
			{
				$attributes['group'] = 'reserve|' . $scriptName;
			}

			// Create new compression group if reserving script file name is required
			if ($attributes['group'] != $groupType)
			{
				// Add collected files to compress list
				if (isset($groupFiles[$groupIndex]) && !empty($groupFiles[$groupIndex]))
					$compress[] = array(
						'files' => $groupFiles[$groupIndex],
						'group' => $groupType
					);

				// Increase index number of the group
				$groupIndex++;
				$groupType = $attributes['group'];
			}

			// Initial group
			if (!isset($groupFiles[$groupIndex]))
				$groupFiles[$groupIndex] = array();

			$src = $attributes['src'];
			$queryStringIndex = strpos($src, '?');

			if ($queryStringIndex !== false) {
				$src = substr($src, 0, $queryStringIndex);
			}

			// Add file to the group
			$groupFiles[$groupIndex][] = $src;
		}

		// Add collected files to result list
		if (isset($groupFiles[$groupIndex]) && !empty($groupFiles[$groupIndex])) {
			$compress[] = array(
				'files' => $groupFiles[$groupIndex],
				'group' => $groupType
			);
		}

		// Initial compress result
		$compressResult = array();

		// Loop to each compress element to compress file
		foreach ($compress as $group)
		{
			// Ignore compress when group is a external file
			if (isset($group['src']))
			{
				$compressResult[] = sprintf('<script src="%s" type="text/javascript"></script>', $group['src']);
				continue;
			}

			// Template information
			$templateName	= JFactory::getApplication()->getTemplate();

			// Generate compressed file name
			if ( ! preg_match('#^(/|\\|[a-z]:)#i', $document->params->get('cacheDirectory')))
			{
				$compressPath = JPATH_ROOT . '/' . rtrim($document->params->get('cacheDirectory'), '\\/');
			}
			else
			{
				$compressPath = rtrim($document->params->get('cacheDirectory'), '\\/');
			}

			$compressPath = $compressPath . '/' . $templateName . '/';
			$lastModified = 0;

			// Check if reserving stylesheet file name is required
			if (isset($group['group']) AND preg_match('/^reserve\|(.+)$/', $group['group'], $m))
			{
				$compressFile = $m[1];
			}
			else
			{
				$compressFile = md5(implode('', $group['files'])) . '.js';
			}

			// Create temporary file if not exists
			if (!is_dir($compressPath))
				mkdir($compressPath);

			// Check last modified time for each file in the group
			foreach ($group['files'] as $file)
			{
				$path = JSNTplCompressHelper::getFilePath($file);
				$lastModified = (is_file($path) && filemtime($path) > $lastModified) ? filemtime($path) : $lastModified;
			}

			// Compress group when expired
			if (!is_file($compressPath . $compressFile) || filemtime($compressPath . $compressFile) < $lastModified)
			{
				// Open cache file in write mode
				$fileHandle = fopen($compressPath . $compressFile, 'w+');

				// Go to each file for read content of the file
				// and write it to the cache file
				foreach ($group['files'] as $file)
				{
					$filePath = JSNTplCompressHelper::getFilePath($file);

					// Skip when cannot access to file
					if (!is_file($filePath) || !is_readable($filePath))
						continue;

					// Open source file in read mode
					fwrite($fileHandle, file_get_contents($filePath) . ";\r\n\r\n");
				}

				// Close the file
				fclose($fileHandle);
			}

			// Add compressed file to the compress result list
			$compressUrl = str_replace(str_replace('\\', '/', JPATH_ROOT), JUri::root(true), str_replace('\\', '/', $compressPath)) . $compressFile;
			$compressResult[] = sprintf('<script src="%s" type="text/javascript"></script>', $prefix . $compressUrl);
		}

		return implode("\r\n", $compressResult);
	}
}
