jQuery(document).ready(function(){
	jQuery('#tdl_upload_button').click(function(e){
		e.preventDefault();
		wp.media.editor.send.attachment = function(props, attachment){
			jQuery('#tdl_xmlfile').val(attachment.url);
		}
		wp.media.editor.open(this);
		return false;
	});
});