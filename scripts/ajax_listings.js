// JavaScript Document

function load_cat(){
	
		// submit to ajax to deliver result
		jQuery.ajax({
				type: "POST",
				url: "../wp-admin/admin-ajax.php",
				
				dataType: "html",
				data: { action: "load_menu" },
				
				beforeSend: function() {
                                    jQuery("#remark").html('Loading lists...');
				},
				
				error: function() {
						 jQuery("#remark").html('<span style="margin-left:20px; color:red">failed.</span>')
						 .slideDown('slow');

				},
				
				success: function(data) {
						jQuery("#remark").html("");
					 	jQuery("#menu-listings").html(data);
					 
				}
		});
}

function add_cat(){
	
		// submit to ajax to deliver result
		var ermsg = "";
		var days ="test";
		var desc = "";
		var ctr = 1;
		
		if(jQuery('#category_name').val() == ""){
			ermsg = ermsg + "Category Name is required\n";
		}

		if(jQuery('#category_desc').val() == ""){
			ermsg = ermsg + "Category Description is required\n";
		} else {
			desc = jQuery('#category_desc').val();
		}

		 if(jQuery(".days-all").is(":checked")){
			 days = "all";
		 } else {
		 	jQuery(".days").each(function(){
				if(jQuery(this).prop('checked') == true){
					days = days + "x" + jQuery(this).val();
				}
			});
		 }
		 if(days == "test"){
 			ermsg = ermsg + "Select at least 1 day for the menu availability";
		 }
        
		if(ermsg == ""){
		
		jQuery.ajax({
				type: "POST",
				url: "../wp-admin/admin-ajax.php",
				
				dataType: "html",
				data: { action: "add_category" ,name: jQuery('#category_name').val(), day: days, description: desc },
				
				beforeSend: function() {
                                     jQuery("#remarks").html('&nbsp;&nbsp;&nbsp;&nbsp; Adding Category...');
				},
				
				error: function() {
						 jQuery("#remarks").html('<span style="margin-left:20px; color:red">failed.</span>')
						 .slideDown('slow');

				},
				
				success: function(data) {
						jQuery("#remarks").html('');
						jQuery("#category_name").val('');
						jQuery("#category_desc").val('');
						jQuery(".days").prop('checked', false);
						jQuery(".days").prop('disabled', false);
						jQuery(".days-all").prop('checked', false);
						load_cat();
					 
				}
		});
		} else {
			alert(ermsg);
		}
}

jQuery(document).ready(function(){
	  
                 //SET CURSOR POSITION
		jQuery.fn.setCursorPosition = function(pos) {
		  this.each(function(index, elem) {
			if (elem.setSelectionRange) {
			  elem.setSelectionRange(pos, pos);
			} else if (elem.createTextRange) {
			  var range = elem.createTextRange();
			  range.collapse(true);
			  range.moveEnd('character', pos);
			  range.moveStart('character', pos);
			  range.select();
			}
		  });
		  return this;
		};
	  
	  load_cat();
	  
	  jQuery(".del_btn").live("click",function(){
	  		    
				var _this = jQuery(this);
				
					// submit to ajax to deliver result
				jQuery.ajax({
						type: "POST",
						url: "../wp-admin/admin-ajax.php",
						
						dataType: "html",
						data: { action: "delete_category" , id: jQuery(this).prevAll(".menu_id").eq(0).val() },
						
						beforeSend: function() {
									jQuery("#remark").html('deleting...');
						},
						
						error: function() {
								 jQuery("#remark").html('<span style="margin-left:20px; color:red">failed.</span>')
								 .slideDown('slow');
		
						},
						
						success: function(data) {
								jQuery("#remark").html('');
								_this.parent().parent().parent().hide('slow');
							 
						}
				});
		
	  });
	  
	  jQuery(".edt_btn").live("click",function(){
	  		    
				var _this = jQuery(this);
				
				_this.parent().prevAll('.mtle').eq(0).hide();
				_this.parent().parent().nextAll('.alist').hide();
				
				_this.parent().prevAll('.menu_title').eq(0).show().setCursorPosition(0).focus();

				_this.parent().prevAll('.menu_cap').eq(0).show();
				_this.parent().prevAll('.menu_desc').eq(0).show();
				_this.parent().prevAll('.check_box').eq(0).show();

				_this.hide();
				_this.nextAll('.sav_btn').show();
				_this.nextAll('.can_btn').show();
				_this.nextAll('.del_btn').hide();
		
	  });	 

	  jQuery(".can_btn").live("click",function(){
	  		    
				var _this = jQuery(this);
				
				_this.parent().prevAll('.mtle').eq(0).show();
				_this.parent().parent().nextAll('.alist').show();
				_this.parent().prevAll('.menu_title').eq(0).hide();

				_this.parent().prevAll('.menu_cap').eq(0).hide();
				_this.parent().prevAll('.menu_desc').eq(0).hide();
				_this.parent().prevAll('.check_box').eq(0).hide();

				_this.hide();
				_this.prevAll('.edt_btn').show();
				_this.prevAll('.sav_btn').hide();
				_this.nextAll('.del_btn').show();

	  });		  

	  jQuery(".sav_btn").live("click",function(){
	  		    
				var _this = jQuery(this);
				
				var ermsg = "";
				
				var n = _this.parent().prevAll('.menu_title').eq(0).val();

				
				if(_this.parent().prevAll(".menu_title").eq(0).val() == ""){
					
					ermsg = ermsg + "Category title cant be blanks\n";
				
				}
				
				if(_this.parent().prevAll(".menu_desc").eq(0).val() == ""){
					
					ermsg = ermsg + "Category Description cant be blanks\n";
				
				}
				
				
				var days = _this.parent().prevAll("div").eq(0).find('.days2');
				var days_all = _this.parent().prevAll("div").eq(0).find('.days-all2');
				
				var dy ="test";
				
				days.each(function(){
					if(jQuery(this).prop("checked") == true){
						dy = dy + "x" + jQuery(this).val();
					}
				}); 
				days_all.each(function(){
					if(jQuery(this).prop("checked") == true){
						dy = dy + "x" + jQuery(this).val();
					}
				}); 

				if(dy == "test"){
						ermsg = ermsg + "Select a day for the availability of category\n";
				
				}
				
				if(ermsg == ""){
				
				jQuery.ajax({
						type: "POST",
						url: "../wp-admin/admin-ajax.php",
						
						dataType: "html",
						data: { action: "save_category" , id: jQuery(this).prevAll(".menu_id").eq(0).val(), name:n, desc: _this.parent().prevAll(".menu_desc").eq(0).val(), day:dy },
						
						success: function(data) {
								_this.parent().prevAll('.mtle').eq(0).show();
								_this.hide();
								_this.prevAll('.edt_btn').eq(0).show();
								_this.parent().prevAll('.menu_title').eq(0).hide();
								_this.parent().prevAll('.mtle').eq(0).html(_this.parent().prevAll('.menu_title').eq(0).val());
								
								_this.parent().prevAll('.mtle').eq(0).show();
								_this.parent().parent().nextAll('.alist').show();
								_this.parent().prevAll('.menu_title').eq(0).hide();
				
								_this.parent().prevAll('.menu_cap').eq(0).hide();
								_this.parent().prevAll('.menu_desc').eq(0).hide();
								_this.parent().prevAll('.check_box').eq(0).hide();
				
								_this.prevAll('.edt_btn').show();
								_this.hide();
								_this.nextAll('.can_btn').hide();
								_this.nextAll('.del_btn').show();
						}
				});
				} else {
					alert(ermsg);
				}

		
	  });	 	
	  jQuery(".days-all").click(function(){
			  if(jQuery(".days-all").is(":checked")){
				   jQuery(".days").prop('checked', false); 
				   jQuery(".days").attr("disabled", true);
			  } else {
				   jQuery(".days").attr("disabled", false);
			  }   
	  });
	  jQuery(".days-all2").live('click',function(){
			  if(jQuery(this).is(":checked")){
		           jQuery(this).parent().parent().nextAll("div").find(".days2").prop('checked', false); 
				   jQuery(this).parent().parent().nextAll("div").find(".days2").attr("disabled", true);
			  } else {
				   jQuery(this).parent().parent().nextAll("div").find(".days2").attr("disabled", false);
			  }   
	  });
	  jQuery(".days2").live('click',function(){
			  if(jQuery(this).is(":checked")){
		           jQuery(this).parent().parent().prevAll("div").find(".days-all2").prop('checked', false); 
			  }   
	  });	  

	  jQuery(".quickv").live('mouseover',function(){
		    var _ax = jQuery(this).nextAll('.qview').eq(0); 
			_ax.eq(0).stop().show();	
	  } );
	  jQuery(".quickv").live('mouseout',function(){
			jQuery(this).nextAll('.qview').eq(0).stop().hide();	
	  } );	  	  


});

