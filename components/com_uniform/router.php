<?php
/**
 * @package                Joomla.Site
 * @copyright        Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license                GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @param        array
 * @return        array
 */
function UniformBuildRoute(&$query)
{
	$segments = array();

	// get a menu item based on Itemid or currently active
	$app = JFactory::getApplication();
	$menu = $app->getMenu();
	$params = JComponentHelper::getParams('com_uniform');
	$advanced = $params->get('sef_advanced_link', 0);
	if (empty($query['Itemid']))
	{
		$menuItem = $menu->getActive();
	}
	else
	{
		$menuItem = $menu->getItem($query['Itemid']);
	}
	$mView = (empty($menuItem->query['view']))?null:$menuItem->query['view'];
	$mId = (empty($menuItem->query['data_id']))?null:$menuItem->query['data_id'];

	if (isset($query['view']))
	{
		$view = $query['view'];
		if (empty($query['Itemid']))
		{
			$segments[] = $query['view'];
		}
		unset($query['view']);
	}
	// are we dealing with a contact that is attached to a menu item?
	if (isset($view) && ($mView == $view) and (isset($query['id'])) and ($mId == intval($query['data_id'])))
	{
		unset($query['view']);
		unset($query['data_id']);

		return $segments;
	}
	if (isset($view) and ($view == 'submission'))
	{
		if ($mId != intval($query['data_id']) || $mView != $view)
		{
			if ($advanced)
			{
				list($tmp, $id) = explode(':', $query['data_id'], 2);
			}
			else
			{
				$id = $query['data_id'];
			}
			$segments[] = 'submission';
			$segments[] = $id;
		}
		unset($query['data_id']);
	}
	if (isset($view) and ($view == 'captcha'))
	{

		if ($mId != intval($query['sid']) || $mView != $view)
		{
			if ($advanced)
			{
				list($tmp, $id) = explode(':', $query['sid'], 2);
			}
			else
			{
				$id = $query['sid'];
			}
			$segments[] = 'captcha';
			$segments[] = $id;
			$segments[] = $query['layout'];
		}
		unset($query['sid']);
		unset($query['layout']);
	}
	if (isset($query['view']))
	{
		$view = $query['view'];
		if (empty($query['Itemid']))
		{
			$segments[] = $query['view'];
		}
		unset($query['view']);
	}
	return $segments;
}

/**
 * @param        array
 * @return        array
 */
function UniformParseRoute($segments)
{
	$vars = array();

	//Get the active menu item.
	$app = JFactory::getApplication();
	$menu = $app->getMenu();
	$item = $menu->getActive();
	$params = JComponentHelper::getParams('com_uniform');
	$advanced = $params->get('sef_advanced_link', 0);
	$count = count($segments);

	// Standard routing for newsfeeds.
	if (!isset($item))
	{
		$vars['view'] = $segments[0];
		$vars['data_id'] = $segments[$count - 1];
		return $vars;
	}
	if (isset($segments[0]) && $segments[0] == "submission")
	{
		$vars['data_id'] = isset($segments[1])?$segments[1]:0;
		$vars['view'] = 'submission';
	}
	if (isset($segments[0]) && $segments[0] == "captcha")
	{
		$vars['sid'] = isset($segments[1])?$segments[1]:0;
		$vars['view'] = isset($segments[0])?$segments[0]:"";
		$vars['layout'] = isset($segments[2])?$segments[2]:"";
	}
	return $vars;
}
