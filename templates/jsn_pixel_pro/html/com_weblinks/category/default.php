<?php
/**
 * @version		$Id: default.php 17015 2012-10-15 03:45:21Z quocanhd $
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Load template framework
if (!defined('JSN_PATH_TPLFRAMEWORK')) {
	require_once JPATH_ROOT . '/plugins/system/jsntplframework/defines.php';
	require_once JPATH_ROOT . '/plugins/system/jsntplframework/libraries/joomlashine/loader.php';
}

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
?>

<div class="com-weblink <?php echo $this->pageclass_sfx; ?>">
	<div class="category-list">
		<?php if ($this->params->def('show_page_heading', 1)) : ?>
		<h2 class="componentheading"> <?php echo $this->escape($this->params->get('page_heading')); ?> </h2>
		<?php endif; ?>
		<?php if($this->params->get('show_category_title', 1)) : ?>
		<?php echo JHtml::_('content.prepare', $this->category->title, '', 'com_weblinks.category'); ?>
		<?php endif; ?>
		<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
		<div class="contentdescription clearafter">
			<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
			<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
			<?php endif; ?>
			<?php if ($this->params->get('show_description') && $this->category->description) : ?>
			<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_weblinks.category'); ?>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<?php echo $this->loadTemplate('items'); ?>
		<?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
		<div class="cat-children">
			<h3><?php echo JText::_('JGLOBAL_SUBCATEGORIES') ; ?></h3>
			<?php echo $this->loadTemplate('children'); ?> </div>
		<?php endif; ?>
	</div>
</div>