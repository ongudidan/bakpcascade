<?php

/**
 * @version     $Id: submissions.php 19014 2012-11-28 04:48:56Z thailv $
 * @package     JSNUniform
 * @subpackage  Models
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * JSNUniform model Submissions
 *
 * @package     Models
 * @subpackage  Submissions
 * @since       1.6
 */
class JSNUniformModelSubmissions extends JModelList
{

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function getItemsExport()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$query = $this->_getListQuery();
		$items = $this->_getList($query);

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;
		return $this->cache[$store];
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$filterFormId = $this->getState('filter.filter_form_id');
		$tblSubmitssion = '#__jsn_uniform_submissions_' . (int) $filterFormId;
		// Select some fields
		$query->select('sb.*');
		$query->from($tblSubmitssion . ' AS sb');

		$query->select('dt.*');
		$query->join('INNER', '#__jsn_uniform_data AS dt ON dt.data_id = sb.data_id');

		$query->select('us.username as data_created_by');
		$query->join('LEFT', '#__users AS us ON us.id = dt.data_created_by');

		// Filter by search in title
		$search = $this->getState('filter.search');
		$dateSubmission = $this->getState('filter.date_submission');

		$where = "";

		// Filter by search in title
		if (!empty($dateSubmission))
		{
			$dateSubmission = @explode(" - ", $dateSubmission);
			$dateStart = @explode("/", $dateSubmission[0]);
			$dateStart = @$dateStart[2] . "-" . @$dateStart[0] . "-" . @$dateStart[1];
			if (@$dateSubmission[1])
			{
				$dateEnd = @explode("/", $dateSubmission[1]);
				$dateEnd = @$dateEnd[2] . "-" . @$dateEnd[0] . "-" . @$dateEnd[1];
				$query->where('( date(data_created_at) BETWEEN ' . $db->quote($dateStart) . ' AND ' . $db->quote($dateEnd) . ')', 'AND');
			}
			else
			{
				$query->where(' date(data_created_at) = ' . $db->quote($dateStart), 'AND');
			}
		}

		if (!empty($search))
		{
			if (stripos($search, 'data_id:') === 0)
			{
				$query->where('dt.data_id = ' . (int) substr($search, 3));
			}
			else
			{
				$listviewField = $this->getState('filter.list_view_field');
				$search = $db->escape($search, true);
				$search = str_replace("  ", " ", $search);
				$search = str_replace(" ", "%", $search);
				$search = $db->Quote('%' . $search . '%');

				if ($listviewField)
				{
					$fields = str_replace('"sb_', '"sb.sb_', $listviewField);
					$fields = str_replace('"data_', '"dt.data_', $fields);
					$fields = str_replace('"', '', $fields);
					$fields = explode(',', $fields);

					foreach ($fields as $field)
					{
						$where[] = '(' . preg_replace('/[^a-z0-9-._]/i', "", $field) . ' LIKE ' . $search . ')';
					}
				}
			}
		}
		if ($where)
		{
			$query->where("(".implode(" OR ",$where).")", 'AND');
		}
		$edition = defined('JSN_UNIFORM_EDITION') ? strtolower(JSN_UNIFORM_EDITION) : "free";
		if ($edition == "free")
		{
			$db->setQuery(
			$db->getQuery(true)
			->select('data_id')
			->from($tblSubmitssion)
			->order('data_id ASC')
			, 300, 1);
			$maxId = $db->loadResult();
			if (!empty($maxId))
			{
				$query->where('dt.data_id <' . (int) $maxId, 'AND');
			}
		}
		$query->order($db->escape($this->getState('list.ordering', 'sb.data_id')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * 
	 * to be called on the first call to the getState() method unless the model
	 * 
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');
		$post = $_POST;

		$filterFormId = $this->getUserStateFromRequest($this->context . '.filter.filter_form_id', 'filter_form_id', '', 'string');
		$this->setState('filter.filter_form_id', $filterFormId);
		if (!empty($post['old_form_id']) && $post['old_form_id'] != $filterFormId)
		{
			$this->setState('filter.list_view_field', "");
			$this->setState('filter.position_field', "");
			$this->setState('filter.position_title_field', "");
			$this->setState('filter.search', "");
			$this->setState('filter.date_submission', "");
		}
		else
		{
			$listViewField = $this->getUserStateFromRequest($this->context . '.filter.list_view_field', 'list_view_field', '', 'string');
			$this->setState('filter.list_view_field', $listViewField);

			$filterPositionField = $this->getUserStateFromRequest($this->context . '.filter.position_field', 'filter_position_field', '', 'string');
			$this->setState('filter.position_field', $filterPositionField);

			$filterPositionTitleField = $this->getUserStateFromRequest($this->context . '.filter.position_title_field', 'filter_position_title_field', '', 'string');
			$this->setState('filter.position_title_field', $filterPositionTitleField);

			$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
			$this->setState('filter.search', $search);

			$dateSubmission = $this->getUserStateFromRequest($this->context . '.filter.date_submission', 'filter_date_submission');
			$this->setState('filter.date_submission', $dateSubmission);
		}
		$filterFields = array(
		'dt.data_ip', 'dt.data_country', 'dt.data_browser', 'dt.data_os', 'dt.data_created_by'
		, 'dt.data_created_at', 'dt.data_state'
		);
		if ($this->GetFieldsForm())
		{
			foreach ($this->GetFieldsForm() as $field)
			{
				$filterFields[] = "sb.sb_" . $field->field_id;
			}
		}
		$this->filter_fields = $filterFields;
		// List state information.
		parent::populateState('sb.data_id', 'DESC');
	}

	/**
	 * Retrieve fields from for use in page list submission
	 * 
	 * @return Object List
	 */
	public function GetFieldsForm()
	{
		$formId = $this->getUserStateFromRequest($this->context . '.filter.filter_form_id', 'filter_form_id', '', 'string');
		if (!empty($formId) && is_numeric($formId))
		{
			$this->_db->setQuery(
			$this->_db->getQuery(true)
			->select('*')
			->from('#__jsn_uniform_fields')
			->where('form_id=' . intval($formId))
			->where('field_type!="static-heading"')
			->where('field_type!="paragraph-text"')
			->order('field_id DESC')
			);
			return $this->_db->loadObjectList();
		}
	}
	/**
	 * get data Pages
	 *
	 * @return Object
	 */
	public function getDataPages()
	{
		$filterFormId = $this->getState('filter.filter_form_id');
		$this->_db->setQuery(
		$this->_db->getQuery(true)
		->select('*')
		->from("#__jsn_uniform_form_pages")
		->where("form_id=" . (int) $filterFormId)
		);
		return $this->_db->loadObjectList();
	}

	/**
	 * get count submission
	 *
	 * @return Object
	 */
	public function getCountSubmission()
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$filterFormId = $this->getState('filter.filter_form_id');
		$tblSubmitssion = '#__jsn_uniform_submissions_' . (int) $filterFormId;
		$db->setQuery(
		$db->getQuery(true)
		->select('count(data_id)')
		->from($tblSubmitssion)
		);
		return $db->loadResult();
	}

	/**
	 * get info form
	 *
	 * @return Object
	 */
	public function getInfoForm()
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$filterFormId = $this->getState('filter.filter_form_id');
		$db->setQuery(
		$db->getQuery(true)
		->select('*')
		->from('#__jsn_uniform_forms')
		->where('form_id=' . (int) $filterFormId)
		);
		return $db->loadObject();
	}

}
