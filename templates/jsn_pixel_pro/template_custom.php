<?php
$jsnutils = JSNTplUtils::getInstance();
$doc = $this->_document;
$doc->hasRight			= $doc->countModules('right');
$doc->hasLeft			= $doc->countModules('left');
$doc->hasPromo			= $doc->countModules('promo');
$doc->hasPromoLeft		= $doc->countModules('promo-left');
$doc->hasPromoRight		= $doc->countModules('promo-right');
$doc->hasInnerLeft		= $doc->countModules('innerleft');
$doc->hasInnerRight		= $doc->countModules('innerright');

$doc->columnPromoLeft	= $doc->params->get('columnPromoLeft', 23);
$doc->columnPromoRight	= $doc->params->get('columnPromoRight', 23);
$doc->columnLeft		= $doc->params->get('columnLeft', 23);
$doc->columnRight		= $doc->params->get('columnRight', 23);
$doc->columnInnerleft	= $doc->params->get('columnInnerleft', 28);
$doc->columnInnerright	= $doc->params->get('columnInnerright', 28);
$doc->templateColors	= array('blue', 'red', 'green', 'yellow', 'pink', 'grey');

if (isset($doc->sitetoolsColorsItems)) {
	$this->_document->templateColors = $doc->sitetoolsColorsItems;
}

// apply K2 style
if ($jsnutils->checkK2()) {
	$doc->addStylesheet($doc->templateUrl . "/ext/k2/jsn_ext_k2.css");
}
// apply VM style
if ($jsnutils->checkVM()) {
	$doc->addStylesheet($doc->templateUrl . "/ext/vm/jsn_ext_vm.css");
}

$tw				= 100;
$ieOffset		= 0;

$customCss = '
	#jsn-page {
		min-width: ' . $doc->templateWidth . ';
	}
	#jsn-header-inner,
	#jsn-body-inner,
	#jsn-content-bottom-inner,
	#jsn-footer-inner {
		width: ' . $doc->templateWidth . ';
	}
	#jsn-pos-promo-left {
		float: left;
		width: ' . $doc->columnPromoLeft . '%;
		left: -' . ($tw - $ieOffset) . '%;
	}
	#jsn-pos-promo {
		width: ' . ($tw - $ieOffset) . '%;
		left: '.($doc->hasPromoLeft ? $doc->columnPromoLeft . '%' : 0) . ';
	}
	#jsn-pos-promo-right {
		float: right;
		width: ' . $doc->columnPromoRight . '%;
	}
';

if ($doc->hasPromoRight) {
	$tw -= $doc->columnPromoRight;
	$customCss .= '
		#jsn-pos-promo {
			float: left;
			width: ' . ($tw - $ieOffset) . '%;
		}
	';
}

if ($doc->hasPromoLeft) {
	$tw -= $doc->columnPromoLeft;
	$customCss .= '
		#jsn-pos-promo {
			width: ' . ($tw - $ieOffset) . '%;
			float: right;
			left: auto;
		}
		#jsn-pos-promo-left { left: auto; }
	';
}

if ($doc->hasPromoLeft && $doc->hasPromoRight) {
	$tw -= $doc->columnPromoLeft;
	$customCss .= '
		#jsn-pos-promo {
			float: left;
			left: ' . ($doc->hasPromoLeft ? $doc->columnPromoLeft . '%' : 0) . ';
		}
		#jsn-pos-promo-left { left: -' . ($tw + $doc->columnPromoLeft) . '%; }
	';
}

if (!$doc->hasPromo) {
	$customCss .= '
		#jsn-pos-promo-left {
			left: auto;
			display: auto;
		}
	';
}

// Setup width of content area
$tw = 100;
if ($doc->hasLeft) {
	$tw -= $doc->columnLeft;
	$customCss .= '
		#jsn-maincontent { right: '.(100 - $doc->columnLeft).'%; }
		#jsn-maincontent_inner { left: '.(100 - $doc->columnLeft).'%; }
	';
}
if ($doc->hasRight) {
	$tw -= $doc->columnRight;
	$customCss .= '
		#jsn-maincontent_inner2 { left: ' . (100 - $doc->columnRight) . '%; }
		#jsn-maincontent_inner3 { right: ' . (100 - $doc->columnRight) . '%; }
	';
}
$customCss .= '
	#jsn-leftsidecontent {
		float: left;
		width: ' . $doc->columnLeft . '%;
		left: -' . ($tw - $ieOffset) . '%;
	}
	#jsn-maincontent {
		float: left;
		width: ' . ($tw - $ieOffset) . '%;
		left: ' . ($doc->hasLeft ? $doc->columnLeft . '%' : 0) . ';
	}
	#jsn-rightsidecontent {
		float: right;
		width: ' . $doc->columnRight . '%;
	}
';

$tw = 100;
if ($doc->hasInnerLeft)
	$tw -= $doc->columnInnerleft;

if ($doc->hasInnerRight)
	$tw -= $doc->columnInnerright;

$customCss .= '
	div.jsn-hasinnerleft #mainbody-content-inner1 {
		left: -'.$tw.'%;
	}
	div.jsn-hasinnerleft #mainbody-content-inner2 {
		left: '.$tw.'%;
	}
	div.jsn-hasinnerright #mainbody-content-inner1 {
		left: -'.(100-$tw).'%;
	}
	div.jsn-hasinnerright #mainbody-content-inner2 {
		left: '.(100-$tw).'%;
	}
	#jsn-pos-innerleft {
		float: left;
		width: ' . $doc->columnInnerleft . '%;
		left: -' . ($tw - $ieOffset) . '%;
	}
	#mainbody-content-inner {
		float: left;
		width: '.($tw-$ieOffset).'%;
		left: '.(($doc->hasInnerLeft)?$doc->columnInnerleft.'%':0).';
		position: relative;
	}
	#jsn-pos-innerright {
		float: right;
		width: ' . $doc->columnInnerright . '%;
	}
';

// Setup main menu width parameter
if($doc->mainMenuWidth)
{
	$menuMargin = $doc->mainMenuWidth - 1;
	$customCss .= '
		div.jsn-modulecontainer ul.menu-mainmenu ul,
		div.jsn-modulecontainer ul.menu-mainmenu ul li {
			width: ' . $doc->mainMenuWidth . 'px;
		}
		div.jsn-modulecontainer ul.menu-mainmenu ul ul {
		';
		if($doc->direction == 'ltr'){
			$customCss .= 'margin-left: ' . $menuMargin . 'px;';
		}
		if($doc->direction == 'rtl'){
			$customCss .= 'margin-right: ' . $menuMargin . 'px;';
		}
		$customCss .= '
		}
		#jsn-pos-toolbar div.jsn-modulecontainer ul.menu-mainmenu ul ul {
		';
		if($doc->direction == 'ltr'){
			$customCss .= '	
				margin-right: '.$menuMargin.'px;
				margin-left : auto';
		}
		if($doc->direction == 'rtl'){
			$customCss .= '
				margin-left : '.$menuMargin.'px;
				margin-right: auto';
		}
		$customCss .= '
		}
	';
}

// Setup slide menu width parameter
if($doc->sideMenuWidth) {
	$sideMenuMargin = $doc->sideMenuWidth;
	$customCss .= '
		div.jsn-modulecontainer ul.menu-sidemenu ul,
		div.jsn-modulecontainer ul.menu-sidemenu ul li {
			width: ' . $doc->sideMenuWidth . 'px;
		}
		div.jsn-modulecontainer ul.menu-sidemenu li ul {
			right: -' . $doc->sideMenuWidth . 'px;
		}
		body.jsn-direction-rtl div.jsn-modulecontainer ul.menu-sidemenu li ul {
			left: -' . $doc->sideMenuWidth . 'px;
			right: auto;
		}
		div.jsn-modulecontainer ul.menu-sidemenu ul ul {
		';
		if($doc->direction == 'ltr'){
			$customCss .= 'margin-left: ' . $sideMenuMargin . 'px;';
		}
		if($doc->direction == 'rtl'){
			$customCss .= 'margin-right: ' . $sideMenuMargin . 'px;';
		}
		$customCss .= '
		}
	';
}

// Include CSS3 support for IE browser
if($doc->isIE) {
	$customCss .= '
		.text-box,
		.text-box-highlight,
		.text-box-highlight:hover,
		div[class*="box-"] div.jsn-modulecontainer_inner,
		div[class*="solid-"] div.jsn-modulecontainer_inner,
		h3.jsn-moduletitle,
		#jsn-menu,
		#jsn-content-top,
		#jsn-pos-breadcrumbs,
		#jsn-pos-user-top,
		#jsn-usermodules1,
		#jsn-mainbody-content,
		#jsn-usermodules2,
		#jsn-content-bottom,
		#jsn-usermodules3,
		#jsn-pos-user-bottom,
		#jsn-pos-promo-left div.jsn-modulecontainer_inner,
		#jsn-pos-promo-right div.jsn-modulecontainer_inner {
			behavior: url(' . $doc->rootUrl . '/templates/'.strtolower($doc->template).'/css/PIE.htc);
		}
		.link-button {
			zoom: 1;
			position: relative;
			behavior: url(' . $doc->rootUrl . '/templates/'.strtolower($doc->template).'/css/PIE.htc);
		}
	';
}

$doc->addStyleDeclaration($customCss);
