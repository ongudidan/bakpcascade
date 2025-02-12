<?php

/**
 * @version     $Id: form.php 19014 2012-11-28 04:48:56Z thailv $
 * @package     JSNUniform
 * @subpackage  Controller
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */
defined('_JEXEC') or die('Restricted access');
/**
 * Form controllers of JControllerForm
 * 
 * @package     Controllers
 * @subpackage  Form
 * @since       1.6
 */
class JSNUniformControllerForm extends JControllerForm
{

	protected $option = JSN_UNIFORM;

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   11.1
	 */
	public function save($key = null, $urlVar = null)
	{

		$input = JFactory::getApplication()->input;
		$redirectUrl = $input->getVar('redirect_url', '');
		$redirectUrlForm = $input->getVar('redirect_url_form', '');
		$openArticle = $input->getVar('open_article', '');
		parent::save();
		$redirect = $this->redirect;
		if ($redirectUrl)
		{
			$this->setRedirect(JRoute::_($redirectUrl, false), "");
		}
		if ($openArticle)
		{
			$this->setRedirect($redirect . '&opentarticle=open');
		}
		if ($redirectUrlForm)
		{
			$this->setRedirect($redirectUrlForm);
		}
		$session = JFactory::getSession();
		$sessionQueue = $session->get('registry');
		$sessionQueue->set('com_jsnuniform', null);

	}

	/**
	 * Save page form to session
	 * 
	 * @return void
	 */
	public function savePage()
	{
		$post = $_POST;
		$session = JFactory::getSession();
		$formId = isset($post['form_id']) ? $post['form_id'] : 0;

		if (!empty($post['form_page_name']))
		{
			$tmpIdentify = array();
			$formContent = '';
			if (isset($post['form_content']))
			{
				$formContent = is_array($post['form_content']) ? json_encode($post['form_content']) : $post['form_content'];
				$formContent = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($formContent) : $formContent;
			}
			$formPageName = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($post['form_page_name']) : $post['form_page_name'];
			$session->set('form_page_' . $formPageName, $formContent, 'form-design-' . $formId);
		}
		if (!empty($post['form_list_page']))
		{
			$count = 0;
			foreach ($post['form_list_page'] as $listPage)
			{
				$dataField = "";
				$pageName = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($listPage[0]) : $listPage[0];
				if (isset($pageName) && isset($post['form_page_name']))
				{
					$dataField = $session->get('form_page_' . $pageName, '', 'form-design-' . $formId);
					if (!empty($dataField))
					{
						if (!is_array($dataField))
						{
							$dataField = json_decode($dataField);
						}
						foreach ($dataField as $index => $field)
						{
							$count++;
							if (!empty($field->identify))
							{
								while (in_array($field->identify, $tmpIdentify))
								{
									$field->identify = $field->identify . '_' . ($count + 1);
								}
								$tmpIdentify[] = $field->identify;
								$dataField[$index]->identify = preg_replace('/[^a-z0-9-._]/i', "", $field->identify);
							}
						}
						$session->set('form_page_' . $pageName, json_encode($dataField), 'form-design-' . $formId);
					}
				}
			}
			$formListPage = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes(json_encode($post['form_list_page'])) : json_encode($post['form_list_page']);
			$session->set('form_list_page', $formListPage, 'form-design-' . $formId);
		}
		jexit();

	}

	/**
	 * load data field on session
	 * 
	 * @return json code
	 */
	public function loadSessionField()
	{
		$post = $_POST;
		$formId = isset($post['form_id']) ? $post['form_id'] : 0;
		$session = JFactory::getSession();
		$formPage = array();
		$tmpIdentify = array();

		if (isset($post['form_page_name']) && isset($post['form_content']))
		{
			$formContent = is_array($post['form_content']) ? json_encode($post['form_content']) : $post['form_content'];
			$formContent = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($formContent) : $formContent;

			$formPageName = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($post['form_page_name']) : $post['form_page_name'];
			$session->set('form_page_' . $formPageName, $formContent, 'form-design-' . $formId);
		}
		if (!empty($post['form_list_page']))
		{
			$count = 0;
			foreach ($post['form_list_page'] as $listPage)
			{
				$dataField = "";
				$pageName = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($listPage[0]) : $listPage[0];
				if (isset($pageName) && isset($post['form_page_name']))
				{
					$dataField = $session->get('form_page_' . $pageName, '', 'form-design-' . $formId);

					if (isset($dataField))
					{
						if (!is_array($dataField))
						{
							$dataField = json_decode($dataField);
						}
						if (is_array($dataField))
						{
							foreach ($dataField as $index => $field)
							{
								$count++;
								while (in_array($field->identify, $tmpIdentify))
								{
									$field->identify = $field->identify . '_' . ($count + 1);
								}
								$tmpIdentify[] = $field->identify;
								$dataField[$index]->identify = preg_replace('/[^a-z0-9-._]/i', "", $field->identify);
							}
							$session->set('form_page_' . $pageName, json_encode($dataField), 'form-design-' . $formId);
						}

						if (!empty($dataField) && $dataField != 'null')
						{
							$formPage = array_merge($formPage, $dataField);
						}
					}
				}
			}
			if (!empty($formPage))
			{
				echo json_encode($formPage);
			}
		}

		jexit();

	}

	/**
	 * load page on session
	 * 
	 * @return json code
	 */
	public function loadPage()
	{
		$post = $_POST;
		$formId = isset($post['form_id']) ? $post['form_id'] : 0;
		$dataPage = "";
		$pageDefault = isset($post['join_page']) ? $post['join_page'] : '';
		if (!empty($post['form_page_name']))
		{
			$session = JFactory::getSession();
			$formPageName = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($post['form_page_name']) : $post['form_page_name'];
			$formPage = $session->get('form_page_' . $formPageName, '', 'form-design-' . $formId);

			if (isset($post['form_page_old_name']) && $post['form_page_old_name'] != $formPageName)
			{

				if (!empty($post['form_page_old_content']))
				{
					$formContentOld = is_array($post['form_page_old_content']) ? json_encode($post['form_page_old_content']) : $post['form_page_old_content'];
					$formOldContent = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($formContentOld) : $formContentOld;
					$session->set('form_page_' . $post['form_page_old_name'], $formOldContent, 'form-design-' . $formId);
				}
			}

			if (isset($formPage) && $pageDefault != 'defaultPage')
			{
				if (is_array($formPage))
				{
					$dataPage = json_encode($formPage);
				}
				else
				{
					$dataPage = $formPage;
				}
			}
			else
			{
				if (!empty($post['form_id']))
				{
					$formId = (int) $post['form_id'];
					$model = $this->getModel('form');
					$items = $model->getItem($formId);

					if (!empty($items->form_content))
					{
						foreach ($items->form_content as $formContent)
						{
							$session->set('form_page_' . $formContent->page_id, $formContent->page_content, 'form-design-' . $formId);
						}

						$dataPage = $session->get('form_page_' . $formPageName, '', 'form-design-' . $formId);
					}
				}
				else
				{
					$dataPage = $session->get('form_page_' . $formPageName, '', 'form-design-' . $formId);
				}
			}
		}

		if (!empty($post['join_page']) && $post['join_page'] == "join" && isset($post['form_list_page']) && count($post['form_list_page']) > 1)
		{
			$dataListPage = array();
			$listPage = $session->get('form_list_page');
			$formPageIndex = array();
			foreach ($post['form_list_page'] as $index => $listPage)
			{
				$pageName = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($listPage[0]) : $listPage[0];
				if (!empty($pageName) && !empty($post['form_page_name']))
				{
					$pageContent = $session->get('form_page_' . $pageName, '', 'form-design-' . $formId);

					if (!empty($pageContent) && $pageContent != 'null')
					{
						$pageContent = json_decode($pageContent);
						$dataListPage = array_merge($dataListPage, $pageContent);
					}
				}
				if ($index == 0)
				{
					$formPageIndex[] = $pageName;
					$pageName1 = (get_magic_quotes_gpc() == true || get_magic_quotes_runtime() == true) ? stripslashes($listPage[1]) : $listPage[1];
					$formPageIndex[] = $pageName1;
				}
				else
				{
					$session->clear('form_page_' . $pageName, 'form-design-' . $formId);
				}
			}
			$dataListPageEncode = json_encode($dataListPage);
			$session->clear('form_list_page', 'form-design-' . $formId);
			$session->set('form_page_' . $formPageIndex[0], $dataListPageEncode, 'form-design-' . $formId);
			$session->set('form_list_page', json_encode($formPageIndex), 'form-design-' . $formId);
			echo $dataListPageEncode;
		}
		else
		{
			echo $dataPage;
		}
		jexit();

	}

	/**
	 * get count field 
	 */
	public static function getcountfield()
	{

		$post = $_POST;
		$fieldId = isset($post['field_id']) ? $post['field_id'] : 0;
		$formId = isset($post['form_id']) ? $post['form_id'] : 0;
		if ($formId && $fieldId)
		{
			echo json_encode(JSNUniformHelper::getDataSumbissionByField($fieldId, $formId));
		}
		jexit();

	}

}
