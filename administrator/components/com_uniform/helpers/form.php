<?php

/**
 * @version     $Id: form.php 19013 2012-11-28 04:48:47Z thailv $
 * @package     JSNUniform
 * @subpackage  Helpers
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 *
 */
defined('_JEXEC') or die('Restricted access');

/**
 *  JSNUniform generate form helper
 *
 * @package     Joomla.Administrator
 * @subpackage  com_uniform
 * @since       1.6
 */
class JSNFormGenerateHelper
{

	/**
	 * Generate html code for a form which includes all the required fields
	 *
	 * @param   object  $dataGenrate     Data genrate
	 *
	 * @param   string  $layout          The layout genrate
	 *
	 * @param   object  $dataSumbission  Data submission
	 *
	 * @return void
	 */
	public static function generate($dataGenrate = null, $layout = null, $dataSumbission = null)
	{
		$formElement = array();
		$manifestFile = JPath::clean(JSN_UNIFORM_PAGEDESIGN_LAYOUTS_PATH . $layout . "/uniform.xml");
		$layoutXml = simplexml_load_file($manifestFile);
		$rows = $layoutXml->xpath('/layout/structure/row');
		$hasDefault = false;

		foreach ($dataGenrate as $data)
		{
			$fileType = preg_replace('/[^a-z]/i', "", $data->field_type);
			$method = "field{$fileType}";
			if (method_exists('JSNFormGenerateHelper', $method))
			{
				$formElement[$data->field_position][] = self::$method($data, $dataSumbission);
			}
		}

		foreach ($rows as $row)
		{
			$columns = $row->children();
			$columnSpans = self::getColumnSizes((string) $row['columnSize'], count($columns));
			$columnOutput = '';
			$columnIndex = 0;

			foreach ($columns as $column)
			{
				$columnName = (string) $column['name'];
				$columnStyle = 'form-region';
				$columnDefault = isset($column['default']) && $column['default'] == 'true' && $hasDefault == false;

				if (isset($column['style']))
				{
					$columnStyle .= "form-{$column['style']}";
				}
				if ($columnDefault)
				{
					$hasDefault = true;
					$columnStyle .= ' form-default';
				}
				$dataColumn = isset($formElement[$columnName])?$formElement[$columnName]:array();
				$columnOutput .= "<div class=\"jsn-container-{$columnName}\">";
				$columnOutput .= implode("\n", $dataColumn);
				$columnOutput .= "</div>";
				$columnIndex++;
			}
			return $columnOutput;
		}
	}

	/**
	 * Return span number based on bootstrap grid layout
	 *
	 * @param   string  $styles       Style Column
	 *
	 * @param   int     $columnCount  Count column
	 *
	 * @return array
	 */
	public static function getColumnSizes($styles, $columnCount)
	{
		$spans = explode('-', $styles);
		$spanCount = count($spans);

		if ($spanCount < $columnCount)
		{
			$spans = array_merge($spans, array_fill(0, $columnCount - $spanCount, 1));
		}
		elseif ($spanCount > $columnCount)
		{
			$spans = array_slice($spans, 0, $columnCount);
		}

		$spanSum = array_sum($spans);
		$ratio = 12 / $spanSum;

		foreach ($spans as $index => $span)
		{
			$spans[$index] = ceil($span * $ratio);
		}

		$spans[] = 12 - array_sum($spans);
		return $spans;
	}

	/**
	 * Generate html code for "Website" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldWebsite($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$requiredWebsite = !empty($settings->options->required)?'website-required':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$sizeInput = !empty($settings->options->size)?$settings->options->size:'';
		$defaultValue = !empty($dataSumbission[$data->field_id])?$dataSumbission[$data->field_id]:'';
		$placeholder = !empty($settings->options->value)?JText::_($settings->options->value):"";
		$html = "<div class=\"control-group {$identify} {$hideField}\"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls\"><input class=\"website {$requiredWebsite} {$sizeInput}\" id=\"{$data->field_id}\" name=\"{$data->field_id}\" type=\"text\" value=\"{$defaultValue}\" placeholder=\"" . htmlentities($placeholder, ENT_QUOTES, "UTF-8") . "\" /></div></div>";
		return $html;
	}

	/**
	 * Generate html code for "SingleLineText" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldSingleLineText($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$limitValue = "";
		$styleClassLimit = "";
		$identify = !empty($settings->identify)?$settings->identify:"";
		if (isset($settings->options->limitation) && $settings->options->limitation == 1)
		{
			$josnLimit = json_encode(array('limitMin' => $settings->options->limitMin, 'limitMax' => $settings->options->limitMax, 'limitType' => $settings->options->limitType));
			if ($settings->options->limitMax != 0 && $settings->options->limitType == 'Characters')
			{
				$limitValue = "data-limit='{$josnLimit}' maxlength=\"{$settings->options->limitMax}\"";
			}
			else
			{
				$limitValue = "data-limit='{$josnLimit}'";
			}
			$styleClassLimit = "limit-required";
		}
		$defaultValue = !empty($dataSumbission[$data->field_id])?$dataSumbission[$data->field_id]:'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$requiredBlank = !empty($settings->options->required)?'blank-required':'';
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$sizeInput = !empty($settings->options->size)?$settings->options->size:'';
		$placeholder = !empty($settings->options->value)?JText::_($settings->options->value):"";
		$html = "<div class=\"control-group {$identify} {$hideField}\"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls {$requiredBlank}\"><input {$limitValue} class=\"{$styleClassLimit} {$sizeInput}\" id=\"{$data->field_id}\" name=\"{$data->field_id}\" type=\"text\" value=\"" . htmlentities($defaultValue, ENT_QUOTES, "UTF-8") . "\" placeholder=\"" . htmlentities($placeholder, ENT_QUOTES, "UTF-8") . "\" /></div></div>";
		return $html;
	}

	/**
	 * Generate html code for "Phone" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldDate($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$requiredBlank = !empty($settings->options->required)?'group-blank-required':'';
		$sizeInput = 'input-small';
		$valueDate = '';
		$valueDateRange = '';
		if (isset($dataSumbission['date'][$data->field_id]))
		{
			$valueDate = isset($dataSumbission['date'][$data->field_id]['date'])?$dataSumbission['date'][$data->field_id]['date']:"";
			$valueDateRange = isset($dataSumbission['date'][$data->field_id]['daterange'])?$dataSumbission['date'][$data->field_id]['daterange']:"";
		}
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$dateSettings = json_encode($settings->options);
		$placeholder = !empty($settings->options->dateValue)?JText::_($settings->options->dateValue):"";
		$placeholderDateRange = !empty($settings->options->dateValueRange)?JText::_($settings->options->dateValueRange):"";
		if (isset($settings->options->timeFormat) && $settings->options->timeFormat == "1" && isset($settings->options->dateFormat) && $settings->options->dateFormat == "1")
		{
			$sizeInput = 'input-medium';
		}
		$html = "<div class=\"control-group {$identify} {$hideField}\">
					<label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label>
						<div class=\"controls {$requiredBlank}\">
							<div class=\"input-append jsn-inline\"><input date-settings=\"" . htmlentities($dateSettings, ENT_QUOTES, "UTF-8") . "\" placeholder=\"" . htmlentities($placeholder, ENT_QUOTES, "UTF-8") . "\" value=\"" . $valueDate . "\" class=\"jsn-daterangepicker {$sizeInput}\" id=\"{$data->field_id}\" name=\"date[{$data->field_id}][date]\" type=\"text\" readonly /></div>
								";
		if ($settings->options->enableRageSelection == "1" || $settings->options->enableRageSelection == 1)
		{
			$html .= "<div class=\"input-append jsn-inline\"><input date-settings=\"" . htmlentities($dateSettings, ENT_QUOTES, "UTF-8") . "\" placeholder=\"" . htmlentities($placeholderDateRange, ENT_QUOTES, "UTF-8") . "\" value=\"" . htmlentities($valueDateRange, ENT_QUOTES, "UTF-8") . "\" class=\"jsn-daterangepicker {$sizeInput}\" id=\"range_{$data->field_id}\" name=\"date[{$data->field_id}][daterange]\" type=\"text\" readonly /></div>";
		}
		$html .= "</div></div>";
		return $html;
	}

	/**
	 * Generate html code for "Currency" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldCurrency($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$requiredBlank = !empty($settings->options->required)?'group-blank-required':'';
		//$sizeInput = !empty($settings -> options -> size) ? $settings -> options -> size : '';
		$defaultValue = "";
		$centsValue = "";
		if (isset($dataSumbission['currency'][$data->field_id]))
		{
			$defaultValue = isset($dataSumbission['currency'][$data->field_id]['value'])?$dataSumbission['currency'][$data->field_id]['value']:"";
			$centsValue = isset($dataSumbission['currency'][$data->field_id]['cents'])?$dataSumbission['currency'][$data->field_id]['cents']:"";
		}
		$options['Haht'] = array('prefix' => '฿', 'cents' => 'Satang');
		$options['Dollars'] = array('prefix' => '$', 'cents' => 'Cents');
		$options['Euros'] = array('prefix' => '€', 'cents' => 'Cents');
		$options['Forint'] = array('prefix' => 'Ft', 'cents' => 'Filler');
		$options['Francs'] = array('prefix' => 'CHF', 'cents' => 'Rappen');
		$options['Koruna'] = array('prefix' => 'Kč', 'cents' => 'Haléřů');
		$options['Krona'] = array('prefix' => 'kr', 'cents' => 'Ore');
		$options['Pesos'] = array('prefix' => '$', 'cents' => 'Cents');
		$options['Pounds'] = array('prefix' => '£', 'cents' => 'Pence');
		$options['Ringgit'] = array('prefix' => 'RM', 'cents' => 'Sen');
		$options['Shekel'] = array('prefix' => '₪', 'cents' => 'Agora');
		$options['Yen'] = array('prefix' => '¥', 'cents' => '');
		$options['Zloty'] = array('prefix' => 'zł', 'cents' => 'Grosz');

		//	$defaultValue = !empty($dataSumbission[$data -> field_id]) ? $dataSumbission[$data -> field_id] : '';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$placeholder = !empty($settings->options->value)?JText::_($settings->options->value):"";
		$placeholderCents = !empty($settings->options->cents)?JText::_($settings->options->cents):"";
		$inputContent = "";
		if (isset($settings->options->format))
		{
			$showHelpBlock = "";
			if (!empty($settings->options->showCurrencyTitle) && $settings->options->showCurrencyTitle == "Yes")
			{
				$showHelpBlock = "<span class=\"jsn-help-block-inline\">" . $settings->options->format . "</span>";
			}

			$inputContent = "<div class=\"input-prepend jsn-inline currency-value\"><div class=\"controls-inner\"><span class=\"add-on\">" . $options[$settings->options->format]['prefix'] . "</span><input name=\"currency[{$data->field_id}][value]\" type=\"text\" placeholder=\"" . htmlentities($placeholder, ENT_QUOTES, "UTF-8") . "\" class=\"input-medium currency\" value=\"{$defaultValue}\"></div>{$showHelpBlock}</div>";
			if ($settings->options->format != "Yen")
			{
				$showHelpBlockSents = "";
				if (!empty($settings->options->showCurrencyTitle) && $settings->options->showCurrencyTitle == "Yes")
				{
					$showHelpBlockSents = "<span class=\"jsn-help-block-inline\">" . $options[$settings->options->format]['cents'] . "</span>";
				}
				$inputContent .= "<div class=\"jsn-inline currency-cents\"><div class=\"controls-inner\"><input name=\"currency[{$data->field_id}][cents]\" type=\"text\" placeholder=\"{$placeholderCents}\" class=\"input-mini currency\" value=\"{$centsValue}\"></div>{$showHelpBlockSents}</div>";
			}
		}
		$html = "<div class=\"control-group {$identify} {$hideField}\"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls {$requiredBlank} currency-control clearfix\"><div class=\"clearfix\">{$inputContent}</div></div></div>";
		return $html;
	}

	/**
	 * Generate html code for "Phone" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldPhone($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$requiredBlank = !empty($settings->options->required)?'group-blank-required':'';
		//$sizeInput = !empty($settings -> options -> size) ? $settings -> options -> size : '';
		$defaultValue = "";
		$oneValue = "";
		$twoValue = "";
		$threeValue = "";
		if (isset($dataSumbission['phone'][$data->field_id]))
		{
			$defaultValue = isset($dataSumbission['phone'][$data->field_id]['default'])?$dataSumbission['phone'][$data->field_id]['default']:"";
			$oneValue = isset($dataSumbission['phone'][$data->field_id]['one'])?$dataSumbission['phone'][$data->field_id]['one']:"";
			$twoValue = isset($dataSumbission['phone'][$data->field_id]['two'])?$dataSumbission['phone'][$data->field_id]['two']:"";
			$threeValue = isset($dataSumbission['phone'][$data->field_id]['three'])?$dataSumbission['phone'][$data->field_id]['three']:"";
		}

		//	$defaultValue = !empty($dataSumbission[$data -> field_id]) ? $dataSumbission[$data -> field_id] : '';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$placeholder = !empty($settings->options->value)?JText::_($settings->options->value):"";
		$placeholderOneField = !empty($settings->options->oneField)?JText::_($settings->options->oneField):"";
		$placeholderTwoField = !empty($settings->options->twoField)?JText::_($settings->options->twoField):"";
		$placeholderThreeField = !empty($settings->options->threeField)?JText::_($settings->options->threeField):"";
		$inputContent = "<input class=\"phone jsn-input-medium-fluid\" id=\"{$data->field_id}\" name=\"phone[{$data->field_id}][default]\" type=\"text\" value=\"{$defaultValue}\" placeholder=\"" . htmlentities($placeholder, ENT_QUOTES, "UTF-8") . "\" />";
		if (isset($settings->options->format) && $settings->options->format == "3-field")
		{
			$inputContent = "<div class=\"jsn-inline\"><input id=\"one_{$data->field_id}\" name=\"phone[{$data->field_id}][one]\" value='" . htmlentities($oneValue, ENT_QUOTES, "UTF-8") . "' type=\"text\" placeholder=\"" . htmlentities($placeholderOneField, ENT_QUOTES, "UTF-8") . "\" class=\"phone jsn-input-mini-fluid\"></div>
							<span class=\"jsn-field-prefix\">-</span>
							<div class=\"jsn-inline\"><input id=\"two_{$data->field_id}\" name=\"phone[{$data->field_id}][two]\" value='" . htmlentities($twoValue, ENT_QUOTES, "UTF-8") . "' type=\"text\" placeholder=\"" . htmlentities($placeholderTwoField, ENT_QUOTES, "UTF-8") . "\" class=\"phone jsn-input-mini-fluid\"></div>
							<span class=\"jsn-field-prefix\">-</span>
							<div class=\"jsn-inline\"><input id=\"three_{$data->field_id}\" name=\"phone[{$data->field_id}][three]\" value='" . htmlentities($threeValue, ENT_QUOTES, "UTF-8") . "' type=\"text\" placeholder=\"" . htmlentities($placeholderThreeField, ENT_QUOTES, "UTF-8") . "\" class=\"phone jsn-input-mini-fluid\"></div>";
		}
		$html = "<div class=\"control-group {$identify} {$hideField}\"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls {$requiredBlank}\">{$inputContent}</div></div>";
		return $html;
	}

	/**
	 * Generate html code for "ParagraphText" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldParagraphText($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$limitValue = "";
		$styleClassLimit = "";
		if (isset($settings->options->limitation) && $settings->options->limitation == 1)
		{
			$josnLimit = json_encode(array('limitMin' => $settings->options->limitMin, 'limitMax' => $settings->options->limitMax, 'limitType' => $settings->options->limitType));
			if ($settings->options->limitMax != 0 && $settings->options->limitType == 'Characters')
			{
				$limitValue = "data-limit='{$josnLimit}' maxlength=\"{$settings->options->limitMax}\"";
			}
			else
			{
				$limitValue = "data-limit='{$josnLimit}'";
			}
			$styleClassLimit = "limit-required";
		}
		$sizeInput = !empty($settings->options->size)?$settings->options->size:'';
		$defaultValue = !empty($dataSumbission[$data->field_id])?$dataSumbission[$data->field_id]:'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$requiredBlank = !empty($settings->options->required)?'blank-required':'';
		$rows = !empty($settings->options->rows) && (int) $settings->options->rows?$settings->options->rows:'10';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$placeholder = !empty($settings->options->value)?JText::_($settings->options->value):"";
		$html = "<div class=\"control-group {$identify} {$hideField}\"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls {$requiredBlank}\"><textarea {$limitValue} rows=\"{$rows}\" class=\" {$styleClassLimit} {$sizeInput}\" id=\"{$data->field_id}\" name=\"{$data->field_id}\" placeholder=\"" . htmlentities($placeholder, ENT_QUOTES, "UTF-8") . "\">{$defaultValue}</textarea></div></div>";
		return $html;
	}

	/**
	 * Generate html code for "Number" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldNumber($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$limitValue = "";
		$styleClassLimit = "";
		if (isset($settings->options->limitation) && $settings->options->limitation == 1)
		{
			$settings->options->limitType = isset($settings->options->limitType)?$settings->options->limitType:'Characters';
			$josnLimit = json_encode(array('limitMin' => $settings->options->limitMin, 'limitMax' => $settings->options->limitMax, 'limitType' => $settings->options->limitType));
			$limitValue = "data-limit='{$josnLimit}'";
			$styleClassLimit = "number-limit-required";
		}
		$sizeInput = !empty($settings->options->size)?$settings->options->size:'';
		$defaultValue = "";
		$defaultValueDecimal = "";
		if ($dataSumbission)
		{
			$defaultValue = isset($dataSumbission['number'][$data->field_id]['value'])?$dataSumbission['number'][$data->field_id]['value']:"";
			$defaultValueDecimal = isset($dataSumbission['number'][$data->field_id]['decimal'])?$dataSumbission['number'][$data->field_id]['decimal']:"";
		}
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$requiredInteger = !empty($settings->options->required)?'integer-required':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$placeholder = !empty($settings->options->value)?JText::_($settings->options->value):"";
		$placeholderDecimal = !empty($settings->options->decimal)?JText::_($settings->options->decimal):"";
		$showDecimal = "";

		if (!empty($settings->options->showDecimal) && $settings->options->showDecimal == "1")
		{
			$showDecimal = "<span class=\"jsn-field-prefix\">.</span><input {$limitValue} class=\"number input-mini\" name=\"number[{$data->field_id}][decimal]\" type=\"number\" value=\"" . htmlentities($defaultValueDecimal, ENT_QUOTES, "UTF-8") . "\" placeholder=\"" . htmlentities($placeholderDecimal, ENT_QUOTES, "UTF-8") . "\" />";
		}
		$html = "<div class=\"control-group {$identify} {$hideField}\"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls\"><input {$limitValue} class=\"number {$requiredInteger} {$styleClassLimit} {$sizeInput}\" id=\"{$data->field_id}\" name=\"number[{$data->field_id}][value]\" type=\"number\" value=\"" . htmlentities($defaultValue, ENT_QUOTES, "UTF-8") . "\" placeholder=\"" . htmlentities($placeholder, ENT_QUOTES, "UTF-8") . "\" />{$showDecimal}</div></div>";
		return $html;
	}

	/**
	 * Generate html code for "Name" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldName($data, $dataSumbission)
	{

		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$requiredBlank = !empty($settings->options->required)?'group-blank-required':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$html = "<div class=\"control-group {$identify} {$hideField}\"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div id=\"{$data->field_id}\" class=\"controls {$requiredBlank}\">";
		$valueFirstName = '';
		$valueLastName = '';
		$valueMiddle = '';

		if (!empty($dataSumbission))
		{
			$valueFirstName = isset($dataSumbission['name'][$data->field_id]['first'])?$dataSumbission['name'][$data->field_id]['first']:"";
			$valueLastName = isset($dataSumbission['name'][$data->field_id]['last'])?$dataSumbission['name'][$data->field_id]['last']:"";
			$valueTitle = isset($dataSumbission['name'][$data->field_id]['title'])?$dataSumbission['name'][$data->field_id]['title']:"";
			$valueMiddle = isset($dataSumbission['name'][$data->field_id]['suffix'])?$dataSumbission['name'][$data->field_id]['suffix']:"";
		}

		$sizeInput = !empty($settings->options->size)?$settings->options->size:'';
		if (!empty($settings->options->vtitle))
		{

			$html .= "<select class=\"jsn-input-fluid\" name=\"name[{$data->field_id}][title]\">";
			if (isset($settings->options->items) && is_array($settings->options->items))
			{
				foreach ($settings->options->items as $option)
				{
					if (!empty($valueTitle))
					{
						if (isset($option->text) && $option->text == $valueTitle)
						{
							$selected = "selected='true'";
						}
						else
						{
							$selected = "";
						}
					}
					else
					{
						if ($option->checked == 1 || $option->checked == 'true')
						{
							$selected = "selected='true'";
						}
						else
						{
							$selected = "";
						}
					}
					$html .= "<option {$selected} value=\"{$option->text}\">{$option->text}</option>";
				}
			}
			$html .= "</select>&nbsp;&nbsp;";
		}
		if (!empty($settings->options->vfirst))
		{
			$html .= "<input type=\"text\" class=\"{$sizeInput}\" value='" . htmlentities($valueFirstName, ENT_QUOTES, "UTF-8") . "' name=\"name[{$data->field_id}][first]\" placeholder=\"" . htmlentities(JText::_("First"), ENT_QUOTES, "UTF-8") . "\" />&nbsp;&nbsp;";
		}
		if (!empty($settings->options->vmiddle))
		{
			$html .= "<input name=\"name[{$data->field_id}][suffix]\" type=\"text\" value=\"" . htmlentities($valueMiddle, ENT_QUOTES, "UTF-8") . "\" class=\"{$sizeInput}\" placeholder=\"" . htmlentities(JText::_("Middle"), ENT_QUOTES, "UTF-8") . "\" />&nbsp;&nbsp;";
		}
		if (!empty($settings->options->vlast))
		{
			$html .= "<input type=\"text\" class=\"{$sizeInput}\" value='" . htmlentities($valueLastName, ENT_QUOTES, "UTF-8") . "' name=\"name[{$data->field_id}][last]\" placeholder=\"" . htmlentities(JText::_("Last"), ENT_QUOTES, "UTF-8") . "\" />";
		}
		$html .= "</div></div>";
		return $html;
	}

	/**
	 * Generate html code for "FileUpload" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldFileUpload($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$requiredBlank = !empty($settings->options->required)?'blank-required':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$html = "<div class=\"control-group {$identify} {$hideField}\"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls {$requiredBlank}\"><input id=\"{$data->field_id}\" class=\"input-file\" name=\"{$data->field_id}\" type=\"file\" /></div></div>";
		return $html;
	}

	/**
	 * Generate html code for "Email" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldEmail($data, $dataSumbission)
	{

		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$requiredEmail = !empty($settings->options->required)?'email-required':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';


		$defaultValue = !empty($dataSumbission[$data->field_id])?$dataSumbission[$data->field_id]:'';
		$defaultValueConfirm = !empty($dataSumbission[$data->field_id . "_confirm"])?$dataSumbission[$data->field_id . "_confirm"]:'';


		$sizeInput = !empty($settings->options->size)?$settings->options->size:'';
		$placeholder = !empty($settings->options->value)?JText::_($settings->options->value):"";
		$placeholderConfirm = !empty($settings->options->valueConfirm)?JText::_($settings->options->valueConfirm):"";

		$html = "<div class=\"control-group {$identify} {$hideField}\"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls\">";
		$html .= "<div class=\"row-fluid\"><input class=\"email {$requiredEmail} {$sizeInput}\" id=\"{$data->field_id}\" name=\"{$data->field_id}\" type=\"text\" value=\"" . htmlentities($defaultValue, ENT_QUOTES, "UTF-8") . "\" placeholder=\"" . htmlentities($placeholder, ENT_QUOTES, "UTF-8") . "\" /></div>";
		if (!empty($settings->options->requiredConfirm))
		{
			$html .= "<div class=\"row-fluid\"><input class=\"{$sizeInput} jsn-email-confirm\" id=\"{$data->field_id}_confirm\" name=\"{$data->field_id}_confirm\" type=\"text\" value=\"" . htmlentities($defaultValueConfirm, ENT_QUOTES, "UTF-8") . "\" placeholder=\"" . htmlentities($placeholderConfirm, ENT_QUOTES, "UTF-8") . "\" /></div>";
		}
		$html .= "</div></div>";
		return $html;
	}

	/**
	 * Generate html code for "DropDown" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldDropdown($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$randomDropdown = !empty($settings->options->randomize)?'dropdown-randomize':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$defaultValue = !empty($dataSumbission[$data->field_id])?$dataSumbission[$data->field_id]:"";
		$sizeInput = !empty($settings->options->size)?$settings->options->size:'';
		$dataSettings = !empty($settings->options->itemAction)?$settings->options->itemAction:'';
		$requiredBlank = !empty($settings->options->firstItemAsPlaceholder) && !empty($settings->options->required)?'dropdown-required':'';
		$html = "<div class=\"control-group {$identify} {$hideField}\" data-settings=\"" . htmlentities($dataSettings, ENT_QUOTES, "UTF-8") . "\"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls {$requiredBlank}\"><select id=\"{$data->field_id}\" class=\"dropdown {$sizeInput} {$randomDropdown}\" name=\"{$data->field_id}\">";

		if (isset($settings->options->items) && is_array($settings->options->items))
		{
			foreach ($settings->options->items as $index => $option)
			{
				if (!empty($defaultValue))
				{
					if (isset($option->text) && $option->text == $defaultValue)
					{
						$selected = "selected='true'";
					}
					else
					{
						$selected = "";
					}
				}
				else
				{
					if ($option->checked == 1 || $option->checked == 'true')
					{
						$selected = "selected='true'";
					}
					else
					{
						$selected = "";
					}
				}
				$selectDefault = "";
				if ($selected)
				{
					$selectDefault = "selectdefault=\"true\"";
				}
				if (!empty($settings->options->firstItemAsPlaceholder) && $index == 0)
				{
					$html .= "<option {$selected} {$selectDefault} value=\"\">" . JText::_($option->text) . "</option>";
				}
				else
				{
					$html .= "<option class=\"jsn-column-item\" {$selected} {$selectDefault} value=\"" . htmlentities($option->text, ENT_QUOTES, "UTF-8") . "\">" . htmlentities(JText::_($option->text), ENT_QUOTES, "UTF-8") . "</option>";
				}
			}
		}
		$textOthers = !empty($settings->options->labelOthers)?$settings->options->labelOthers:"Others";
		if (!empty($settings->options->allowOther))
		{
			$html .= "<option class=\"lbl-allowOther\" value=\"Others\">" . JText::_($textOthers) . "</option>";
			$html .= "</select>";
			$html .= "<div class=\"jsn-column-item jsn-uniform-others\"><textarea class='jsn-dropdown-Others hide' name=\"fieldOthers[{$data->field_id}]\"  rows=\"3\"></textarea></div></div>";
		}
		else
		{
			$html .= "</select></div>";
		}
		$html .= "</div>";
		return $html;
	}

	/**
	 * Generate html code for "DropDown" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldList($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$requiredBlank = !empty($settings->options->required)?'list-required':'';
		$randomList = !empty($settings->options->randomize)?'list-randomize':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$defaultValue = !empty($dataSumbission[$data->field_id])?$dataSumbission[$data->field_id]:"";
		$sizeInput = !empty($settings->options->size)?$settings->options->size:'';
		$multiple = !empty($settings->options->multiple)?"multiple":"size='4'";
		$html = "<div class=\"control-group {$identify} {$hideField} \"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls {$requiredBlank}\"><select {$multiple} id=\"{$data->field_id}\" class=\"list {$sizeInput} {$randomList}\" name=\"{$data->field_id}[]\">";

		if (isset($settings->options->items) && is_array($settings->options->items))
		{
			foreach ($settings->options->items as $option)
			{
				if (!empty($defaultValue))
				{
					if (isset($option->text) && $option->text == $defaultValue)
					{
						$selected = "selected='true'";
					}
					else
					{
						$selected = "";
					}
				}
				else
				{
					if ($option->checked == 1 || $option->checked == 'true')
					{
						$selected = "selected='true'";
					}
					else
					{
						$selected = "";
					}
				}
				$html .= "<option class=\"jsn-column-item\" {$selected} value=\"" . $option->text . "\">" . htmlentities(JText::_($option->text), ENT_QUOTES, "UTF-8") . "</option>";
			}
		}
		$html .= "</select></div></div>";
		return $html;
	}

	/**
	 * Generate html code for "Country" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldCountry($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$requiredBlank = !empty($settings->options->required)?'blank-required':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$defaultValue = !empty($dataSumbission[$data->field_id])?$dataSumbission[$data->field_id]:"";
		$sizeInput = !empty($settings->options->size)?$settings->options->size:'';
		$html = "<div class=\"control-group {$identify} {$hideField} \"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls {$requiredBlank}\"><select id=\"{$data->field_id}\" class=\"{$sizeInput}\" name=\"{$data->field_id}\">";
		if (isset($settings->options->items) && is_array($settings->options->items))
		{
			foreach ($settings->options->items as $option)
			{
				if (!empty($defaultValue))
				{
					if (isset($option->text) && $option->text == $defaultValue)
					{
						$selected = "selected='true'";
					}
					else
					{
						$selected = "";
					}
				}
				else
				{
					if (isset($option->checked) && $option->checked == 1)
					{
						$selected = "selected='true'";
					}
					else
					{
						$selected = "";
					}
				}
				$html .= "<option {$selected} value=\"" . htmlentities($option->text, ENT_QUOTES, "UTF-8") . "\">" . htmlentities(JText::_($option->text), ENT_QUOTES, "UTF-8") . "</option>";
			}
		}
		$html .= "</select></div></div>";
		return $html;
	}

	/**
	 * Generate html code for "Choices" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldChoices($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$requiredChoices = !empty($settings->options->required)?'choices-required':'';
		$randomChoices = !empty($settings->options->randomize)?'choices-randomize':'';
		$dataSettings = !empty($settings->options->itemAction)?$settings->options->itemAction:'';
		$html = "<div class=\"control-group {$identify} {$hideField} \" data-settings=\"" . htmlentities($dataSettings, ENT_QUOTES, "UTF-8") . "\" ><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls {$requiredChoices}\"><div id=\"{$data->field_id}\" class=\"choices jsn-columns-container {$settings->options->layout} {$randomChoices}\">";

		$defaultValue = isset($dataSumbission[$data->field_id])?$dataSumbission[$data->field_id]:"";
		if (isset($settings->options->items) && is_array($settings->options->items))
		{
			foreach ($settings->options->items as $i => $option)
			{
				if (!empty($defaultValue))
				{
					if (isset($option->text) && $option->text == $defaultValue)
					{
						$checked = "checked='true'";
					}
					else
					{
						$checked = "";
					}
				}
				else
				{
					if (isset($option->checked) && $option->checked == "true")
					{
						$checked = "checked='true'";
					}
					else
					{
						$checked = "";
					}
				}
				$html .= "<div class=\"jsn-column-item\"><label class=\"radio\"><input {$checked} name=\"{$data->field_id}\" value=\"" . htmlentities($option->text, ENT_QUOTES, "UTF-8") . "\" type=\"radio\" />" . htmlentities(JText::_($option->text), ENT_QUOTES, "UTF-8") . "</label></div>";
			}
		}
		$textOthers = !empty($settings->options->labelOthers)?$settings->options->labelOthers:"Others";
		if (!empty($settings->options->allowOther))
		{
			$html .= "<div class=\"jsn-column-item jsn-uniform-others\"><label class=\"radio lbl-allowOther\"><input class=\"allowOther\" name=\"{$data->field_id}\" value=\"Others\" type=\"radio\" />" . htmlentities(JText::_($textOthers), ENT_QUOTES, "UTF-8") . "</label>";
			$html .= "<textarea disabled=\"true\" class='jsn-value-Others' name=\"fieldOthers[{$data->field_id}]\" rows=\"3\"></textarea></div>";
		}
		$html .= "<div class=\"clearbreak\"></div></div></div></div>";

		return $html;
	}

	/**
	 * Generate html code for "Checkboxes" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldCheckboxes($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';
		$requiredCheckbox = !empty($settings->options->required)?'checkbox-required':'';
		$randomCheckbox = !empty($settings->options->randomize)?'checkbox-randomize':'';
		$dataSettings = !empty($settings->options->itemAction)?$settings->options->itemAction:'';
		$html = "<div class=\"control-group {$identify} {$hideField} \" data-settings=\"" . htmlentities($dataSettings, ENT_QUOTES, "UTF-8") . "\"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div class=\"controls\"><div id=\"{$data->field_id}\" class=\"checkboxes jsn-columns-container {$settings->options->layout} {$randomCheckbox} {$requiredCheckbox}\">";
		$defaultValue = isset($dataSumbission[$data->field_id])?$dataSumbission[$data->field_id]:"";
		if (isset($settings->options->items) && is_array($settings->options->items))
		{
			foreach ($settings->options->items as $i => $option)
			{
				$checked = "";
				if (!empty($defaultValue))
				{
					if (isset($option->text) && in_array($option->text, $defaultValue))
					{
						$checked = "checked='true'";
					}
				}
				else
				{
					if (isset($option->checked) && $option->checked == "true")
					{
						$checked = "checked='true'";
					}
				}

				$html .= "<div class=\"jsn-column-item\"><label class=\"checkbox\"><input {$checked} name=\"{$data->field_id}[]\" value=\"" . htmlentities($option->text, ENT_QUOTES, "UTF-8") . "\" type=\"checkbox\" />" . htmlentities(JText::_($option->text), ENT_QUOTES, "UTF-8") . "</label></div>";
			}
		}
		$textOthers = !empty($settings->options->labelOthers)?$settings->options->labelOthers:"Others";
		if (!empty($settings->options->allowOther))
		{
			$html .= "<div class=\"jsn-column-item jsn-uniform-others\"><label class=\"checkbox lbl-allowOther\"><input class=\"allowOther\" value=\"Others\" type=\"checkbox\" />" . htmlentities(JText::_($textOthers), ENT_QUOTES, "UTF-8") . "</label>";
			$html .= "<textarea disabled=\"true\" class='jsn-value-Others' name=\"{$data->field_id}[]\"  rows=\"3\"></textarea></div>";
		}
		$html .= "<div class=\"clearbreak\"></div></div></div></div>";

		return $html;
	}

	/**
	 * Generate html code for "Address" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldAddress($data, $dataSumbission)
	{
		$valueStreet = '';
		$valueLine2 = '';
		$valueCity = '';
		$valueCode = '';
		$valueState = '';
		$valueCountry = '';
		if (!empty($dataSumbission))
		{
			$valueStreet = isset($dataSumbission['address'][$data->field_id]['street'])?$dataSumbission['address'][$data->field_id]['street']:"";
			$valueLine2 = isset($dataSumbission['address'][$data->field_id]['line2'])?$dataSumbission['address'][$data->field_id]['line2']:"";
			$valueCity = isset($dataSumbission['address'][$data->field_id]['city'])?$dataSumbission['address'][$data->field_id]['city']:"";
			$valueCode = isset($dataSumbission['address'][$data->field_id]['code'])?$dataSumbission['address'][$data->field_id]['code']:"";
			$valueState = isset($dataSumbission['address'][$data->field_id]['state'])?$dataSumbission['address'][$data->field_id]['state']:"";
			$valueCountry = isset($dataSumbission['address'][$data->field_id]['country'])?$dataSumbission['address'][$data->field_id]['country']:"";
		}
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$requiredBlank = !empty($settings->options->required)?'group-blank-required':'';
		$required = !empty($settings->options->required)?'<span class="required">*</span>':'';
		$instruction = !empty($settings->options->instruction)?" <i original-title=\"" . htmlentities(JText::_($settings->options->instruction), ENT_QUOTES, "UTF-8") . "\" class=\"icon-question-sign\"></i>":'';

		$html = "<div class=\"control-group {$identify} {$hideField} \"><label for=\"" . htmlentities(JText::_($data->field_title), ENT_QUOTES, "UTF-8") . "\" class=\"control-label\">" . JText::_($data->field_title) . "{$required}{$instruction}</label><div id=\"{$data->field_id}\" class=\"controls {$requiredBlank}\">";
		if (isset($settings->options->vstreetAddress) && $settings->options->vstreetAddress == 1)
		{
			$html .= "<div class=\"row-fluid\"><input type=\"text\" value='" . htmlentities($valueStreet, ENT_QUOTES, "UTF-8") . "' name=\"address[{$data->field_id}][street]\" placeholder=\"" . htmlentities(JText::_("Street_Address"), ENT_QUOTES, "UTF-8") . "\" class=\"span12\" /></div>";
		}
		if (isset($settings->options->vstreetAddress2) && $settings->options->vstreetAddress2 == 1)
		{
			$html .= "<div class=\"row-fluid\"><input type=\"text\" value='" . htmlentities($valueLine2, ENT_QUOTES, "UTF-8") . "' name=\"address[{$data->field_id}][line2]\" placeholder=\"" . htmlentities(JText::_("Address_Line_2"), ENT_QUOTES, "UTF-8") . "\" class=\"span12\" /></div>";
		}
		$html .= "<div class=\"row-fluid\">";
		if ((isset($settings->options->vcity) && $settings->options->vcity == 1) || (isset($settings->options->vcode) && $settings->options->vcode == 1))
		{
			$html .= "<div class=\"span6\">";
			if (isset($settings->options->vcity) && $settings->options->vcity == 1)
			{
				$html .= "<input value='" . htmlentities($valueCity, ENT_QUOTES, "UTF-8") . "' type=\"text\" name=\"address[{$data->field_id}][city]\" class=\"jsn-input-medium-fluid\" placeholder=\"" . htmlentities(JText::_("City"), ENT_QUOTES, "UTF-8") . "\" />";
			}
			if (isset($settings->options->vcode) && $settings->options->vcode == 1)
			{
				$html .= "<input value='" . htmlentities($valueCode, ENT_QUOTES, "UTF-8") . "'  type=\"text\" name=\"address[{$data->field_id}][code]\" class=\"jsn-input-medium-fluid\" placeholder=\"" . htmlentities(JText::_("Postal_Zip_code"), ENT_QUOTES, "UTF-8") . "\" />";
			}
			$html .= "</div>";
		}
		if ((isset($settings->options->vstate) && $settings->options->vstate == 1) || (isset($settings->options->vcountry) && $settings->options->vcountry == 1))
		{
			$html .= "<div class=\"span6\">";
			if (isset($settings->options->vstate) && $settings->options->vstate == 1)
			{
				$html .= "<input value='" . htmlentities($valueState, ENT_QUOTES, "UTF-8") . "'  name=\"address[{$data->field_id}][state]\" type=\"text\" placeholder=\"" . htmlentities(JText::_("State_Province_Region"), ENT_QUOTES, "UTF-8") . "\" class=\"jsn-input-medium-fluid\" />";
			}
			if (isset($settings->options->vcountry) && $settings->options->vcountry == 1)
			{
				$html .= "<select name=\"address[{$data->field_id}][country]\">";
				if (isset($settings->options->country) && is_array($settings->options->country))
				{
					foreach ($settings->options->country as $option)
					{
						if (!empty($valueCountry))
						{
							if (isset($option->text) && $option->text == $valueCountry)
							{
								$selected = "selected='true'";
							}
							else
							{
								$selected = "";
							}
						}
						else
						{
							if (isset($option->checked) && $option->checked == 1)
							{
								$selected = "selected='true'";
							}
							else
							{
								$selected = "";
							}
						}
						$html .= "<option {$selected} value=\"" . htmlentities($option->text, ENT_QUOTES, "UTF-8") . "\">" . htmlentities(JText::_($option->text), ENT_QUOTES, "UTF-8") . "</option>";
					}
				}
				$html .= "</select>";
			}
			$html .= "</div>";
		}
		$html .= "</div></div></div>";
		return $html;
	}

	/**
	 * Generate html code for "Static Content" data field
	 *
	 * @param   object  $data            Data field
	 *
	 * @param   array   $dataSumbission  Data submission
	 *
	 * @return string
	 */
	public static function fieldStaticContent($data, $dataSumbission)
	{
		$settings = json_decode($data->field_settings);
		$identify = !empty($settings->identify)?$settings->identify:"";
		$hideField = !empty($settings->options->hideField)?'hide':'';
		$value = isset($settings->options->value)?JText::_($settings->options->value):"";
		$html = "<div class=\"control-group {$identify} {$hideField} \"><div class=\"controls\">{$value}</div></div>";
		return $html;
	}
}