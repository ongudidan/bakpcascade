<?php

/**
 * @version     $Id: form.php 19014 2012-11-28 04:48:56Z thailv $
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
 * JSNUniform model Form
 *
 * @package     Modales
 * @subpackage  Form
 * @since       1.6
 */
class JSNUniformModelForm extends JModelAdmin
{

	protected $option = JSN_UNIFORM;

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
	public function getTable($type = 'Form', $prefix = 'JSNUniformTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 * 
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_uniform.form', 'form', array('control' => 'jform', 'load_data' => $loadData));
		return $form;
	}

	/**
	 * (non-PHPdoc)
	 * 
	 * @see JModelForm::loadFormData()
	 * 
	 * @return object
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_uniform.edit.form.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
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
		$item = parent::getItem($pk);
		$formId = isset($item->form_id) ? $item->form_id : "";
		$item->form_content = $this->getFormPages($formId);

		// Set default layout for form when it is not exists
		if (empty($item->form_layout) || !is_dir(JSN_UNIFORM_PAGEDESIGN_LAYOUTS_PATH . DS . $item->form_layout))
		{
			$item->form_layout = 'default';
		}
		if (empty($item->form_id))
		{
			$dataConfig = $this->getDataConfig();

			if (!empty($dataConfig))
			{
				$session = JFactory::getSession();
				foreach ($dataConfig as $data)
				{
					if (isset($data->name) && $data->name == 'form_action')
					{
						$decoData = json_decode($data->value);
						$item->form_post_action = isset($decoData->action) ? $decoData->action : '';
						$item->form_post_action_data = isset($decoData->action_data) ? $decoData->action_data : '';
					}
					if (isset($data->name) && $data->name == 'list_email')
					{
						$dataConfigListEmail = json_decode($data->value);
						$configEmailNotify0 = new stdClass;
						$configEmailNotify1 = new stdClass;
						$configEmailNotify0->template_message = isset($dataConfigListEmail->template_message) ? $dataConfigListEmail->template_message : "";
						$configEmailNotify0->template_subject = isset($dataConfigListEmail->template_subject) ? $dataConfigListEmail->template_subject : "";
						$configEmailNotify0->template_notify_to = '0';
						$configEmailNotify1->template_message = isset($dataConfigListEmail->template_message) ? $dataConfigListEmail->template_message : "";
						$configEmailNotify1->template_subject = isset($dataConfigListEmail->template_subject) ? $dataConfigListEmail->template_subject : "";
						$configEmailNotify1->template_notify_to = '1';
						$session->set('emailsettings_notify_1', $configEmailNotify1);
						$session->set('emailsettings_notify_0', $configEmailNotify0);
					}
				}
			}
		}
		return $item;
	}

	/**
	 * Override save method to save form fields to database
	 * 
	 * @param   array  $data  Data form
	 * 
	 * @return boolean
	 */
	public function save($data)
	{
		$user = JFactory::getUser();
		$post = $_POST;

		$checkCreate = true;
		$data['form_submitter'] = isset($post['form_submitter']) ? json_encode($post['form_submitter']) : '';
		$data['form_post_action_data'] = isset($post['form_post_action_data' . $data['form_post_action']]) ? $post['form_post_action_data' . $data['form_post_action']] : '';
		if ($data['form_post_action_data'])
		{
			$data['form_post_action_data'] = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($data['form_post_action_data']) : $data['form_post_action_data'];
		}
		$data['form_notify_submitter'] = isset($data['form_notify_submitter']) ? "1" : "0";
		$data['form_style'] = !empty($post['form_style'])?json_encode($post['form_style']):"";
		$data['form_style'] = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($data['form_style']) : $data['form_style'];
		if (empty($data['form_id']) || $data['form_id'] == 0)
		{
			$data['form_created_by'] = $user->id;
			$data['form_created_at'] = date('Y-m-d H:i:s');
			$edition = defined('JSN_UNIFORM_EDITION') ? JSN_UNIFORM_EDITION : "free";
			if (strtolower($edition) == "free")
			{
				$dataListForm = JSNUniformHelper::getForms();

				if (count($dataListForm) >= 3)
				{
					$checkCreate = false;
				}
			}
		}
		else
		{
			$data['form_modified_by'] = $user->id;
			$data['form_modified_at'] = date('Y-m-d H:i:s');
		}

		if ($checkCreate)
		{
			if (($result = parent::save($data)))
			{
				$formId = $this->getState($this->getName() . '.id');
				$this->saveField($data['form_id'], $data['form_layout']);
				$this->saveListEmail($post, $formId);
				$this->EmailTepmplates($formId, $data['form_id']);
				//create table submission
			}
			return $result;
		}
		else
		{
			$msg = JText::sprintf('JSN_UNIFORM_YOU_HAVE_REACHED_THE_LIMITATION_OF_3_FORM_IN_FREE_EDITION',0) . ' <a class="jsn-link-action" href="index.php?option=com_uniform&view=upgrade">' . JText::_("JSN_UNIFORM_UPGRADE_EDITION") . '</a>';
			$this->setError($msg);
			return false;
		}
	}

	/**
	 * Save all field in page form
	 * 
	 * @param   type  $dataFormId      Form id
	 * 
	 * @param   type  $dataFormLayout  Form layout
	 * 
	 * @return void
	 */
	public function saveField($dataFormId, $dataFormLayout)
	{
		$post = $_POST;
		$edition = defined('JSN_UNIFORM_EDITION') ? JSN_UNIFORM_EDITION : "free";
		$session = JFactory::getSession();
		$fieldUpdate = array();
		$identify = array();
		$fieldOld = array();
		$fieldSB = array();
		$pageId = array();
		$count = 0;
		$checkTableSubmission = true;
		$formId = $this->getState($this->getName() . '.id');
		$formId = intval($formId);
		$fieldSubmission = array();
		$fieldIds = array();
		$fieldSubmission[] = "`data_id` int(11)";
		//get data form page
		$this->_db->setQuery(
		$this->_db->getQuery(true)
		->select('page_id')
		->from('#__jsn_uniform_form_pages')
		->where('form_id=' . $formId)
		);
		$dataFormPages = $this->_db->loadObjectList();
		$listFormPages = array();
		if (!empty($dataFormPages))
		{
			foreach ($dataFormPages as $FormPage)
			{
				$listFormPages[] = $FormPage->page_id;
			}
		}
		if (!JSNUniformHelper::checkTableSql("jsn_uniform_submissions_{$formId}"))
		{
			$checkTableSubmission = false;
		}
		else
		{
			$this->_db->setQuery("SHOW COLUMNS FROM #__jsn_uniform_submissions_{$formId}");
			try
			{
				$columnSubmission = $this->_db->loadObjectList();
			}
			catch (Exception $exc)
			{
				echo $exc->getTraceAsString();
			}
		}
		if (isset($post['name_page']))
		{

			foreach ($post['name_page'] as $value => $text)
			{
				$dataField = array();
				$parsedFields = array();
				$formPages = "";
				$formFields = "";
				if ($dataFormId == 0)
				{
					$formFields = json_decode($session->get('form_page_' . $value, '', 'form-design-'));
				}
				else
				{
					$formFields = json_decode($session->get('form_page_' . $value, '', 'form-design-' . $dataFormId));
				}
				if (!empty($formFields) && is_array($formFields))
				{
					foreach ($formFields as $index => $field)
					{
						if (($index < 10 && strtolower($edition) == "free") || strtolower($edition) != "free")
						{
							$count++;
							$options = $field->options;
							if (in_array($field->identify, $identify))
							{
								$field->identify = $field->identify . $count;
							}
							$identify[] = $field->identify;
							$field->identify = preg_replace('/[^a-z0-9-._]/i', "", $field->identify);

							$checkFieldNew = 0;
							$table = JTable::getInstance('Field', 'JSNUniformTable');
							$table->bind(
							array('form_id' => $formId,
							'field_id' => isset($field->id) ? $field->id : null,
							'field_type' => $field->type,
							'field_identifier' => $field->identify,
							'field_title' => $options->label,
							'field_instructions' => isset($options->instruction) ? $options->instruction : null,
							'field_position' => $field->position,
							'field_ordering' => $index,
							'field_settings' => json_encode($field)
							));
							if (!$table->store())
							{
								$this->setError($table->getError());
								$result = false;
							}

							if ($dataFormId)
							{
								if (!empty($columnSubmission))
								{

									foreach ($columnSubmission as $clSubmission)
									{
										if (isset($clSubmission->Field) && isset($field->id) && $clSubmission->Field == "sb_" . $table->field_id)
										{

											$checkFieldNew = 1;
										}
										$fieldOld[] = $clSubmission->Field;
									}
								}
							}
							if ($checkFieldNew == 0 && !empty($table->field_id) && $table->field_type != "static-content")
							{

								$fieldUpdate[] = "ADD `sb_{$table->field_id}` " . JSNUniformHelper::replaceField($table->field_type);
							}
							$fieldIds[] = $table->field_id;
							$fieldSB[] = "sb_" . $table->field_id;
							if (!empty($table->field_id) && $table->field_type != "static-content")
							{
								$fieldSubmission[] = '`sb_' . $table->field_id . '` ' . JSNUniformHelper::replaceField($table->field_type);
							}
							$dataField[$index] = new stdClass;
							$dataField[$index]->field_type = $table->field_type;
							$dataField[$index]->field_id = "sb_" . $table->field_id;
							$dataField[$index]->field_title = $table->field_title;
							$dataField[$index]->field_instructions = $table->field_instructions;
							$dataField[$index]->field_position = $table->field_position;
							$dataField[$index]->field_settings = $table->field_settings;
							$parsedFields[] = array(
							'id' => $table->field_id,
							'type' => $table->field_type,
							'position' => $table->field_position,
							'identify' => $table->field_identifier,
							'label' => $table->field_title,
							'instruction' => $table->field_instructions,
							'options' => $field->options
							);
						}
					}
				}
				if (in_array($value, $listFormPages))
				{
					$formPages['page_id'] = $value;
				}
				$formPages['page_title'] = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($text) : $text;
				$formPages['form_id'] = $formId;
				$formPages['page_content'] = isset($parsedFields) ? json_encode($parsedFields) : "";

				if (!empty($dataField))
				{
					$formPages['page_template'] = json_encode(array('dataField' => $dataField, 'dataFormLayout' => $dataFormLayout));
				}
				else
				{
					$formPages['page_template'] = "";
				}
				$table = JTable::getInstance('Page', 'JSNUniformTable');
				$table->bind($formPages);
				if (!$table->store())
				{
					$this->setError($table->getError());
					$result = false;
				}
				$pageId[] = $table->page_id;
			}
		}

		if (!empty($fieldOld))
		{
			foreach ($fieldOld as $fieldO)
			{
				if (!in_array($fieldO, $fieldSB) && $fieldO != "data_id")
				{
					$fieldUpdate[] = "DROP `{$fieldO}`";
				}
			}
		}
		if ($fieldUpdate && $checkTableSubmission)
		{
			$this->_db->setQuery("ALTER TABLE `#__jsn_uniform_submissions_{$formId}` " . implode(', ', array_unique($fieldUpdate)));
			try
			{
				$this->_db->execute();
			}
			catch (Exception $exc)
			{
				echo $exc->getTraceAsString();
			}
		}
		if (!empty($fieldIds))
		{
			$this->_db->setQuery("DELETE FROM #__jsn_uniform_fields WHERE form_id={$formId} AND field_id NOT IN (" . implode(', ', $fieldIds) . ")");
			$this->_db->execute();
		}
		else
		{
			$this->_db->setQuery("DELETE FROM #__jsn_uniform_fields WHERE form_id={$formId}");
			$this->_db->execute();
		}
		if (!empty($pageId))
		{
			$this->_db->setQuery("DELETE FROM #__jsn_uniform_form_pages WHERE form_id={$formId} AND page_id NOT IN (" . implode(', ', $pageId) . ")");
			$this->_db->execute();
		}

		if (empty($dataFormId) || !$checkTableSubmission)
		{

			$fieldSubmission = implode(",", $fieldSubmission);

			$this->_db->setQuery("DROP TABLE IF EXISTS #__jsn_uniform_submissions_{$formId}");
			$this->_db->execute();
			$this->_db->setQuery("CREATE TABLE IF NOT EXISTS #__jsn_uniform_submissions_{$formId} ({$fieldSubmission})");
			$this->_db->execute();
		}
		if (empty($fieldIds))
		{
			$this->_db->setQuery("DROP TABLE IF EXISTS #__jsn_uniform_submissions_{$formId}");
			$this->_db->execute();
		}
	}

	/**
	 * Save list email submit
	 * 
	 * @param   type  $post    Post submit data form
	 * 
	 * @param   type  $formId  Form id
	 * 
	 * @return void
	 */
	public function saveListEmail($post, $formId)
	{
		$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$/';

		if (!empty($post['form_email_notification']) && is_array($post['form_email_notification']))
		{
			$sendEmail = isset($post['form_email_notification']) ? $post['form_email_notification'] : '';
			$postEmailName = isset($post['form_email_notification_name']) ? $post['form_email_notification_name'] : '';
			$postEmailUserId = isset($post['form_email_notification_user_id']) ? $post['form_email_notification_user_id'] : '';

			if ($post['task'] != 'save2copy')
			{
				$postEmailId = isset($post['semail_id']) ? $post['semail_id'] : '';
			}
			$this->_db->setQuery("DELETE FROM #__jsn_uniform_emails WHERE form_id=" . (int) $formId);
			$this->_db->execute();
			foreach (array_unique($sendEmail) as $email)
			{
				if (preg_match($regex, $email))
				{
					$table = JTable::getInstance('Email', 'JSNUniformTable');
					$table->bind(array('form_id' => $formId, 'email_name' => isset($postEmailName[$email]) ? $postEmailName[$email] : null, 'email_address' => $email, 'user_id' => isset($postEmailUserId[$email]) ? $postEmailUserId[$email] : null));
					if (!$table->store())
					{
						$this->setError($table->getError());
					}
				}
			}
		}
		else
		{
			$this->_db->setQuery("DELETE FROM #__jsn_uniform_emails WHERE form_id={$formId}");
			$this->_db->execute();
		}
	}

	/**
	 * Get data email template by form id
	 *
	 * @param   type  $formId      Form id
	 * 
	 * @param   type  $dataFormId  Data form id
	 * 
	 * @return void
	 */
	public function EmailTepmplates($formId, $dataFormId = null)
	{
		if (empty($dataFormId))
		{
			//Create Emailsettings
			$session = JFactory::getSession();
			$emailSettingsEmailSubmitted = $session->get('emailsettings_notify_0');
			$emailSettingsListEmail = $session->get('emailsettings_notify_1');

			if ($emailSettingsListEmail)
			{
				$emailSettingsListEmail->form_id = $formId;
				$this->saveEmailTemplates($emailSettingsListEmail);
			}
			else
			{
				$emailSettingsListEmail->form_id = $formId;
				$emailSettingsListEmail->template_notify_to = 1;
				$this->saveEmailTemplates($emailSettingsListEmail);
			}
			if ($emailSettingsEmailSubmitted)
			{
				$emailSettingsEmailSubmitted->form_id = $formId;
				$this->saveEmailTemplates($emailSettingsEmailSubmitted);
			}
			else
			{
				$emailSettingsEmailSubmitted->form_id = $formId;
				$emailSettingsEmailSubmitted->template_notify_to = 0;
				$this->saveEmailTemplates($emailSettingsEmailSubmitted);
			}
			$session->clear('emailsettings_notify_0');
			$session->clear('emailsettings_notify_1');
		}
	}

	/**
	 * Save email template
	 * 
	 * @param   type  $dataEmailTemplate  Data email template
	 * 
	 * @return void
	 */
	public function saveEmailTemplates($dataEmailTemplate)
	{
		if (!empty($dataEmailTemplate))
		{
			$table = JTable::getInstance('Template', 'JSNUniformTable');
			$table->bind($dataEmailTemplate);
			if (!$table->store())
			{
				$this->setError($table->getError());
			}
		}
	}

	/**
	 * Get data default config
	 *
	 * @return Object list
	 */
	public function getDataConfig()
	{

		$this->_db->setQuery(
		$this->_db->getQuery(true)
		->select('*')
		->from("#__jsn_uniform_config")
		);
		return $this->_db->loadObjectList();
	}

	/**
	 * Override delete method to also delete form fields that associated
	 * 
	 * @param   array  &$pks  id form
	 * 
	 * @return boolean
	 */
	public function delete(&$pks)
	{
		$pks = (array) $pks;
		foreach ($pks as $id)
		{
			$this->_db->setQuery("DELETE FROM #__jsn_uniform_config WHERE name='position_form_{$id}'");
			$this->_db->execute();

			$this->_db->setQuery("DELETE FROM #__jsn_uniform_fields WHERE form_id={$id}");
			$this->_db->execute();

			$this->_db->setQuery("DELETE FROM #__jsn_uniform_data WHERE form_id={$id}");
			$this->_db->execute();

			$this->_db->setQuery("DELETE FROM #__jsn_uniform_form_pages WHERE form_id={$id}");
			$this->_db->execute();

			$this->_db->setQuery("DELETE FROM #__jsn_uniform_templates WHERE form_id={$id}");
			$this->_db->execute();

			$this->_db->setQuery("DELETE FROM #__jsn_uniform_emails WHERE form_id={$id}");
			$this->_db->execute();

			$this->_db->setQuery("DROP TABLE IF EXISTS #__jsn_uniform_submissions_{$id}");
			$this->_db->execute();

			if (!parent::delete($id))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Retrieve form email for use in page design
	 * 
	 * @return ObjectList
	 */
	public function getFormEmail()
	{
		$formId = $this->getState($this->getName() . '.id');

		if (!empty($formId) && is_numeric($formId))
		{
			$this->_db->setQuery(
			$this->_db->getQuery(true)
			->select('*')
			->from('#__jsn_uniform_emails')
			->where('form_id=' . intval($formId))
			);

			return $this->_db->loadObjectList();
		}
	}

	/**
	 * Get all page in form
	 * 
	 * @param   type  $formId  Form id
	 * 
	 * @return Object list
	 */
	public function getFormPages($formId)
	{
		if (!empty($formId) && is_numeric($formId))
		{
			$this->_db->setQuery(
			$this->_db->getQuery(true)
			->select('*')
			->from('#__jsn_uniform_form_pages')
			->where('form_id=' . intval($formId))
			);
			return $this->_db->loadObjectList();
		}
	}

	/**
	 * Method to duplicate modules.
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @since   1.6
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		// Initialise variables.
		$user = JFactory::getUser();
		$db = $this->getDbo();

		// Access checks.
		if (!$user->authorise('core.create', 'com_uniform'))
		{
			throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();
		$checkCreate = true;
		foreach ($pks as $pk)
		{
			$edition = defined('JSN_UNIFORM_EDITION') ? JSN_UNIFORM_EDITION : "free";
			if (strtolower($edition) == "free")
			{
				$dataListForm = JSNUniformHelper::getForms();

				if (count($dataListForm) >= 3)
				{
					$checkCreate = false;
				}
			}
			if ($checkCreate)
			{
				if ($table->load($pk, true))
				{
					// Reset the id to create a new record.
					$table->form_id = 0;

					// Alter the title.
					$m = null;
					if (preg_match('#\((\d+)\)$#', $table->form_title, $m))
					{
						$table->form_title = preg_replace('#\(\d+\)$#', '(' . ($m[1] + 1) . ')', $table->form_title);
					}
					else
					{
						$table->form_title .= ' (2)';
					}
					// Unpublish duplicate module
					$table->form_state = 0;
					$table->form_submission_cout = 0;
					$table->form_last_submitted = '';
					if (!$table->check() || !$table->store())
					{
						throw new Exception($table->getError());
					}

					// Email
					$query = $db->getQuery(true);
					$query->select('*');
					$query->from('#__jsn_uniform_emails');
					$query->where('form_id=' . (int) $pk);

					$this->_db->setQuery((string) $query);
					$emails = $this->_db->loadObjectList();
					foreach ($emails as $email)
					{
						$email->email_id = 0;
						$email->form_id = $table->form_id;
						$tableEmail = JTable::getInstance('Email', 'JSNUniformTable');
						$tableEmail->bind($email);
						if (!$tableEmail->store())
						{
							$this->setError($tableEmail->getError());
						}
					}
					//Email template
					$query = $db->getQuery(true);
					$query->select('*');
					$query->from('#__jsn_uniform_templates');
					$query->where('form_id=' . (int) $pk);

					$this->_db->setQuery((string) $query);
					$templates = $this->_db->loadObjectList();
					foreach ($templates as $template)
					{
						$template->template_id = 0;
						$template->form_id = $table->form_id;
						$tableTemplate = JTable::getInstance('Template', 'JSNUniformTable');
						$tableTemplate->bind($template);
						if (!$tableTemplate->store())
						{
							$this->setError($tableTemplate->getError());
						}
					}
					//Page and Field
					$query = $db->getQuery(true);
					$query->select('*');
					$query->from('#__jsn_uniform_form_pages');
					$query->where('form_id=' . (int) $pk);

					$this->_db->setQuery((string) $query);
					$pages = $this->_db->loadObjectList();


					$fieldSubmission = array();
					$fieldSubmission[] = "`data_id` int(11)";

					foreach ($pages as $page)
					{
						$dataField = array();
						$fields = json_decode($page->page_content);
						$pageTemplate = json_decode($page->page_template);
						$formPages = array();
						$parsedFields = array();
						foreach ($fields as $index => $item)
						{
							$dataField[$index] = new stdClass;
							$tableField = JTable::getInstance('Field', 'JSNUniformTable');
							$fieldSettings = '';
							if (!empty($pageTemplate->dataField))
							{
								foreach ($pageTemplate->dataField as $pageTemp)
								{
									if ('sb_' . $item->id == $pageTemp->field_id)
									{
										$fieldSettings = $pageTemp->field_settings;
									}
								}
							}
							$tableField->bind(
							array(
							'form_id' => $table->form_id,
							'field_type' => $item->type,
							'field_identifier' => $item->identify,
							'field_title' => $item->label,
							'field_instructions' => isset($item->instruction) ? $item->instruction : null,
							'field_position' => $item->position,
							'field_ordering' => $index,
							'field_settings' => $fieldSettings
							));

							if (!$tableField->store())
							{
								$this->setError($tableField->getError());
							}

							$dataField[$index]->field_type = $tableField->field_type;
							$dataField[$index]->field_id = 'sb_' . $tableField->field_id;
							$dataField[$index]->field_title = $tableField->field_title;
							$dataField[$index]->field_instructions = $tableField->field_instructions;
							$dataField[$index]->field_position = $tableField->field_position;
							$dataField[$index]->field_settings = $fieldSettings;

							$parsedFields[] = array(
							'id' => $tableField->field_id,
							'type' => $tableField->field_type,
							'position' => $tableField->field_position,
							'identify' => $tableField->field_identifier,
							'label' => $tableField->field_title,
							'instruction' => $tableField->field_instructions,
							'options' => json_decode($tableField->field_settings)
							);
							if (!empty($tableField->field_id) && $tableField->field_type != "static-content")
							{
								$fieldSubmission[] = '`sb_' . $tableField->field_id . '` ' . JSNUniformHelper::replaceField($tableField->field_type);
							}
						}
						$formPages['page_id'] = 0;
						$formPages['page_title'] = $page->page_title;
						$formPages['form_id'] = $table->form_id;
						$formPages['page_content'] = isset($parsedFields) ? json_encode($parsedFields) : "";
						if (!empty($dataField))
						{
							$formPages['page_template'] = json_encode(array('dataField' => $dataField, 'dataFormLayout' => $pageTemplate->dataFormLayout));
						}
						else
						{
							$formPages['page_template'] = "";
						}
						$tablePage = JTable::getInstance('Page', 'JSNUniformTable');
						$tablePage->bind($formPages);
						if (!$tablePage->store())
						{
							$this->setError($tablePage->getError());
						}
					}
					$fieldSubmission = implode(",", $fieldSubmission);
					$this->_db->setQuery("DROP TABLE IF EXISTS #__jsn_uniform_submissions_{$table->form_id}");
					$this->_db->execute();
					$this->_db->setQuery("CREATE TABLE IF NOT EXISTS #__jsn_uniform_submissions_{$table->form_id} ({$fieldSubmission})");
					$this->_db->execute();
				}
				else
				{
					throw new Exception($table->getError());
				}
			}
		}
		if (!$checkCreate)
		{
			$msg = JText::sprintf('JSN_UNIFORM_YOU_HAVE_REACHED_THE_LIMITATION_OF_3_FORM_IN_FREE_EDITION', 0) . ' <a class="jsn-link-action" href="index.php?option=com_uniform&view=upgrade">' . JText::_("JSN_UNIFORM_UPGRADE_EDITION") . '</a>';
			throw new Exception($msg);
		}
		return true;
	}

}
