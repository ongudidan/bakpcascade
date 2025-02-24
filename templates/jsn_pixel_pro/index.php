<?php
/**
 * @author    JoomlaShine.com http://www.joomlashine.com
 * @copyright Copyright (C) 2008 - 2011 JoomlaShine.com. All rights reserved.
 * @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted index access');

// Load template framework
if (!defined('JSN_PATH_TPLFRAMEWORK')) {
	require_once JPATH_ROOT . '/plugins/system/jsntplframework/defines.php';
	require_once JPATH_ROOT . '/plugins/system/jsntplframework/libraries/joomlashine/loader.php';
}

// Preparing template parameters
JSNTplTemplateHelper::prepare();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- jsn_pixel_pro 2.0.1 -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head" />
	<!-- html5.js and respond.min.js for IE less than 9 -->
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<script src="<?php echo JURI::root(true) ?>/plugins/system/jsntplframework/assets/3rd-party/respond/respond.min.js"></script>
	<![endif]-->
	<?php
	/*====== Show analytics code configured in template parameter ======*/
	if ($this->codePosition == 0) echo $this->codeAnalytic;
	?>
</head>
<body id="jsn-master" class="<?php echo $this->bodyClass ?>">
	<a name="top" id="top"></a>
	<div id="jsn-page">
	<?php
		/*====== Show modules in position "stick-lefttop" ======*/
		if ($this->countModules('stick-lefttop') > 0) {
	?>
		<div id="jsn-pos-stick-lefttop">
			<jdoc:include type="modules" name="stick-lefttop" style="jsnmodule" />
		</div>
	<?php
		}

		/*====== Show modules in position "stick-leftmiddle" ======*/
		if ($this->countModules('stick-leftmiddle') > 0) {
	?>
		<div id="jsn-pos-stick-leftmiddle">
			<jdoc:include type="modules" name="stick-leftmiddle" style="jsnmodule" />
		</div>
	<?php
		}

		/*====== Show modules in position "stick-leftbottom" ======*/
		if ($this->countModules('stick-leftbottom') > 0) {
	?>
		<div id="jsn-pos-stick-leftbottom">
			<jdoc:include type="modules" name="stick-leftbottom" style="jsnmodule" />
		</div>
	<?php
		}

		/*====== Show modules in position "stick-righttop" ======*/
		if ($this->countModules('stick-righttop') > 0) {
	?>
		<div id="jsn-pos-stick-righttop">
			<jdoc:include type="modules" name="stick-righttop" style="jsnmodule" />
		</div>
	<?php
		}

		/*====== Show modules in position "stick-rightmiddle" ======*/
		if ($this->countModules('stick-rightmiddle') > 0) {
	?>
		<div id="jsn-pos-stick-rightmiddle">
			<jdoc:include type="modules" name="stick-rightmiddle" style="jsnmodule" />
		</div>
	<?php
		}

		/*====== Show modules in position "stick-rightbottom" ======*/
		if ($this->countModules('stick-rightbottom') > 0) {
	?>
		<div id="jsn-pos-stick-rightbottom">
			<jdoc:include type="modules" name="stick-rightbottom" style="jsnmodule" />
		</div>
	<?php
		}
	?>
		<div id="jsn-header" class="clearafter">
			<div id="jsn-header-inner" class="clearafter">
				<div id="jsn-logo">
				<?php
					/*====== Show top H1 tag with site name and slogan ======*/
					if ($this->enableH1) {
						echo '<h1 class="jsn-topheading">' . $this->app->getCfg('sitename') . ' - ' . $this->logoSlogan . '</h1>';
					}
	
					/*====== Show modules in position "logo" ======*/
					if ($this->countModules('logo') > 0) {
				?>
					<div id="jsn-pos-logo">
						<jdoc:include type="modules" name="logo" style="jsnmodule" />
					</div>
	
				<?php
					/*====== If there are NO modules in position "logo", then show logo image file "logo.png" ======*/
					} else {
						/*====== Attach link to logo image ======*/
						if (!empty($this->logoLink)) {
							echo '<a href="' . $this->logoLink . '" title="' . $this->logoSlogan . '">';
						}
						
						/*====== Show mobile logo ======*/
						if (!empty($this->mobileLogo)) {
							echo '<img src="' . $this->mobileLogo . '" alt="' . $this->logoSlogan . '" id="jsn-logo-mobile" />';
						}
						
						/*====== Show desktop logo ======*/
						if (!empty($this->logoFile)) {
							echo '<img src="' . $this->logoFile . '" alt="' . $this->logoSlogan . '" id="jsn-logo-desktop" />';
						}
						
						if ($this->logoLink != "") {
							echo '</a>';
						}
					}
				?>
				</div>
				<div id="jsn-headerright-wrapper">
				<?php
					/*====== Show modules in position "top" ======*/
					if ($this->countModules('top') > 0) {
				?>
					<div id="jsn-headerright">
						<div id="jsn-pos-top">
							<jdoc:include type="modules" name="top" style="jsnmodule" />
						</div>
					</div>
					<div class="clearbreak"></div>
				<?php
					}
				?>
				
				<?php
					if ($this->helper->countPositions('mainmenu', 'toolbar') || $this->textSizeSelector || $this->colorSelector || $this->widthSelector) {
				?>
				<div id="jsn-menu">
					<div id="jsn-menu-inner-1">
						<div id="jsn-menu-inner">
						<?php
							/*====== Show modules in position "mainmenu" ======*/
							if ($this->countModules('mainmenu') > 0) {
						?>
							<div id="jsn-pos-mainmenu">
								<jdoc:include type="modules" name="mainmenu" style="jsnmodule" />
							</div>
						<?php
							}

							/*====== Show elements in Sitetools ======*/
							if ($this->textSizeSelector || $this->widthSelector || $this->colorSelector) {
						?>
							<div id="jsn-sitetoolspanel" class="<?php echo ($this->textSizeSelector || $this->widthSelector)?' jsn-include-size':''; ?><?php echo ($this->colorSelector)?' jsn-include-color':''; ?>">
									<ul id="jsn-sitetools-<?php echo $this->sitetoolStyle; ?>">
										<li class="clearafter jsn-sitetool-control">
										<a href="javascript:void(0)"></a>
											<ul>
								<?php
									if ($this->textSizeSelector || $this->widthSelector) {
								?>
												<li class="clearafter jsn-selector-size">
									<?php
										if ($this->textSizeSelector) {
											foreach ($this->attributes['textsize']['type'] as $tsize) {
									?>
													<a id="jsn-selector-<?php echo $tsize; ?>" title="<?php echo JText::_('JSN_TPLFW_SITETOOLS_SELECT_TEXTSIZE').': '.JText::_('JSN_TPLFW_STYLE_FONTSIZE_' . $tsize); ?>" href="#" onclick="javascript: JSNUtils.setTemplateAttribute('<?php echo $this->templatePrefix ?>','textsize','<?php echo $tsize; ?>'); return false;"<?php echo ($tsize == $this->textSize)?' class="current"':''; ?>></a>
									<?php
											}
										}
										if ($this->widthSelector) {
											foreach ($this->attributes['width']['type'] as $tw) {
									?>
													<a id="jsn-selector-<?php echo $tw; ?>" title="<?php echo JText::_('JSN_TPLFW_SITETOOLS_SELECT_WIDTH').': '.JText::_('JSN_TPLFW_LAYOUT_' . $tw); ?>" href="#" onclick="javascript: JSNUtils.setTemplateAttribute('<?php echo $this->templatePrefix ?>','width','<?php echo $tw; ?>'); return false;"<?php echo ($tw == $this->layoutWidth)?' class="current"':''; ?>></a>
									<?php
											}
										}
									?>
												</li>
								<?php
									}
									if ($this->colorSelector) {
								?>
												<li class="clearafter jsn-selector-color">
									<?php
										foreach ($this->templateColors as $tcolor) {
									?>
													<a id="jsn-selector-<?php echo $tcolor; ?>" title="<?php echo JText::_('JSN_TPLFW_SITETOOLS_SELECT_COLOR').': '.JText::_('JSN_TPLFW_COLOR_' . $tcolor); ?>" href="#" onclick="javascript: JSNUtils.setTemplateAttribute('<?php echo $this->templatePrefix ?>','color','<?php echo $tcolor; ?>'); return false;" <?php echo ($tcolor == $this->templateColor)?' class="current"':''; ?>></a>
									<?php
										}
									?>
												</li>
								<?php
									}
								?>
							</ul>
						</li>
					</ul>
				</div>
						<?php
							}

							/*====== Show button to jump to mobile view if user is using mobile device ======*/
							if ($this->desktopSwitcher) {
						?>
							<span id="jsn-desktopswitch">
								<a href="#" onclick="javascript: JSNUtils.setTemplateAttribute('<?php echo $this->templatePrefix ?>','mobile','no'); return false;"></a>
							</span>
							<span id="jsn-mobileswitch">
								<a href="#" onclick="javascript: JSNUtils.setTemplateAttribute('<?php echo $this->templatePrefix ?>','mobile','yes'); return false;"></a>
							</span>
						<?php
							}

							/*====== Show modules in position "toolbar" ======*/
							if ($this->countModules('toolbar') > 0) {
						?>
							<div id="jsn-pos-toolbar">
								<jdoc:include type="modules" name="toolbar" style="jsnmodule" />
							</div>
						<?php
							}
						?>
						<div class="clearbreak"></div>
					</div>
					</div>
				</div>
				<?php
					}
				?>
				</div>
			</div>
		</div>
		<div id="jsn-body">
			<div id="jsn-body-inner">
			<?php
				/*====== Show modules in content top area ======*/
				if ($this->helper->countPositions('promo-left', 'promo', 'promo-right')) {
			?>
				<div id="jsn-promo" class="<?php echo (($this->hasPromoLeft)?'jsn-haspromoleft ':'') ?><?php echo (($this->hasPromoRight)?'jsn-haspromoright ':'') ?>">
				<?php
	
					/*====== Show modules in position "promo" ======*/
					if ($this->countModules('promo') > 0) {
				?>
						<div id="jsn-pos-promo">
							<div id="jsn-pos-promo_inner">
								<jdoc:include type="modules" name="promo" style="jsnmodule" />
							</div>
						</div>
				<?php
					}
	
					/*====== Show modules in position "promo-left" ======*/
					if ($this->countModules('promo-left') > 0) {
				?>
						<div id="jsn-pos-promo-left">
							<jdoc:include type="modules" name="promo-left" style="jsnmodule" />
						</div>
				<?php
					}
	
					/*====== Show modules in position "promo-right" ======*/
					if ($this->countModules('promo-right') > 0) {
				?>
						<div id="jsn-pos-promo-right">
							<jdoc:include type="modules" name="promo-right" style="jsnmodule" />
						</div>
				<?php
					}
				?>
					<div class="clearbreak"></div>
				</div>	
			<?php
				}
			?>
			<?php
				/*====== Show modules in position "content-top" ======*/
				if ($this->countModules('content-top') > 0) {
			?>
				<div id="jsn-content-top" class="jsn-modulescontainer jsn-horizontallayout jsn-modulescontainer<?php echo $this->countModules('content-top'); ?>">
					<div id="jsn-pos-content-top">
						<jdoc:include type="modules" name="content-top" style="jsnmodule" />
						<div class="clearbreak"></div>
					</div>
				</div>
				<div class="clearbreak"></div>
			<?php
				}
			?>
				
				<div id="jsn-content" class="<?php echo (($this->hasLeft)?'jsn-hasleft ':'') ?><?php echo (($this->hasRight)?'jsn-hasright ':'') ?><?php echo (($this->hasInnerLeft)?'jsn-hasinnerleft ':'') ?><?php echo (($this->hasInnerRight)?'jsn-hasinnerright ':'') ?>">
					<div id="jsn-content_inner">
						<div id="jsn-maincontent">
							<div id="jsn-centercol">
								<div id="jsn-centercol_inner">
									<?php
										/*====== Show modules in position "breadcrumbs" ======*/
										if ($this->countModules('breadcrumbs') > 0) {
									?>
									<div id="jsn-breadcrumbs">
										<jdoc:include type="modules" name="breadcrumbs" />
									</div>
									<?php
										}

										/*====== Show modules in position "user-top" ======*/
										if ($this->countModules('user-top') > 0) {
									?>
									<div id="jsn-pos-user-top" class="jsn-modulescontainer jsn-horizontallayout jsn-modulescontainer<?php echo $this->countModules('user-top'); ?>">
										<jdoc:include type="modules" name="user-top" style="jsnmodule" />
										<div class="clearbreak"></div>
									</div>
									<?php
										}
	
									/*====== Show modules in position "user1" and "user2" ======*/
									$positionCount = $this->helper->countPositions('user1', 'user2');
									if ($positionCount)
									{
										$grid_suffix = $positionCount;
									?>
									<div id="jsn-usermodules1" class="jsn-modulescontainer jsn-modulescontainer<?php echo $grid_suffix; ?>">
										<div id="jsn-usermodules1_inner_grid<?php echo $grid_suffix; ?>">
									<?php
										/*====== Show modules in position "user1" ======*/
										if ($this->countModules('user1') > 0) {
									?>
										<div id="jsn-pos-user1">
											<jdoc:include type="modules" name="user1" style="jsnmodule" />
										</div>
									<?php
										}
	
									/*====== Show modules in position "user2" ======*/
									if ($this->countModules('user2') > 0) {
									?>
											<div id="jsn-pos-user2">
												<jdoc:include type="modules" name="user2" style="jsnmodule" />
											</div>
									<?php
										}
									?>
											<div class="clearbreak"></div>
										</div>
									</div>
									<?php
										}
									?>
									<div id="jsn-mainbody-content" class="<?php echo (($this->hasInnerLeft)?'jsn-hasinnerleft ':'') ?><?php echo (($this->hasInnerRight)?'jsn-hasinnerright ':'') ?><?php echo ($this->countModules('mainbody-top') > 0)?' jsn-hasmainbodytop':'' ?><?php echo ($this->countModules('mainbody-bottom') > 0)?' jsn-hasmainbodybottom':'' ?><?php echo ($this->showFrontpage)?' jsn-hasmainbody':'' ?>">
										<div id="mainbody-content-inner1"><div id="mainbody-content-inner2"><div id="mainbody-content-inner3"><div id="mainbody-content-inner4">
											<div id="mainbody-content-inner">
												<?php
													/*====== Show modules in position "mainbody-top" ======*/
													if ($this->countModules('mainbody-top') > 0) {
												?>
												<div id="jsn-pos-mainbody-top" class="jsn-modulescontainer jsn-horizontallayout jsn-modulescontainer<?php echo $this->countModules('mainbody-top'); ?>">
													<jdoc:include type="modules" name="mainbody-top" style="jsnmodule" />
													<div class="clearbreak"></div>
												</div>
												<?php
													}
										
													/*====== Show mainbody ======*/
													if ($this->showFrontpage) {
												?>
												<div id="jsn-mainbody">
													<jdoc:include type="message" />
													<jdoc:include type="component" />
												</div>
												<?php
													}
													/*====== Show modules in position "mainbody-bottom" ======*/
													if ($this->countModules('mainbody-bottom') > 0) {
												?>
												<div id="jsn-pos-mainbody-bottom" class="jsn-modulescontainer jsn-horizontallayout jsn-modulescontainer<?php echo $this->countModules('mainbody-bottom'); ?>">
													<jdoc:include type="modules" name="mainbody-bottom" style="jsnmodule" />
													<div class="clearbreak"></div>
												</div>
												<?php
													}
												?>
											</div>
											<?php
												/*====== Show modules in position "innerleft" ======*/
												if ($this->countModules('innerleft') > 0) {
											?>
															<div id="jsn-pos-innerleft">
																<div id="jsn-pos-innerleft_inner">
																	<jdoc:include type="modules" name="innerleft" style="jsnmodule" />
																</div>
															</div>
															
											<?php
												}
									
												/*====== Show modules in position "innerright" ======*/
												if ($this->countModules('innerright') > 0) {
											?>
															<div id="jsn-pos-innerright">
																<div id="jsn-pos-innerright_inner">
																	<jdoc:include type="modules" name="innerright" style="jsnmodule" />
																</div>
															</div>
											<?php
												}
											?>
											<div class="clearbreak"></div>
										</div></div></div></div>
									</div>
			<?php
				/*====== Show modules in position "user3" and "user4" ======*/
				$positionCount = $this->helper->countPositions('user3', 'user4');
				if ($positionCount) {
					$grid_suffix = $positionCount;
			?>
									<div id="jsn-usermodules2" class="jsn-modulescontainer jsn-modulescontainer<?php echo $grid_suffix; ?>">
										<div id="jsn-usermodules2_inner_grid<?php echo $grid_suffix; ?>">
				<?php
					/*====== Show modules in position "user3" ======*/
					if ($this->countModules('user3') > 0) {
				?>
											<div id="jsn-pos-user3">
												<jdoc:include type="modules" name="user3" style="jsnmodule" />
											</div>
				<?php
					}
	
					/*====== Show modules in position "user4" ======*/
					if ($this->countModules('user4') > 0) { ?>
											<div id="jsn-pos-user4">
												<jdoc:include type="modules" name="user4" style="jsnmodule" />
											</div>
				<?php
					}
				?>
											<div class="clearbreak"></div>
										</div>
									</div>
			<?php
				}
	
				/*====== Show modules in position "user-bottom" ======*/
				if ($this->countModules('user-bottom') > 0) { ?>
									<div id="jsn-pos-user-bottom" class="jsn-modulescontainer jsn-horizontallayout jsn-modulescontainer<?php echo $this->countModules('user-bottom'); ?>">
										<jdoc:include type="modules" name="user-bottom" style="jsnmodule" />
										<div class="clearbreak"></div>
									</div>
			<?php
				}
	
				/*====== Show modules in position "banner" ======*/
				if ($this->countModules('banner') > 0) {
			?>
									<div id="jsn-pos-banner">
										<jdoc:include type="modules" name="banner" style="jsnmodule" />
									</div>
			<?php
				}
			?>
								</div>
							</div>
							<div class="clearbreak"></div>
							</div>
						</div>
			<?php
				/*====== Show modules in position "left" ======*/
				if ($this->countModules('left') > 0) {
			?>
						<div id="jsn-leftsidecontent">
							<div id="jsn-leftsidecontent_inner">
								<div id="jsn-pos-left">
									<jdoc:include type="modules" name="left" style="jsnmodule" />
								</div>
							</div>
						</div>
			<?php
				}
	
				/*====== Show modules in position "right" ======*/
				if ($this->countModules('right') > 0) {
			?>
						<div id="jsn-rightsidecontent">
							<div id="jsn-rightsidecontent_inner">
								<div id="jsn-pos-right">
									<jdoc:include type="modules" name="right" style="jsnmodule" />
								</div>
							</div>
						</div>
			<?php
				}
			?>
					<div class="clearbreak"></div></div>
				</div>
			</div>
			
			<?php
			/*====== Show elements in content bottom area ======*/
			if ($this->helper->countPositions('content-bottom', 'user5', 'user6', 'user7')) {
			?>
				<div id="jsn-content-bottom" class="clearafter">
					<div id="jsn-content-bottom-inner">
					<?php
						/*====== Show modules in position "content-bottom" ======*/
						if ($this->countModules('content-bottom') > 0) {
					?>
						<div id="jsn-pos-content-bottom" class="jsn-modulescontainer jsn-horizontallayout jsn-modulescontainer<?php echo $this->countModules('content-bottom'); ?> clearafter">
							<jdoc:include type="modules" name="content-bottom" style="jsnmodule" />
						</div>
					<?php
						}
		
						/*====== Show modules in position "user5", "user6", "user7" ======*/
						$positionCount = $this->helper->countPositions('user5', 'user6', 'user7');
						if ($positionCount) {
							$grid_suffix = $positionCount;
					?>
						<div id="jsn-usermodules3" class="jsn-modulescontainer jsn-modulescontainer<?php echo $grid_suffix; ?>">
						<?php
							/*====== Show modules in position "user5" ======*/
							if ($this->countModules('user5') > 0) {
						?>
							<div id="jsn-pos-user5">
								<jdoc:include type="modules" name="user5" style="jsnmodule" />
							</div>
						<?php
							}
		
							/*====== Show modules in position "user6" ======*/
							if ($this->countModules('user6') > 0) {
						?>
							<div id="jsn-pos-user6">
								<jdoc:include type="modules" name="user6" style="jsnmodule" />
							</div>
						<?php
							}
		
							/*====== Show modules in position "user7" ======*/
							if ($this->countModules('user7') > 0) {
						?>
							<div id="jsn-pos-user7">
								<jdoc:include type="modules" name="user7" style="jsnmodule" />
							</div>
						<?php
							}
						?>
							<div class="clearbreak"></div>
						</div>
					<?php
						}
					?>
					</div>
				</div>
			<?php
				}
			?>
			<?php
				/*====== Show modules in position "footer" and "bottom" ======*/
				$positionCount = $this->helper->countPositions('footer', 'bottom');
				if ($positionCount) {
					$grid_suffix = $positionCount;
			?>
				<div id="jsn-footer">
					<div id="jsn-footer-inner">
						<div id="jsn-footermodules" class="jsn-modulescontainer jsn-modulescontainer<?php echo $grid_suffix; ?>">
							<?php
								/*====== Show modules in position "footer" ======*/
								if ($this->countModules('footer') > 0) {
							?>
								<div id="jsn-pos-footer">
									<jdoc:include type="modules" name="footer" style="jsnmodule" />
								</div>
							<?php
								}
			
								/*====== Show modules in position "bottom" ======*/
								if ($this->countModules('bottom') > 0) {
							?>
								<div id="jsn-pos-bottom">
									<jdoc:include type="modules" name="bottom" style="jsnmodule" />
								</div>
							<?php
								}
							?>
							<div class="clearbreak"></div>
						</div>
					</div>
				</div>
			<?php
				}
			?>
		</div>
	
	<?php
		/*====== Show "Go to top" link ======*/
		if ($this->gotoTop) {
			// Get rid of hitcount=0 that may prevent the button's action
			$hitcount = JRequest::getVar('hitcount', null, 'GET');
			if ($hitcount === null) {
				$this->uri->delVar('hitcount');
			}

			$return = $this->uri->toString();
	?>
		<a id="jsn-gotoplink" href="#top">
			<span><?php echo JText::_('JSN_TPLFW_GOTO_TOP'); ?></span>
		</a>
	<?php
		}
	
		/*====== Show modules in position "background" ======*/
		if ($this->countModules('background') > 0) {
	?>
			<div id="jsn-pos-background">
				<jdoc:include type="modules" name="background" style="jsnmodule" />
			</div>
	<?php
		}
?>
<jdoc:include type="modules" name="debug" />
<?php
	/*====== Show analytics code configured in template parameter ======*/
	if ($this->codePosition == 1) {
		echo $this->codeAnalytic;
	}
?>
</body>
</html>
