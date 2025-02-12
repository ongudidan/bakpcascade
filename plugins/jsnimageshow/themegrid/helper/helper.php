<?php
/**
 * @author JoomlaShine.com Team
 * @copyright JoomlaShine.com
 * @link joomlashine.com
 * @package JSN ImageShow - Theme Classic
 * @version $Id: helper.php 14559 2012-07-28 11:50:34Z haonv $
 * @license GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die( 'Restricted access' );
$objJSNShowcaseTheme = JSNISFactory::getObj('classes.jsn_is_showcasetheme');
$objJSNShowcaseTheme->importTableByThemeName($this->_showcaseThemeName);
$objJSNShowcaseTheme->importModelByThemeName($this->_showcaseThemeName);
$modelShowcaseTheme = JModelLegacy::getInstance($this->_showcaseThemeName);
$items = $modelShowcaseTheme->getTable($themeID);

JSNISFactory::importFile('classes.jsn_is_htmlselect');

/**
 * /////////////////////////////////////////////////////////Image Panel Begin////////////////////////////////////////////////////////////////////////////
 */
$imgLayout = array(
	'0' => array('value' => 'fixed', 'text' => JText::_('THEME_GRID_LAYOUT_FIXED')),
	'1' => array('value' => 'fluid', 'text' => JText::_('THEME_GRID_LAYOUT_FLUID'))
);
$lists['imgLayout'] = JHTML::_('select.genericList', $imgLayout, 'img_layout', 'class="inputbox imagePanel"', 'value', 'text', $items->img_layout );
$thumbnailShadow = array(
	'0' => array('value' => '0', 'text' => JText::_('THEME_GRID_THUMBNAIL_NO_SHADOW')),
	'1' => array('value' => '1', 'text' => JText::_('THEME_GRID_THUMBNAIL_LIGHT_SHADOW')),
	'2' => array('value' => '2', 'text' => JText::_('THEME_GRID_THUMBNAIL_BOLD_SHADOW'))
);
$lists['thumbnailShadow'] = JHTML::_('select.genericList', $thumbnailShadow, 'thumbnail_shadow', 'class="inputbox imagePanel"', 'value', 'text', $items->thumbnail_shadow );