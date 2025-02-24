<?php
/**
 * @version		$Id: default_profile.php 19737 2012-12-27 07:53:42Z tuyetvt $
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app 		= JFactory::getApplication();
$template 	= $app->getTemplate();
$jsnUtils   = JSNTplUtils::getInstance();
?>
<?php if (JPluginHelper::isEnabled('user', 'profile')) :
	$fields = $this->item->profile->getFieldset('profile'); ?>
<div class="contact-profile" id="users-profile-custom">
	<dl <?php if ($jsnUtils->isJoomla3()){echo 'class="dl-horizontal"';} ?>>
	<?php foreach ($fields as $profile) :
		if ($profile->value) : 
			echo '<dt>'.$profile->label.'</dt>';
			$profile->text = htmlspecialchars($profile->value, ENT_COMPAT, 'UTF-8');

			switch ($profile->id) :
				case "profile_website":
					$v_http = substr ($profile->profile_value, 0, 4);

					if ($v_http == "http") :
						echo '<dd><a href="'.$profile->text.'">'.$profile->text.'</a></dd>';
					else :
						echo '<dd><a href="http://'.$profile->text.'">'.$profile->text.'</a></dd>';
					endif;
					break;

				default:
					echo '<dd>'.$profile->text.'</dd>';
					break;
			endswitch;
		endif;
	endforeach; ?>
	</dl>
</div>
<?php endif; ?>
