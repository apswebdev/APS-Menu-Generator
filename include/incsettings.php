
<div class="wrap">
<div id="icon-edit" class="icon32 icon32-posts-menucategory-post"><br>
</div>

<h2>Menu Generator Settings </h2>

<div class="widefat" style="margin-top:20px !important; float:left; min-height:200px"> 
	
	<div style="padding:20px">
	
		<div class="wp-menu-name mlist" style="min-height:8px; font-size:14px; clear:both;">
		
		<h2 style="font-size:17px; margin-top:-20px;margin-bottom:-12px; color:#333">Select Post/Page to Insert Menu</h2>

		<div style="float:left;clear:both; margin-top:10px; font-size:12px; margin-bottom:0px">This part is where you select a specific post or page to where you want the menu list to appear in front view of the site.</div>
				<input type="hidden" id="settings_id" value="<?php echo(md5(rand(1,1000) * rand(1,10000))); ?>">
				<div style="float:left; margin:8px 12px; padding: 5px 12px; width:350px; background:#ececec;border-radius:5px; border:1px solid #CCC;">
				<h2 style="font-size:14px; float:left; margin-top:-10px; color:#000"><label><input name="post_type" type="radio" id="post_btn"  <?php echo ($ptype == "post") ? "checked" : ""; ?>> Post</label></h2>
				 &nbsp;<?php 	echo self::load_post_data("post",$ptype,$pid);  ?>
				</div>
				
				<div style="float:left; margin:8px 12px; padding: 5px 12px; width:350px; background:#ececec;border-radius:5px; border:1px solid #CCC;">
				<h2 style="font-size:14px; float:left; margin-top:-10px; color:#000"><label><input name="post_type" type="radio" id="page_btn" <?php echo ($ptype == "page") ? "checked" : ""; ?>> Page</label></h2>
				<?php 	echo self::load_post_data("page",$ptype,$pid);  ?>
				</div>
				
		</div>
		
		<div class="wp-menu-name mlist" style="min-height:8px; font-size:14px; clear:both;">
		
		<h2 style="font-size:17px; margin-top:-20px;margin-bottom:-12px; color:#333">Option for Insert Type</h2>
		
		<div style="float:left;clear:both; margin-top:10px; font-size:12px; margin-bottom:0px">"Insert to post" - the menu is inserted into themes page/post content. <br/> "Redirect as Indepedent" - the menu is redirected to an independent page. </div>

				<div style="float:left; margin:8px 12px; padding:3px 12px; width:350px; background:#ececec;border-radius:5px; border:1px solid #CCC;">
					<h2 style="font-size:14px; float:left; margin-top:-10px; color:#000"><label><input name="insert_type" type="radio" id="insert_cont" <?php echo ($cont == "content") ? "checked" : ""; ?>> Insert to post</label></h2>
				</div>
				
				<div style="float:left; margin:8px 12px; padding:3px 12px; width:350px; background:#ececec;border-radius:5px; border:1px solid #CCC;">
					<h2 style="font-size:14px; float:left; margin-top:-10px; color:#000"><label><input name="insert_type" type="radio" id="insert_redi"   <?php echo ($cont == "redirect") ? "checked" : ""; ?>> Redirect as Independent</label></h2>
				</div>

		</div>

		<div class="wp-menu-name mlist" style="min-height:8px; font-size:14px; clear:both;">
		
		<h2 style="font-size:17px; margin-top:-20px;margin-bottom:-12px; color:#333">Select Output as Arabic / English </h2>
		
		<div style="float:left;clear:both; margin-top:10px; font-size:12px; margin-bottom:0px">This will set the content text in the output either English or Arabic Font. </div>

				<div style="float:left; margin:8px 12px; padding:3px 12px; width:350px; background:#ececec;border-radius:5px; border:1px solid #CCC;">
				<h2 style="font-size:14px; float:left; margin-top:-10px; color:#000"><label><input name="output_type" type="radio" id="out_en"   <?php echo ($lang == "english") ? "checked" : ""; ?>> Output as English</label></h2>
				</div>
				
				<div style="float:left; margin:8px 12px; padding:3px 12px; width:350px; background:#ececec;border-radius:5px; border:1px solid #CCC;">
				<h2 style="font-size:14px; float:left; margin-top:-10px; color:#000"><label><input name="output_type" type="radio" id="out_ar"   <?php echo ($lang == "arabic") ? "checked" : ""; ?>> Output as Arabic</label></h2>
				</div>

		</div>
		
		<div class="wp-menu-name mlist" style="min-height:8px; font-size:14px; clear:both;">
		
		<center><input type="button"class="button-primary" style="width:200px; font-size:17px; height:40px" id="save_settings" value="Save Settings"> </center><br/>
		<center><div id="remark"></div></center>
		
		
		</div>
		
	
	</div>

</div>
