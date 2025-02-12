<?php
/**
 * @version     $Id: poweradmin.php 16454 2012-09-26 09:13:12Z hiepnv $
 * @package     JSNPoweradmin
 * @subpackage  item
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 *
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Poweradmin component helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_poweradmin
 * @since       1.6
 */
class PoweradminHelper
{

	/**
	 * Method to add side menu
	 *
	 * @param   string  $vName  The name of the active view
	 *
	 * @return	void
	 */
	public static function addSubmenu($vName)
	{
		JToolbarHelper::custom('index.php?option=' . JRequest::getCmd('option', 'com_poweradmin') . '&view=rawmode', 'jsn-config','',JText::_('JSN_POWERADMIN_MENU_RAWMODE_TEXT'));

// 		JSubMenuHelper::addEntry(
// 				JText::_('JSN_POWERADMIN_MENU_RAWMODE_TEXT'), 'index.php?option=' . JRequest::getCmd('option', 'com_poweradmin') . '&view=rawmode', $vName == 'rawmode'
// 		);
// 		JSubMenuHelper::addEntry(
// 				JText::_('JSN_POWERADMIN_MENU_SITESEARCH_TEXT'), 'index.php?option=' . JRequest::getCmd('option', 'com_poweradmin') . '&task=search.query', $vName == 'search'
// 		);
// 		JSubMenuHelper::addEntry(
// 				JText::_('JSN_POWERADMIN_MENU_CONFIGURATION_TEXT'), 'index.php?option=' . JRequest::getCmd('option', 'com_poweradmin') . '&view=configuration', $vName == 'configuration'
// 		);
// 		JSubMenuHelper::addEntry(
// 				JText::_('JSN_POWERADMIN_MENU_ABOUT_TEXT'), 'index.php?option=' . JRequest::getCmd('option', 'com_poweradmin') . '&view=about', $vName == 'about'
// 		);

	}

	private static $_cachedManifest = null;
    private static $_installedComponents = null;

    function getAssetsPath()
    {
        return JURI::root().'administrator/components/com_poweradmin/assets/';
    }

    /**
     * Retrieve current version of PowerAdmin from manifest file
     * @return string version
     */
    public static function getVersion ()
    {
        return self::getCachedManifest()->version;
    }


    /**
     * Retrieve cached manifest information from database
     * @return object
     */
    public static function getCachedManifest ($extension = 'com_poweradmin')
    {
        if (self::$_cachedManifest === null) {
            $dbo = JFactory::getDbo();
            $dbo->setQuery(
                sprintf(
                    'SELECT manifest_cache FROM #__extensions WHERE element=%s LIMIT 1',
                    $dbo->quote($extension)
                )
            );

            self::$_cachedManifest = json_decode($dbo->loadResult());
        }

        return self::$_cachedManifest;
    }

    /**
    * Return array of search coverage
    */
    public static function getSearchCoverages()
    {
        $coverages = array(
            'articles',
            'categories',
            'components',
            'modules',
            'plugins',
            'menus',
            'adminmenus',
            'templates',
            'users'
        );

        include_once (JPATH_ROOT . '/administrator/components/com_poweradmin/helpers/extensions.php');
        $installedComponents = self::getInstalledComponents();
        $supportedList	= JSNPaExtensionsHelper::getSupportedExtList();

        if (count($supportedList))
        {
			foreach ($supportedList as $extName=>$value)
			{
 				if (in_array($extName, $installedComponents))
 		        {
		            $coverages[] = $value->coverage;
		        }

			}
        }

//         if (in_array('com_k2', $installedComponents))
//         {
//             $coverages[] = JSN_3RD_EXTENSION_STRING . '-k2';
//         }

//         if (in_array('com_zoo', $installedComponents)) {
//         	$coverages[] = 'zoo';
//         }

//         if (in_array('com_easyblog', $installedComponents)) {
//         	$coverages[] = 'easyblog';
//         }

//         if (in_array('com_virtuemart', $installedComponents)) {
//         	$coverages[] = 'virtuemart';
//         }

        return $coverages;
    }

    /**
     * Retrieve list installed components
     * @return mixed
     */
    public static function getInstalledComponents ()
    {
        if (self::$_installedComponents == null) {
            $dbo = JFactory::getDBO();
            $dbo->setQuery("SELECT element FROM #__extensions WHERE type='component'");

            self::$_installedComponents = $dbo->loadColumn();
        }

        return self::$_installedComponents;
    }

    /**
     * Genarate url with suffix is current
     * version of jsn poweradmin
     */
    public static function makeUrlWithSuffix($fileUrl)
    {
        $currentVersion = '';
        if($fileUrl){
            $currentVersion     = self::getVersion();
            $fileUrl    .= '?v=' . $currentVersion;
        }
        return $fileUrl;
    }
}
