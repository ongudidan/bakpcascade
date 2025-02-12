<?php
/**
 * @package		EasyBlog
 * @copyright	Copyright (C) 2010 Stack Ideas Private Limited. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 *
 * EasyBlog is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="ezblog-latestcomment" class="ezb-mod latest-comments<?php echo $params->get( 'moduleclass_sfx' ) ?>">
<?php if( $comments ){ ?>
	<?php foreach( $comments as $comment ){

		$tmpObj = new stdClass();
		$tmpObj->category_id    = $comment->category_id;
		$tmpObj->created_by     = $comment->author_id;
		$tmpObj->id    			= $comment->post_id;

		$itemId     = modEasyBlogLatestCommentHelper::_getMenuItemId($tmpObj, $params);

	?>
	<div class="mod-item">
		<div class="mod-comment-head">
			<?php if( $params->get( 'showavatar' ) ){ ?>
			<div class="mod-avatar">
				<img src="<?php echo $comment->author->getAvatar();?>" width="30" height="30" class="avatar" />
			</div>
			<?php } ?>

			<?php
			if( $params->get( 'showauthor') )
			{
			    $author = '';
			    if( $comment->created_by == 0)
			    {
			        $author = $comment->name;
			    }
			    else
			    {
					$author	= '<a href="' . EasyBlogRouter::_( 'index.php?option=com_easyblog&view=blogger&layout=listings&id=' . $comment->author->id . $itemId ) . '"><b>' . $comment->author->getName() . '</b></a>';
			    }
			?>
			<span class="comment-author"><?php echo $author;?></span>
			<?php
			}
			?>
		</div>
		<div class="mod-comment-content">
			<?php
			$text	= EasyBlogHelper::getHelper( 'Comment' )->parseBBCode( $comment->comment );

			echo JString::strlen( strip_tags( $text ) ) > $maxCharacter ? JString::substr( strip_tags( $text ) , 0 , $maxCharacter ) . '...' : $text;
			?>
		</div>

		<div class="mod-comment-meta small">
			<?php echo $comment->dateString; ?>
			<?php echo JText::_('MOD_EASYBLOGLATESTCOMMENT_IN'); ?>
			<a href="<?php echo EasyBlogRouter::_( 'index.php?option=com_easyblog&view=entry&id=' . $comment->post_id . $itemId );?>#comment-<?php echo $comment->id;?>">
			<?php if( $params->get( 'showtitle' ) ){ ?>
			<?php echo (JString::strlen($comment->blog_title) > $maxTitleLen) ? JString::substr($comment->blog_title, 0, $maxTitleLen) . '...' : $comment->blog_title; ?></a>
			<?php } else { ?>
				<span>#</span>
			<?php } ?>
			</a>
		</div>
	</div>
	<?php } ?>
<?php } else { ?>
	<div><?php echo JText::_('MOD_EASYBLOGLATESTCOMMENT_NO_POST'); ?></div>
<?php } ?>
</div>
