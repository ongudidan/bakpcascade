<?php
/**
 * @version     $Id: jsnmenubutton.php 17065 2012-10-16 04:06:37Z giangnd $
 * @package     JSN.ImageShow
 * @subpackage  item
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 *
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.utilities.utility');
include_once JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'jsn_is_showlist.php';
include_once JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'jsn_is_showcase.php';

/**
 * Button base class
 *
 * The JButton is the base class for all JButton types
 *
 * @package  JSN.ImageShow
 *
 * @since    2.5
 */
class JButtonJSNMenuButton extends JButton
{

	/**
	 * element name
	 *
	 * This has to be set in the final renderer classes.
	 *
	 * @param   string The name of JButton.
	 */

	protected $_name = 'JSNMenuButton';

	/**
	 * Get the button
	 *
	 * Defined in the final button class
	 *
	 * @param   string  $type  The name of JButton.
	 *
	 * @since   2.5
	 * @return string
	 */

	public function fetchButton($type = 'JSNMenuButton')
	{
		$tmpl = JRequest::getVar('tmpl');

		if ($tmpl == 'component') return '';

		$edit 		= JRequest::getVar('edit');
		$text		= JText::_('JSN_MENU_BUTTON');
		$document 	= JFactory::getDocument();
		$strAlert 	= '';

		if (!is_null($edit))
		{
			$strAlert = 'var objISOneImageShow = new $.JQJSNISImageShow();
							 objISOneImageShow.comfirmBox("' . JText::_('JSN_MENU_CONFIRM_BOX_ALERT', true) . '");';
		}

		$document->addScriptDeclaration("
				(function($){
					$(document).ready(function () {
					" . $strAlert . "
					});
				})(jQuery);");

		$objJSNShowlist 		= new JSNISShowlist;
		$objJSNShowcase 		= new JSNISShowcase;
		$options		 		= array();
		$options[] 				= array(
				'title' 					=> JText::_('JSN_MENU_LAUNCH_PAD'),
				'link'						=> 'index.php?option=com_imageshow',
				'class'				   		=> 'parent primary',
				'icon'						=> 'jsn-icon-off',
		);
		$options[] 				= array(
				'title' 					=> JText::_('JSN_MENU_SHOWLISTS'),
				'link'						=> 'index.php?option=com_imageshow&controller=showlist',
				'class'				   		=> 'parent primary',
				'sub_menu_link'		   		=> 'index.php?option=com_imageshow&controller=showlist&task=edit&cid[]={$item_id}',
				'sub_menu_field_title'		=> 'item_title',
				'sub_menu_link_add_title' 	=> 'Create new items',
				'sub_menu_link_add'	   		=> 'index.php?option=com_imageshow&controller=showlist&task=add',
				'data_sub_menu'		   		=> $objJSNShowlist->getLastestShowlist(5),
				'icon'						=> 'jsn-icon-file',
		);
		$options[] 				= array(
				'title' 					=> JText::_('JSN_MENU_SHOWCASES'),
				'link'						=> 'index.php?option=com_imageshow&controller=showcase',
				'class'				   		=> 'parent primary',
				'sub_menu_link'		   		=> 'index.php?option=com_imageshow&controller=showcase&task=edit&cid[]={$item_id}',
				'sub_menu_field_title'		=> 'item_title',
				'sub_menu_link_add_title' 	=> 'Create new items',
				'sub_menu_link_add'	   		=> 'index.php?option=com_imageshow&controller=showcase&task=add',
				'data_sub_menu'		   		=> $objJSNShowcase->getLastestShowcase(5),
				'icon'						=> 'jsn-icon-monitor',
		);
		$options[] = array(
				'class'	=> 'separator'
				);
				$options[] = array(
				'title'		=> JText::_('JSN_MENU_CONFIGURATION_AND_MAINTENANCE'),
				'link'	 	=> 'index.php?option=com_imageshow&controller=maintenance&type=configs'
				);
				// 		$options[] = array(
				// 				'title'		=> JText::_('JSN_MENU_HELP_AND_SUPPORT'),
				// 				'link'	 	=> 'index.php?option=com_imageshow&controller=help'
				// 		);
				$options[] = array(
				'title' 	=> JText::_('JSN_MENU_ABOUT'),
				'link'  	=> 'index.php?option=com_imageshow&controller=about'
				);
				$html   = JSNHtmlGenerate::menuToolbar($options);
				return $html;
	}

	/**
	 * fetch Id
	 *
	 * @return string
	 */
	public function fetchId()
	{
		return "jsn-is-menu-button";
	}
}
