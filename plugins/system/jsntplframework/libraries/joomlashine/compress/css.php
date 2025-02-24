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
abstract class JSNTplCompressCss
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
		$groupType	= 'screen';
		$groupFiles	= array();
		$compress	= array();

		// Sometime, stylesheet file need to be stored in the original file name
		$document = JFactory::getDocument();
		$leaveAlone = preg_split('/[\r\n]+/', $document->params->get('compressionExclude'));

		// We already know that the file galleria.classic.css must be excluded
		$leaveAlone[] = 'galleria.classic.css';

		// Goto each link tag to parse attributes and add parsed file
		// to compress list
		foreach (explode('>', $htmlMarkup[0]) AS $line)
		{
			$attributes = JSNTplCompressHelper::parseAttributes($line);

			// Set default media attribute
			$attributes['media'] = ! isset($attributes['media']) ? '' : strtolower($attributes['media']);

			// Skip if not have attibute href
			if ( ! isset($attributes['href']))
			{
				continue;
			}

			// Add to result list if is external file
			if ( ! JUri::isInternal($attributes['href']))
			{
				$compress[] = array(
					'href' => $attributes['href'],
					'media' => $attributes['media']
				);

				continue;
			}

			// Set a special media attribute
			$stylesheetName = basename(
				($pos = strpos($attributes['href'], '?')) !== false ? substr($attributes['href'], 0, $pos) : $attributes['href']
			);

			if (in_array($stylesheetName, $leaveAlone))
			{
				$attributes['media'] .= '|reserve|' . $stylesheetName;
			}

			// Create new compression group when media attribute different with group type
			if ($attributes['media'] != $groupType)
			{
				// Add collected files to compress list
				if (isset($groupFiles[$groupIndex]) AND ! empty($groupFiles[$groupIndex]))
				{
					$compress[] = array(
						'files' => $groupFiles[$groupIndex],
						'media' => $groupType
					);
				}

				// Increase index number of the group
				$groupIndex++;
				$groupType = $attributes['media'];
			}

			// Initial group
			if ( ! isset($groupFiles[$groupIndex]))
			{
				$groupFiles[$groupIndex] = array();
			}

			$href = $attributes['href'];
			$queryStringIndex = strpos($href, '?');

			if ($queryStringIndex !== false)
			{
				$href = substr($href, 0, $queryStringIndex);
			}

			// Add file to the group
			$groupFiles[$groupIndex][] = $href;
		}

		// Add collected files to result list
		if (isset($groupFiles[$groupIndex]) AND ! empty($groupFiles[$groupIndex]))
		{
			$compress[] = array(
				'files' => $groupFiles[$groupIndex],
				'media' => $groupType
			);
		}

		// Initial compress result
		$compressResult = array();

		// Loop to each compress element to compress file
		foreach ($compress AS $group)
		{
			// Ignore compress when group is a external file
			if (isset($group['href']))
			{
				$link = '<link rel="stylesheet" href="' . $group['href'] . '" ';

				if (isset($group['media']) AND ! empty($group['media']))
				{
					$link.= 'media="' . $group['media'] . '" ';
				}

				$link.= '/>';

				$compressResult[] = $link;

				continue;
			}

			// Template information
			$templateName = JFactory::getApplication()->getTemplate();

			// Generate path to cache directory
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
			if (isset($group['media']) AND preg_match('/\|reserve\|(.+)$/', $group['media'], $m))
			{
				$compressFile = $m[1];
			}
			else
			{
				$compressFile = md5(implode('', $group['files'])) . '.css';
			}

			// Create temporary file if not exists
			is_dir($compressPath) OR mkdir($compressPath);

			// Check last modified time for each file in the group
			foreach ($group['files'] AS $file)
			{
				$path = JSNTplCompressHelper::getFilePath($file);
				$lastModified = (is_file($path) && filemtime($path) > $lastModified) ? filemtime($path) : $lastModified;
			}

			// Compress group when expired
			if ( ! is_file($compressPath . $compressFile) OR filemtime($compressPath . $compressFile) < $lastModified)
			{
				// Open cache file in write mode
				$fileHandle = fopen($compressPath . $compressFile, 'w+');

				// Preset remote file array
				$remoteFiles = array();

				// Preset some variables to hold compression status
				$processedFiles	= array();
				$maxFileSize	= 1024 * (int) $document->params->get('maxCompressionSize');
				$currentSize	= 0;

				// Read content of each file and write it to the cache file
				foreach ($group['files'] AS $file)
				{
					$filePath = JSNTplCompressHelper::getFilePath($file);

					// Skip when cannot access to file
					if ( ! is_file($filePath) OR ! is_readable($filePath))
					{
						continue;
					}

					// Do compression
					$result = trim(self::_loadFileInto($fileHandle, $filePath, $maxFileSize, $currentSize, $remoteFiles));

					if (empty($result))
					{
						// Store processed file
						$processedFiles[] = $file;
					}
					else
					{
						// Close current file handler
						fclose($fileHandle);

						// Rename created cache file
						$newFileName = md5(implode('', $processedFiles)) . '.css';
						rename($compressPath . $compressFile, $compressPath . $newFileName);

						// Add compressed file to the remote file import list
						$remoteFiles[] = str_replace(str_replace('\\', '/', JPATH_ROOT), JUri::root(true), str_replace('\\', '/', $compressPath)) . $newFileName;

						// Open new file handler
						$fileHandle = fopen($compressPath . $compressFile, 'w+');

						// Write returned content to new file
						fwrite($fileHandle, $result);

						// Reset compression status variables
						$currentSize	= strlen($result);
						$processedFiles	= array();
					}
				}

				// Close file handler
				fclose($fileHandle);

				if ( ! empty($remoteFiles))
				{
					for ($n = count($remoteFiles), $i = $n - 1; $i >= 0; $i--)
					{
						self::_prepend("@import url({$remoteFiles[$i]});" . ($i + 1 < $n ? "\n" : "\n\n"), $compressPath . $compressFile);
					}
				}
			}

			// Add compressed file to the compress result list
			$compressUrl = str_replace(str_replace('\\', '/', JPATH_ROOT), JUri::root(true), str_replace('\\', '/', $compressPath)) . $compressFile;

			$link = '<link rel="stylesheet" href="' . $prefix . $compressUrl . '" ';

			if (isset($group['media']) AND ! empty($group['media']))
			{
				$link .= 'media="' . preg_replace('/\|reserve\|(.+)$/', '', $group['media']) . '" ';
			}

			$link .= '/>';

			$compressResult[] = $link;
		}

		return implode("\r\n", $compressResult);
	}

	/**
	 * Load content from a file and append into existing opened file
	 *
	 * @param   resource  $fileHandle          Opened file pointer.
	 * @param   string    $sourcePath          Path to source file.
	 * @param   integer   $maxFileSize         Maximum allowed file size.
	 * @param   integer   &$currentFileSize    Current file size.
	 * @param   array     &$remoteFilesImport  Array of remotely imported file.
	 *
	 * @return  mixed  Compressed content if max file size is reached.
	 */
	private static function _loadFileInto($fileHandle, $sourcePath, $maxFileSize, &$currentFileSize, &$remoteFilesImport)
	{
		// Read source file
		$source = file_get_contents($sourcePath);

		// Clean all comments
		$source = preg_replace('#/\*(.|[\r\n])*?\*/#', '', $source);

		// Rewrite all relative URLs
		if (preg_match_all('/(@import\s+|[a-z0-9\-]+\s*:\s*)([^;\r\n]*)url\s*\(([^\)]+)\)/i', $source, $matches, PREG_SET_ORDER))
		{
			foreach ($matches AS $match)
			{
				$fileUrl = JSNTplCompressHelper::getRelativeFilePath(dirname($sourcePath), trim($match[3], '"\''));

				if (trim($match[1]) != '@import')
				{
					$fileUrl = ltrim(str_replace('\\', '/', $fileUrl), '/');

					if (strpos($match[3], '://') === false && strpos($match[3], '//') !== 0)
					{
						$source = str_replace($match[0], $match[1] . $match[2] . 'url(/' . $fileUrl . ')', $source);
					}
				}
				elseif (strpos($match[3], 'http://') === false)
				{
					// Get file path
					$filePath = getenv('DOCUMENT_ROOT') . $fileUrl;

					// Compress file being imported
					$imports[] = self::_loadFileInto($fileHandle, $filePath, $maxFileSize, $currentFileSize, $remoteFilesImport);

					// Remove @import file inclusion for local file
					$source = str_replace($match[0], '', $source);
				}
				else
				{
					// Store @import file inclusion for remote file
					$remoteFilesImport[] = $match[3];
				}
			}
		}

		// Strip all tab, return and new-line characters
		$source = preg_replace('/[\t\r\n]/', '', $source);

		// Prepend path to source file
		$source	= ($currentFileSize == 0 ? '' : "\n\n")
				. '/* FILE: ' . str_replace(str_replace('\\', '/', JPATH_ROOT), '', str_replace('\\', '/', $sourcePath)) . ' */'
				. "\n{$source}";

		// Get length of processed content
		$length = strlen($source);

		// Check if max file size is reached?
		if ($currentFileSize == 0 OR $currentFileSize + $length <= $maxFileSize)
		{
			// Write processed content to cache file
			fwrite($fileHandle, $source);

			// Update current file size
			$currentFileSize += $length;

			return '';
		}
		else
		{
			return (isset($imports) ? implode($imports) : '') . $source;
		}
	}

	/**
	 * Method to write content to top of file
	 *
	 * @param   string    $string    Content will be appended to file
	 * @param   resource  $filename  File to prepend
	 * @return  void
	 */
	private static function _prepend ($string, $filename)
	{
		$context = stream_context_create();
		$fp = fopen($filename, 'r', 1, $context);
		$tmpname = dirname($filename) . '/' . md5($string) . '.tmp';

		file_put_contents($tmpname, $string);
		file_put_contents($tmpname, $fp, FILE_APPEND);

		fclose($fp);
		unlink($filename);
		rename($tmpname, $filename);
	}
}
