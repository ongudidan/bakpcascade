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
 * Helper class to generate admin UI for template
 *
 * @package     JSNTPLFramework
 * @subpackage  Template
 * @since       1.0.0
 */
class JSNTplTemplateAdmin
{
	/**
	 * Instance of template administrator object
	 *
	 * @var  JSNTplTemplateAdmin
	 */
	private static $_instance;

	/**
	 * Joomla version object
	 * @var JVersion
	 */
	protected $version;

	/**
	 * Joomla document instance
	 * @var JDocumentHTML
	 */
	protected $doc;

	/**
	 * Base URL of joomla instance
	 * @var string
	 */
	protected $baseUrl;

	/**
	 * Base URL of template administrator assets
	 * @var string
	 */
	protected $baseAssetUrl;

	/**
	 * Template form context
	 * @var JForm
	 */
	protected $context;

	/**
	 * Template form data
	 * @var JObject
	 */
	protected $data;

	/**
	 * Template config XML document
	 * @var SimpleXMLDocument
	 */
	protected $configXml;

	/**
	 * Template details XML document
	 * @var SimpleXMLDocument
	 */
	protected $templateXml;

	/**
	 * Template admin form
	 * @var JForm
	 */
	protected $adminForm;

	/**
	 * Original template admin form
	 * @var JForm
	 */
	protected $templateForm;

	/**
	 * Template edition manager
	 * @var JSNTplTemplateEdition
	 */
	protected $templateEdition;

	/**
	 * Retrieve initialized instance of template
	 * admin object
	 *
	 * @param   JForm    $context  Current context of template admin
	 *
	 * @return  JSNTplTemplateAdmin
	 */
	public static function getInstance (JForm $context)
	{
		if (self::$_instance == null || !(self::$_instance instanceOf JSNTplTemplateAdmin))
			self::$_instance = new JSNTplTemplateAdmin($context);

		return self::$_instance;
	}

	/**
	 * Register asset files for the template admin
	 *
	 * @return void
	 */
	public function registerAssets ()
	{
		// Specified asset files for joomla 2.5
		if (version_compare($this->version->getShortVersion(), '3.0', '<'))
		{
			$this->doc->addStyleSheet($this->baseAssetUrl . '/3rd-party/bootstrap/css/bootstrap.min.css');
			$this->doc->addScript($this->baseAssetUrl . '/3rd-party/jquery/jquery-1.8.2.js');
			$this->doc->addScript($this->baseAssetUrl . '/3rd-party/bootstrap/js/bootstrap.min.js');
		}

		$this->doc->addStyleSheet($this->baseAssetUrl . '/3rd-party/jquery-ui/css/ui-bootstrap/jquery-ui-1.9.0.custom.css');
		$this->doc->addStyleSheet($this->baseAssetUrl . '/3rd-party/jquery-dynatree/skin/ui.dynatree.css');
		$this->doc->addStyleSheet($this->baseAssetUrl . '/3rd-party/jquery-tipsy/tipsy.css');
		$this->doc->addStyleSheet($this->baseAssetUrl . '/3rd-party/font-awesome/css/font-awesome.css');
		$this->doc->addStyleSheet($this->baseAssetUrl . '/joomlashine/css/jsn-gui.css');
		$this->doc->addStyleSheet($this->baseAssetUrl . '/joomlashine/css/jsn-admin.css');

		// jQuery UI
		$this->doc->addScript($this->baseAssetUrl . '/3rd-party/jquery-ui/js/jquery-ui-1.9.1.custom.min.js');
		$this->doc->addScript($this->baseAssetUrl . '/3rd-party/jquery-dynatree/jquery.dynatree.min.js');
		$this->doc->addScript($this->baseAssetUrl . '/3rd-party/jquery-layout/jquery.layout.min.js');
		$this->doc->addScript($this->baseAssetUrl . '/3rd-party/jquery-ck/jquery.ck.js');
		$this->doc->addScript($this->baseAssetUrl . '/3rd-party/jquery-tipsy/jquery.tipsy.js');
		$this->doc->addScript($this->baseAssetUrl . '/joomlashine/js/media.js');
		$this->doc->addScript($this->baseAssetUrl . '/joomlashine/js/sample-data.js');
		$this->doc->addScript($this->baseAssetUrl . '/joomlashine/js/update.js');
		$this->doc->addScript($this->baseAssetUrl . '/joomlashine/js/upgrade.js');
		$this->doc->addScript($this->baseAssetUrl . '/joomlashine/js/quickstart.js');
		$this->doc->addScript($this->baseAssetUrl . '/joomlashine/js/core.js');

		$templateEdition = JSNTplHelper::getTemplateEdition($this->data->template);
		$templateName = JText::_($this->data->template);

		$this->doc->addScriptDeclaration("
			!function ($) {
				\"use strict\";

				$(function () {
					new $.JSNTPLFrameworkCore({
						template: '{$this->data->template}',
						templateName: '{$templateName}',
						edition: '{$templateEdition}',
						styleId : '{$this->data->id}'
					});
				});
			}(jQuery);
		");
	}

	/**
	 * Render HTML Markup for administrator UI
	 *
	 * @return  string
	 */
	public function render ()
	{
		$adminFormXml = $this->_generateFormXML();

		// Create form instance
		$this->adminForm = new JForm('template-setting');
		$this->adminForm->addFieldPath(JSN_PATH_TPLFRAMEWORK . '/libraries/joomlashine/form/fields');
		$this->adminForm->load($adminFormXml->asXML());

		$params = $this->helper->loadParams($this->data->params, $this->data->template);

		// Bind value of parameters to form
		foreach ($params AS $key => $value)
		{
			$this->adminForm->setValue($key, 'jsn', $value);
		}

		// Store current compression parameters
		$app = JFactory::getApplication();
		$app->setUserState('jsn.template.maxCompressionSize',	$params['maxCompressionSize']);
		$app->setUserState('jsn.template.cacheDirectory',		$params['cacheDirectory']);

		// Start rendering
		ob_start();
		include JSN_PATH_TPLFRAMEWORK_LIBRARIES . '/template/tmpl/default.php';

		JResponse::setBody(preg_replace('/<form([^>]*)\s*name="adminForm"([^>]*)>(.*?)<\/form>/ius', ob_get_clean(), JResponse::getBody()));
	}

	private function _addNodes ($nodes, $parentNode, $context)
	{
		foreach ($nodes as $node)
		{
			$nodeType = $node->getName();
			$nodeName = (string) $node['name'];
			$nodeText = trim((string) $node);

			if (isset($context['remove'][$nodeName])) {
				continue;
			}

			if ($nodeType == 'field' && isset($context['replace'][$nodeName])) {
				$newNode = $parentNode->addChild($nodeType, trim((string) $context['replace'][$nodeName]));
				foreach ($context['replace'][$nodeName]->attributes() as $key => $value) {
					$newNode->addAttribute($key, $value);
				}

				$this->_addNodes($context['replace'][$nodeName]->children(), $newNode, $context);
				continue;
			}

			$newNode = $parentNode->addChild($nodeType, $nodeText);
			foreach ($node->attributes() as $key => $value) {
				$newNode->addAttribute($key, $value);
			}

			if (isset($context['replace'][$nodeName])) {
				$this->_addNodes($context['replace'][$nodeName]->children(), $newNode, $context);
			}
			elseif (isset($context['prepend'][$nodeName])) {
				$this->_addNodes($context['prepend'][$nodeName]->children(), $newNode, $context);
				$this->_addNodes($node->children(), $newNode, $context);
			}
			else {
				$this->_addNodes($node->children(), $newNode, $context);

				if (isset($context['append'][$nodeName])) {
					$this->_addNodes($context['append'][$nodeName]->children(), $newNode, $context);
				}
			}
		}
	}

	/**
	 * This method use to generate XML for template form definition
	 *
	 * @return  object
	 */
	private function _generateFormXML ()
	{
		$adminXml	= simplexml_load_string('<?xml version="1.0" encoding="utf-8" ?><form><fields name="jsn"></fields></form>');
		$formXml	= simplexml_load_file(JSN_PATH_TPLFRAMEWORK . '/libraries/joomlashine/template/params.xml');
		$optionsXml	= $this->templateXml->options;
		$context = array();

		foreach ($optionsXml->xpath('//*[@method]') as $node) {
			$nodeType = (string) $node->getName();
			$method   = (string) $node['method'];

			if (!in_array($nodeType, array('fieldset', 'field'))) {
				continue;
			}

			if (!isset($context[$method])) {
				$context[$method] = array();
			}

			$context[$method][(string) $node['name']] = $node;
		}

		$this->_addNodes($formXml->fields->children(), $adminXml->fields, $context);

		// // Disable fieldset when edition is free
		if (strtolower($this->templateEdition->getEdition()) == 'free') {
			foreach ($adminXml->xpath('//fieldset[@pro="true"]') as $fieldset) {
				foreach ($fieldset->children() as $input) {
					if ($input->getName() == 'fieldset') {
						foreach ($input->children() as $_input)
							$_input->addAttribute('disabled', 'true');
						continue;
					}

					$input->addAttribute('disabled', 'true');
				}
			}
		}

		$replacement = array(
			'{templateUrl}' => JUri::root(true) . '/templates/' . $this->data->template
		);

		// Set default values
		foreach ($this->templateXml->xpath('//defaults/option') as $option) {
			$name = (string) $option['name'];
			$value = '';

			if (isset($option['value'])) {
				$value = (string) $option['value'];
			}
			elseif (count($option->children()) > 0) {
				$_value = array();

				foreach ($option->children() as $item) {
					$_value[] = (string) $item;
				}

				$value = implode("\r\n", $_value);
			}

			foreach ($adminXml->xpath('//field[@name="' . $name . '"]') as $field) {
				$field['defaultValue'] = str_replace(array_keys($replacement), array_values($replacement), $value);
			}
		}

		$logoField = current($adminXml->xpath('//field[@name="logoFile"]'));
		$logoField['defaultValue'] = 'templates/' . $this->data->template . '/images/logo.png';

		return $adminXml;
	}

	/**
	 * Constructor for template admin
	 *
	 * @param   JForm    $context  Current context of template admin
	 */
	private function __construct (JForm $context)
	{
		$request				= JFactory::getApplication()->input;
		$templateModel			= class_exists('JModelLegacy') ? JModelLegacy::getInstance('Style', 'TemplatesModel') : JModel::getInstance('Style', 'TemplatesModel');
		$this->baseUrl			= JUri::root(true);
		$this->baseAssetUrl		= $this->baseUrl . '/plugins/system/jsntplframework/assets';
		$this->context			= $context;
		$this->data				= $templateModel->getItem($request->getInt('id'));
		$this->version			= new JVersion();
		$this->doc				= JFactory::getDocument();
		$this->helper			= JSNTplTemplateHelper::getInstance($this->data->template);
		$this->templateXml		= JSNTplHelper::getManifest($this->data->template);

		// Retrieve template form instance
		$this->templateForm		= JForm::getInstance('com_templates.style', 'style', array('control' => 'jform', 'load_data' => true));
		$this->templateEdition	= JSNTplTemplateEdition::getInstance($this->data);

		// Load cache engine
		$this->cache			= JFactory::getCache('plg_system_jsntplframework');

		$language = JFactory::getLanguage();
		$language->load('tpl_' . $this->data->template, JPATH_ROOT);
	}

	/**
	 * Disable object cloneable for template admin
	 *
	 * @return void
	 */
	private function __clone ()
	{}
}
