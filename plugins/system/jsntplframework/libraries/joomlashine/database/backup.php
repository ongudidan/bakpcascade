<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  JSNTPLFramework
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
 * Class use to backup existing database
 * to a file
 * 
 * @package     JSNTPLFramework
 * @subpackage  Database
 * @since       1.0.0
 */
abstract class JSNTplDatabaseBackup
{
	/**
	 * This method use to dump data of all tables in
	 * the database
	 *
	 * @param   array   $tables  List of tables will be backup
	 * @param   string  $file    File to save exported data
	 * 
	 * @return string
	 */
	public static function dump ($tables, $file)
	{
		$dbo = JFactory::getDBO();
		$existedTables = $dbo->getTableList();

		$fileHandle = fopen($file, 'w+');

		foreach ($tables as $table)
		{
			if (in_array($table, $existedTables))
				self::dumpTable($table, $fileHandle);
		}
	}

	/**
	 * Dump a table data to a file or string
	 * 
	 * @param   string  $table  Name of the table
	 * @param   mixed   $output  Output can be file handler resource, path to file
	 * 
	 * @return  mixed
	 */
	public static function dumpTable ($table, $output)
	{
		$dbo = JFactory::getDBO();
		$createQuery = $dbo->getTableCreate($table);

		$dumpBuffer = array();
		$columns    = array();
		$fileHandle = is_resource($output) ? $output : fopen($output, 'w+');

		// Append create query to file
		fwrite($fileHandle, "DROP TABLE IF EXISTS `{$table}`;\r\n");
		fwrite($fileHandle, current($createQuery) . ";\r\n\r\n");

		// Query all rows in table
		$dbo->setQuery("SELECT * FROM {$table}");

		foreach ($dbo->loadAssocList() as $row)
		{
			$dumpBuffer[] = $row;

			if (empty($columns))
				$columns = array_keys($row);

			if (count($dumpBuffer) == 10) {
				self::_writeBuffer($fileHandle, $table, $columns, $dumpBuffer);
				$dumpBuffer = array();
			}
		}

		if (!empty($dumpBuffer))
			self::_writeBuffer($fileHandle, $table, $columns, $dumpBuffer);
	}

	/**
	 * Save buffered content to file
	 * 
	 * @param   resource  $fileHandle  Resource that pointed to opened file handler
	 * @param   string    $table       Name of the table
	 * @param   array     $columns     Columns of the table
	 * @param   array     $rows        Buffered data rows
	 * 
	 * @return  void
	 */
	private static function _writeBuffer ($fileHandle, $table, $columns, $rows)
	{
		$dbo = JFactory::getDBO();

		// Write insert query
		fwrite($fileHandle, "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\r\n");

		// Buffer parsed content
		$content = array();

		// Loop to each row in buffer and write it to file
		foreach ($rows as $row)
		{
			$values = array();
			foreach ($row as $value)
				$values[] = sprintf('\'%s\'', $dbo->escape($value));

			$content[] = sprintf("\t(%s)", implode(', ', $values));
		}

		fwrite($fileHandle, implode(",\r\n", $content) . ";\r\n\r\n");
	}
}
