jQuery(document).ready(function($) {
	var loading_img = $("#clbc-loading-img");
	$('#clbc-form .clbc-add-button').click(function(e){
		var clicked = $(this);
		clicked.attr("disabled", true);
		loading_img.show();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'clbc_add_comment_ajax',
				post_id: $('#clbc-form .clbc-post-id').val(),
				comment: $('#clbc-form .clbc-textarea').val()
			},
			success:function(data, textStatus, XMLHttpRequest){
				loading_img.hide();
				clicked.attr("disabled", false);
				$('#clbc-form .clbc-textarea').val('');
				$('#clbc-comments').html(data);
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	});
	$('body').on('click', '#clbc-comments .clbc-edit-link', function(e){
		e.preventDefault();
		var comment_content = $(this).closest('.clbc-comment-content');
		comment_content.hide();
		comment_content.siblings('.clbc-comment-edit-form').first().fadeIn();
	});
	$('body').on('click', '#clbc-comments .clbc-comment-edit-form .clbc-update-comment-button', function(e){
		e.preventDefault();
		var clicked = $(this);
		clicked.attr("disabled", true);
		loading_img.show();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'clbc_update_comment_ajax',
				post_id: $('#clbc-form .clbc-post-id').val(),
				comment: clicked.siblings('textarea').first().val(),
				key: clicked.closest('.clbc-comment').children('.clbc-comment-id').first().val()
			},
			success:function(data, textStatus, XMLHttpRequest){
				loading_img.hide();
				clicked.attr("disabled", false);
				$('#clbc-comments').html(data);
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	});
	$('body').on('click', '#clbc-comments .clbc-comment-edit-form .clbc-cancel-update-button', function(e){
		e.preventDefault();
		var edit_form = $(this).closest('.clbc-comment-edit-form');
		edit_form.hide();
		edit_form.siblings('.clbc-comment-content').first().fadeIn();
	});
	$('body').on('click', '#clbc-comments .clbc-delete-link', function(e){
		e.preventDefault();
		var clicked = $(this);
		loading_img.show();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'clbc_delete_comment_ajax',
				post_id: $('#clbc-form .clbc-post-id').val(),
				key: clicked.closest('.clbc-comment').children('.clbc-comment-id').first().val()
			},
			success:function(data, textStatus, XMLHttpRequest){
				loading_img.hide();
				$('#clbc-comments').html(data);
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	});
	$('#clbc-form .clbc-delete-all-button').click(function(e){
		var clicked = $(this);
		clicked.attr("disabled", true);
		loading_img.show();
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'clbc_delete_all_ajax',
				post_id: $('#clbc-form .clbc-post-id').val()
			},
			success:function(data, textStatus, XMLHttpRequest){
				loading_img.hide();
				clicked.attr("disabled", false);
				$('#clbc-comments').html('');
			},
			error: function(MLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		});
	});
});