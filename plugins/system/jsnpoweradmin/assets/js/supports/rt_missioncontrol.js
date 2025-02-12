/*------------------------------------------------------------------------
 # JSN PowerAdmin
 # ------------------------------------------------------------------------
 # author    JoomlaShine.com Team
 # copyright Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 # Websites: http://www.joomlashine.com
 # Technical Support:  Feedback - http://www.joomlashine.com/joomlashine/contact-us.html
 # @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # @version $Id
 -------------------------------------------------------------------------*/

var JSNAdminBarUIHelper = new Class
({
	getMenuPosition: function () {
		return $('mctrl-menu');
	},

	getStatusPosition: function () {
		return null;
	},

	getComponentMenu: function (menubar) {
		try{
			var componentMenu = $(menubar).getElement('li.li-extend').getElement('ul').getChildren();
			return componentMenu;
		}catch (e) {
			
		}
	},

	getExtensionMenu: function (menubar) {
		return this.getComponentMenu(menubar);
	},

	getMenuSeparator: function () {
		return new Element('li', { 'class': 'misioncontrol separator' }).adopt(new Element('span'))
	},

	createMenuItem: function (title, url, target, className) {
		return new Element('li').adopt(
			new Element('a', { 'class': 'item', 'href': url, 'target': target, 'text': title })
		);
	},

	createMenuContainer: function () {
		return new Element('ul', { 'class': 'parent level3' })
	},

	formatParentMenu: function (menu) {
		menu.getElement('a').addClass('daddy');
	}
});