<?php 

	include "template.php";
	
	$op = template_class::get_option_setting();
	
	if(!empty($op)){
		
		$language = $op[0];
	
		$permalink = get_permalink($op[2]);
	} else {
		$op = array();
		$op[] = "";
		$op[] = "";
	}
	if($op[1] == "redirect"){

 ?>
<!DOCTYPE HTML>

<html>

<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Menu Generator</title>
		<link rel="stylesheet" href="menu.css">
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
		
		<?php if ($language == "arabic"){ ?>
		
		<style>
				@font-face {
					font-family: 'afarat_ibn_bladyregular';
					src: url('<?php echo plugins_url(); ?>/menu-generator/font/afaratibnblady-webfont.eot');
					src: url('<?php echo plugins_url(); ?>/menu-generator/font/afaratibnblady-webfont.eot?#iefix') format('embedded-opentype'),
						 url('<?php echo plugins_url(); ?>/menu-generator/font/afaratibnblady-webfont.woff') format('woff'),
						 url('<?php echo plugins_url(); ?>/menu-generator/font/afaratibnblady-webfont.ttf') format('truetype');
					font-weight: normal;
					font-style: normal;
				
				}
				
				#menu-cont, .mhead-inner, .item_box, .left-rows {
					font-family: afarat_ibn_bladyregular !important;
				}
				.itm_details{
					clear:none !important;
					width:50% !important;
				}
				
		</style>
		
		<?php } ?>
		
		<script type="text/javascript">
			
			jQuery(document).ready(function(){
				
				jQuery(".left-rows").click(function(){
					jQuery(".mhead-inner").html(jQuery(this).html());
					
					if(jQuery(this).attr("id") == "menu-all"){
							jQuery(".item_box").show();
					
					} else {	
							var cls = jQuery(this).attr("id");
							
							jQuery(".item_box").each(function(){
								
								if(jQuery(this).hasClass(cls)){
									jQuery(this).show();
								} else {
									jQuery(this).hide();
								}
								
							});
					}
					
				});
			
			});
			
		</script>
		
</head>

<body>


<div id="menu-cont">
	
	<div id="cont">
			
			<div id="logo">
				<h2 style="margin-left:20px">your logo here</h2>
			</div>
			
			<div id="m-heading">
				<div class="mhead-inner">All Categories</div>
			</div>
			
			<div id="left-menu" <?php if($language == "arabic"){echo 'dir="rtl"';} ?>>

				<?php 
						
					template_class::get_menu();
					
				?>					
		
			
			</div>
			<div id="right-menu">
					<div id="right-cont">
						
						<div id="items_dish"<?php if($language == "arabic"){echo 'dir="rtl"';} ?>>
							
							<?php 
								
								template_class::get_dishes();
							
							?>		

							
						
						</div>
			
					</div>
			</div>
			
			<div id="m-heading">
			
			</div>
	
	</div>
	
</div>

</body>
</html>
<?php } else {
	if(empty($permalink)){ $permalink = get_bloginfo('wpurl');}
	echo '<center><h2>This Page is not accessible since the Menu is inserted in this <a href="'.$permalink.'">post.</a></h2></center>';

} ?>


