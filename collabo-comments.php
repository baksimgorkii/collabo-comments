<?php
/*
Plugin Name: Collabo Comments
Plugin URI: http://dev.gorkii.com/
Description: leave comments to editor and reply
Version: 0.9
Author: Gorkii
Author URI: http://dev.gorkii.com/
License: GPL2
*/

function clbc_enqueue_files($hook){
	if($hook != 'post.php')
		return;

	wp_enqueue_script('clbc-metabox-script', plugins_url('js/clbc.js', __FILE__), array('jquery'));
	wp_enqueue_style('clbc-metabox-style', plugins_url('css/clbc.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'clbc_enqueue_files');

function clbc_post_meta_boxes_setup() {
	add_action( 'add_meta_boxes', 'clbc_add_post_meta_boxes' );
}
add_action( 'load-post.php', 'clbc_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'clbc_post_meta_boxes_setup' );

function clbc_add_post_meta_boxes() {
	global $post;
	if(($post->post_status == 'publish' || $post->post_status == 'pending' || $post->post_status == 'draft') && current_user_can('edit_post', $post->ID))
		add_meta_box(
			'clbc-collabo-comments',			// Unique ID
			'Collabo Comments',					// Title
			'clbc_meta_box_callback',			// Callback function
			null,								// Admin page (or post type)
			'normal',							// Context
			'default'							// Priority
		);
}

function clbc_meta_box_callback( $post, $box ) {
?>
	<table id="clbc-comments">
		<tbody>
			<?php clbc_print_comments($post->ID); ?>
		</tbody>
	</table>
	<div id="clbc-form">
		<textarea class="clbc-textarea"></textarea>
		<input type="hidden" class="clbc-post-id" value="<?php echo $post->ID; ?>" />
		<button class="clbc-add-button button" type="button">Add Comment</button>
		<?php if(current_user_can('moderate_comments')): ?><button class="clbc-delete-all-button button" type="button">Delete All</button><?php endif; ?>
		<img src="<?php echo plugins_url('img/loading.gif', __FILE__); ?>" id="clbc-loading-img" />
	</div>
<?php
}

function clbc_print_comments($post_id, $comments = false){
	if(!$comments)
		$comments = get_post_meta( $post_id, 'clbc_collabo_comments', true );
?>
		<?php if(!empty($comments) && count($comments)>0): ?>
			<?php foreach ($comments as $key => $comment): ?>
				<tr class="clbc-row">
					<th class="clbc-user">						
						<?php echo get_the_author_meta('display_name', $comment['user_id']); ?>
					</th>
					<td class="clbc-comment">
						<div class="clbc-comment-content">
							<?php echo wpautop(stripslashes($comment['comment'])); ?>
							<?php if($comment['user_id'] == get_current_user_id()): ?>
								<a href="#" class="clbc-edit-link">Edit</a> <a href="#" class="clbc-delete-link">Delete</a>
							<?php endif; ?>
						</div>
						<?php if($comment['user_id'] == get_current_user_id()): ?>
							<div class="clbc-comment-edit-form">
								<textarea><?php echo stripslashes($comment['comment']); ?></textarea>
								<button type="button" class="clbc-update-comment-button button">Update</button>
								<button type="button" class="clbc-cancel-update-button button">Cancel</button>
							</div>
						<?php endif; ?>
						<input type="hidden" class="clbc-comment-id" value="<?php echo $key; ?>" />
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
<?php
}

function clbc_add_comment($post_id, $comment){
	$user_id = get_current_user_id();
	$comments = get_post_meta( $post_id, 'clbc_collabo_comments', true );
	$comments = (!$comments)?array():$comments;
	$comments[] = array(
					'user_id' => $user_id,
					'comment' => $comment
				);
	update_post_meta($post_id, 'clbc_collabo_comments', $comments);
	return $comments;
}

function clbc_add_comment_ajax(){
	$post_id = $_POST['post_id'];
	$comment = $_POST['comment'];
	$updated_comments = clbc_add_comment($post_id, $comment);
	clbc_print_comments($post_id, $updated_comments);
	die();
}
add_action( 'wp_ajax_clbc_add_comment_ajax', 'clbc_add_comment_ajax' );

function clbc_update_comment($post_id, $comment, $key){
	$comments = get_post_meta( $post_id, 'clbc_collabo_comments', true );
	if($comments[$key]['user_id'] != get_current_user_id())
		return $comments;
	$comments = (!$comments)?array():$comments;
	$comments[$key]['comment'] = $comment;
	update_post_meta($post_id, 'clbc_collabo_comments', $comments);
	return $comments;
}
function clbc_update_comment_ajax(){
	$post_id = $_POST['post_id'];
	$comment = $_POST['comment'];
	$key = $_POST['key'];
	$updated_comments = clbc_update_comment($post_id, $comment, $key);
	clbc_print_comments($post_id, $updated_comments);
	die();
}
add_action( 'wp_ajax_clbc_update_comment_ajax', 'clbc_update_comment_ajax' );

function clbc_delete_comment($post_id, $key){
	$comments = get_post_meta( $post_id, 'clbc_collabo_comments', true );
	if($comments[$key]['user_id'] != get_current_user_id())
		return $comments;
	$comments = (!$comments)?array():$comments;
	if(isset($comments[$key]))
		unset($comments[$key]);
	$comments = array_values($comments);
	update_post_meta($post_id, 'clbc_collabo_comments', $comments);
	return $comments;
}

function clbc_delete_comment_ajax(){
	$post_id = $_POST['post_id'];
	$key = $_POST['key'];
	$updated_comments = clbc_delete_comment($post_id, $key);
	clbc_print_comments($post_id, $updated_comments);
	die();
}
add_action( 'wp_ajax_clbc_delete_comment_ajax', 'clbc_delete_comment_ajax' );

function clbc_delete_all($post_id){
	delete_post_meta($post_id, 'clbc_collabo_comments');
}

function clbc_delete_all_ajax(){
	$post_id = $_POST['post_id'];
	clbc_delete_all($post_id);
	die();
}
add_action( 'wp_ajax_clbc_delete_all_ajax', 'clbc_delete_all_ajax' );

function clbc_add_admin_column_headers($headers, $something){
	$headers['clbc_e_comments'] = "cc";
	return $headers;
}
add_filter( 'manage_posts_columns', 'clbc_add_admin_column_headers', 10, 2 );

function clbc_add_admin_column_contents($header, $post_id){
	$comments = get_post_meta( $post_id, 'clbc_collabo_comments', true );
	if($header == 'clbc_e_comments')
		echo (!empty($comments))?count($comments):0;
}
add_filter( 'manage_posts_custom_column', 'clbc_add_admin_column_contents', 10, 2 );