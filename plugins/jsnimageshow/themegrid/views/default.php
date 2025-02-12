<?php
/**
 * @author JoomlaShine.com Team
 * @copyright JoomlaShine.com
 * @link joomlashine.com
 * @package JSN ImageShow - Theme Classic
 * @version $Id: default.php 16892 2012-10-11 04:07:40Z giangnd $
 * @license GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die( 'Restricted access' );
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}
$objJSNUtils = JSNISFactory::getObj('classes.jsn_is_utils');
$url 		 = $objJSNUtils->overrideURL();
$user 		 = JFactory::getUser();
?>
<script type="text/javascript">
	(function($) {
		$(document).ready(function(){
			$('#jsn-themegrid-container').gridtheme();
			$('#jsn-is-themegrid').tabs();
			$('#jsn-themegrid-container').stickyfloat({
				   duration: 0
		    });	
		    
			$('#background-color-selector').ColorPicker({
				color: $('#background_color').val(),
				onShow: function (colpkr) {
					$(colpkr).fadeIn(500);
					return false;
				},
				onHide: function (colpkr) {
					$(colpkr).fadeOut(500);
					return false;
				},
				onChange: function (hsb, hex, rgb) {
					$('#background_color').val('#' + hex);
					$('#background-color-selector div').css('backgroundColor', '#' + hex);
					$('#jsn-themegrid-container').css('background-color', '#' + hex);
				}
			});	
			
			$('#thumbnail-border-color-selector').ColorPicker({
				color: $('#thumbnail_border_color').val(),
				onShow: function (colpkr) {
					$(colpkr).fadeIn(500);
					return false;
				},
				onHide: function (colpkr) {
					$(colpkr).fadeOut(500);
					return false;
				},
				onChange: function (hsb, hex, rgb) {
					$('#thumbnail_border_color').val('#' + hex);
					$('#thumbnail-border-color-selector div').css('backgroundColor', '#' + hex);
					$('.jsn-themegrid-box').css('border-color', '#' + hex);
				}
			});				
		})
	})(jsnThemeGridjQuery);
</script>

<table class="jsn-showcase-theme-settings">
	<tr>
		<td valign="top" id="jsn-theme-parameters-wrapper">
			<div id="jsn-is-themegrid" class="jsn-tabs">
				<ul>
					<li><a href="#themegrid-container-tab"><?php echo JText::_('THEME_GRID_IMAGE_CONTAINER'); ?>
					</a></li>
					<li><a href="#themegrid-thumbnail-tab"><?php echo JText::_('THEME_GRID_IMAGE_PRESENTATION'); ?>
					</a></li>
				</ul>
				<div id="themegrid-container-tab" class="jsn-bootstrap">
					<div class="form-horizontal">
						<div class="row-fluid show-grid">
							<div class="span12">
								<div class="control-group">
									<label class="control-label hasTip"
										title="<?php echo htmlspecialchars(JText::_('THEME_GRID_BACKGROUND_COLOR_TITLE'));?>::<?php echo htmlspecialchars(JText::_('THEME_GRID_BACKGROUND_COLOR_DESC')); ?>"><?php echo JText::_('THEME_GRID_BACKGROUND_COLOR_TITLE');?>
									</label>
									<div class="controls">
										<input type="text"
											value="<?php echo (!empty($items->background_color))?$items->background_color:'#ffffff'; ?>"
											readonly="readonly" name="background_color"
											id="background_color" class="input-mini" />
										<div class="color-selector" id="background-color-selector">
											<div style="background-color: <?php echo (!empty($items->background_color))?$items->background_color:'#ffffff'; ?>"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="themegrid-thumbnail-tab" class="jsn-bootstrap">
					<div class="form-horizontal">
						<div class="row-fluid show-grid">
							<div class="span12">
								<div class="control-group">
									<label class="control-label hasTip"
										title="<?php echo htmlspecialchars(JText::_('THEME_GRID_LAYOUT_TITLE'));?>::<?php echo htmlspecialchars(JText::_('THEME_GRID_LAYOUT_DESC')); ?>"><?php echo JText::_('THEME_GRID_LAYOUT_TITLE');?>
									</label>
									<div class="controls">
									<?php echo $lists['imgLayout']; ?>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label hasTip"
										title="<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_WIDTH_TITLE'));?>::<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_WIDTH_DESC')); ?>"><?php echo JText::_('THEME_GRID_THUMBNAIL_WIDTH_TITLE');?>
									</label>
									<div class="controls">
										<input type="number" id="thumbnail_width"
											name="thumbnail_width" class="imagePanel input-mini"
											value="<?php echo $items->thumbnail_width; ?>" />
											<?php echo JText::_('THEME_GRID_PIXEL');?>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label hasTip"
										title="<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_HEIGHT_TITLE'));?>::<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_HEIGHT_DESC')); ?>"><?php echo JText::_('THEME_GRID_THUMBNAIL_HEIGHT_TITLE');?>
									</label>
									<div class="controls">
										<input type="number" name="thumbnail_height"
											id="thumbnail_height" class="imagePanel input-mini"
											value="<?php echo $items->thumbnail_height; ?>" />
											<?php echo JText::_('THEME_GRID_PIXEL');?>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label hasTip"
										title="<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_SPACE_TITLE'));?>::<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_SPACE_DESC')); ?>"><?php echo JText::_('THEME_GRID_THUMBNAIL_SPACE_TITLE');?>
									</label>
									<div class="controls">
										<input type="number" name="thumbnail_space"
											id="thumbnail_space" class="imagePanel input-mini"
											value="<?php echo $items->thumbnail_space; ?>" />
											<?php echo JText::_('THEME_GRID_PIXEL');?>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label hasTip"
										title="<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_BORDER_TITLE'));?>::<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_BORDER_DESC')); ?>"><?php echo JText::_('THEME_GRID_THUMBNAIL_BORDER_TITLE');?>
									</label>
									<div class="controls">
										<input type="number" name="thumbnail_border"
											id="thumbnail_border" class="imagePanel input-mini"
											value="<?php echo $items->thumbnail_border; ?>" />
											<?php echo JText::_('THEME_GRID_PIXEL');?>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label hasTip"
										title="<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_ROUNDED_CORNER_TITLE'));?>::<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_ROUNDED_CORNER_DESC')); ?>"><?php echo JText::_('THEME_GRID_THUMBNAIL_ROUNDED_CORNER_TITLE');?>
									</label>
									<div class="controls">
										<input type="number" name="thumbnail_rounded_corner"
											id="thumbnail_rounded_corner" class="imagePanel input-mini"
											value="<?php echo $items->thumbnail_rounded_corner; ?>" />
											<?php echo JText::_('THEME_GRID_PIXEL');?>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label hasTip"
										title="<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_SHADOW_TITLE'));?>::<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_SHADOW_DESC')); ?>"><?php echo JText::_('THEME_GRID_THUMBNAIL_SHADOW_TITLE');?>
									</label>
									<div class="controls">
									<?php echo $lists['thumbnailShadow']; ?>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label hasTip"
										title="<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_BORDER_COLOR_TITLE'));?>::<?php echo htmlspecialchars(JText::_('THEME_GRID_THUMBNAIL_BORDER_COLOR_DESC')); ?>"><?php echo JText::_('THEME_GRID_THUMBNAIL_BORDER_COLOR_TITLE');?>
									</label>
									<div class="controls">
										<input class="thumbnailColor input-mini" type="text"
											value="<?php echo (!empty($items->thumbnail_border_color))?$items->thumbnail_border_color:'#F0F0F0'; ?>"
											readonly="readonly" name="thumbnail_border_color"
											id="thumbnail_border_color" />
										<div class="color-selector"
											id="thumbnail-border-color-selector">
											<div style="background-color: <?php echo (!empty($items->thumbnail_border_color))?$items->thumbnail_border_color:'#F0F0F0'; ?>"></div>
										</div>
									</div>

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</td>
		<td id="jsn-theme-preview-wrapper">
			<div>
				<?php include dirname(__FILE__).DS.'preview.php'; ?>
			</div>
		</td>
	</tr>
</table>
<!--  important -->
<input
	type="hidden" name="theme_name"
	value="<?php echo strtolower($this->_showcaseThemeName); ?>" />
<input
	type="hidden" name="theme_id"
	value="<?php echo (int) @$items->theme_id; ?>" />
<!--  important -->
<div style="clear: both;"></div>
