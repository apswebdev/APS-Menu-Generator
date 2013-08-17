<?php 
   /*
	* Check page for accessing validations
	*/
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
			
				jQuery(".inner_item").click(function(){
					var ln = jQuery("#menu-cont").width();
					var sr = jQuery(this).find(".img_cont > img").attr("src");
					var im = "<img src='"+sr+"' width='100%' height='auto'>";
					im = im + "<div id='sl_up'></div>";
					jQuery("#slide").css({marginLeft:ln+"px"});
					jQuery("#slide").height(jQuery(window).height()+"px");
					jQuery("#slide").width(jQuery(window).width()+"px");
					jQuery("#slide_img").html(im);
	
					// details	
					var title = jQuery(this).find("#title").html();
					var desc = jQuery(this).find("#desc").html();
					var price = jQuery(this).find("#price").html();
					var icons = jQuery(this).find("#icons").html();	
					title = "<h2>"+title+"</h3>";
					desc = "<div>"+desc+"</div><br/>";
					price = "<div style='margin:12px'; font-size:24px; color:green; font-weight:bold>"+price+"</div><br/>"; 
					icons = "<div id='id' style='float:left;'>"+icons+"</div><br/>";
					var ht ="<div style='margin:2%'>" + title + desc + price + "</div>";
					jQuery("#sl_up").html(ht);	
					
					jQuery("#menu-cont").animate({marginLeft:"-"+ln+"px"},1000,
						function(){
							jQuery(this).hide();
							jQuery("#slide").show();
							var imgs = jQuery("#slide_img").find("img");
							var h = jQuery("#slide_img").height();
							if(imgs.height() < h){
								imgs.css({width:"100%", height:h+"px"});
							} 
							var h_ = h/2; 
							var hide = h_ + 50;
							jQuery("#sl_up").height(h_ +"px");
							jQuery("#sl_up").css({bottom:"-"+hide+"px"});	
							jQuery("#slide").animate({marginLeft:"0px"},700);
						}
					);
				});
				
				jQuery("#slide_img").click(function(){
					var mrgn = (jQuery(window).width() - jQuery("#ic").width())/2
					jQuery("#ic").css({marginLeft: mrgn + "px"});
					
					if(jQuery("#sl_up").hasClass("dislayed")){
						var h = jQuery("#slide_img").height();
						var h_ = h/2; 
						var hide = h_ + 50;
						jQuery("#sl_up").animate({opacity:0,bottom:"-"+hide+"px"},1000);
						jQuery("#sl_up").removeClass("dislayed");
					} else {
						jQuery("#sl_up").css({opacity:0});
						jQuery("#sl_up").animate({opacity:1,bottom:"50px"},1000);
						jQuery("#sl_up").addClass("dislayed");
					}
				});
				
				jQuery("#back_recipe").click(function(){
					var ln = jQuery(window).width();
							jQuery("#slide").animate({marginLeft:ln+"px"},1000, function(){
								jQuery(this).hide();
								jQuery("#menu-cont").show();
								jQuery("#menu-cont").animate({marginLeft:"0px"},700);
							});
				});
				
				jQuery("#menu-cont").height(jQuery(window).height()+"px");
                jQuery("#menu-cont").width(jQuery(window).width()+"px");
				jQuery(window).resize(function() {
					jQuery("#menu-cont").height(jQuery(window).height()+"px");
					jQuery("#menu-cont").width(jQuery(window).width()+"px");
                                        jQuery(".img_cont").each(function(){
                                            var imgs = jQuery(this).find("img");
                                            var h = jQuery(this).height();
                                            if(imgs.height() < h){
												imgs.css({width:"auto", height:h+"px"});
                                            } 
                                        });	
                                });
				
                                        jQuery(".img_cont").each(function(){
                                            var imgs = jQuery(this).find("img");
                                            var h = jQuery(this).height();
                                            if(imgs.height() < h){
												imgs.css({width:"auto", height:h+"px"});
                                            } 
                                        });		
				
				jQuery(".left-rows").click(function(){
					
					jQuery(".mhead-inner").html("&nbsp;&nbsp;" + jQuery(this).html());
					
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
			<div class="m-heading m-title">
				<center>Recipes</center>
                                
			</div>		
			<div class="m-heading">
				<div class="mhead-inner">&nbsp;&nbsp;All Categories</div>
                                <span id="doc_width" style="font-size:20px; font-weight:bold"></span>
			</div>
			
			<div id="left-menu" <?php if($language == "arabic"){echo 'dir="rtl"';} ?>>
                            <div id="left_cont">    
				<?php 
						
					template_class::get_menu();
					
				?>					
                            </div>       
			</div>
			<div id="right-menu">
                            
                <div id="right_cont">    
        			<div id="items_dish"<?php if($language == "arabic"){echo 'dir="rtl"';} ?>>
							
							<?php 
								
								template_class::get_dishes();
							
							?>		
						
						</div>
                    </div>  
			</div>
	</div>
</div>
<?php include_once("slide.php"); ?>
</body>
</html>
<?php } else {
	if(empty($permalink)){ $permalink = get_bloginfo('wpurl');}
	echo '<center><h2>This Page is not accessible since the Menu is inserted in this <a href="'.$permalink.'">post.</a></h2></center>';
} ?>


