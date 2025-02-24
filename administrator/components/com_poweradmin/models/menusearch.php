<?php
/*------------------------------------------------------------------------
# JSN PowerAdmin
# ------------------------------------------------------------------------
# author    JoomlaShine.com Team
# copyright Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
# Websites: http://www.joomlashine.com
# Technical Support:  Feedback - http://www.joomlashine.com/joomlashine/contact-us.html
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @version $Id: menusearch.php 12506 2012-05-09 03:55:24Z hiennh $
-------------------------------------------------------------------------*/
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once JPATH_ADMINISTRATOR.'/components/com_menus/models/items.php';

class PowerAdminModelMenuSearch extends MenusModelItems
{
	protected function getListQuery () {
		$this->setState('filter.menutype', null);
		$query = parent::getListQuery();
		$query->order($this->_db->getEscaped('a.menutype, '. $this->getState('list.ordering', 'a.lft')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));
		
		return $query;
	}
}
