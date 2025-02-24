<?php
/**
 * @version		$Id: comments.php 14991 2012-08-13 05:01:21Z sonlm $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>

<div id="k2ModuleBox<?php echo $module->id; ?>" class="k2LatestCommentsBlock">
	<?php if(count($comments)): ?>
	<ul>
		<?php foreach ($comments as $key=>$comment):	?>
		<li class="<?php echo ($key%2) ? "odd" : "even"; if(count($comments)==$key+1) echo ' lastItem'; ?>">
			<?php if($comment->userImage): ?>
			<a class="k2Avatar lcAvatar" href="<?php echo $comment->link; ?>" title="<?php echo K2HelperUtilities::cleanHtml($comment->commentText); ?>"> <img src="<?php echo $comment->userImage; ?>" alt="<?php echo JFilterOutput::cleanText($comment->userName); ?>" style="width:<?php echo $lcAvatarWidth; ?>px;height:auto;" /> </a>
			<?php endif; ?>
			<?php if($params->get('commentLink')): ?>
			<a href="<?php echo $comment->link; ?>">&quot;<span class="lcComment"><?php echo $comment->commentText; ?></span>&quot;</a>
			<?php else: ?>
			<span class="lcComment"><?php echo $comment->commentText; ?></span>
			<?php endif; ?>
			<div class="clr"></div>
			<?php if($params->get('commenterName')): ?>
			<p class="lcUsername author"><?php echo JText::_('K2_WRITTEN_BY'); ?>
				<?php if(isset($comment->userLink)): ?>
				<a rel="author" href="<?php echo $comment->userLink; ?>"><?php echo $comment->userName; ?></a>
				<?php elseif($comment->commentURL): ?>
				<a target="_blank" rel="nofollow" href="<?php echo $comment->commentURL; ?>"><?php echo $comment->userName; ?></a>
				<?php else: ?>
				<?php echo $comment->userName; ?>
				<?php endif; ?>
			</p>
			<?php endif; ?>
			<?php if($params->get('commentDate')): ?>
			<p class="lcCommentDate createdate">
				<?php if($params->get('commentDateFormat') == 'relative'): ?>
				<?php echo $comment->commentDate; ?>
				<?php else: ?>
				<?php echo JHTML::_('date', $comment->commentDate, JText::_('K2_DATE_FORMAT_LC')); ?>
				<?php endif; ?>
			</p>
			<?php endif; ?>
			<div class="clr"></div>
			<?php if($params->get('itemTitle')): ?>
			<?php echo JText::_('K2_PUBLISHED_IN') ; ?>: <span class="lcItemTitle"><a href="<?php echo $comment->itemLink; ?>"><?php echo $comment->title; ?></a></span>
			<?php endif; ?>
			<?php if($params->get('itemCategory')): ?>
			<span class="lcItemCategory"> in <a href="<?php echo $comment->catLink; ?>"><?php echo $comment->categoryname; ?></a></span>
			<?php endif; ?>
			<div class="clr"></div>
		</li>
		<?php endforeach; ?>
		<li class="clearList"></li>
	</ul>
	<?php endif; ?>
	<?php if($params->get('feed')): ?>
	<div class="k2FeedIcon"> <a href="<?php echo JRoute::_('index.php?option=com_k2&view=itemlist&format=feed&moduleID='.$module->id); ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>"> <span class="link-icon icon-rss"><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?></span> </a>
		<div class="clr"></div>
	</div>
	<?php endif; ?>
</div>
