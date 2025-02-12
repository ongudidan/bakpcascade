<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  JSNTPLFramework
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
 * Template upgrade
 *
 * @package     JSNTPLFramework
 * @subpackage  Template
 * @since       1.0.0
 */
class JSNTplWidgetUpgrade extends JSNTplWidgetBase
{
	/**
	 * Render intro view
	 *
	 * @return  void
	 */
	public function introAction ()
	{
		$this->render('intro', array('template' => $this->template));
	}

	/**
	 * Render login view
	 *
	 * @return  void
	 */
	public function loginAction ()
	{
		$this->render('login', array('template' => $this->template));
	}

	/**
	 * Authentication action before install sample data
	 *
	 * @return  void
	 */
	public function loadEditionsAction ()
	{
		// Process posted back data that sent from client
		if ($this->request->getMethod() == 'POST')
		{
			$username = $this->request->getString('username', '');
			$password = $this->request->getString('password', '');

			// Create new HTTP Request
			try
			{
				$editions = JSNTplApiLightcart::getOrderedEditions($this->template['id'], $username, $password);
			}
			catch (Exception $e)
			{
				throw $e;
			}

			// Remove current edition
			$currentEdition = $this->template['edition'];

			if ( ! preg_match('/^PRO /i', $currentEdition))
			{
				$currentEdition = strtoupper("PRO {$currentEdition}");
			}

			if ($index = array_search($currentEdition, $editions))
			{
				unset($editions[$index]);
				$editions = array_filter($editions);
			}

			$this->setResponse($editions);
		}
	}

	/**
	 * Render upgrade view
	 *
	 * @return  void
	 */
	public function upgradeAction ()
	{
		$edition = $this->request->getString('edition');

		if (!in_array($edition, array('PRO UNLIMITED', 'PRO STANDARD'))) {
			throw new Exception('Invalid template edition: ' . $edition);
		}

		$this->render('upgrade', array(
			'template' => $this->template,
			'edition' => $edition
		));
	}

	/**
	 * Replace edition in templateDetails.xml
	 *
	 * @return  void
	 */
	public function replaceAction ()
	{
		// Load template manifest to replace content
		$manifestContent = file_get_contents(JPATH_ROOT . "/templates/{$this->template['name']}/templateDetails.xml");
		$manifestContent = str_replace('<edition>STANDARD</edition>', '<edition>UNLIMITED</edition>', $manifestContent);

		// Write manifest content
		file_put_contents(JPATH_ROOT . "/templates/{$this->template['name']}/templateDetails.xml", $manifestContent);
	}

	/**
	 * Download template package from lightcart
	 *
	 * @return  void
	 */
	public function downloadPackageAction ()
	{
		if (!JSNTplHelper::isDisabledFunction('set_time_limit')) {
			set_time_limit(0);
		}

		try
		{
			$packageFile = JSNTplApiLightcart::downloadPackage(
				$this->template['id'],
				$this->request->getString('edition'),
				$this->request->getString('username'),
				$this->request->getString('password')
			);
		}
		catch (Exception $e)
		{
			throw $e;
		}

		$this->session->set($this->template['id'] . '.upgradePackage', $packageFile);
	}

	/**
	 * Install downloaded package to joomla system
	 *
	 * @return  void
	 */
	public function installAction ()
	{
		if (!JSNTplHelper::isDisabledFunction('set_time_limit')) {
			set_time_limit(0);
		}

		$config	= JFactory::getConfig();
		$config->set('debug', 0);

		$packageFile = $this->session->get($this->template['id'] . '.upgradePackage');

		if (!is_file($packageFile)) {
			throw new Exception("Package file not found: {$packageFile}");
		}

		// Load extension installation library
		jimport('joomla.installer.helper');

		$language = JFactory::getLanguage();
		$language->load('lib_joomla', JPATH_SITE);

		$unpackedInfo	= JInstallerHelper::unpack($packageFile);
		$installer		= JInstaller::getInstance();
		$installer->setUpgrade(true);

		if (empty($unpackedInfo) || !isset($unpackedInfo['dir'])) {
			throw new Exception(JText::_('JSN_TPLFW_ERROR_CANNOT_EXTRACT_TEMPLATE_PACKAGE_FILE'));
		}

		$installResult = $installer->install($unpackedInfo['dir']);
		if ($installResult === false) {
			foreach (JError::getErrors() as $error) {
				throw $error;
			}
		}

		// Clean up temporary data
		JInstallerHelper::cleanupInstall($packageFile, $unpackedInfo['dir']);

		// Retrieve style id of installed package
		list($prefix, $name) = explode('_', $this->template['id']);

		$query = $this->dbo->getQuery(true);
		$query->select('id')
			  ->from('#__template_styles')
			  ->where("template='jsn_{$name}_pro'");

		$this->dbo->setQuery($query);
		$styleId = $this->dbo->loadResult();

		$this->dbo->setQuery("UPDATE #__template_styles SET home=0 WHERE client_id=0");
		$this->dbo->query();

		$this->dbo->setQuery("UPDATE #__template_styles SET home=1 WHERE id={$styleId}");
		$this->dbo->query();

		$this->setResponse(array(
			'styleId' => $styleId
		));
	}

	/**
	 * Migrate all settings from free edition to upgraded edition
	 *
	 * @return  void
	 */
	public function migrateAction ()
	{
		$from	= $this->request->getInt('from');
		$to		= $this->request->getInt('to');

		if ($from <= 0 || $to <= 0 || $from == $to) {
			throw new Exception("Invalid style ID");
		}

		$query = $this->dbo->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__template_styles')
			->where('id IN(' . $from . ', ' . $to . ')');

		$this->dbo->setQuery($query);
		$count = intval($this->dbo->loadResult());

		if ($count != 2) {
			throw new Exception("Invalid style ID");
		}

		$query = $this->dbo->getQuery(true);
		$query->select('params')
			->from('#__template_styles')
			->where('id=' . $from);

		$this->dbo->setQuery($query);
		$fromParams = $this->dbo->loadResult();

		$this->dbo->setQuery("UPDATE #__template_styles SET params=" . $this->dbo->quote($fromParams) . " WHERE id={$to}");
		$this->dbo->{$this->queryMethod}();

		$this->setResponse("UPDATE #__template_styles SET params=" . $this->dbo->quote($fromParams) . " WHERE id={$to}");
	}
}
