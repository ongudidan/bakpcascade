/**
 * @version    $Id$
 * @package    JSNPoweradmin
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */
var JSNAdminBar = new Class
({
	/**
	 * Class initialize
	 * @param options
	 */
	initialize: function (options)
	{
		var windowSize = $(window).getSize();
		var maxSupportedWindowSize	= 975;
		if (windowSize.x < maxSupportedWindowSize) 
		{
			$('header-box').show();
			$('border-top').show();	
			return;			
		}
		
		this.uiHelper			= new JSNAdminBarUIHelper();
		var self = this;
		this.options = options;
		try{
			this.uiHelper.beforeRenderHook();
		}catch (e) {
			// TODO: handle exception
		}
		
		this.createUI();
		try{
			this.uiHelper.afterCreateUiHook();
		}catch (e) {
			// TODO: handle exception
		}
		this.addEvents();
		
		this.preloadImages();
		
		this.sessionTime = parseInt(Cookie.read('last-request-time'));
		this.lifetime    = parseInt(Cookie.read('session-life-time'));
		
		// Start configuration monitor
		this.monitorInterval = setInterval(function () {
			var sessionTime = parseInt(Cookie.read('last-request-time'));
			if (sessionTime != self.sessionTime) {
				self.sessionTime 				= sessionTime;
				self.lifetime    				= parseInt(Cookie.read('session-life-time'));				
				self.options.warningTime		= parseInt(Cookie.read('session-warning-time'));
				self.options.disableWarning		= parseInt(Cookie.read('session-disable-warning')) === 1;
				
				clearInterval(self.countdownInterval);				
				self.startCountdown();					
								
			}
		}, 5000);
		
		
		this.lastRequestTime = parseInt(Cookie.read('last-request-time'));
		this.startCountdown();
				
		try{
			this.uiHelper.afterRenderHook();
		}catch (e) {
			// TODO: handle exception
		}
	},
	
	preloadImages: function () {
		var baseUrl = this.options.rootUrl + 'plugins/system/jsnpoweradmin/assets/images/';
		if (this.options.preloadImages !== undefined) {
			var imageList = new Array();
			this.options.preloadImages.each(function (name) {
				var image = new Image();
				image.src = baseUrl + name;
				
				imageList.push(image);
			});
		}
	},
	
	/**
	 * Create user interface for admin bar
	 * @return void
	 */
	createUI: function ()
	{
		var self = this;	
		this.bodyWrapper 		= $('jsn-body-wrapper');
		this.body 				= $(document.body);
		this.toolbarWrapper     = $('jsn-adminbar');

		this.body.addClass((this.options.pinned == true) ? 'jsn-adminbar-pinned' : 'jsn-adminbar-notpinned');

		// Copy styles of body to body wrapper element
		this.bodyWrapper.setStyles({
			paddingLeft 	: parseInt(this.body.getStyle('padding-left')) + parseInt(this.body.getStyle('margin-left')),
			paddingRight 	: parseInt(this.body.getStyle('padding-right')) + parseInt(this.body.getStyle('margin-right')),
			paddingTop 		: parseInt(this.body.getStyle('padding-top')) + parseInt(this.body.getStyle('margin-top')),
			paddingBottom 	: parseInt(this.body.getStyle('padding-bottom')) + parseInt(this.body.getStyle('margin-bottom')),
			borderLeft		: this.body.getStyle('border-left'),
			borderRight		: this.body.getStyle('border-right'),
			borderTop		: this.body.getStyle('border-top'),
			borderBottom	: this.body.getStyle('border-bottom')
		});

		var background = this.body.getStyle('background');
		if (background !== undefined) {
			this.bodyWrapper.setStyle('background', background);
		}

		// Reset styles for body element
		this.body.setStyles({
			margin: 0,
			padding: 0,
			border: 0,
			backgroundImage: 'none'
		});
		
		//this.headerbox 	= new Element('div', { id: 'jsn-adminbar-headerbox' });
		this.toolbar 	= new Element(Mooml.render('admin-toolbar', this.options));
		this.toolbar.inject($('jsn-adminbar'), 'top');

		// Append menu bar
		this.menubar	= this.toolbar.getElementById('jsn-adminbar-mainmenu');
		this.menubar.grab(this.uiHelper.getMenuPosition());

		if (this.options.logoFile !== undefined && this.options.logoFile != 'N/A') {
			// Declare logo position
			this.logo = this.toolbar.getElement('#jsn-adminbar-logo');
			this.loadLogo();
		}


		this.statusPosition 	= this.toolbar.getElement('#module-status');
		this.moduleStatus = this.uiHelper.getStatusPosition();
		this.siteMenuStatus = this.toolbar.getElement('#jsn-adminbar-sitemenu-status');

		if (this.moduleStatus != null) {
			var loggedInItem = this.moduleStatus.getElement('.loggedin-users'),
				backLoggedInItem = this.moduleStatus.getElement('.backloggedin-users');

			if (loggedInItem != null) {
				var loggedInIcon = loggedInItem.getStyle('background-image');

				loggedInItem.setStyle('background-image', loggedInIcon);
				this.siteMenuStatus.grab(loggedInItem);
			}

			if (backLoggedInItem) {
				var backLoggedInIcon = loggedInItem.getStyle('background-image');

				backLoggedInItem.setStyle('background-image', backLoggedInIcon);
				this.siteMenuStatus.grab(backLoggedInItem);
			}
		}

		if (this.moduleStatus != null) {
			var self = this;
			this.moduleStatus.getChildren().each(function (element) {
				if (element.id == 'user-status' || 
					element.hasClass('loggedin-users') || 
					element.hasClass('backloggedin-users') || 
					element.hasClass('no-unread-messages') ||
					element.hasClass('viewsite') ||
					element.hasClass('logout')) {
					return;
				}
				
				element.inject(self.statusPosition);
			});
		}
		
		this.spotlightBox = this.toolbar.getElement('#jsn-adminbar-spotlight');
		this.spotlightInput = this.spotlightBox.getElement('input');
		this.spotlightPlaceHolder = this.spotlightBox.getElement('span.placeholder');
		this.spotlightCloseButton = this.spotlightBox.getElement('a.close');
		this.spotlightItems = this.spotlightBox.getElement('div.items');
		
		this.historyButton = this.toolbar.getElement('#jsn-adminbar-history-button');
		this.historyItems = this.historyButton.getElement('div.items');
		
		this.toolbarWrapper.setStyle('display', 'block');
		if (this.options.pinned == true) {
			this.bodyWrapper.setStyle('margin-top', 40);
		}

		this.toolbarWrapper.addClass(JoomlaShine.defaultTemplate);
		this.addMenus();
		this.refresh();
		
		
		this.dispatchMenuBarPosition('jsn-adminbar');
		
	},

	loadLogo: function () {
		if (this.options.logoFile === undefined || this.options.logoFile == null || this.options.logoFile == '' || this.options.logoFile == 'N/A') {
			return;
		}

		// Load logo file
		var self = this,
			logoImage = new Element('img');
		
		logoImage.src = this.options.logoFile;
		logoImage.addEvent('load', function () {
			this.setStyles({ 'visibility': 'hidden', 'position'  : 'absolute' });
			this.inject(self.body);

			// Get real size of image
			var logoSize = this.getSize();
			if (logoSize.y > 24) {
				this.setStyle('height', 24);
			}

			if (self.options.logoLink != '' && self.options.logoLink != 'N/A') {
				var logoLink = new Element('a', { 'href': self.options.logoLink, 'target': self.options.logoLinkTarget, 'title': self.options.logoTitle });
				logoLink.grab(this);
				logoLink.inject(self.logo);
			}
			else {
				// Inject image to logo position
				this.inject(self.logo);
			}

			this.setStyles({
				visibility: 'visible',
				position: 'relative'
			})
		});
	},
	
	/**
	 * Register events for elements on admin toolbar
	 * @return void
	 */
	addEvents: function ()
	{
		var self 			= this,
			dropdownMenus 	= this.toolbar.getElements('.jsn-adminbar-menu-dropdown'),
			dropdownButtons = this.toolbar.getElements('.jsn-adminbar-button-dropdown');
		
		this.toolbar.getElements('.jsn-adminbar-menu-dropdown').addEvent('mouseenter', function () {
			self.abortSpotlightProgress();
			self.setSpotlightState((self.spotlightKeyword !== undefined && self.spotlightKeyword != '') ? 'inactive' : 'closed');
		});
		
		// User profile link
		this.toolbar.getElement('#jsn-adminbar-usermenu-profile a')
			.addEvent('click', function (e) {
				e.stop();
				self.openProfile(); 
			});
			
		// History button
		var historyButton = this.historyButton.getElement('.jsn-history-button-wrapper');
		historyButton.addEvent('click', function (e) {
			self.abortSpotlightProgress();
			self.setSpotlightState((self.spotlightKeyword !== undefined && self.spotlightKeyword != '') ? 'inactive' : 'closed');
			
			self.historyButton
				.toggleClass('active')
				.getElement('ul');

			historyButton
				.addEvent('click', function (e) {
					e.stop();
				});

			// Retrieve data from server when button is active
			if (self.historyButton.hasClass('active')) {
				self.loadHistory(self.historyButton);
			}
			
			e.stop();
		});
		
		// Spotlight events
		self.spotlightPlaceHolder.addEvent('click', function (e) {
			self.setSpotlightState('init');
			e.stop();
		});
		
		self.spotlightInput.addEvents({
			'click': function (e) {
				if (self.spotlightKeyword !== undefined && self.spotlightKeyword != null && self.spotlightKeyword != '') {
					self.setSpotlightState('active');
				}

				e.stop();
			},
			
			'keyup': function (e) {				
				var newKeyword = self.spotlightInput.get('value').trim();
				if(newKeyword.length < 3){
					self.spotlightBox.removeClass('has-results');
					self.setSpotlightState('active');
					return false;
				}
				if (newKeyword != self.spotlightKeyword) {
					self.spotlightKeyword = newKeyword;
					self.spotlightItems
						.getElements('li.item, li.group')
							.destroy();
					
					self.spotlightBox
						.removeClass('has-results')
						.removeClass('no-results');
					
					if (newKeyword == '') {
						self.abortSpotlightProgress();
						self.setSpotlightState('init');
						return;
					}
					
					self.resetSpotlight();
					self.loadSpotlightResults();
				}
			}
		});
		
		self.spotlightCloseButton.addEvent('click', function (e) {
			self.setSpotlightState('closed');
			self.spotlightKeyword = undefined;
			e.stop();
		});
		
		self.spotlightInput.addEvent('focus', function (e) {
			dropdownButtons.removeClass('active');
		});
		
		// Mouse over event for dropdown menus to close all active dropdown buttons
		dropdownMenus.addEvent('mouseover', function () {
			dropdownButtons.removeClass('active');
		});
		
		// Close dropdown buttons when clicked to outside of button
		$(document).addEvents({
			'click': function (e) {
				if((typeof(self.spotlightScrollbar) != 'undefined' && e.target != self.spotlightScrollbar.handle) 
					|| typeof(self.spotlightScrollbar) == 'undefined'){
					
					self.setSpotlightState((self.spotlightKeyword !== undefined && self.spotlightKeyword != '') ? 'inactive' : 'closed');
					dropdownButtons.removeClass('active');
				}								
			},
			'touchstart': function (e) {
				self.setSpotlightState((self.spotlightKeyword !== undefined && self.spotlightKeyword != '') ? 'inactive' : 'closed');
				dropdownButtons.removeClass('active');
			},
			'keydown': function (e) {
				var activeDropdownButton = self.toolbar.getElement('.jsn-adminbar-button-dropdown.active, #jsn-adminbar-spotlight.state-done.has-results');
				if (activeDropdownButton != null && ['up', 'down', 'enter'].indexOf(e.key) != -1) {
					self.navigateItem(activeDropdownButton, e);
					e.stop();
				}
				
				if (['esc'].indexOf(e.key) != -1) {										
					self.setSpotlightState((self.spotlightKeyword !== undefined && self.spotlightKeyword != '') ? 'inactive' : 'closed');
					dropdownButtons.removeClass('active');					
				} 
					
			}
		});
		
		this.spotlightItems.addEvents({
			'touchstart': function (e){
				e.stopPropagation();
			}
		});
		this.spotlightBox.addEvents({
			'touchstart': function (e){
				e.stopPropagation();
			}
		});
		
		$(window).addEvent('resize', function () { self.refresh(); });
		$(window).addEvent('scroll', function () { self.refresh(); });
	},
	
	resetSpotlight: function ()
	{
		this.spotlightItems.getElements('li.item').destroy();
		this.spotlightItems.getElements('li.group').destroy();
		this.spotlightItems.getElements('li.spotlight-notice').destroy();
		this.spotlightItems.setStyle('height', 'auto');
		
		if (!/iPhone|iPod|iPad/.test(navigator.userAgent)) {
		if (this.spotlightScrollbar !== undefined) {
			this.spotlightScrollbar.refresh();
			this.spotlightScrollbar.scrollToPosition('top');
		}
		}

	},
	
	/**
	 * Set current state for spotlight box
	 * @param state
	 * @param clearValue
	 * @return void
	 */
	setSpotlightState: function (state, clearValue)
	{
		if (this.spotlightState !== undefined) {
			this.spotlightBox.removeClass('state-' + this.spotlightState);
		}
		
		this.spotlightState = state;
		this.spotlightBox.addClass('state-' + this.spotlightState);
		
		if (state == 'init') {
			this.spotlightInput.set('value', '');
			this.spotlightInput.focus();
		}

		// Clear result items
		if (state == 'closed') {
			this.spotlightItems
				.getElements('li.item, li.group')
				.destroy();
		}
		
		this.refreshSpotlightSize();
	},
	
	/**
	 * Retrieve results for the keyword to display on spotlight box
	 * @param index
	 * @return void
	 */
	loadSpotlightResults: function (index) 
	{
		var coverageIndex 	= index || 0,
			coverage		= this.options.searchCoverages[coverageIndex] || null,
			self			= this;
		
		if (coverage == null) {
			this.spotlightBox.removeClass('has-results no-results');
			this.spotlightBox.addClass((this.spotlightItems.getElements('li.item').length > 0) ? 'has-results' : 'no-results');
			this.setSpotlightState('done');
			return;
		}
		
		this.setSpotlightState('loading');
		this.abortSpotlightProgress();
		
		if (coverage == 'adminmenus') {
			var result = this.lookupMenus(this.spotlightKeyword.toLowerCase());
			if (result.length > 0) {
				$(Mooml.render('admin-spotlight-item', {
					title: JoomlaShine.language.JSN_ADMINBAR_ADMINMENUS,
					type: 'group',
					hasMore: false
				})).inject(self.spotlightItems.getElement('li.jsn-loading'), 'before');
				
				result.each(function (item) {
					item.keyword = self.spotlightKeyword;
					item.target  = self.options.linkTarget;
					item.title   = self.highlight(item.title, self.spotlightKeyword);
						
					$(Mooml.render('admin-spotlight-item', item)).inject(self.spotlightItems.getElement('li.jsn-loading'), 'before');
				});
			}
			
			self.refreshSpotlightSize();
			self.loadSpotlightResults(coverageIndex + 1);
			return;
		}

		this.spotlightRequest = new Request.JSON({
			url: 'index.php',
			onSuccess: function (response) {
				if (response.length > 0) {
					response.each(function (item) {
						item.keyword = self.spotlightKeyword;
						item.target  = self.options.linkTarget;

						if (item.type == 'item') {
							item.title   = self.highlight(item.title, self.spotlightKeyword);
							if(item.checkedOut > 0){
								item.title += '<i class="icon-lock"></i>';
							}
						}
												
						$(Mooml.render('admin-spotlight-item', item)).inject(self.spotlightItems.getElement('li.jsn-loading'), 'before');
					});
				}
				
				self.refreshSpotlightSize();
				self.loadSpotlightResults(coverageIndex + 1);
			}
		})
		.get({
			option: 'com_poweradmin',
			task: 'search.json',
			keyword: this.spotlightKeyword,
			coverages: coverage
		});
	},
	
	lookupMenus: function (keyword)
	{
		var matchedMenu = [];
		var parseMenuItem = function (menuItem, hasParent) {
			var menuLink = menuItem.getElement('a');
			
			if (menuLink !== null) {
				var menuText = menuLink.get('text').trim() + '';

				if (menuText != null && menuText.toLowerCase().indexOf(keyword) != -1) {
					var parent = hasParent !== undefined && hasParent == true ? menuItem.getParent().getPrevious() : null;
					
					var item = {
						title: menuText,
						link: menuLink.getProperty('href'),
						icon: menuLink.getProperty('class'),
						type: 'item',
						description: parent != null ? JoomlaShine.language.JSN_ADMINBAR_PARENT_MENUS + parent.get('text') : ''
					};
					
					matchedMenu.push(item);
				}
			}
		};
		
		this.menubar
			.getElement('ul')
			.getChildren().each(function (rootMenu) {
				var children = rootMenu.getElement('ul');
				if (children != null) {
					children.getChildren().each(function (menuItem) {
						parseMenuItem(menuItem);
						
						var submenu = menuItem.getElement('ul');
						if (submenu != null) {
							submenu.getChildren().each(function (submenuItem) {
								parseMenuItem(submenuItem, true);
							});
						}
					});
				}
			});
		
		return matchedMenu.slice(0, this.options.spotlight.limit);
	},
	
	refreshSpotlightSize: function ()
	{
		var contentSize = this.spotlightItems.getElement('ul').getSize();
		var containerSize = this.spotlightItems.getSize();
		var windowSize = windowSize = $(window).getSize();
		
		if (containerSize.y < contentSize.y) {
			this.spotlightItems.setStyle('height', (contentSize.y > windowSize.y - 100) ? windowSize.y - 100 : contentSize.y);
		}
		else {
			this.spotlightItems.setStyle('height', contentSize.y);
		}
		
		if (!/iPhone|iPod|iPad/.test(navigator.userAgent)) {
		if (this.spotlightScrollbar === undefined) {
			this.spotlightScrollbar = new JSNScrollbar(this.spotlightItems);
			this.spotlightBox.store('scrollbar', this.spotlightScrollbar);
		}
		
		this.spotlightScrollbar.refresh();
		}
		
	},
	
	/**
	 * Stop search progress for spotlight
	 * @return void
	 */
	abortSpotlightProgress: function ()
	{
		if (this.spotlightTimeout !== undefined) {
			clearTimeout(this.spotlightTimeout);
		}
		
		if (this.spotlightRequest !== undefined && 
			instanceOf(this.spotlightRequest, Request.JSON) == true) {
			this.spotlightRequest.cancel();
		}
	},
	
	/**
	 * Add uninstall menu to removable extensions
	 * @return void
	 */
	addMenus: function ()
	{
		var self = this;
		var components = this.uiHelper.getComponentMenu(this.menubar);//this.menubar.getChildren()[4].getElement('ul').getChildren();
		var extensions = this.uiHelper.getExtensionMenu(this.menubar);

		if (components != null && this.options.allowUninstall == true) {
			components.each(function (menu) {
				var elm = menu.getElement('a');
				if (elm == null) { return; }

				var link = elm.getProperty('href');
				var caption = elm.get('text');
				var params = link.substring(link.indexOf('?')+1).parseQueryString();
				
				// Add uninstall menu if component is unprotected
				if (self.options.protected.indexOf(params['option']) == -1)
				{
					var submenu = menu.getElement('ul');
					if (submenu == null) {
						submenu = self.uiHelper.createMenuContainer();
						submenu.inject(menu);
						
						menu.addClass('node');
						menu.addClass('has-child');
						self.uiHelper.formatParentMenu(menu);
					}
					
					var hasChildren = submenu.getChildren().length > 0;
					if (hasChildren) {
						submenu.adopt(self.uiHelper.getMenuSeparator());
					}

					var uninstallMenu = self.uiHelper.createMenuItem(
						JoomlaShine.language.JSN_ADMINBAR_UNINSTALL, 
						'index.php?option=com_poweradmin&task=removeExtension&component=' + params['option'], 
						'_parent', 
						'icon-16-uninstall'
					);

					uninstallMenu
						.inject(submenu)
						.getElement('a').addEvent('click', function (e) {
							e.stop();
							var element = this;
							var answer = confirm(JoomlaShine.language.JSN_ADMINBAR_UNINSTALL_CONFIRM.replace('{component}', caption));
							if ( answer) {
								window.location = element.href;
							}else{
								return;
							}							
						});
				}
			});
		}
		
		// Create submenu for menu "Extensions"
		if (extensions != null) {
			extensions.each(function (menu) {
				var element	= menu.getElement('a');
				if (element == null) {
					return true;
				}
				
				var link	= element.getProperty('href') + '';
				var params 	= link.substring(link.indexOf('?')+1).parseQueryString();
				
				if (params['option'] == 'com_templates') {
					self.addTemplateMenus(menu);
				}
				
				if (params['option'] == 'com_installer') {
					self.addExtensionMenus(menu);
				}
			});
		}
	},
	
	/**
	 * make menu be absolute if the submenu list
	 * is longer than the window height
	 * @param menuWrapper : menubar wrapper id
	 * @param menuContainer: root menu ul id
	 */
	dispatchMenuBarPosition: function (menuWrapper, menuContainer)
	{
		var oddTopSpace			= 0;			
		var windowOffsetTop 	= 	window.pageYOffset;
		$$("#module-menu").getChildren('ul>li>ul').each(function (el){
			el.addEvent('mouseover',function (e){				
				var scrollableHeight 	= 	(window.innerHeight-20);
				if((this.getHeight()) >= scrollableHeight){
					$(menuWrapper).setStyle('position','absolute');
					if(window.pageYOffset < windowOffsetTop){
						$(menuWrapper).setStyle('top',window.pageYOffset+oddTopSpace);						
					}else{
						$(menuWrapper).setStyle('top',windowOffsetTop+oddTopSpace);						
					}
				}				
				$(this).addEvent('mouseleave',function (){
					$(menuWrapper).setStyle('top',oddTopSpace);
					$(menuWrapper).setStyle('position','fixed');
					windowOffsetTop	= $(this).getOffsets().y+10;					
				});
			});
		});
	},
	/**
	 * Add submenus for Template Manage menu of Joomla
	 * @param menu
	 * @return void
	 */
	addTemplateMenus: function (menu)
	{
		var buttons = {};
		var uOption = this.options.urlparams.option;
		var uView = this.options.urlparams.view;
		var uLayout = this.options.urlparams.layout;
		var uId = this.options.urlparams.id;
		var extParam = '';
		var refreshAfterClosing = false;
		
		// Add template and style ID to frame url
		// if it is being edited
		if (uOption == 'com_templates') {				
			if (uView == 'style' && uLayout == "edit") {				
				extParam = '&sid=' + uId;				
			}else if (uView == 'template') {
				extParam = '&tid=' + uId;					
			}else{
				refreshAfterClosing = true;
			}
		}else if (uOption == 'com_poweradmin' && uView == 'rawmode') {
			refreshAfterClosing = true;
		}
		
		buttons[JoomlaShine.language.JCLOSE] = function (ui) {
			ui.close();

			var currentUrl = window.location + '';
			if (refreshAfterClosing) {
				window.location.reload();
			}
		};

		var menuTemplate = this.uiHelper.createMenuItem(JoomlaShine.language.JSN_ADMINBAR_STYLES, 'index.php?option=com_poweradmin&view=templates', '_parent', 'icon-16-themes');
		menuTemplate.addEvent('click', function (e) {
			e.stop();
			var modal = new JSNWindow({
				handle: 'iframe',
				source: 'index.php?option=com_poweradmin&view=templates&tmpl=component' + extParam,
				width: 830,
				height: 730,
				title: JoomlaShine.language.JSN_ADMINBAR_STYLES_MANAGER,
				buttons: buttons,
				toolbarPosition: 'bottom'
			});
			
			modal.open();
		});
		
		this.menuStyles = new Element('li').adopt(menuTemplate);

		var container = menu.getElement('ul');
		if (container == null) {
			container = this.uiHelper.createMenuContainer();
		}
		else {
			container.adopt(this.uiHelper.getMenuSeparator())
		}
			
		container
			.adopt(this.menuStyles)
			.adopt(this.uiHelper.getMenuSeparator())
			.adopt(this.uiHelper.createMenuItem(this.options.defaultStyles.site.title, 'index.php?option=com_templates&task=style.edit&id=' + this.options.defaultStyles.site.id, this.options.linkTarget, 'icon-16-default'))
			.adopt(this.uiHelper.createMenuItem(this.options.defaultStyles.admin.title, 'index.php?option=com_templates&task=style.edit&id=' + this.options.defaultStyles.admin.id, this.options.linkTarget, 'icon-16-default'))
			.inject(menu);
			
		menu.addClass('node');
		menu.addClass('has-child');
		this.uiHelper.formatParentMenu(menu);
	},
	
	/**
	 * Extension Manager submenus
	 * @param menu
	 * @return void
	 */
	addExtensionMenus: function (menu) 
	{
		menu.addClass('node');
		menu.addClass('has-child');

		this.uiHelper.formatParentMenu(menu);

		var container = this.uiHelper.createMenuContainer();
		container.adopt(this.uiHelper.createMenuItem(JoomlaShine.language.JSN_ADMINBAR_EXT_INSTALL, 'index.php?option=com_installer', this.options.linkTarget, 'icon-16-newarticle'));
		container.adopt(this.uiHelper.createMenuItem(JoomlaShine.language.JSN_ADMINBAR_EXT_MANAGE, 'index.php?option=com_installer&view=manage', this.options.linkTarget, 'icon-16-install'));
		container.adopt(this.uiHelper.createMenuItem(JoomlaShine.language.JSN_ADMINBAR_EXT_UPDATE, 'index.php?option=com_installer&view=update', this.options.linkTarget, 'icon-16-update'));
		container.inject(menu);
	},
	
	/**
	 * Run countdown for session lifetime
	 * @return void
	 */
	startCountdown: function ()
	{
		var self 			= this;
		var lifetime 		= this.lifetime * 60;
		var lefttime 		= lifetime;
		var userButton		= self.toolbar.getElement('#jsn-adminbar-user-button');
		var countdown 		= self.toolbar.getElement('#jsn-adminbar-usermenu-welcome .countdown');
		var icon 			= self.toolbar.getElement('#jsn-adminbar-user-button .jsn-icon-user');
		var warningTime		= self.options.warningTime * 60;
		var disableWarning 	= self.options.disableWarning;
		
		self.countdownInterval = setInterval(function () {
			var time = self.secondsToTime(lefttime, true);
			countdown.set('text', time['h'] + ':' + time['m'] + ':' + time['s']);
			userButton.addClass('has-countdown');

			if (lefttime == warningTime && disableWarning === false) {
				alert(JoomlaShine.language.JSN_ADMINBAR_TIMEOUT_WARNING.replace('{minutes}', Math.round(lefttime/60)));
			}
			
			if (lefttime == 0) {
				clearInterval(self.countdownInterval);
			}
			
			self.updateUserIcon(icon, lefttime, lifetime);
			lefttime--;
		}, 1000);
	},
	
	/**
	 * Stop and hide countdown
	 * @return void
	 */
	removeCountdown: function ()
	{
		var self 		= this;
		var countdown 	= self.toolbar.getElement('#jsn-adminbar-usermenu-welcome .countdown');
		
		countdown.setStyle('visibility', 'hidden');
		clearInterval(self.countdownInterval);
	},
	
	/**
	 * Update background position for user menu icon
	 * @param icon
	 * @param lefttime
	 * @param lifetime
	 */
	updateUserIcon: function (icon, lefttime, lifetime)
	{
		var remainingPercent = Math.round(lefttime/lifetime*100);
		var backgroundTop = 0;
					
		if (lefttime == 0)
			backgroundTop = 96;
		else if (remainingPercent < 10)
			backgroundTop = 80;
		else if(remainingPercent < 30)
			backgroundTop = 64;
		else if(remainingPercent < 50)
			backgroundTop = 48;
		else if(remainingPercent < 70)
			backgroundTop = 32;
		else if(remainingPercent < 90)
			backgroundTop = 16;
		else
			backgroundTop = 0;
			
		icon.setStyle('background-position', '0 -' + backgroundTop + 'px');
	},	
	
	/**
	 * Open user profile on modal box
	 * @return void
	 */
	openProfile: function ()
	{
		var buttons = {};
		buttons[JoomlaShine.language.JSAVE] = function (ui) {
			if (ui.processForm()) {
				ui.submitForm('form');
			}
		};
		
		buttons[JoomlaShine.language.JCANCEL] = function (ui) {
			ui.close();
		};
		
		new JSNWindow({
			source: 'index.php?option=com_admin&task=profile.edit&id=' + this.options.userId + '&tmpl=component&poweradmin=true',
			handle: 'iframe',
			
			injectCSS: this.options.rootUrl + 'plugins/system/jsnpoweradmin/assets/css/adminbar.extra.css',
			
			width: 900,
			height: 550,
			scrollable: true,
			title: JoomlaShine.language.JSN_ADMINBAR_EDIT_PROFILE,
			buttons: buttons,
			toolbarPosition: 'bottom',
			// Callback method to listen state of window
			stateChanged: function (ui) {
				if (ui.currentState == 'active') {
					var form = ui.getDocument().getElement('form');
					if (form == null) {
						return;
					}
					
					// Inject processForm method to set window state to process
					ui.processForm = function (e) {
						var password = form.getElement('input#jform_password').value,
							confirm  = form.getElement('input#jform_password2').value;

						if ((password != '' || confirm != '') && password != confirm) {
							form.getElement('input#jform_password')
								.getParent()
								.getChildren()
								.addClass('invalid');

							form.getElement('input#jform_password2')
								.getParent()
								.getChildren()
								.addClass('invalid');

							return false;
						}

						ui.setOptions({
							contentLoaded: function () {
								ui.close();
								return true;
							}
						});
						
						ui.setState('processing');
						return true;
					};
					
					form.getElements('input')
						.removeEvents('keydown')
						.addEvent('keydown', function (e) {
							if (e.key == 'enter') {
								ui.processForm(e);
							}
						});

					form.getElements('input#jform_email')
						.addEvent('keydown', function (e) {
							if (e.key == 'space') {
								e.preventDefault();
							}
						});

					form.getElements('#jform_username, input.readonly').setProperty('tabindex', '-1');
					
					// Add necessarily information to form
					form.getElement('input[name=task]').setProperty('value', 'profile.save');
				}
			}
		})
		.open();
	},
	
	/**
	 * Retrieve all history from server and show to browser as a list
	 * @param button Button clicked to load history
	 * @return void
	 */
	loadHistory: function (button)
	{
		var self = this;
		var list = button.getElement('ul');

		if (this.historyRequest !== undefined && instanceOf(this.historyRequest, Request.JSON) === true && this.historyRequest.isRunning()) {
			this.historyRequest.cancel();
		}
		
		if (list.getElements('li.item') !== null) {
			list.getElements('li.item').destroy();
		}
		
		this.setHistoryState('loading');
		this.historyRequest = new Request.JSON({
			url: self.options.history.url,
			onSuccess: function (data) {
				if (data.length == 0) {
					self.setHistoryState('done-empty');
				}
				else {
					data.each(function (item) {
						item.target = self.options.linkTarget;
						Mooml.render('admin-history-item', item).inject(list);
					});
					
					self.setHistoryState('done');
				}
				
				self.refreshHistorySize();
			}
		}).get();
	},
	
	refreshHistorySize: function ()
	{
		var contentSize = this.historyItems.getElement('ul').getSize();
		var containerSize = this.historyItems.getSize();
		var windowSize = windowSize = $(window).getSize();
		
		if (containerSize.y < contentSize.y || windowSize.y < contentSize.y) {
			this.historyItems.setStyle('height', (contentSize.y > windowSize.y - 100) ? windowSize.y - 100 : contentSize.y);
		}
		else {
			this.historyItems.setStyle('height', contentSize.y);
		}
		
		if (this.historyScrollbar === undefined) {
			this.historyScrollbar = new JSNScrollbar(this.historyItems);
			this.historyButton.store('scrollbar', this.historyScrollbar);
		}
		
		this.historyScrollbar.refresh();
	},
	
	/**
	 * Set current state of history button
	 * @param state
	 * @return void
	 */
	setHistoryState: function (state)
	{
		if (this.historyState !== undefined) {
			this.historyButton.removeClass('state-' + this.historyState);
		}
		
		this.historyState = state;
		this.historyButton.addClass('state-' + state);
		this.refreshHistorySize();
	},
	
	/**
	 * Navigate to item on list items by using keyboard
	 * @param button
	 * @param event
	 * @return void
	 */
	navigateItem: function (button, event)
	{
		var container = button.getElements('div.items');
		if (container === null) {
			return;
		}
		
		var items = button.getElements('div.items ul li.item');
		var current = button.getElement('div.items ul li.item.jsn-active');
		var currentIndex = -1;
		
		if (current != null) {
			currentIndex = items.indexOf(current);
		}
		
		switch (event.key) {
			case 'up':
				currentIndex--;
				if (currentIndex < 0) currentIndex = 0;
			break;
			
			case 'down':
				currentIndex++;
				if (currentIndex > items.length) currentIndex = items.length - 1;
			break;
			
			case 'enter':
				if (items[currentIndex] !== undefined) {
					var link = items[currentIndex].getElement('a');
					if (link != null) {
						window.location = link.getProperty('href');
					}
				}
			break;
		}

		if (items[currentIndex] !== undefined) {
			items.removeClass('jsn-active');
			items[currentIndex].addClass('jsn-active');
			
			if (container.hasClass('scrollable')) {
				var scrollbar = button.retrieve('scrollbar');
				if (scrollbar == null) {
					return;
				}
				
				var topHeight = items[currentIndex].getSize().y;
				items[currentIndex].getAllPrevious().each(function (element) {
					topHeight += element.getSize().y;
				});
				
				var currentOffset = scrollbar.getCurrentOffset();
				var viewportHeight = button.getElement('.viewport').getSize().y;
				var mustScrollDown = topHeight >= currentOffset + viewportHeight;
				
				if (mustScrollDown) {
					scrollbar.scrollToPosition(topHeight - viewportHeight);
				}
				else if (topHeight - items[currentIndex].getSize().y < currentOffset) {
					scrollbar.scrollToPosition(topHeight - items[currentIndex].getSize().y);
				}
			}
		}
	},
	
	/**
	 * Convert number of seconds into time object
	 * @param integer secs Number of seconds to convert
	 * @param boolean addZero
	 * @return object
	 */
	secondsToTime: function (secs, addZero)
	{
		var hours = Math.floor(secs / (60 * 60));
		
		var divisor_for_minutes = secs % (60 * 60);
		var minutes = Math.floor(divisor_for_minutes / 60);

		var divisor_for_seconds = divisor_for_minutes % 60;
		var seconds = Math.ceil(divisor_for_seconds);
		
		var isAddZero = (typeof(addZero) == 'undefined' || addZero == true);
		
		var obj = {
			"h": (hours < 10 && isAddZero) ? '0' + hours : hours,
			"m": (minutes < 10 && isAddZero) ? '0' + minutes : minutes,
			"s": (seconds < 10 && isAddZero) ? '0' + seconds : seconds
		};
		return obj;
	},
	
	/**
	 * Update toolbar size, position
	 * @return void
	 */
	refresh: function ()
	{
		this.toolbar.getParent().setStyle('width', '100%').setStyle('min-width', '960px');
		this.toolbar.setStyle('width', '100%');
		this.refreshSpotlightSize();
		this.refreshHistorySize();
	},

	/**
	 * This is the function that actually highlights a text string by
	 * adding HTML tags before and after all occurrences of the search
	 * term. You can pass your own tags if you'd like, or if the
	 * highlightStartTag or highlightEndTag parameters are omitted or
	 * are empty strings then the default <font> tags will be used.
	 * 
	 * @param String text Original text
	 * @param String keyword Keyword to highlight
	 * @param String startTag Start tag markup
	 * @param String endTag End tag markup
	 * @return String
	 */
	highlight: function(text, keyword, startTag, endTag) {
        // the highlightStartTag and highlightEndTag parameters are optional
        if ((!startTag) || (!endTag)) {
            startTag = "<span class='jsn-filter-highlight'>";
        	endTag = "</span>";
        }

        var newText = "";
        var i = -1;
        var lcSearchTerm = keyword.toLowerCase();
        var lcBodyText = text.toLowerCase();

        while(text.length > 0) {
            i = lcBodyText.indexOf(lcSearchTerm, i + 1);
            if(i < 0) {
                newText += text;
                text = "";
            } else {
                // skip anything inside an HTML tag
                if(text.lastIndexOf(">", i) >= text.lastIndexOf("<", i)) {
                    // skip anything inside a <script> block
                    if(lcBodyText.lastIndexOf("/script>", i) >= lcBodyText.lastIndexOf("<script", i)) {
                        newText += text.substring(0, i) + startTag + text.substr(i, keyword.length) + endTag;
                        text = text.substr(i + keyword.length);
                        lcBodyText = text.toLowerCase();
                        i = -1;
                    }
                }
            }
        }
        
        return newText;
	}
});

/**
 * Register admin toolbar template
 */
Mooml.register('admin-toolbar', function (options) {
	div({ id: 'jsn-adminbar-wrapper', 'class': 'clearafter' },
		div({ id: 'jsn-adminbar-container', 'class': 'clearafter' },
			div({ id: 'jsn-adminbar-logo' }),
			div({ id: 'jsn-adminbar-mainmenu' }),
			div({ id: 'jsn-adminbar-plugins' },
				/* Site menu */
				div({ id: 'jsn-adminbar-site-button', 'class': 'jsn-adminbar-menu-dropdown' },
					span({ 'class': 'jsn-icon-16 icon-display' }),
					ul(
						li({ id: 'jsn-adminbar-sitemenu-status' }),
						li({ id: 'jsn-adminbar-sitemenu-manager' }, a({ target: options.linkTarget, href: options.sitemenu.manager }, JoomlaShine.language.JSN_ADMINBAR_SITEMANAGER)),
						li({ id: 'jsn-adminbar-sitemenu-preview' }, a({ target: '_blank', href: options.sitemenu.preview }, JoomlaShine.language.JSN_ADMINBAR_SITEPREVIEW))
					)
				),
				
				/* User menu */
				div({ id: 'jsn-adminbar-user-button', 'class': 'jsn-adminbar-menu-dropdown' },
					span({ 'class': 'jsn-icon-16 jsn-icon-user' }),
					ul(
						li({ id: 'jsn-adminbar-usermenu-welcome' }, span({ 'class': 'jsn-welcome-text' }, JoomlaShine.language.JSN_ADMINBAR_USERMENU_WELCOME, span({ 'class': 'countdown', 'text': '&nbsp;' }))),
						li({ id: 'jsn-adminbar-usermenu-profile' }, a({ target: '_parent', href: options.usermenu.profileLink }, JoomlaShine.language.JSN_ADMINBAR_USERMENU_PROFILE)),
						li({ id: 'jsn-adminbar-usermenu-message' }, a({ target: '_parent', href: options.usermenu.messageLink }, JoomlaShine.language.JSN_ADMINBAR_USERMENU_MESSAGE, span(options.usermenu.messages))),
						li({ id: 'jsn-adminbar-usermenu-logout'  }, a({ target: '_parent', href: options.usermenu.logoutLink }, JoomlaShine.language.JSN_ADMINBAR_USERMENU_LOGOUT))
					)
				),
				
				/* Separator */
				div({ 'class': 'jsn-adminbar-separator' }),
				
				/* History */
				div({ id: 'jsn-adminbar-history-button', 'class': 'jsn-adminbar-button-dropdown' },
					div({ 'class': 'jsn-history-button-wrapper' },
						span({ 'class': 'jsn-icon-16 icon-timer', 'title': JoomlaShine.language.JSN_ADMINBAR_HISTORY_TITLE })
					),
					div({ 'class': 'items' }, 
						ul(
							li({ 'class': 'jsn-loading' }, ' '),
							li({ 'class': 'jsn-empty' }, JoomlaShine.language.JSN_ADMINBAR_HISTORY_EMPTY)
						)
					)
				),
				
				/* Spotlight */
				div({ id: 'jsn-adminbar-spotlight' }, 
					span({ 'class': 'placeholder' }, JoomlaShine.language.JSN_ADMINBAR_SPOTLIGHT_SEARCH),
					input({ id: 'jsn-adminbar-spotlight-box', type: 'text', autocomplete: 'off' }),
					a({ 'class': 'close', href: 'javascript:void()' }),
					div({ 'class': 'items scrollable' },
						ul(
							li({ 'class': 'jsn-loading' }, ' '),
							li({ 'class': 'jsn-empty' }, JoomlaShine.language.JSN_ADMINBAR_SPOTLIGHT_EMPTY)
						)
					)
				),
				
				div({ 'class': 'jsn-adminbar-separator' }),
				div({ id: 'jsn-adminbar-jsnlogo' },
						a({href: 'http://www.joomlashine.com', target: '_blank'})
					)
			),

			div({ id: 'module-status' })
		)
	);
});

/**
 * Admin open button bar template
 */
Mooml.register('admin-buttonbar', function (options) {
	div({ id: 'jsn-adminbar-openbar' },
		button({ id:'jsn-adminbar-open-button' },
			JoomlaShine.language.JSN_ADMINBAR_BUTTON
		)
	);
});

Mooml.register('admin-uninstall-menu', function (options) {
	li(
		a({ 'class': 'icon-16-uninstall', href: 'index.php?option=com_poweradmin&task=removeExtension&component=' + (options.component) }, options.caption)
	);
});

/**
 * Template for history item
 */
Mooml.register('admin-history-item', function (data) {
	li({ 'class': 'item' },
		a({ 'class': data.css, title: data.fulltitle, href: data.link, 'target': data.target }, data.title)
	);
});

/**
 * Template for spotlight item
 */
Mooml.register('admin-spotlight-item', function (data) {
	if (data.type == 'item') {
		if (data.icon != null && data.icon.indexOf(':') != -1) {
			data.icon = data.icon.substring(data.icon.indexOf(':') + 1);
		}
		
		li({ 'class': 'item' },
			a({ 'class': data.icon + '', 'title': data.description, 'href': data.link, 'target': data.target }, data.title)
		);
	}
	else if (data.type == 'notice')
	{
		li({ 'class': 'spotlight-notice' },
				a({ 'class': data.icon + '', 'title': data.description, 'href': data.link, 'target': data.target }, data.title)
			);
	}
	else {
		li({ 'class': 'group ' + ((data.hasMore > 0) ? 'has-more' : 'no-more') },
			span(data.title),
			a({ 'class': 'more', 'target': data.target, 'href': 'index.php?option=com_poweradmin&view=search&keyword=' + data.keyword + '&coverages=' + data.type }, 
				JoomlaShine.language.JSN_ADMINBAR_SPOTLIGHT_SEE_MORE.replace('{num}', data.hasMore)
			)
		);
	}
});

