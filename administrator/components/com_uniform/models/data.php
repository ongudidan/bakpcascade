<?php

/**
 * @version     $Id: data.php 19014 2012-11-28 04:48:56Z thailv $
 * @package     JSNUniform
 * @subpackage  Models
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Data model of JSN Framework Sample component
 *
 * @package     Models
 * @subpackage  Data
 * @since       1.6
 */
class JSNUniformModelData extends JSNDataModel
{

	/**
	 * Do any preparation needed before doing real data backup.
	 *
	 * @param   array  &$options  Backup options.
	 * @param   array  &$name     array('zip' => 'zip_backup_file_name', 'xml' => 'xml_backup_file_name')
	 *
	 * @return  void
	 */
	protected function beforeBackup(&$options, &$name)
	{

		$options['tables'] = array(
		'#__jsn_uniform_config',
		'#__jsn_uniform_data',
		'#__jsn_uniform_emails',
		'#__jsn_uniform_fields',
		'#__jsn_uniform_forms',
		'#__jsn_uniform_form_pages',
		'#__jsn_uniform_messages',
		'#__jsn_uniform_templates'
		);
		$dataForm = $this->getForm();
		if (!empty($dataForm) && count($dataForm))
		{
			foreach ($dataForm as $formId)
			{
				$options['tables'][] = '#__jsn_uniform_submissions_' . (int) $formId->form_id;
			}
		}
		if (!empty($options['files']))
		{
			$folderUpload = $this->getFolderUploadConfig();
			$folderUpload = !empty($folderUpload->value) ? $folderUpload->value : 'images/jsnuniform/';

			$folderUrl = $folderUpload . '/jsnuniform_uploads/';

			if (is_dir(JPath::clean(JPATH_ROOT . $folderUrl)))
			{
				$options['files'] = array($folderUrl => '.');
			}
			else
			{
				$options['files'] = null;
			}
		}
	}

	/**
	 * Store backed up table data to XML object.
	 *
	 * @param   array  $table  Name of data table.
	 * @param   array  $rows   Dumped data from the table.
	 *
	 * @return  void
	 */
	protected function storeTableData($table, $rows)
	{
		// Create new node for storing backed up table data
		$node = $this->data->tables->addChild('table');
		$node->addAttribute('name', $table);

		// Store backed up table data to table node
		$node = $node->addChild('rows');
		foreach ($rows AS $row)
		{
			// Create new node for storing current row of data
			$rowNode = $node->addChild('row');
			foreach ($row AS $name => $value)
			{
				$value = str_replace("&nbsp;", " ", $value);
				$rowNode->addChild($name, htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false));
			}
		}
	}

	/**
	 * get data folder upload in data default config
	 * 
	 * @return Object
	 */
	public function getFolderUploadConfig()
	{
		$this->_db->setQuery(
		$this->_db->getQuery(true)
		->select('*')
		->from('#__jsn_uniform_config')
		->where('name="folder_upload"')
		);
		return $this->_db->loadObject();
	}

	/**
	 * Get all id form
	 * 
	 * @return Object
	 */
	public function getForm()
	{
		$this->_db->setQuery(
		$this->_db->getQuery(true)
		->select('form_id')
		->from('#__jsn_uniform_forms')
		);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get all fields table in form
	 *
	 * @param   type  $formId  Form id
	 * 
	 * @return type 
	 */
	public function getFields($formId)
	{
		$this->_db->setQuery(
		$this->_db->getQuery(true)
		->select('field_id,field_type')
		->from('#__jsn_uniform_fields')
		->where('form_id = ' . (int) $formId)
		);
		return $this->_db->loadObjectList();
	}

	/**
	 * Do any extra work needed after doing real data restore.
	 *
	 * @param   array  &$backup  Uploaded backup file.
	 *
	 * @return  void
	 */
	protected function afterRestore(&$backup)
	{
		$session = JFactory::getSession();
		$sessionQueue = $session->get('registry');
		$sessionQueue->set('com_jsnuniform', null);
		$dataForm = $this->getForm();

		if (!empty($dataForm) && count($dataForm))
		{
			foreach ($dataForm as $formId)
			{
				if (!empty($formId->form_id) && (int) $formId->form_id)
				{
					$fieldSubmission = array();
					$fieldSubmission[] = "`data_id` int(11)";
					$getFields = $this->getFields($formId->form_id);

					if (!empty($getFields) && is_array($getFields) && count($getFields))
					{
						foreach ($getFields as $field)
						{
							if ($field->field_type != 'static-heading' && $field->field_type != 'static-paragraph' && $field->field_type != 'horizontal-ruler' && $field->field_type != 'static-content')
							{
								$fieldSubmission[] = '`sb_' . $field->field_id . '` ' . JSNUniformHelper::replaceField($field->field_type);
							}
						}
						$fieldSubmission = implode(",", $fieldSubmission);
						$this->_db->setQuery("DROP TABLE IF EXISTS #__jsn_uniform_submissions_{$formId->form_id}");
						$this->_db->execute();
						$this->_db->setQuery("CREATE TABLE IF NOT EXISTS #__jsn_uniform_submissions_{$formId->form_id} ({$fieldSubmission})");
						$this->_db->execute();
					}
				}
			}
		}
		$folderUpload = $this->getFolderUploadConfig();
		if (!empty($folderUpload))
		{
			$folderUrl = $folderUpload->value . '/jsnuniform_uploads/';
			if (is_dir(JPath::clean(JPATH_ROOT . $folderUrl)))
			{
				if (!file_exists(JPATH_ROOT . $folderUrl . '/.htaccess'))
				{

					@$fp = fopen(JPATH_ROOT . $folderUrl . '/.htaccess', 'w');
					@fwrite($fp, "RemoveHandler .php .phtml .php3 \nRemoveType .php .phtml .php3 \nphp_flag engine off \n ");
					@fclose($fp);
				}
			}
		}
		$this->restoreTables();

		$query = $this->_db->getQuery(true);
		$query->update($this->_db->quoteName("#__jsn_uniform_fields"));
		$query->set("field_type = " . $this->_db->Quote("static-content"));
		$query->where("field_type='static-heading'", "OR");
		$query->where("field_type='static-paragraph'", "OR");
		$query->where("field_type='horizontal-ruler'", "OR");
		$this->_db->setQuery($query);
		$this->_db->execute();

		$this->_db->setQuery(
		$this->_db->getQuery(true)
		->select('*')
		->from("#__jsn_uniform_form_pages")
		);
		if ($data = $this->_db->loadObjectList())
		{
			foreach ($data as $item)
			{
				$newContent = array();
				$newTemplate = new stdClass();
				$templateItem = array();
				if (isset($item->page_content))
				{
					$pageContent = json_decode($item->page_content);
					if ($pageContent && (is_array($pageContent) || is_object($pageContent)))
					{
						foreach ($pageContent as $content)
						{
							if ($content->type == 'static-heading')
							{
								$content->type = 'static-content';
								$typeHeading = isset($content->options->type) ? $content->options->type : '';
								$labelHeading = isset($content->options->label) ? $content->options->label : '';
								$content->options->value = "<{$typeHeading}>{$labelHeading}</{$typeHeading}>";
							}
							else if ($content->type == 'static-paragraph')
							{
								$content->type = 'static-content';
							}
							else if ($content->type == 'horizontal-ruler')
							{
								$content->type = 'static-content';
								$sizeHr = isset($content->options->size) ? $content->options->size : '';
								$content->options->value = "<hr class=\"{$sizeHr}\"/>";
							}
							$newContent[] = $content;
						}
					}
				}
				if (isset($item->page_template))
				{
					$pageTemplate = json_decode($item->page_template);
					$newTemplate->dataFormLayout = isset($pageTemplate->dataFormLayout) ? $pageTemplate->dataFormLayout : 'default';
					if (isset($pageTemplate->dataField) && (is_array($pageTemplate->dataField) || is_object($pageTemplate->dataField)))
					{
						foreach ($pageTemplate->dataField as $template)
						{
							if ($template->field_type == 'static-heading')
							{
								$template->field_type = 'static-content';
								$settingsHeading = isset($template->field_settings) ? json_decode($template->field_settings) : '';
								if ($settingsHeading && (is_object($settingsHeading) || is_object($settingsHeading)))
								{
									$typeHeading = isset($settingsHeading->options->type) ? $settingsHeading->options->type : '';
									$labelHeading = isset($settingsHeading->options->label) ? $settingsHeading->options->label : '';
									$settingsHeading->options->value = "<{$typeHeading}>{$labelHeading}</{$typeHeading}>";
								}
								$template->field_settings = json_encode($settingsHeading);
							}
							else if ($template->field_type == 'static-paragraph')
							{
								$template->field_type = 'static-content';
							}
							else if ($template->field_type == 'horizontal-ruler')
							{
								$template->field_type = 'static-content';
								$settingsHr = isset($template->field_settings) ? json_decode($template->field_settings) : '';
								if ($settingsHr && (is_object($settingsHr) || is_object($settingsHr)))
								{
									$sizeHr = isset($settingsHr->options->size) ? $settingsHr->options->size : '';
									$settingsHr->options->value = "<hr class=\"{$sizeHr}\"/>";
								}
								$template->field_settings = json_encode($settingsHr);
							}
							$templateItem[] = $template;
						}
						$newTemplate->dataField = $templateItem;
					}
				}
				$query = $this->_db->getQuery(true);
				$query->update($this->_db->quoteName("#__jsn_uniform_form_pages"));
				$query->set("page_content = " . $this->_db->Quote(json_encode($newContent)));
				$query->set("page_template = " . $this->_db->Quote(json_encode($newTemplate)));
				$query->where("page_id = " . intval($item->page_id));
				$this->_db->setQuery($query);
				$this->_db->execute();
			}
		}
	}

	/**
	 * Restore database table data from backup.
	 *
	 * @return  void
	 */
	protected function restoreTables()
	{
		// Get database object
		$db = JFactory::getDbo();

		foreach ($this->data->tables->table AS $table)
		{
			// Truncate current table data
			if (JSNUniformHelper::checkTableSql((string) $table['name']))
			{
				$query = $db->getQuery(true);
				$query->delete((string) $table['name']);
				$query->where('1');

				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $e)
				{
					throw $e;
				}
				// Get table columns
				$columns = array();

				foreach ($table->rows->row[0]->children() AS $column)
				{
					$columns[] = $column->getName();
				}

				// Restore database table data from backup
				$query = $db->getQuery(true);

				$query->insert((string) $table['name']);
				$query->columns(implode(', ', $columns));

				foreach ($table->rows->row AS $row)
				{
					$columns = array();

					foreach ($row->children() AS $column)
					{
						// Initialize column value
						$column = html_entity_decode((string) $column, ENT_QUOTES, 'UTF-8');
						$column = !is_numeric($column) ? $db->quote($column) : $column;

						$columns[] = $column;
					}

					$query->values(implode(', ', $columns));
				}

				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $e)
				{
					throw $e;
				}
			}
		}
	}

	/**
	 * Do any preparation needed before doing real data restore.
	 *
	 * @param   string   &$backup       Path to folder containing extracted backup files.
	 * @param   boolean  $checkEdition  Check for matching edition before restore?
	 *
	 * @return  void
	 */
	protected function beforeRestore(&$backup, $checkEdition = true)
	{
		$dataForm = $this->getForm();
		if (!empty($dataForm) && count($dataForm))
		{
			foreach ($dataForm as $formId)
			{
				if (!empty($formId->form_id) && (int) $formId->form_id)
				{
					$this->_db->setQuery("DROP TABLE IF EXISTS #__jsn_uniform_submissions_{$formId->form_id}");
					$this->_db->execute();
				}
			}
		}
		parent::beforeRestore($backup, $checkEdition);
	}

}
