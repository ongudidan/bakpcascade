<?php
/**
 * @author JoomlaShine.com Team
 * @copyright JoomlaShine.com
 * @link joomlashine.com
 * @package JSN ImageShow - Theme Classic
 * @version $Id: themegrid.php 14559 2012-07-28 11:50:34Z haonv $
 * @license GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
class TableThemeGrid extends JTable
{
	var $theme_id 					= null;
	var $img_layout					= 'fixed';
	var $background_color			= '#ffffff';
	var $thumbnail_width			= '50';
	var $thumbnail_height			= '50';
	var $thumbnail_space			= '10';
	var $thumbnail_border			= '3';
	var $thumbnail_rounded_corner	= '3';
	var $thumbnail_border_color		= '#ffffff';
	var $thumbnail_shadow			= '1';//0:noshadow,1:lightshadow,2:boldshadow

	function __construct(& $db) {
		parent::__construct('#__imageshow_theme_grid', 'theme_id', $db);
	}
}
?>