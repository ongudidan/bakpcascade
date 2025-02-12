<?php

/**
 * @version     $Id:$
 * @package     JSNUniform
 * @subpackage  Submission
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.modal');
JHtml::_('behavior.tooltip');
$dataFields = $this->_dataFields;
$submission = $this->_dataSubmission;
$user = JFactory::getUser();
$listUser = JSNUniformHelper::getUserNameById();
$groupEditSubmision = isset($this->_infoForm->form_access)?$this->_infoForm->form_access:"";
$checkEditSubmission = JSNUniformHelper::checkEditSubmission($user->id, $groupEditSubmision);
$disabledNext = "disabled=\"disabled\"";
$disabledPrevious = "disabled=\"disabled\"";
$clickNext = 'href="javascript:void(0)"';
$clickPrevious = 'href="javascript:void(0)"';
$checkPage = false;
if (!empty($this->nextAndPreviousForm['next']))
{
	$disabledNext = "";
	$clickNext = 'href="' . JRoute::_('index.php?option=com_uniform&view=submission&data_id=' . $this->nextAndPreviousForm['next']) . '"';
	$checkPage = true;
}
if (!empty($this->nextAndPreviousForm['previous']))
{
	$disabledPrevious = "";
	$clickPrevious = 'href="' . JRoute::_('index.php?option=com_uniform&view=submission&data_id=' . $this->nextAndPreviousForm['previous']) . '"';
	$checkPage = true;
}
?>
<div class="jsn-master">
<div id="submission-settings" class="jsn-bootstrap">
<div class="jsn-pane jsn-bgpattern pattern-sidebar">
<form action="<?php echo JRoute::_('index.php?option=com_uniform&view=submission&data_id=' . $submission->data_id); ?>" method="post" name="adminForm" id="adminForm">
<?php
if ($checkPage || $checkEditSubmission)
{
	?>
<div class="jsn-section-header">
	<?php
	if ($checkPage)
	{
		?>
	    <div class="pull-left btn-toolbar">
		<div class="btn-group">
		    <a original-title="<?php echo JText::_('JSN_UNIFORM_PREVIOUS_SUBMISSION');?>" title="<?php echo JText::_('JSN_UNIFORM_PREVIOUS_SUBMISSION');?>" <?php echo $disabledPrevious . " " . $clickPrevious;?>  class="btn btn-icon">
			<i class="icon-arrow-left"></i></a>
		    <a original-title="<?php echo JText::_('JSN_UNIFORM_NEXT_SUBMISSION');?>" title="<?php echo JText::_('JSN_UNIFORM_NEXT_SUBMISSION');?>" <?php echo $disabledNext . " " . $clickNext;?> class="btn btn-icon">
			<i class="icon-arrow-right"></i></a>
		</div>
	    </div>
		<?php
	}
	if ($checkEditSubmission)
	{
		?>
	    <div class="pull-right btn-toolbar">
		<div class="btn-group">
		    <button onclick="Joomla.submitbutton('submission.apply');" class="btn btn-primary"><?php echo JText::_('JSN_UNIFORM_SAVE'); ?></button>
		    <button data-toggle="dropdown" class="btn btn-primary dropdown-toggle">
			&nbsp;<span class="caret"></span>&nbsp;
		    </button>
		    <ul class="dropdown-menu">
			<li>
			    <a onclick="Joomla.submitbutton('submission.save');" href="javascript:void(0);"><?php echo JText::_('JSN_UNIFORM_SAVE_CLOSE'); ?></a>
			</li>
			    <?php
			    if (!empty($this->nextAndPreviousForm['next']))
			    {
				    ?>
				<li>
				    <a onclick="Joomla.submitbutton('submission.saveNext')" href="javascript:void(0);"><?php echo JText::_('JSN_UNIFORM_SAVE_NEXT'); ?></a>
				    <input type="hidden" name="nextId" value="<?php echo $this->nextAndPreviousForm['next'];?>">
				</li>
				    <?php
			    }
			    ?>
		    </ul>
		</div>
		<button onclick="Joomla.submitbutton('submission.cancel');" class="btn">
		    <i class="icon-remove"></i><?php echo JText::_('JSN_UNIFORM_CLOSE'); ?></button>
	    </div>
		<?php
	}
	?>
    <div class="clearbreak"></div>
</div>
	<?php
}
?>
<div class="row-fluid jsn-tabs">
    <ul>
	<li><a href="#submission-details"><?php echo JText::_('JSN_UNIFORM_SUBMISSION_DETAIL'); ?></a></li>
	<li><a href="#submission-data"><?php echo JText::_('JSN_UNIFORM_SUBMISSION_DATA'); ?></a></li>
    </ul>
    <div id="submission-details" class="submission-details">

	<table class="table table-bordered">
	    <tr>
		<th>
			<?php echo JText::_('JSN_UNIFORM_FORMS'); ?>
		</th>
		<td><?php echo isset($dataFields[0]->form_title)?$dataFields[0]->form_title:""; ?></td>
	    </tr>
	    <tr>
		<th>
			<?php echo JText::_('JSN_UNIFORM_SUBMISSION_DATA_CREATED_AT'); ?>
		</th>
		<td>
			<?php
			$dateTime = new DateTime($this->_item->data_created_at);
			echo $dateTime->format("j F Y h:i:s");
			?>
		</td>
	    </tr>
	    <tr>
		<th>
			<?php echo JText::_('JSN_UNIFORM_SUBMISSION_DATA_CREATED_BY'); ?>
		</th>
		<td><?php echo isset($listUser[$this->_item->data_created_by])?$listUser[$this->_item->data_created_by]:"Guest"; ?></td>
	    </tr>
	    <tr>
		<th>
			<?php echo JText::_('JSN_UNIFORM_SUBMISSION_DATA_IP'); ?>
		</th>
		<td><?php echo $this->_item->data_ip; ?></td>
	    </tr>
	    <tr>
		<th>
			<?php echo JText::_('JSN_UNIFORM_SUBMISSION_DATA_COUNTRY'); ?>
		</th>
		<td> <?php echo $this->_item->data_country; ?></td>
	    </tr>
	    <tr>
		<th>
			<?php echo JText::_('JSN_UNIFORM_SUBMISSION_DATA_BROWSER'); ?>
		</th>
		<td><?php echo $this->_item->data_browser; ?></td>
	    </tr>
	    <tr>
		<th>
			<?php echo JText::_('JSN_UNIFORM_SUBMISSION_DATA_OS'); ?>
		</th>
		<td> <?php echo $this->_item->data_os; ?></td>
	    </tr>
	</table>
    </div>

    <div id="submission-data" class="submission-data">

	<div class="jsn-form-bar">
		<?php
		$formType = isset($this->_infoForm->form_type)?$this->_infoForm->form_type:1;
		if ($formType == 2)
		{
			?>
		    <div class="control-group ">
			<label class="control-label"><?php echo JText::_('JSN_UNIFORM_DATA_PRESENTATION'); ?>
			    :</label>

			<div class="controls">
			    <select class="jsn-input-fluid" data-value="<?php echo $formType; ?>" id="jform_form_type">
				<option value="1"><?php echo JText::_('JSN_UNIFORM_TYPE_SINGLE_PAGE'); ?></option>
				<option value="2"><?php echo JText::_('JSN_UNIFORM_TYPE_MULTIPLE_PAGES'); ?></option>
			    </select>
			</div>
		    </div>
			<?php
		}

		if ($checkEditSubmission)
		{
			?>
		    <div class="control-group pull-right">
			<div class="controls">
			    <button class="btn" id="jsn-submission-edit" onclick="return false;">
				<i class="icon-pencil"></i><?php echo JText::_('JTOOLBAR_EDIT'); ?></button>
			    <button class="btn btn-primary hide" id="jsn-submission-save" onclick="return false;">
				<i class="icon-pencil"></i><?php echo JText::_('JSN_UNIFORM_DONE'); ?></button>
			</div>
		    </div>
			<?php
		}
		?>
	</div>
	<div class="submission-content">
	    <div class="jsn-page-actions btn-group" style="display: block;">
		<button class="btn btn-icon prev-page hide" onclick="return false;" disabled="disabled">
		    <i class="icon-arrow-left"></i></button>
		<button class="btn btn-icon next-page hide" onclick="return false;" disabled="disabled">
		    <i class="icon-arrow-right"></i></button>
	    </div>
		<?php
		if ($this->_formPages)
		{
			foreach ($this->_formPages as $formPages)
			{
				$pageContent = json_decode($formPages->page_content);
				$submissionDetail = "";
				$submissionEdit = "";
				foreach ($pageContent as $fields)
				{
					$key = "sb_" . $fields->id;
					if (isset($fields->type) && $fields->type != 'static-content')
					{
						$submissionDetail .= '<dt>' . $fields->label . ':</dt><dd id="' . $key . '">';
						$submissionEdit .= '<div class="control-group ">
												<label class="control-label">' . $fields->label . ':</label>
												<div class="controls">';
						$contentField = "";
						$contentFieldEdit = "";
						$contentFieldDetail = "";

						if (isset($submission->$key))
						{
							$contentField = JSNUniformHelper::getDataField($fields->type, $submission, $key, $this->_item->form_id, false);
							$contentFieldEdit = $contentField;

							if ($fields->type == "email")
							{

								$contentFieldDetail = !empty($contentField)?'<a href="mailto:' . $contentField . '">' . $contentField . '</a>':"N/A";
							}
							else
							{
								$contentFieldDetail = $contentField;
							}
						}
						$submissionDetail .= $contentFieldDetail?str_replace("\n", "<br/>", trim($contentFieldDetail)):"N/A";
						if (isset($fields->type) && ($fields->type == "checkboxes" || $fields->type == "list" || $fields->type == "paragraph-text"))
						{
							if ($fields->type == "checkboxes" || $fields->type == "list")
							{
								$contentFieldEdit = str_replace("<br/>", "\n", $contentFieldEdit);
								$contentFieldEdit = str_replace("\n\n", "\n", $contentFieldEdit);
							}
							$submissionEdit .= "<textarea name=\"submission[{$key}]\" class=\"jsn-input-xxlarge-fluid\" dataValue='" . $fields->id . "' typeValue='" . $fields->type . "' rows=\"5\" >{$contentFieldEdit}</textarea>";
						}
						else if (isset($fields->type) && $fields->type == "file-upload")
						{
							$submissionEdit .= $contentFieldEdit;
						}
						else
						{
							$submissionEdit .= "<input type=\"text\" name=\"submission[{$key}]\" dataValue='" . $fields->id . "' typeValue='" . $fields->type . "' class=\"jsn-input-xxlarge-fluid\" value='{$contentFieldEdit}' />";
						}
						$submissionEdit .= '</div></div>';
						$submissionDetail .= '</dd>';
					}
				}

				?>
			    <div class="submission-page" data-title="<?php echo $formPages->page_title; ?>" data-value="<?php echo $formPages->page_id; ?>">
				<div class="submission-page-header">
				    <h3><?php echo $formPages->page_title; ?></h3>
				</div>

				<dl class="submission-page-content" id="dl_<?php echo $formPages->page_id; ?>">
					<?php echo $submissionDetail; ?>
				</dl>
				<div class="submission-page-content hide" id="div_<?php echo $formPages->page_id; ?>">
					<?php echo $submissionEdit; ?>
				</div>
			    </div>
				<?php
			}
		}
		?>
	</div>
    </div>
</div>
<input type="hidden" name="filter_form_id" id="filter_form_id" value="<?php echo $this->_item->form_id; ?>" />
<input type="hidden" name="cid" id="cid" value="<?php echo $submission->data_id; ?>" />
<input type="hidden" name="action" id="action" value="" />
<input type="hidden" name="option" value="com_uniform" />
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
<?php
$edition = defined('JSN_UNIFORM_EDITION')?JSN_UNIFORM_EDITION:"free";
if (strtolower($edition) == "free")
{
	echo "<div class=\"jsn-text-center\"><a href=\"http://www.joomlashine.com/joomla-extensions/jsn-uniform.html\" target=\"_blank\">" . JText::_('JSN_UNIFORM_POWERED_BY') . "</a> by <a href=\"http://www.joomlashine.com\" target=\"_blank\">JoomlaShine</a></div>";
}
?>
</form>
</div>
</div>
</div>
