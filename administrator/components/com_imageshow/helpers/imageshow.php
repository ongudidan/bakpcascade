<?php
/**
 * @version    $Id: imageshow.php 16294 2012-09-22 04:07:32Z giangnd $
 * @package    JSN.ImageShow
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 *
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * ImageShow component helper.
 *
 * @package  JSN.ImageShow
 *
 * @since    2.5
 */

class JSNISImageShowHelper
{

	/**
	 * Configure the linkbar
	 *
	 * @param   string  $controller  The name of the active controller
	 *
	 * @return	void
	 */

	public static function addSubmenu($controller)
	{
		JSubMenuHelper::addEntry(JText::_('JSN_IMAGESHOW_MENU_LAUNCHPAD'), 'index.php?option=com_imageshow', $controller == '');
		JSubMenuHelper::addEntry(JText::_('JSN_IMAGESHOW_MENU_SHOWLISTS'), 'index.php?option=com_imageshow&controller=showlist', $controller == 'showlist');
		JSubMenuHelper::addEntry(JText::_('JSN_IMAGESHOW_MENU_SHOWCASES'), 'index.php?option=com_imageshow&controller=showcase', $controller == 'showcase');
		JSubMenuHelper::addEntry(JText::_('JSN_IMAGESHOW_MENU_CONFIGURATION'), 'index.php?option=com_imageshow&controller=maintenance', $controller == 'maintenance');
		//JSubMenuHelper::addEntry(JText::_('JSN_IMAGESHOW_MENU_HELP'), 'index.php?option=com_imageshow&controller=help', $controller == 'help');
		JSubMenuHelper::addEntry(JText::_('JSN_IMAGESHOW_MENU_ABOUT'), 'index.php?option=com_imageshow&controller=about', $controller == 'about');
	}
}
