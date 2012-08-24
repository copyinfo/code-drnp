<?php 
// Prevent Comments page from being accessed directly
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thank You!');
// Prevent Comments page from being displayed if password protected
if ( post_password_required() ) { ?> <p class="nocomments">This post is password protected. Enter the password to view comments.</p>
<?php return; } ?>





<?php // Formatted Comments Function
function Karma_comments($comment, $args, $depth) { $GLOBALS['comment'] = $comment; ?>
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
<div class="comment-wrap">
  <div class="comment-content" id="comment-<?php comment_ID(); ?>">
  	<div class="comment-gravatar"><?php echo get_avatar($comment,$size='60',$default=get_template_directory_uri().'/images/_global/default-grav.jpg' ); ?>
  	</div><!-- end comment-gravatar -->
  
  	<div class="comment-text">
	<span class="comment-author"><?php comment_author_link() ?></span> &nbsp;<span class="comment-date"><?php comment_date('F j, Y'); ?></span><br />
	<?php if ($comment->comment_approved == '0') : ?><?php _e('Your comment is awaiting moderation.','truethemes_localize') ?><?php endif; ?>
	<?php comment_text() ?>
	<?php comment_reply_link(array_merge( $args, array('reply_text' => '(reply)', 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>  <?php edit_comment_link(__('(edit)','truethemes_localize'),' ','') ?>
    </div><!-- end comment-text -->   
  </div><!-- end comment-content -->
</div><!-- end comment-wrap -->
<?php }






function list_pings($comment, $args, $depth) {
$GLOBALS['comment'] = $comment; ?>
<li>
<span class="comment-author"><?php comment_author_link() ?></span> &nbsp;<span class="comment-date"><?php comment_date('F j, Y'); ?></span><br />
<?php } ?>
<?php if (have_comments()) : ?>
<?php $comment_count = count($comments_by_type['comment']); ($comment_count !== 1) ? $comment_txt = __('Comments','truethemes_localize') : $comment_txt = __('Comment','truethemes_localize');?>
<?php $trackback_count = count($comments_by_type['pings']); ($comment_count !== 1) ? $comment_txt_trackback = __('Trackbacks','truethemes_localize') : $comment_txt_trackback = __('Trackback','truethemes_localize');?>







<div class="tabs-area" id="blog-tabs">
<p class="comment-title">Discussion</p>
<ul class="tabset">
<li><a href="#tab-0" class="tab"><span><?php echo "<strong>".$comment_count."</strong> &nbsp;".$comment_txt; ?></span></a></li>
<li><a href="#tab-1" class="tab"><span><?php echo "<strong>".$trackback_count."</strong> &nbsp;".$comment_txt_trackback; ?></span></a></li>
</ul>
<div id="tab-0" class="blog-tab-box">
<?php if ( ! empty($comments_by_type['comment']) ) : ?>
<ol class="comment-ol" id="post-comments">
<?php wp_list_comments('callback=Karma_comments&type=comment'); ?>
</ol>


<!-- BEGIN COMMENTS PAGINATION -->
<div id="comments">
<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
<nav id="comment-nav-below">
<div class="nav-next"><?php paginate_comments_links(); ?></div>
</nav>
<?php endif; // check for comment navigation ?>
</div>
<!-- END COMMENT PAGINATION -->


<?php endif; ?>
<?php else : if ('open' == $post->comment_status) : else : endif; endif; ?>




<?php // Wordpress Coments Form
if ('open' == $post->comment_status) : ?>

<div id="respond">
<div class="comments-rss"><?php post_comments_feed_link(__('Subscribe to Comments','truethemes_localize')); ?></div><!-- end comments-rss -->
<h1 class="comment-title"><?php comment_form_title(__('Add a Comment','truethemes_localize'),__('Reply to %s','truethemes')); ?></h1>

<div class="comment-cancel"><?php cancel_comment_reply_link(); ?></div><!-- end comment-cancel -->


<?php if ( get_option('comment_registration') && !$user_ID) : ?>
<p>You must be<a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>"> logged in</a> to post a comment.</p>
<?php else : ?>


<form method="post" action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" id="commentform" class="ka-form">
<?php if ($user_ID) : ?>
<p class="logged"><?php _e('Logged in as','truethemes_localize'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account"><?php _e('Log out &raquo','truethemes_localize'); ?></a></p>
<?php else : ?>
<p class="comment-input-wrap pad"><label class="comment-label" for="author"><?php _e('Name','truethemes_localize'); ?> <span class="mc_required">*</span></label>
<input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" tabindex="1" class="comment-input" /></p
>
<p class="comment-input-wrap pad"><label class="comment-label" for="email"><?php _e('Email','truethemes_localize'); ?> <span class="mc_required">*</span></label>
<input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" tabindex="2" class="comment-input comment-email" /></p>

<p class="comment-input-wrap"><label  class="comment-label" for="url"><?php _e('Website','truethemes_localize'); ?></label>
<input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" tabindex="3" class="comment-input comment-website" /></p>
<?php endif; ?>

<p class="comment-textarea-wrap"><label  class="comment-label" for="comment"><?php _e('Your Comments','truethemes_localize'); ?></label><textarea name="comment" class="comment-textarea" tabindex="4" rows="5" cols="5" id="comment"></textarea></p>
<p><input type="submit" value="<?php _e('Add Comment','truethemes_localize'); ?>" id="ka-submit" /><?php comment_id_fields(); ?></p>
<p><?php do_action('comment_form', $post->ID); ?></p>
</form>	
<?php endif; ?>
</div><!--end comment-response-->
<?php endif; ?>






<?php // Output Trackbacks
if (have_comments()) : ?>
</div>
<div id="tab-1" class="blog-tab-box">
<?php if ( ! empty($comments_by_type['pings']) ) : ?>
<ol class="commentlist">
<?php wp_list_comments('callback=list_pings&type=pings'); ?>
</ol>
<?php endif; ?>
</div>
</div>
<?php else : if ('open' == $post->comment_status) : else : endif; endif; ?>