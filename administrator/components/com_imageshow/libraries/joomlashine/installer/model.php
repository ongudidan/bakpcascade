<?php
/**
 * @version    $Id$
 * @package    JSN_Sample
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import necessary Joomla libraries
jimport('joomla.application.component.model');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.file');
jimport('joomla.installer.installer');

/**
 * Model class of JSN Installer library.
 *
 * @package  JSN_Sample
 * @since    1.1.0
 */
class JSNInstallerModel extends JSNBaseModel
{
	/**
	 * Base download link.
	 *
	 * @var  string
	 */
	protected $downloadLink = 'http://www.joomlashine.com/index.php?option=com_lightcart&controller=remoteconnectauthentication&task=authenticate&tmpl=component&upgrade=yes&';

	/**
	 * Check version link.
	 *
	 * @var  string
	 */
	protected $checkLink = 'http://www.joomlashine.com/versioning/product_version.php?category=cat_extension';

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Load language manually
		$lang = JFactory::getLanguage();
		$lang->load('jsn_installer', JPATH_COMPONENT_ADMINISTRATOR . '/libraries/joomlashine/installer');

		// Get application object
		$this->app = JFactory::getApplication();

		// Get input object
		$this->input = $this->app->input;

		// Get Joomla config
		$this->config = JFactory::getConfig();
	}

	/**
	 * Download dependency package.
	 *
	 * @return  string  Package name.
	 */
	public function download()
	{
		// Get Joomla version
		$joomlaVersion = new JVersion;

		// Get product edition
		$edition = $this->getEdition();

		// Get dependency declaration
		$extension = (object) $_GET;

		// Build query string
		$query[] = 'joomla_version=' . $joomlaVersion->RELEASE;
		$query[] = 'username=' . $this->input->getUsername('customer_username');
		$query[] = 'password=' . $this->input->getString('customer_password');
		$query[] = 'identified_name=' . $extension->identified_name;

		// Build final download link
		$url = $this->downloadLink . implode('&', $query);

		// Generate file name for depdendency package
		$name[]	= 'jsn';
		$name[]	= $extension->identified_name;

		if ($edition)
		{
			$name[] = strtolower(str_replace(' ', '-', isset($extension->edition) ? $extension->edition : $edition));
		}

		$name[]	= 'j' . $joomlaVersion->RELEASE;
		$name[]	= 'install.zip';
		$name	= implode('_', $name);

		// Set maximum execution time
		ini_set('max_execution_time', 300);

		// Try to download the update package
		try
		{
			$result = $this->fetchHttp($url);

			// Validate downloaded data
			if (strlen($result) < 10)
			{
				// Get LightCart error code
				throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_ERROR_' . $result));
			}

			if ( ! JFile::write($this->config->get('tmp_path') . '/' . $name, $result))
			{
				throw new Exception(JText::_('JSN_EXTFW_INSTALLER_PACKAGE_SAVING_FAILED'));
			}
		}
		catch (Exception $e)
		{
			throw new Exception($url);
		}

		return $name;
	}

	/**
	 * Install dependency package.
	 *
	 * @return  string
	 */
	public function install()
	{
		// Finalize upload
		if (isset($_FILES['package']))
		{
			if ( ! JFile::upload($_FILES['package']['tmp_name'], $this->config->get('tmp_path') . '/' . $_FILES['package']['name']))
			{
				throw new Exception(JText::_('JSN_EXTFW_INSTALLER_PACKAGE_SAVING_FAILED'));
			}

			$this->input->set('package', $_FILES['package']['name']);
		}

		// Initialize dependency package path
		$file = $this->config->get('tmp_path') . '/' . $this->input->getString('package');
		$path = substr($file, 0, -4);

		if ( ! is_file($file))
		{
			throw new Exception(JText::sprintf('JSN_EXTFW_INSTALLER_PACKAGE_NOT_FOUND', $this->input->getString('package')));
		}

		// Extract dependency package
		if ( ! JArchive::extract($file, $path))
		{
			throw new Exception(JText::_('JSN_EXTFW_INSTALLER_EXTRACT_PACKAGE_FAIL'));
		}

		// Get dependency declaration
		if ($this->input->getString('package') OR isset($_FILES['package']))
		{
			// Get dependency declaration
			$extension = (object) $_GET;
			$extension->source = $path;

			// Get JSN Installer
			require_once JPATH_COMPONENT_ADMINISTRATOR . '/subinstall.php';

			$installer = $this->input->getCmd('option') . 'InstallerScript';
			$installer = new $installer;

			$installer->installExtension($extension);

			// Check if installation success
			$messages = $this->app->getMessageQueue();

			foreach ($messages AS $message)
			{
				if ($message['type'] == 'error')
				{
					throw new Exception($message['message']);
				}
			}
		}
		else
		{
			// Get Joomla installer object
			$installer = JInstaller::getInstance();

			// Install dependency package
			if ( ! $installer->update($path))
			{
				throw new Exception(JText::_('JSN_EXTFW_INSTALLER_INSTALL_PACKAGE_FAIL'));
			}
		}

		return 'SUCCESS';
	}

	/**
	 * Finalize dependency installation.
	 *
	 * @return  void
	 */
	public function finalize()
	{
		// Save live update notification setting to config table
		$model	= new JSNConfigModel;
		$form	= $model->getForm(array(), true);
		$data	= array('live_update_notification' => (string) $this->input->getInt('live_update_notification', 0));

		try
		{
			$model->save($form, $data);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	/**
	 * Check dependency.
	 *
	 * @param   array  &$dependencies  An array of dependency package.
	 *
	 * @return  mixed
	 */
	public function check(&$dependencies)
	{
		// Initialize variables
		$missingDependency		= 0;
		$authenticationRequired	= false;

		// Get product edition
		$edition = $this->getEdition();

		// Get object for working with extension table
		$extension = JTable::getInstance('Extension');

		// Get installed Joomla version
		$joomlaVersion = new JVersion;

		// Check dependency
		foreach ($dependencies AS & $dependency)
		{
			if ($dependency instanceof SimpleXMLElement)
			{
				$tmp = (array) $dependency;
				$tmp = (object) $tmp['@attributes'];
				$tmp->title = trim((string) $dependency != '' ? (string) $dependency : ($dependency['title'] ? (string) $dependency['title'] : $tmp->name));

				$dependency = $tmp;
			}

			// Build dependency path
			switch ($dependency->type = strtolower($dependency->type))
			{
				case 'component':
				case 'module':
					$path = (( ! isset($dependency->client) OR $dependency->client != 'site') ? JPATH_BASE : JPATH_ROOT) . "/{$dependency->type}s";
				break;

				case 'plugin':
					$path = JPATH_ROOT . '/plugins/' . $dependency->folder;
				break;

				case 'template':
					$path = JPATH_ROOT . '/templates';
				break;
			}

			$path .= '/' . $dependency->name;

			// Check if dependency is installed
			if (file_exists($path))
			{
				// Load dependency details
				$extension->load(
					array(
						'type'		=> $dependency->type,
						'element'	=> $dependency->name,
						'folder'	=> isset($dependency->folder) ? $dependency->folder : ''
					)
				);

				// Get currently installed dependency version
				$current = json_decode($extension->manifest_cache);
				$current = (is_object($current) AND isset($current->version)) ? $current->version : '0.0.0';
			}
			else
			{
				$current = '0.0.0';
			}

			// Get latest version for dependency
			if ( ! isset($dependency->identified_name) OR ! ($result = $this->hasUpdate($dependency->identified_name, $current, $joomlaVersion->RELEASE)) OR ($hasError = $result instanceof Exception))
			{
				// Store errors
				! isset($result) OR ! $result OR ! $hasError OR $errors[] = $result->getMessage();

				// Skip listing if dependency is installed
				(version_compare($current, '0.0.0', 'gt') OR  ! isset($dependency->identified_name)) ? $dependency->upToDate = true : $missingDependency++;

				if (isset($dependency->upToDate) AND $dependency->upToDate)
				{
					// Update dependency tracking
					$ext = strtolower(substr($this->input->getCmd('option'), 4));
					$dep = ! empty($extension->custom_data) ? (array) json_decode($extension->custom_data) : array();

					if ( ! count($dep) OR ! in_array($ext, $dep))
					{
						$dep[] = $ext;

						try
						{
							$db	= JFactory::getDbo();
							$q	= $db->getQuery(true);

							$q->update('#__extensions');
							$q->set("custom_data = '" . json_encode($dep) . "'");
							$q->where("element = '{$dependency->name}'");
							$q->where("type = '{$dependency->type}'", 'AND');
							$extension->type != 'plugin' OR $q->where("folder = '{$dependency->folder}'", 'AND');

							$db->setQuery($q);
							$db->execute();
						}
						catch (Exception $e)
						{
							$this->app->enqueueMessage($e->getMessage(), 'warning');
						}
					}
				}
			}
			else
			{
				$missingDependency++;

				// Is authentication required?
				$authentication = false;

				if (isset($result->authentication) AND $result->authentication)
				{
					$authentication = true;
				}
				elseif (isset($result->editions))
				{
					foreach ($result->editions AS $item)
					{
						if (strcasecmp($item->edition, $edition) == 0 AND $item->authentication)
						{
							$authentication = true;
						}
					}
				}

				// Prepare for authentication
				if ($authentication)
				{
					$authenticationRequired	= true;
					$dependency->edition	= str_replace(' ', '+', trim(isset($result->edition) ? $result->edition : $edition));
				}
			}
		}

		if ($missingDependency == 0)
		{
			$this->saveDependency($dependencies);

			return -1;
		}

		return isset($errors) ? $errors : $authenticationRequired;
	}

	/**
	 * Get product edition.
	 *
	 * @return  string
	 */
	public function getEdition()
	{
		$edition = 'JSN_' . strtoupper(substr($this->input->getCmd('option'), 4)) . '_EDITION';

		if (defined($edition))
		{
			eval('$edition = ' . $edition . ';');
		}
		else
		{
			$edition = null;
		}

		return $edition;
	}

	/**
	 * Method to get latest dependency version.
	 *
	 * @param   string  $identified_name        Dependency's identified name.
	 * @param   string  $current_version        Current dependency version.
	 * @param   string  $requiredJoomlaVersion  Joomla version required by extension, e.g. 2.5, 3.0, etc.
	 * @param   object  $version                Latest version object used for recursive calls.
	 *
	 * @return  mixed  Object containing update information if dependency is outdated, FALSE otherwise.
	 */
	protected function hasUpdate($identified_name, $current_version, $requiredJoomlaVersion = '3.0', $version = '')
	{
		static $versions, $result;

		// Only communicate with server if check update URLs is not load before
		if (empty($version))
		{
			if ( ! isset($versions))
			{
				try
				{
					$versions = $this->fetchHttp($this->checkLink);
					$versions = json_decode($versions);
				}
				catch (Exception $e)
				{
					return $e;
				}
			}

			$version	= $versions;
			$result		= false;
		}

		// Get installed Joomla version
		$joomlaVersion = new JVersion;

		// Get latest dependency version
		if ( ! $result)
		{
			foreach ($version->items AS $item)
			{
				if (isset($item->items))
				{
					$this->hasUpdate($identified_name, $current_version, $requiredJoomlaVersion, $item);
					continue;
				}

				if (isset($item->identified_name) AND $item->identified_name == $identified_name)
				{
					$result = $item;
					break;
				}
			}

			if (is_object($result))
			{
				// Does product support installed Joomla version?
				$tags = explode(';', $result->tags);

				if ( ! in_array($joomlaVersion->RELEASE, $tags))
				{
					$result = false;
				}

				// Does product upgradable?
				if ($result AND ! empty($requiredJoomlaVersion) AND ! $this->isJoomlaCompatible($requiredJoomlaVersion) AND ! version_compare($result->version, $current_version, '>='))
				{
					$result = false;
				}

				// Does product have newer version?
				if ($result AND (empty($requiredJoomlaVersion) OR $this->isJoomlaCompatible($requiredJoomlaVersion)) AND ! version_compare($result->version, $current_version, '>'))
				{
					$result = false;
				}
			}
		}

		return $result;
	}

	/**
	 * Save dependency declaration to a constant.
	 *
	 * @param   array  &$dependencies  An array of dependency package.
	 *
	 * @return  void
	 */
	public function saveDependency(&$dependencies)
	{
		// Get component name
		$component = substr($this->input->getCmd('option'), 4);

		if ( ! defined('JSN_' . strtoupper($component) . '_DEPENDENCY'))
		{
			// Unset some unnecessary properties
			foreach ($dependencies AS & $dependency)
			{
				unset($dependency->source);
				unset($dependency->upToDate);
			}
			$dependencies = json_encode($dependencies);

			// Store dependency declaration
			file_exists($defines = JPATH_COMPONENT_ADMINISTRATOR . '/defines.php')
			OR file_exists($defines = JPATH_COMPONENT_ADMINISTRATOR . '/' . $component . '.defines.php')
			OR $defines = JPATH_COMPONENT_ADMINISTRATOR . '/' . $component . '.php';

			if (is_writable($defines))
			{
				$buffer = preg_replace(
						'/(defined\s*\(\s*._JEXEC.\s*\)[^\n]+\n)/',
						'\1' . "\ndefine('JSN_" . strtoupper($component) . "_DEPENDENCY', '" . $dependencies . "');\n",
						file_get_contents($defines)
				);

				JFile::write($defines, $buffer);
			}
		}
	}

	/**
	 * Get remote content via http client.
	 *
	 * @param   string  $url  URL to fetch content.
	 *
	 * @return  string  Fetched content.
	 */
	protected function fetchHttp($url)
	{
		$result = '';

		// Initialize HTTP client
		class_exists('http_class') OR require_once JPATH_COMPONENT_ADMINISTRATOR . '/libraries/3rd-party/httpclient/http.php';

		$http = new http_class;
		$http->follow_redirect		= 1;
		$http->redirection_limit	= 5;
		$http->GetRequestArguments($url, $arguments);

		// Open connection
		if (($error = $http->Open($arguments)) == '')
		{
			if (($error = $http->SendRequest($arguments)) == '')
			{
				// Get response body
				while (true)
				{
					if (($error = $http->ReadReplyBody($body, 1000)) != '' OR strlen($body) == 0)
					{
						break;
					}
					$result .= $body;
				}
			}
			else
			{
				throw new Exception($error);
			}

			// Close connection
			$http->Close();
		}
		else
		{
			throw new Exception($error);
		}

		return $result;
	}

	/**
	 * Method for checking if extension is compatible with installed Joomla version.
	 *
	 * @param   string  $requiredJoomlaVersion  Joomla version required by extension, e.g. 2.5, 3.0, etc.
	 *
	 * @return  boolean
	 */
	public static function isJoomlaCompatible($requiredJoomlaVersion)
	{
		// Get installed Joomla version
		$joomlaVersion = new JVersion;

		// Check if installed Joomla version is compatible
		return (strpos($joomlaVersion->getShortVersion(), $requiredJoomlaVersion) !== false);
	}
}
