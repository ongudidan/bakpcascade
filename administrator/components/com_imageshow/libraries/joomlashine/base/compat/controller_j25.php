<?php
/**
 * @version    $Id: controller_j25.php 17915 2012-11-02 09:33:11Z cuongnm $
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

// Import Joomla library
jimport('joomla.application.component.controller');

/**
 * Base controller class for use across JSN libraries and extensions.
 *
 * Below is a sample use of <b>JSNBaseController</b> class:
 *
 * <code>class JSNConfigController extends JSNBaseController
 * {
 *     function save()
 *     {
 *         // Get input object
 *         $input = JFactory::getApplication()->input;
 *
 *         // Validate request
 *         $this->initializeRequest($input);
 *
 *         // Initialize variables
 *         $this->model = $this->getModel(
 *             $input->getCmd('controller') ? $input->getCmd('controller') : $input->getCmd('view')
 *         );
 *         $config = $this->model->getForm();
 *         $data   = $input->getVar('jsnconfig', array(), 'post', 'array');
 *
 *         // Attempt to save the configuration
 *         $return = true;
 *
 *         try
 *         {
 *             $this->model->save($data);
 *         }
 *         catch (Exception $e)
 *         {
 *             $return = $e;
 *         }
 *
 *         // Complete request
 *         $this->finalizeRequest($return, $input);
 *     }
 * }</code>
 *
 * @method	initializeRequest(&amp;$input, $checkToken)	Validate form token and user permission.
 * @method	finalizeRequest($return, &amp;$input)		Redirect based on the value returned by apprpriate model method.
 *
 * @package  JSN_Sample
 * @since    1.0.0
 */
class JSNBaseController extends JController
{
	/**
	 * Method for hiding a message.
	 *
	 * @return	void
	 */
	public function hideMsg()
	{
		jexit(JSNUtilsMessage::hideMessage(JFactory::getApplication()->input->getInt('msgId')));
	}

	/**
	 * Validate task request.
	 *
	 * @param   object   &$input      JInput object.
	 * @param   booealn  $checkToken  Check token by default, set to false to disable token checking.
	 *
	 * @return  void
	 */
	protected function initializeRequest(&$input, $checkToken = true)
	{
		// Validate token
		if ($checkToken AND ! JSession::checkToken())
		{
			jexit(JText::_('JINVALID_TOKEN'));
		}

		// Validate user permission
		if ( ! JFactory::getUser()->authorise('core.admin', $input->getCmd('option')))
		{
			if ($input->getInt('ajax') == 1)
			{
				jexit(JText::_('JERROR_ALERTNOAUTHOR'));
			}
			else
			{
				JFactory::getApplication()->redirect(JRoute::_('index.php'), JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			}
		}
	}

	/**
	 * Finalize task request.
	 *
	 * @param   mixed   $return  Model execution results.
	 * @param   object  &$input  JInput object.
	 *
	 * @return  void
	 */
	protected function finalizeRequest($return, &$input)
	{
		// Check the return value
		if ($return instanceof Exception)
		{
			if ($input->getInt('ajax') == 1)
			{
				jexit(JText::sprintf('JERROR_SAVE_FAILED', $return->getMessage()));
			}
			else
			{
				// Save failed, go back to the screen and display a notice.
				JFactory::getApplication()->redirect(
					JRoute::_('index.php?option=' . $input->getCmd('option') . '&view=' . $input->getCmd('view')),
					JText::sprintf('JERROR_SAVE_FAILED', $return->getMessage()),
					'error'
				);
			}
		}

		// Save successed, complete the task
		if ($input->getInt('ajax') == 1)
		{
			jexit(JText::_('JSN_EXTFW_CONFIG_SAVE_SUCCESS'));
		}
		else
		{
			JFactory::getApplication()->redirect(
				JRoute::_('index.php?option=' . $input->getCmd('option') . '&view=' . $input->getCmd('view')),
				JText::_('JSN_EXTFW_CONFIG_SAVE_SUCCESS')
			);
		}
	}
}
