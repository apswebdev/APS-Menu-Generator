<?php 

#include_once('../../../../wp-load.php');
include_once('../wp-load.php');

class template_class
{
        
		protected static $post_type = "menucategory-post";
		
		protected static $page_title_settings = "Menu Generator Settings";
		
		protected static $page_title_category = "Menu Categories";
		
		protected static $capability = "administrator";
		
		protected static $table_name = "menudish_cat";
		
		protected static $table_name_settings = "menudish_settings";
		
		/*===================================================================
		 *    name   : menu_init()
		 *    desc   : this is initialization process.
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function load_dishes($serve)
		{  
				
				global $wpdb;
				
				$day = strtolower(date("l"));
				
				$dish_list = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.self::$table_name);
				
				if(!empty($dish_list)){
					foreach($dish_list as $key => $val){
						
							$proceed = false;
							$days = explode("x", $val->re_day);
							if(in_array($day,$days)){
								$proceed = true;
							}	
							if(in_array("all",$days)){
								$proceed = true;
							}
						
						
						if($proceed){
						
								$sql = "SELECT p . * 
										FROM " . $wpdb->prefix . "posts  p
										JOIN " . $wpdb->prefix . "postmeta s 
										ON p.id = s.post_id
										WHERE
										s.meta_key = 'meta_menucat'
										AND p.post_type =  '". self::$post_type . "'
										AND s.meta_value = '".$val->re_int."'
										AND p.post_status =  'publish'
										ORDER BY s.meta_key ASC;";			
								
								$custpost = $wpdb->get_results($sql);
								
									if(!empty($custpost)){	
										foreach($custpost as $key2 => $val2){
											$srv = get_post_meta($val2->ID,'post_dishserve',true);
											$srv2 = explode("=",$srv);
											$array=array();
											$array[] = ($srv[0] == true) ? "breakfast":""; 
											$array[] = ($srv[1] == true) ? "lunch":""; 
											$array[] = ($srv[2] == true) ? "dinner":""; 
											
											if(in_array($serve,$array)){
												
												echo "<div class='dish_block'>".$val2->post_title."</div>";
											
											}
												
										}
									}
								
						
						}
						
					}
				}
		}

		
		/*===================================================================
		 *    name   : menu_init()
		 *    desc   : this is initialization process.
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function get_dishes()
		{  
				
				global $wpdb;
				
				$dish_list = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.self::$table_name);
				
				$language = self::get_option_setting();
				
				if($language[0] == "arabic"){$float = "right";} else {$float="left";}
				
				if(!empty($dish_list)){
					
					foreach($dish_list as $key => $val){
						
								$sql = "SELECT p . * 
										FROM " . $wpdb->prefix . "posts  p
										JOIN " . $wpdb->prefix . "postmeta s 
										ON p.id = s.post_id
										WHERE
										s.meta_key = 'meta_menucat'
										AND p.post_type =  '". self::$post_type . "'
										AND s.meta_value = '".$val->re_int."'
										AND p.post_status =  'publish'
										ORDER BY s.meta_key ASC;";			
								
								$custpost = $wpdb->get_results($sql);
								
									if(!empty($custpost)){	
										foreach($custpost as $key2 => $val2){

												$cl = 	$val2->post_title . "===" . 
																	$val2->post_content . "===" .
																	$val->re_int . "===" .
																	get_permalink($val2->ID)  . "===" . 
																	wp_get_attachment_url( get_post_thumbnail_id($val2->ID)) . "===" .
																	get_post_meta( $val2->ID, 'post_dishprice', true ) . "===" .
																	get_post_meta( $val2->ID, 'post_dishprice2', true ) ;
												
												$typ = get_post_meta( $val2->ID, 'post_dishtype', true );
												
												$typ = explode("=",$typ);
												$icon = "";
												if(in_array("1", $typ)){
													$icon = "<div style='float:".$float.";clear:both; width:150px'>";
													if($typ[0]=="1"){
														$icon .= "<img title='New Dish' src='".plugins_url()."/menu-generator/images/new.png' style='margin-left:5px; float:left;'>";
													}
													if($typ[1]=="1"){
														$icon .= "<img title='Special'  src='".plugins_url()."/menu-generator/images/special.png' style='margin-left:5px; float:left;'>";
													}
													if($typ[2]=="1"){
														$icon .= "<img title='Hot'  src='".plugins_url()."/menu-generator/images/hot.gif' style='margin-left:5px; float:left;'>";
													}
													if($typ[3]=="1"){
														$icon .= "<img  title='Spicy'  src='".plugins_url()."/menu-generator/images/spicy.png' style='margin-left:5px; float:left;'>";
													}
													if($typ[4]=="1"){
														$icon .= "<img title='Vegetarian' src='".plugins_url()."/menu-generator/images/veggie.png' style='margin-left:5px; float:left;'>";
													}
													$icon .= "</div>";
	
												}
												
												$cl = explode("===",$cl);	
												$price = "";
												if($cl[6] == "0.00" or empty($cl[6])){
													if(empty($cl[5])){
														$price = "$0.00";
													} else {$price = "$" . $cl[5];}
												} else {
													$price = "<div style='font-size:12px; margin-top:-10px'>$".$cl[6]."<br/><div style='text-decoration:line-through; margin-top:-27px;'>$".$cl[5]."</div></div>";
												} 			
																	
												echo	'<div class="item_box menu-'.$val->re_int.'">
																<div class="inner_item">
																	<div class="img_cont">
																		<img src = "'.$cl[4].'" style="width:100%; height:auto">
																		<div class="price">'.$price.'</div>
                                                                                                                                        </div>
																	<div class="itm_details">
																		<h3>'.strtoupper($cl[0]).'</h3>
																		'.$icon.'
																		<p style="clear:both">'.self::truncate_string($cl[1], 20, " ").'</p>
																		<div id="title" style="display:none">'.$cl[0].'</div>
																		<div id="desc" style="display:none">'.$cl[1].'</div>
																		<div id="price" style="display:none">'.$price.'</div>
																		<div id="icons" style="display:none">'.strip_tags($icon,'<img>').'</div>
																	</div>
																</div>
															</div>';					
																	
												
										}
									}
					}
					echo "<div style='float:left; clear:both; height:150px; width:100%'></div>";
				}
				
		}
                
                public static function truncate_string($string, $limit, $break=".", $pad="<br/><i>read more..</i>"){
                     // return with no change if string is shorter than $limit 
                     if(strlen($string) <= $limit) return $string; // is $break present between $limit and the end of the string? 
                        
                     if(false !== ($breakpoint = strpos($string, $break, $limit))) { 
                            if($breakpoint < strlen($string) - 1) { 
                                    $string = substr($string, 0, $breakpoint) . $pad; 
                             } 
                      } 
                      return $string; 
                 }
                
		/*===================================================================
		 *    name   : menu_init()
		 *    desc   : this is initialization process.
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function get_menu()
		{  
				
				global $wpdb;
				
				$dish_list = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.self::$table_name);
				
				echo	'<div class="left-rows" id="menu-all">All Categories</div>';	
				
				if(!empty($dish_list)){
					
					foreach($dish_list as $key => $val){
																	
							echo	'<div class="left-rows" id="menu-'.$val->re_int.'">'.$val->re_title.'</div>';					
						
					}
					echo "<div style='float:left; clear:both; height:150px; width:100%'></div>";
				}
				
		}	


		/*===================================================================
		 *    name   : menu_init()
		 *    desc   : this is initialization process.
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function get_option_setting()
		{  
				global $wpdb;
				
				$opt_list = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.self::$table_name_settings);
				
				$option =array();
				
				if(!empty($opt_list)){
					
					foreach($opt_list as $key => $val){
																	
							$option[] = $val->rs_lang;
							$option[] = $val->rs_type;					
							$option[] = $val->rs_postid;	
					}
				}
				
				return $option;
				
		}				


}



?>