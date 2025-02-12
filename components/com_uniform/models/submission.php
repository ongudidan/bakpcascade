<?php

/**
 * @version     $Id: submission.php 19013 2012-11-28 04:48:47Z thailv $
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

jimport('joomla.application.component.modeladmin');

/**
 * JSNUniform model Submission
 *
 * @package     Models
 * @subpackage  Submission
 * @since       1.6
 */
class JSNUniformModelSubmission extends JModelAdmin
{

	protected $option = JSN_UNIFORM;
	var $_formId;
	var $_dataId;

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   11.1
	 */
	public function getTable($type = 'data', $prefix = 'JSNUniformTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    mixed    A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_uniform.submission', 'data', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk))?$pk:(int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();

		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
		}

		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item = JArrayHelper::toObject($properties, 'JObject');

		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}
		$edition = defined('JSN_UNIFORM_EDITION')?strtolower(JSN_UNIFORM_EDITION):"free";
		if (isset($item->form_id) && isset($item->data_id) && $edition == "free")
		{
			$tblSubmitssion = '#__jsn_uniform_submissions_' . (int) $item->form_id;
			$this->_db->setQuery($this->_db->getQuery(true)->select('data_id')->from($tblSubmitssion)->order('data_id ASC'), 300, 1);
			$maxId = $this->_db->loadResult();
			if (!empty($maxId) && $maxId < $item->data_id)
			{
				return false;
			}
		}
		$this->_formId = $item->form_id;
		$this->_dataId = $item->data_id;
		return $item;
	}

	/**
	 * Retrieve fields for use in page submitted detail
	 *
	 * @return Object List
	 */
	public function getDataFields()
	{
		if (!empty($this->_formId) && is_numeric($this->_formId))
		{
			$this->_db->setQuery($this->_db->getQuery(true)->select('fi.field_identifier,fi.field_title,fo.form_title,fo.form_id,fi.field_type,fi.field_id')->from('#__jsn_uniform_fields AS fi')->join('INNER', '#__jsn_uniform_forms AS fo ON fo.form_id = fi.form_id')->where('fi.form_id=' . intval($this->_formId))->order('fi.field_ordering ASC'));
			return $this->_db->loadObjectList();
		}
	}

	/**
	 * Retrieve submission for use in page submitted detail
	 *
	 * @return Object
	 */
	public function getDataSubmission()
	{
		if (!empty($this->_formId) && is_numeric($this->_formId) && !empty($this->_dataId) && is_numeric($this->_dataId))
		{
			$this->_db->setQuery($this->_db->getQuery(true)->select('*')->from("#__jsn_uniform_submissions_{$this->_formId}")->where('data_id=' . intval($this->_dataId)));
			return $this->_db->loadObject();
		}
	}

	/**
	 *  get info form
	 *
	 * @return type
	 */
	public function getInfoForm()
	{
		if (!empty($this->_formId) && is_numeric($this->_formId) && !empty($this->_dataId) && is_numeric($this->_dataId))
		{
			$this->_db->setQuery($this->_db->getQuery(true)->select('*')->from("#__jsn_uniform_forms")->where('form_id=' . intval($this->_formId)));

			return $this->_db->loadObject();
		}
	}

	/**
	 * get all page in form
	 *
	 * @return Object list
	 */
	public function getFormPages()
	{
		if (!empty($this->_formId) && is_numeric($this->_formId))
		{
			$this->_db->setQuery($this->_db->getQuery(true)->select('*')->from("#__jsn_uniform_form_pages")->where('form_id=' . intval($this->_formId)));
			return $this->_db->loadObjectList();
		}
	}

	/**
	 * getNextAndPreviousForm
	 *
	 * @return type
	 */
	public function getNextAndPreviousForm()
	{
		$formList = array();
		if (!empty($this->_formId) && is_numeric($this->_formId) && !empty($this->_dataId) && is_numeric($this->_dataId))
		{
			$tblSubmitssion = '#__jsn_uniform_submissions_' . (int) $this->_formId;

			$this->_db->setQuery($this->_db->getQuery(true)->select('data_id')->from($tblSubmitssion)->order('data_id ASC'), 300, 1);
			$maxId = $this->_db->loadResult();

			$edition = defined('JSN_UNIFORM_EDITION')?strtolower(JSN_UNIFORM_EDITION):"free";
			if ($this->_dataId + 1 < $maxId || empty($maxId) || $edition != "free")
			{
				$this->_db->setQuery($this->_db->getQuery(true)->select('data_id')->from($tblSubmitssion)->where('data_id > ' . intval($this->_dataId))->order('`data_id` ASC'), 0, 1);
				$formList['next'] = $this->_db->loadResult();
			}

			$this->_db->setQuery($this->_db->getQuery(true)->select('data_id')->from($tblSubmitssion)->where('data_id < ' . intval($this->_dataId))->order('`data_id` DESC'), 0, 1);
			$formList['previous'] = $this->_db->loadResult();
		}
		return $formList;
	}

	/**
	 * Override save method to save form fields to database
	 *
	 * @return boolean
	 */
	public function save($data)
	{
		$formId = !empty($data['filter_form_id'])?$data['filter_form_id']:0;
		$this->_db->setQuery($this->_db->getQuery(true)->select('form_access')->from("#__jsn_uniform_forms")->where('form_id=' . intval($formId)));
		$infoForm = $this->_db->loadObject();
		$user = JFactory::getUser();
		$groupEditSubmision = isset($infoForm->form_access)?$infoForm->form_access:"";
		$checkEditSubmission = JSNUniformHelper::checkEditSubmission($user->id, $groupEditSubmision);

		if ($checkEditSubmission)
		{
			if (isset($_POST['submission']) && is_array($_POST['submission']) && isset($_POST['filter_form_id']) && isset($_POST['cid']))
			{
				$tblSubmitssion = '#__jsn_uniform_submissions_' . (int) $_POST['filter_form_id'];

				$query = $this->_db->getQuery(true);
				$query->update($this->_db->quoteName($tblSubmitssion));

				foreach ($_POST['submission'] as $key => $value)
				{
					$query->set("{$key} = " . $this->_db->Quote($value));
				}

				$query->where($this->_db->quoteName('data_id') . ' = ' . (int) $_POST['cid']);
				$this->_db->setQuery($query);
				if (!$this->_db->execute())
				{
					$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED_UPDATE_ASSET_ID', $this->_db->getErrorMsg()));
					$this->setError($e);
					return false;
				}
			}
		}
		return true;
	}
}
