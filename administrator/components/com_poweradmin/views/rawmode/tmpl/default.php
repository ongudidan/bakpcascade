<?php
/*------------------------------------------------------------------------
# JSN PowerAdmin
# ------------------------------------------------------------------------
# author    JoomlaShine.com Team
# copyright Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
# Websites: http://www.joomlashine.com
# Technical Support:  Feedback - http://www.joomlashine.com/joomlashine/contact-us.html
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# @version $Id: default.php 15314 2012-08-21 08:18:20Z hiepnv $
-------------------------------------------------------------------------*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<a href="" id="jsn-fullscreen">&nbsp;</a>
<div id="jsn-rawmode-layout">
	<div id="jsn-rawmode-leftcolumn" class="pane ui-layout-west">
		<div class="jsn-heading-panel clearafter">
			<span class="jsn-toggle-button">
			   <button class="disabled" id="menu-manager"  title="<?php echo JText::_('JSN_RAWMODE_SHOW_PUBLISHED_MENUITEM');?>"></button>
			</span>
			<h3 class="jsn-heading-panel-title">
				<?php echo JText::_('MENU');?>
			</h3>
		</div>
		<div class="jsn-menu-selector-container">
			<div class="jsn-menu-selector-container_inner">
				<!-- Dropdown list menu type for choose -->
				<?php echo $this->jsnmenuitems->menuTypeDropDownList(); ?>

				<div id="jsn-rawmode-menuitem-container">
					<!-- Menu items render -->
					<?php echo $this->jsnmenuitems->render();?>
				</div>
			</div>
		</div>
	</div>
	<div id="jsn-rawmode-center" class="pane ui-layout-center">
		<div class="jsn-heading-panel clearafter">
			<span class="jsn-toggle-button">
				<button class="disabled" id="component-manager"></button>
			</span>
			<h3 class="jsn-heading-panel-title">
				<?php echo JText::_('COMPONENT');?>
			</h3>
		</div>
		<div id="jsn-component-details">
			<?php echo $this->component; ?>
		</div>
	</div>
	<div id="jsn-rawmode-rightcolumn" class="ui-layout-east">
		<div class="jsn-heading-panel clearafter">
			<h3 class="jsn-heading-panel-title jsn-module-panel-header">
				<?php echo JText::_('MODULES');?>
			</h3>
			<div class="jsn-module-spotlight-filter" id="module_spotlight_container">
				<input type="input" id="module_spotlight_filter" />
				<a class="close" href="javascript:void(0)"></a>
			</div>
			<span class="jsn-toggle-button">
			   <button class="disabled" id="module-manager"></button>
			</span>
	   </div>
	   <div id="module-show-options" class="module-show-options">
	   		<div class="option">
	   			<input type="checkbox" id="show_unpublished_positions" />
	   			<label for="show_unpublished_positions"><?php echo JText::_('SHOW_UNPUBLISHED_POSITIONS');?></label>
	   		</div>
	   		<div class="option">
	   			<input type="checkbox" id="show_unpublished_modules" />
	   			<label for="show_unpublished_modules"><?php echo JText::_('SHOW_UNPUBLISHED_MODULES');?></label>
	   		</div>
	   </div>
	   <div id="modules-list">
			<div id="module-list-container">
				<?php echo $this->modules; ?>
			</div>
		</div>
    </div>
</div>
<div class="jsn-context-menu" id="position-context"></div>
<div class="jsn-context-menu" id="module-context"></div>
<div class="jsn-context-menu" id="site-manager-eader-context"></div>
<div class="jsn-context-menu" id="site-manager-switch-context"></div>

<?php
$products	=	JSNPaExtensionsHelper::getDependentExtensions();
// Display footer.
JSNHtmlGenerate::footer($products);
?>