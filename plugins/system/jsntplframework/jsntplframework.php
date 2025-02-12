<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  TPLFramework
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Load framework defines
require_once dirname(__FILE__) . '/jsntplframework.defines.php';

// Load class loader
require_once JSN_PATH_TPLFRAMEWORK . '/libraries/joomlashine/loader.php';

/**
 * Implement joomla events for template framework
 *
 * @package     TPLFramework
 * @subpackage  Plugin
 * @since       1.0.0
 */
class PlgSystemJSNTPLFramework extends JPlugin
{
	/**
	 * Joomla application instance
	 * @var JApplication
	 */
	protected $app;

	/**
	 * Template administrator object
	 *
	 * @var JSNTplTemplateAdmin
	 */
	private $_templateAdmin;

	/**
	 * Implement onAfterInitialise event
	 *
	 * @return  void
	 */
	public function onAfterInitialise ()
	{
		$this->app = JFactory::getApplication();
		$this->loadLanguage();

		// Register extension uninstall process hook
		$this->app->registerEvent('onExtensionAfterUninstall', 'PlgSystemJSNTPLFramework');

		// Stop system execution if a widget action is dispatched
		if (JSNTplWidget::dispatch() === true)
			jexit();

		// Get requested component, view and task
		$this->option	= $this->app->input->getCmd('option');
		$this->view		= $this->app->input->getCmd('view');
		$this->task		= $this->app->input->getCmd('task');

		// Redirect to update page if necessary
		if ($this->option == 'com_installer' AND $this->view == 'update' AND $this->task == 'update.update' AND count($cid = (array) $this->app->input->getVar('cid', array())))
		{
			// Check if extension to updated is JoomlaShine product
			$db	= JFactory::getDbo();
			$q	= $db->getQuery(true);

			$q->select('e.extension_id, e.type, e.element, e.folder');
			$q->from('#__extensions AS e');
			$q->join('INNER', '#__updates AS u ON e.extension_id = u.extension_id');
			$q->where('u.update_id = ' . (int) $cid[0]);

			$db->setQuery($q);

			if ($ext = $db->loadObject())
			{
				if (($ext->type == 'template' AND strpos($ext->element, 'jsn_') === 0)
					OR ($ext->type == 'plugin' AND $ext->folder == 'system' AND $ext->element == basename(JSN_PATH_TPLFRAMEWORK)))
				{
					// Get style id
					$q = $db->getQuery(true);

					$q->select('id');
					$q->from('#__template_styles');
					$q->where('template = ' . $q->quote($ext->element), 'OR');
					$q->where('template LIKE ' . $q->quote('jsn_%'));
					$q->order('id');

					$db->setQuery($q);

					if ($styleId = $db->loadResult())
					{
						return $this->app->redirect('index.php?option=com_templates&task=style.edit&id=' . $styleId);
					}
				}
			}
		}
	}

	/**
	 * Save active form context to memory when editing an template
	 *
	 * @param   object  $context  Current context of template form
	 * @param   object  $data     Data of the form
	 * @return  void
	 */
	public function onContentPrepareForm ($context, $data)
	{
		if ($context->getName() == 'com_templates.style' AND ! empty($data))
		{
			$templateName = is_object($data) ? $data->template : $data['template'];

			if (substr($templateName, 0, 4) == 'jsn_')
			{
				$templateManifest	= JSNTplHelper::getManifest($templateName);
				$templateGroup		= isset($templateManifest->group) ? trim((string) $templateManifest->group) : '';

				// Create template admin instance
				if ($templateGroup == 'jsntemplate')
				{
					$this->_templateAdmin = JSNTplTemplateAdmin::getInstance($context);
				}
			}
		}
	}

	/**
	 * Implement onBeforeRender event to register all needed asset files
	 *
	 * @return  void
	 */
	public function onBeforeRender ()
	{
		if (isset($this->_templateAdmin) AND $this->_templateAdmin instanceOf JSNTplTemplateAdmin)
		{
			$this->_templateAdmin->registerAssets();
		}
	}

	/**
	 * Render template admin UI
	 *
	 * @return  void
	 */
	public function onAfterRender ()
	{
		if (isset($this->_templateAdmin) AND $this->_templateAdmin instanceOf JSNTplTemplateAdmin)
		{
			$this->_templateAdmin->render();
		}

		if ($this->app->isSite() AND substr($this->app->getTemplate(), 0, 4) == 'jsn_')
		{
			$document	= JFactory::getDocument();
			$body		= JResponse::getBody();

			// Search for body tag
			if (preg_match('/<body[^>]*id="jsn-master"[^>]*>/', $body, $bt))
			{
				list($h, $b) = explode($bt[0], $body);

				// Search for all scripts of JoomlaShine template and template framework
				if (preg_match_all('#<script[^>]*src=[\'"][^\'"]*(/plugins/system/jsntplframework/assets/joomlashine/js/|/templates/jsn_)[^\'"]+[\'"][^>]*>[\s\t\r\n]*</script>([\r\n][\s\t]*)#i', $h, $matches, PREG_SET_ORDER))
				{
					foreach ($matches AS $match)
					{
						// Remove script tag from head section
						$h = str_replace($match[0], '', $h);

						// Copy script tag to the end of body section
						$b = str_replace('</body>', $match[0] . '</body>', $b);
					}
				}

				// Search for all inline script code in head section
				$parts = explode('<script type="text/javascript">', $h);

				if (($n = count($parts)) > 1)
				{
					for ($i = 1; $i < $n; $i++)
					{
						if (strpos($parts[$i], 'require.config({') === false)
						{
							$part = '<script type="text/javascript">' . substr($parts[$i], 0, strpos($parts[$i], '</script>') + 9);

							// Remove script tag from head section
							$h = str_replace($part, '', $h);

							// Copy script tag to the end of body section
							$b = str_replace('</body>', $part . '</body>', $b);
						}
					}
				}

				// Re-create response body
				$body = $h . $bt[0] . $b;
			}

			if (isset($document->helper) && $document->helper instanceOf JSNTplTemplateHelper && $document->compression > 0)
			{
				// Verify cache directory
				if ( ! preg_match('#^(/|\\|[a-z]:)#i', $document->params->get('cacheDirectory')))
				{
					$cachePath = JPATH_ROOT . '/' . rtrim($document->params->get('cacheDirectory'), '\\/');
				}
				else
				{
					$cachePath = rtrim($document->params->get('cacheDirectory'), '\\/');
				}

				if (is_writable($cachePath))
				{
					// Start compress CSS
					if ($document->compression == 1 OR $document->compression == 2)
					{
						$body = preg_replace_callback('/(<link([^>]+)rel=["|\']stylesheet["|\']([^>]*)>\s*)+/i', array('JSNTplCompressCss', 'compress'), $body);
					}

					// Start compress JS
					if ($document->compression == 1 OR $document->compression == 3)
					{
						$body = preg_replace_callback('/(<script([^>]+)src=["|\']([^"|\']+)["|\']([^>]*)>\s*<\/script>\s*)+/i', array('JSNTplCompressJs', 'compress'), $body);
					}
				}
			}

			JResponse::setBody($body);
		}
		elseif ($this->app->isAdmin())
		{
			// Execute update checker
			$this->checkUpdate();
		}
	}

	/**
	 * Implement onExtensionAfterSave event to save template configuration params
	 *
	 * @param   string  $task  Extension executed task
	 * @param   mixed   $data  Data of task after executed
	 *
	 * @return  void
	 */
	public function onExtensionAfterSave ($task, $data)
	{
		if ($task != 'com_templates.style')
		{
			return;
		}

		// Get options for JoomlaShine template
		$options = isset($_POST['jsn']) ? $_POST['jsn'] : array();

		if (@count($options))
		{
			// Auto strip slashes if magic_quote_gpc is on
			if (get_magic_quotes_runtime() OR get_magic_quotes_gpc())
			{
				foreach ($options AS $k => $v)
				{
					if (is_string($v))
					{
						$options[$k] = stripslashes($v);
					}
				}
			}

			// Check if compression parameters have been changed
			if
			(
				$this->app->getUserState('jsn.template.maxCompressionSize') != $data->params['maxCompressionSize']
				OR
				$this->app->getUserState('jsn.template.cacheDirectory') != $data->params['cacheDirectory']
			)
			{
				// Import necessary Joomla library
				jimport('joomla.filesystem.folder');

				// Generate path to cache directory
				if ( ! preg_match('#^(/|\\|[a-z]:)#i', $this->app->getUserState('jsn.template.cacheDirectory')))
				{
					$cacheDirectory = JPATH_ROOT . '/' . rtrim($this->app->getUserState('jsn.template.cacheDirectory'), '\\/');
				}
				else
				{
					$cacheDirectory = rtrim($this->app->getUserState('jsn.template.cacheDirectory'), '\\/');
				}

				// Remove entire cache directory
				! is_dir($cacheDirectory . '/' . $data->template) OR JFolder::delete($cacheDirectory . '/' . $data->template);
			}

			// Store template style params
			$data->params = json_encode($options);
			$data->store();
		}
	}

	/**
	 * Implement onExtensionAfterUninstall event to remove the template framework.
	 *
	 * @param   object   $parent  Parent installer object.
	 * @param   integer  $eid     Id of the extension that is uninstalled.
	 *
	 * @return  void
	 */
	public static function onExtensionAfterUninstall($parent, $eid)
	{
		// Count installed JoomlaShine templates
		$db	= JFactory::getDbo();
		$q	= $db->getQuery(true);

		$q->select('COUNT(*)');
		$q->from('#__extensions');
		$q->where('type = ' . $q->quote('template'));
		$q->where('custom_data = ' . $q->quote('jsntemplate'));

		$db->setQuery($q);

		// If there is no any JoomlaShine template installed, uninstall the template framework
		if (0 == (int) $db->loadResult())
		{
			JFactory::getLanguage()->load('com_installer');

			// Find extension id of the template framework
			$q = $db->getQuery(true);

			$q->select('extension_id');
			$q->from('#__extensions');
			$q->where('type = ' . $q->quote('plugin'));
			$q->where('folder = ' . $q->quote('system'));
			$q->where('element = ' . $q->quote(basename(JSN_PATH_TPLFRAMEWORK)));

			$db->setQuery($q);

			// Continue un-installation only if the extension that is uninstalled is not the template framework itself
			if (($pluginId = $db->loadResult()) AND $pluginId != $eid)
			{
				// Un-protect the template framework
				$executeMethod	= method_exists($db, 'query') ? 'query' : 'execute';
				$q				= $db->getQuery(true);

				$q->update('#__extensions');
				$q->set('protected = 0');
				$q->where('extension_id = ' . (int) $pluginId);

				$db->setQuery($q);
				$db->{$executeMethod}();

				// Get Joomla installer object to remove template framework
				$installer = JInstaller::getInstance();

				if ($installer->uninstall('plugin', $pluginId))
				{
					JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_INSTALLER_UNINSTALL_SUCCESS', 'plugin'));
				}
			}
		}
	}

	/**
	 * Check if there is new update for installed JoomlaShine product.
	 *
	 * @return  void
	 */
	private function checkUpdate()
	{
		// Check for update every predefined period of time
		if (time() - (int) $this->params->get('update-check', 0) < JSN_TPLFRAMEWORK_CHECK_UPDATE_PERIOD)
		{
			return;
		}

		// Backup request variable
		$backup = JFactory::getApplication()->input->getCmd('template');

		// Get method to execute database query
		$db				= JFactory::getDbo();
		$executeMethod	= method_exists($db, 'query') ? 'query' : 'execute';

		// Get list of installed JoomlaShine template
		$q	= $db->getQuery(true);

		$q->select('extension_id, name, type, element');
		$q->from('#__extensions');
		$q->where('type = ' . $q->quote('template'));
		$q->where('element LIKE ' . $q->quote('jsn_%'));

		$db->setQuery($q);

		if ($templates = $db->loadObjectList())
		{
			foreach ($templates AS $template)
			{
				// Set template name to request variable
				JFactory::getApplication()->input->set('template', $template->element);

				// Trigger check-update action of the update widget
				$widget = new JSNTplWidgetUpdate;
				$widget->checkUpdateAction();

				// Get result
				$result = $widget->getResponse();

				// Do we have update?
				foreach (array('template', 'framework') AS $ext)
				{
					if ($result[$ext]['hasUpdate'])
					{
						// Get extension details for template framework
						if ($ext == 'framework' AND ! isset($framework))
						{
							$q = $db->getQuery(true);

							$q->select('extension_id, name, type, element');
							$q->from('#__extensions');
							$q->where('type = ' . $q->quote('plugin'));
							$q->where('folder = ' . $q->quote('system'));
							$q->where('element = ' . $q->quote(basename(JSN_PATH_TPLFRAMEWORK)));

							$db->setQuery($q);

							$framework = $db->loadObject();
						}

						// Generate extension details
						$ext_id	= $ext == 'template' ? (int) $template->extension_id : (int) $framework->extension_id;
						$name	= $ext == 'template' ? $template->name : $framework->name;
						$type	= $ext == 'template' ? $template->type : $framework->type;
						$elm	= $ext == 'template' ? $template->element : $framework->element;

						// Check if update is stored before
						if ($ext == 'template' OR ! isset($current['framework']))
						{
							$q = $db->getQuery(true);

							$q->select('version');
							$q->from('#__updates');
							$q->where('extension_id = ' . $ext_id);

							$db->setQuery($q);

							$current[$ext] = $db->loadResult();
						}

						// Store update info to Joomla updates table
						$q = $db->getQuery(true);

						if ($current[$ext])
						{
							if (version_compare($current[$ext], $result[$ext]['newVersion'], '<'))
							{
								$q->update('#__updates');
								$q->set('version = ' . $q->quote($result[$ext]['newVersion']));
								$q->where('extension_id = ' . $ext_id);
								$q->where('version = ' . $q->quote($current[$ext]));

								$db->setQuery($q);
								$db->{$executeMethod}();
							}
						}
						else
						{
							$q->insert('#__updates');
							$q->columns('extension_id, name, element, type, version');
							$q->values($ext_id . ', ' . $q->quote(JText::_($name)) . ', ' . $q->quote($elm) . ', ' . $q->quote($type) . ', ' . $q->quote($result[$ext]['newVersion']));

							$db->setQuery($q);
							$db->{$executeMethod}();
						}
					}
				}
			}
		}

		// Reset update checking status
		$q = $db->getQuery(true);

		$q->update('#__extensions');
		$q->set("params = '" . json_encode(array('update-check' => time())) . "'");
		$q->where('type = ' . $q->quote('plugin'));
		$q->where('folder = ' . $q->quote('system'));
		$q->where('element = ' . $q->quote(basename(JSN_PATH_TPLFRAMEWORK)));

		$db->setQuery($q);
		$db->{$executeMethod}();

		// Restore request variable
		JFactory::getApplication()->input->set('template', $backup);
	}
}
