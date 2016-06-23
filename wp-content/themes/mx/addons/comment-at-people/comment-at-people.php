<?php
/**
 * @version 1.0.0
 */
add_filter('theme_addons',function($fns){
	$fns[] = 'theme_comment_at_people::init';
	return $fns;
});
class theme_comment_at_people{
	public static function init(){
		add_filter('get_comment_text' , __CLASS__ . '::get_comment_text', 10, 2);
	}
	public static function get_comment_text($comment_content,$comment){
		/**
		 * has parent
		 */
		if($comment->comment_parent != 0){
			$parent_comment = theme_cache::get_comment($comment->comment_parent);
			
			$parent_author = theme_cache::get_comment_author($parent_comment->comment_ID);
			
			$comment_content = '<a href="' . theme_cache::get_permalink($parent_comment->comment_post_ID) . '#comment-' . $parent_comment->comment_ID . '" class="at" rel="nofollow">@' . $parent_author . '</a> ' . $comment_content;
		}
		return $comment_content;
	}
}