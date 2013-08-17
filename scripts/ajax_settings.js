// JavaScript Document
jQuery(document).ready(function(){
	  
	jQuery("#save_settings").click(function(){
		var _post = "";
		var _ptype = "";
		var _type = "";
		var _lang = "";
		var ermsg = "";

		if(jQuery("#post_btn").prop("checked")==true){
			_post = jQuery("#id_post").val();
			_ptype="post";
		} else if(jQuery("#page_btn").prop("checked")==true) {
			_post = jQuery("#id_page").val();
			_ptype="page";
		} else {
			ermsg ="Select Post or Page\n";
		}
		
		if(jQuery("#insert_cont").prop("checked")==true){
			_type = "content";
		} else if(jQuery("#insert_redi").prop("checked")==true) {
			_type = "redirect";
		} else {
			ermsg = ermsg + "Select Insert Type\n";
		}

		if(jQuery("#out_en").prop("checked")==true){
			_lang = "english";
		} else if(jQuery("#out_ar").prop("checked")==true) {
			_lang = "arabic";
		} else {
			ermsg = ermsg + "Select Language Output\n";
		}		
		
		if(ermsg !=""){
			alert(ermsg);
		} else {
				// submit to ajax to deliver result
				jQuery.ajax({
						type: "POST",
						url: "../wp-admin/admin-ajax.php",
						
						dataType: "html",
						data: { action: "save_setting", pst:_post, typ: _type, lan: _lang, ptype:_ptype },
						
						beforeSend: function() {
							jQuery("#remark").html('Saving...');
						},
						
						error: function() {
							 jQuery("#remark").html('<span style="margin-left:20px; color:red">failed.</span>')
							 .slideDown('slow');
		
						},
						
						success: function(data) {
							 var htm = '<a href="'+data+'" target="_blank">Click Here for Preview</a>';
							 jQuery("#remark").html(htm);
						}
				});
		}
		
	});
	
	jQuery("#page_btn").click(function(){
		//alert(jQuery(this).prop("checked"));
		jQuery("#id_post").prop("disabled",true);
		jQuery("#id_page").prop("disabled",false);
	});

	jQuery("#post_btn").click(function(){
		//alert(jQuery(this).prop("checked"));
		jQuery("#id_page").prop("disabled",true);
		jQuery("#id_post").prop("disabled",false);
	});	
	

});

