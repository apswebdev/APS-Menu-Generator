<?php 
/*
Plugin name: APS Menu Generator
Version: beta 1.0
Description: This is a Wp Menu Generator Plugin
Author: Anwar Saludsong 
Author URI: http://apsaludsonglabs.com
Plugin URI: http://apsaludsonglabs.com
*/

class menu_class
{
        
		/*===================================================================
		 *    name   : properties()
		 *    desc   : this are initial properties needed.
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
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
		public static function menu_init()
		{  
			
			# create db upon install 
                        register_activation_hook(__FILE__, array(__CLASS__, 'Install'));
                        register_deactivation_hook(__FILE__, array(__CLASS__, 'Uninstall'));
			
			# physical data inputs
			add_action( 'init',array( __CLASS__, 'menucategory_post_custom') );
			add_action( 'admin_init', array( __CLASS__,  'menucat_metabox') );
			add_action( 'admin_menu', array( __CLASS__,'menu_subpage_categories') );
			add_action( 'admin_menu', array( __CLASS__,'menu_subpage_settings') );
			
			# manage custom posts
			add_filter( 'manage_edit-'.self::$post_type.'_columns', array( __CLASS__,'edit_post_columns') ) ;
			add_action( 'manage_'.self::$post_type.'_posts_custom_column',array( __CLASS__, 'manage_post_columns'), 10, 2 );
			add_action( 'restrict_manage_posts', array( __CLASS__,'admin_posts_filter_restrict_manage_posts') );
			add_filter( 'parse_query', array( __CLASS__,'parse_posts_filter') );
			
                        # datasaving
			add_action( 'save_post',array( __CLASS__,  'save_custom_post') );
			
			# ajax Requests
			add_action('wp_ajax_add_category', array( __CLASS__,'func_ajax_add_category'));
			add_action('wp_ajax_load_menu', array( __CLASS__,'func_ajax_load_menu'));
			add_action('wp_ajax_delete_category', array( __CLASS__,'func_ajax_del_menu'));
			add_action('wp_ajax_save_category', array( __CLASS__,'func_ajax_sav_menu'));
			add_action('wp_ajax_save_setting', array( __CLASS__,'func_ajax_sav_setting'));
			
			# displaying output
			add_filter('the_content',array( __CLASS__,'display_in_content'));
			add_action( 'template_redirect',array( __CLASS__, 'display_in_redirect'));
			
 
		}

		/*===================================================================
		 *    name   : Install()
		 *    desc   : create necessary table to hold category outside loop.
		 *             creates a folder "menu" in the root for the menu 
		 * 		       in front end redirection type.
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function Install() {

				global $wpdb;
				
				$wpdb->query("  CREATE TABLE IF NOT EXISTS ".$wpdb->prefix.self::$table_name." (
									re_int INT(10) NOT NULL AUTO_INCREMENT,
									re_title VARCHAR(200) NOT NULL,
									re_day VARCHAR(200) NOT NULL,
									re_desc VARCHAR(200) NOT NULL,
									PRIMARY KEY (`re_int`)
								) 
								ENGINE=MyISAM
								DEFAULT CHARSET=utf8
								AUTO_INCREMENT=1");
								
				$wpdb->query("  CREATE TABLE IF NOT EXISTS ".$wpdb->prefix.self::$table_name_settings." (
									rs_int INT(10) NOT NULL AUTO_INCREMENT,
									rs_postid VARCHAR(200) NOT NULL,
									rs_posttype VARCHAR(200) NOT NULL,
									rs_lang VARCHAR(200) NOT NULL,
									rs_type VARCHAR(200) NOT NULL,
									PRIMARY KEY (`rs_int`)
								) 
								ENGINE=MyISAM
								DEFAULT CHARSET=utf8
								AUTO_INCREMENT=1");
								
				# copy files to create menu
  				$src = ABSPATH . '/wp-content/plugins/menu-generator/menu-files/';
				$dst = ABSPATH . '/menu/';
				$dir = opendir($src); 
                                @mkdir($dst); 
				while(false !== ( $file = readdir($dir)) ) { 
						if (( $file != '.' ) && ( $file != '..' )) { 
							if ( is_dir($src . '/' . $file) ) { 
								recurse_copy($src . '/' . $file,$dst . '/' . $file); 
							} 
							else { 
								copy($src . '/' . $file,$dst . '/' . $file); 
							} 
						} 
					} 
				closedir($dir); 
																
		}
		
		/*===================================================================
		 *    name   : Uninstall()
		 *    desc   : delete table upon uninstall
		 *             initiates delete folder menu in wp root
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function Uninstall() {

				global $wpdb;
				
				$wpdb->query("  DROP TABLE IF EXISTS ".$wpdb->prefix."menudish_cat");
				$wpdb->query("  DROP TABLE IF EXISTS ".$wpdb->prefix."menudish_settings");		
				
				self::delete_files(ABSPATH  . '/menu/');
		
		}

		
		/*===================================================================
		 *    name   : delete_files()
		 *    desc   : delete folder "menu" in wproot upon uninstall
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function delete_files($dirPath){

				if (! is_dir($dirPath)) {
						throw new InvalidArgumentException("$dirPath must be a directory");
					}
					if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
						$dirPath .= '/';
					}
					$files = glob($dirPath . '*', GLOB_MARK);
					foreach ($files as $file) {
						if (is_dir($file)) {
							self::delete_files($file);
						} else {
							unlink($file);
						}
				}
				rmdir($dirPath);
		
		
		}		
		
		/*===================================================================
		 *    name   : menucategory_post_custom()
		 *    desc   : this is initialization process.
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function menucategory_post_custom() {
			$labels = array(
				'name'               => _x( 'Dishes', 'post type general name' ),
				'singular_name'      => _x( 'Menu Generator', 'post type singular name' ),
				'add_new'            => _x( 'Add New Dish', 'Menu Dish' ),
				'add_new_item'       => __( 'Add New Dish' ),
				'edit_item'          => __( 'Edit Dish Descriptions' ),
				'new_item'           => __( 'New Dish' ),
				'all_items'          => __( 'All Dishes' ),
				'view_item'          => __( 'View Dish' ),
				'search_items'       => __( 'Search Dishes' ),
				'not_found'          => __( 'Dish not found' ),
				'not_found_in_trash' => __( 'No Dish found in the Trash' ), 
				'parent_item_colon'  => '',
				'menu_name'          => 'Menu Generator'
			);
			$args = array(
				'labels'        => $labels,
				'description'   => 'Menu Categories',
				'public'        => true,
				'menu_position' => 5,
				'supports'      => array( 'title','editor', 'thumbnail' ),
				'has_archive'   => true,
			);
			register_post_type( self::$post_type, $args );	
		}
		
		/*===================================================================
		 *    name   : menucat_metabox()
		 *    desc   : this will initiate all metabox installation
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/		
		 public static function menucat_metabox() {
			
			$position = "side";
			$priority = "low";
			
			add_meta_box( 'custom-metabox', __( 'Dish Category' ), array( __CLASS__,  'category_box'), self::$post_type, $position, "low" );
			add_meta_box( 'custom-metabox2', __( 'Dish Price' ), array( __CLASS__,  'price_box'), self::$post_type, $position, $priority );
			add_meta_box( 'custom-metabox3', __( 'Special Information' ), array( __CLASS__,  'info_box'), self::$post_type, "normal", "high" );
			add_meta_box( 'custom-metabox4', __( 'Dish Serving' ), array( __CLASS__,  'serve_box'), self::$post_type, "normal", "high" );
						
		}
		
		/*===================================================================
		 *    name   : menu_subpage_settings()
		 *    desc   : this is a setup for a submenu page for menu generator 
		 *             settings in custom post type
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/	
		public static function menu_subpage_settings(){
    		
			add_submenu_page('edit.php?post_type='.self::$post_type, 
							 self::$page_title_settings,  
							 'Settings', 
							 self::$capability,
							 'menu_settings',
					    	 array( __CLASS__, 'menu_generator_settings'));
		
		}

		
		/*===================================================================
		 *    name   : menu_subpage_categpries()
		 *    desc   : this is a setup for a submenu page for menu generator 
		 *             for Menu Listings
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/	
		public static function menu_subpage_categories(){
    		
			add_submenu_page('edit.php?post_type='.self::$post_type, 
							 self::$page_title_category,  
							 'Menu Listing', 
							 self::$capability,
							 'menu_listings',
					    	 array( __CLASS__, 'menu_generator_listing'));
		
		}		

		/*===================================================================
		 *    name   : menu_generator_settings()
		 *    desc   : this is a setup for a submenu page for menu generator 
		 *             for Menu Listings
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/	
		 public static function menu_generator_settings() {
			
			global $wpdb;			
			
			#enqueue scripts
			wp_enqueue_script('js-scripts', plugins_url( '/menu-generator/scripts/ajax_settings.js' ) );
			wp_register_style("menu-style", plugins_url( '/menu-generator/styles/main.css' ) );
                        wp_enqueue_style( 'menu-style');
			
			# listings page properties
			$opt = self::get_current_options();
			$pid = $opt[0];
			$lang = $opt[1];
			$ptype = $opt[2];
			$cont = $opt[3];
			include_once "include/incsettings.php";

		}	

		
		/*===================================================================
		 *    name   : menu_generator_listing()
		 *    desc   : this is a setup for a submenu page for menu generator 
		 *             for Menu Listings
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/	
		 public static function menu_generator_listing() {
			
			#enqueue scripts
			wp_enqueue_script('js-scripts', plugins_url( '/menu-generator/scripts/ajax_listings.js' ) );
			wp_register_style("menu-style", plugins_url( '/menu-generator/styles/main.css' ) );
                        wp_enqueue_style( 'menu-style');
			
			include_once "include/inclistings.php";

		}				

		
		/*===================================================================
		 *    name   : category_box()
		 *    desc   : this is a display for the side category box within 
		 *             add/edit of custom post type
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/	 
		public static function category_box() {
			
			global $post;
			global $wpdb;
	
			$cat_list = $wpdb->get_results( "SELECT re_int, re_title FROM ".$wpdb->prefix.self::$table_name);?>
			
			<label for="siteurl">Select the appropriate Menu Category for this current dish.<br /></label>
			<p>
			
				<?php
					if(!empty($cat_list)){
						echo "<select style='width:200px' name='cat_id'>";
						foreach($cat_list as $list => $val){
							$sel = "";
							if(isset($_GET['menucat']) && ($_GET['menucat'] == $val->re_int )) { 
								$sel = ' selected="selected"'; 
							} else {
								$menucat = get_post_meta($post->ID, "meta_menucat", true);
								if($menucat == $val->re_int ) { $sel = ' selected="selected"';} 
							}
							echo "<option value='".$val->re_int."' ".$sel.">".$val->re_title."</option>";
						}
						echo "</select>";
					} else {
						echo "<a href='edit.php?post_type=menucategory-post&page=menu_listings'>Add Categories First.</a>";
					}
				?>
				
			</p>
		
			<?php
		}

		
		/*===================================================================
		 *    name   : price_box()
		 *    desc   : this is a display for the side price box for  
		 *             for custom post type
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/	
		public static function price_box() {
			
			global $post;
		
			$price = explode(".",get_post_meta($post->ID, 'post_dishprice', true ));
			
			if(empty($price[0])){
				$price[0] = "0";
			}
			if(empty($price[1])){
				$price[1] = "00";
			}
			$price2 = explode(".",get_post_meta($post->ID, 'post_dishprice2', true ));
			
			if(empty($price2[0])){
				$price2[0] = "0";
			}
			if(empty($price2[1])){
				$price2[1] = "00";
			}			
			?>
				<script type="text/javascript">
				function isNumber(evt) {
					evt = (evt) ? evt : window.event;
					var charCode = (evt.which) ? evt.which : evt.keyCode;
					if (charCode > 31 && (charCode < 48 || charCode > 57)) {
						return false;
					}
					return true;
				}</script>
			
			<label for="siteurl">Set the current price of this dish.<br /></label>
			<p>
				$ <input type="text" id="dish_price" onkeypress="return isNumber(event)" style="width:100px; text-align:right" name="dish_price" value="<?php echo $price[0]; ?>">
				. <input type="text" id="dish_price_cents" onkeypress="return isNumber(event)" maxlength="2" style="width:25px;" name="dish_price_cents" value="<?php echo $price[1]; ?>">
			</p>
			<label for="siteurl">Set the Discounted price here or leave it as "$0.00" if there is no discounted price.Any value equal or over than $0.01 will be automatically considered as the new discounted price.<br /></label>
			<p>
				$ <input type="text" id="dish_price2" onkeypress="return isNumber(event)" style="width:100px; text-align:right" name="dish_price2" value="<?php echo $price2[0]; ?>">
				. <input type="text" id="dish_price_cents2" onkeypress="return isNumber(event)" maxlength="2" style="width:25px;" name="dish_price_cents2" value="<?php echo $price2[1]; ?>">
			</p>
			<?php
		}		

		/*===================================================================
		 *    name   : info_box()
		 *    desc   : this is a setup for display for specal info box in 
		 *             custom post type edit add 
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/	 
		public static function info_box() {
			
			global $post;
			
			$ptype = get_post_meta($post->ID, 'post_dishtype', true );
			
			if(empty($ptype)){
				$type0 = 0; 
				$type1 = 0; 
				$type2 = 0; 
				$type3 = 0; 
				$type4 = 0; 
			} else {
				$type = explode("=",$ptype);	
				$type0 = $type[0]; 
				$type1 = $type[1]; 
				$type2 = $type[2]; 
				$type3 = $type[3]; 
				$type4 = $type[4]; 
			}
			
			?>
			<div style="margin:20px; float;left; min-height:120px;">

					<div style='float:left; font-size:14px; font-weight:bold; width:100%;'>
					Overview Type of Dish:</p>
					</div>
					<div style='float:left; font-size:14px; margin-left:20px;  width:340px;'><div style="width:150px; float:left">1.) New Dish?</div> 
						<div style="float:left">
						<label><input type="radio" name="new_dish" value="1" <?php echo ($type0 == true) ? "checked":""; ?>>&nbsp;Yes</label>
						<label><input type="radio" name="new_dish" value="0" <?php echo ($type0 == false || empty($type)) ? "checked":""; ?>>&nbsp;No</label>
						</div>
					</div>
					<div style='float:left; font-size:14px; margin-left:20px;  width:340px;'><div style="width:150px; float:left">2.) Special Dish?</div> 
						<div style="float:left">
						<label><input type="radio" name="special_dish" value="1" <?php echo ($type1 == true) ? "checked":""; ?>>&nbsp;Yes</label>
						<label><input type="radio" name="special_dish" value="0" <?php echo ($type1 == false || empty($type)) ? "checked":""; ?>>&nbsp;No</label>
						</div>
					</div>
					<div style='float:left; font-size:14px; margin-left:20px;  width:340px;'><div style="width:150px; float:left">3.) Hot Dish?</div> 
						<div style="float:left">
						<label><input type="radio" name="hot_dish" value="1" <?php echo ($type2 == true) ? "checked":""; ?>>&nbsp;Yes</label>
						<label><input type="radio" name="hot_dish" value="0" <?php echo ($type2 == false || empty($type)) ? "checked":""; ?>>&nbsp;No</label>
					</div>
					</div>
					<div style='float:left; font-size:14px; margin-left:20px;  width:340px;'><div style="width:150px; float:left">4.) Spicy Dish?</div> 
						<div style="float:left">
						<label><input type="radio" name="spicy_dish" value="1" <?php echo ($type3 == true) ? "checked":""; ?>>&nbsp;Yes</label>
						<label><input type="radio" name="spicy_dish" value="0" <?php echo ($type3 == false || empty($type)) ? "checked":""; ?>>&nbsp;No</label>	</div>
					</div>

					<div style='float:left; font-size:14px; margin-left:20px;  width:340px;'><div style="width:150px; float:left">5.) Vegetarian Dish?</div> 
						<div style="float:left">
						<label><input type="radio" name="vegetarian_dish" value="1" <?php echo ($type4 == true) ? "checked":""; ?>>&nbsp;Yes</label>
						<label><input type="radio" name="vegetarian_dish" value="0" <?php echo ($type4 == false || empty($type)) ? "checked":""; ?>>&nbsp;No</label>			</div>
					</div>
			</div>
		
			<?php
		}			


		/*===================================================================
		 *    name   : serve_box()
		 *    desc   : this is a setup for display for dish type in the 
		 *             custom post type edit add 
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/	
		 public static function serve_box() {
			
			global $post;
			
			$stype = get_post_meta($post->ID, 'post_dishserve', true );
			if(empty($stype)){
				$type0 = 0; 
				$type1 = 0; 
				$type2 = 0; 
			} else {
				$type = explode("=",$stype);	
				$type0 = $type[0]; 
				$type1 = $type[1]; 
				$type2 = $type[2]; 
			}
			?>
			<div style="margin:20px; float;left; height:60px;">
					<div style='float:left; font-size:14px; font-weight:bold; width:100%;'>
					Dish Serving Type:</p>
					</div>
					<div style="float:left; font-size:14px; width:500px; margin-left:20px">
						<label style="float:left; margin-right:25px"><input type="checkbox" name="breakfast" <?php echo ($type0 == true) ? "checked" : ""; ?>> Breakfast </label>
						<label style="float:left; margin-right:25px"><input type="checkbox" name="lunch" <?php echo ($type1 == true) ? "checked" : ""; ?>> Lunch </label>
						<label style="float:left; margin-right:25px"><input type="checkbox" name="dinner" <?php echo ($type2 == true) ? "checked" : ""; ?>> Dinner </label>
					</div>
			</div>
			<?php
		}

		/*===================================================================
		 *    name   : admin_posts_filter_restrict_manage_posts()
		 *    desc   : filter by category on all dish display listing.
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function admin_posts_filter_restrict_manage_posts(){

			global $wpdb;
			$type = 'post';
			
			if (isset($_GET['post_type'])) {
				$type = $_GET['post_type'];
			}
		
			# the actual filter
			if (self::$post_type == $type){
				$values = $wpdb->get_results( "SELECT re_int, re_title FROM ".$wpdb->prefix.self::$table_name);
				?>
				<select name="ADMIN_FILTER_FIELD_VALUE">
				<option value=""><?php _e('Filter By Category ', 'wose45436'); ?></option>
				<?php
					$current_v = isset($_GET['ADMIN_FILTER_FIELD_VALUE'])? $_GET['ADMIN_FILTER_FIELD_VALUE']:'';
					foreach ($values as $label => $value) {
						printf
							(
								'<option value="%s"%s>%s</option>',
								$value->re_int,
								$value->re_int == $current_v? ' selected="selected"':'',
								$value->re_title
							);
						}
				?>
				</select>
				<?php
			}
		}

		/*===================================================================
		 *    name   : parse_posts_filter()
		 *    desc   : additional post filter for category
		 *    parm   : $query - wordpress query 
		 *    return : n/a
		 *===================================================================*/
		public static function parse_posts_filter( $query ){
			global $pagenow;
			$type = 'post';
			if (isset($_GET['post_type'])) {
				$type = $_GET['post_type'];
			}
			if ( self::$post_type == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['ADMIN_FILTER_FIELD_VALUE']) && $_GET['ADMIN_FILTER_FIELD_VALUE'] != '') {
				$query->query_vars['meta_key'] = 'meta_menucat';
				$query->query_vars['meta_value'] = $_GET['ADMIN_FILTER_FIELD_VALUE'];
			}
		}
		/*===================================================================
		 *    name   : edit_post_columns()
		 *    desc   : add column to wordpress list columns
		 *    parm   : $columns - column on wordpress post list
		 *    return : $columns 
		 *===================================================================*/
		public static function edit_post_columns( $columns ) {
		
			$columns = array(
				'cb' => '<input type="checkbox" />',
				'title' => __( 'Dish' ),
				'category' => __( 'Category' ),
				'date' => __( 'Date' )
			);
		
			return $columns;
		}

		/*===================================================================
		 *    name   : manage_post_columns()
		 *    desc   : delete table upon uninstall
		 *    parm   : $column - WP post column
		 *             $id - WP post id
		 *    return : n/a
		 *===================================================================*/
                 public static function manage_post_columns( $column, $post_id ) {
			global $post;
		
			switch( $column ) {
		
				case 'category' :
		
					$menucat = get_post_meta( $post->ID, 'meta_menucat', true );
		
					if ( empty( $menucat ) )
						echo __( 'No classification yet' );
		
					else
						$name = self::get_metaname($menucat);
						
						if ( empty( $name ) ) {
							echo __( 'No classification yet' );
						} else {
							echo __($name);
						
						}
		
					break;
		
				default :
					break;
			}
		}	


		/*===================================================================
		 *    name   : get_metaname()
		 *    desc   : delete table upon uninstall
		 *    parm   : $column - WP Column
		 *    return : $name - column name
		 *===================================================================*/
                public static function get_metaname( $column ) {
			
			global $wpdb;
			
			$result = $wpdb->get_results($wpdb->prepare( "SELECT re_title FROM ".$wpdb->prefix.self::$table_name ." WHERE re_int = %d", $column ));
			
			$name="";
			
			if(!empty($result)){
				
				foreach($result as $key => $val){
					
					$name = $val->re_title;
					
				}
				
			}
			
			return $name;

		}			
		
		/*===================================================================
		 *    name   : func_ajax_add_category()
		 *    desc   : ajax for adding category
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function func_ajax_add_category() {
			    
				global $wpdb;
				
				if(!defined('DOING_AJAX')){
            			wp_redirect (site_url());
                } else {
						$self = self::sanitize($_POST['name']);
						$day = self::sanitize($_POST['day']);
						$desc = self::sanitize($_POST['description']);
						$wpdb->query($wpdb->prepare( "INSERT INTO ".$wpdb->prefix.self::$table_name ." VALUES (%d, %s, %s, %s)", null, $self,$day,$desc ));
						die();
				}
		
		}
		
		/*===================================================================
		 *    name   : func_ajax_load_menu()
		 *    desc   : ajax for loading menu listing
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function func_ajax_load_menu() {
			    
				global $wpdb;
				
				if(!defined('DOING_AJAX')){
            			wp_redirect (site_url());
                } else {
						echo self::load_listings();
						die();
				}
		
		}	

		/*===================================================================
		 *    name   : func_ajax_del_menu()
		 *    desc   : ajax delete from dish category
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function func_ajax_del_menu() {
			    
				global $wpdb;
				
				if(!defined('DOING_AJAX')){
            			wp_redirect (site_url());
                } else {
						$id = $_POST['id'];
						$list = $wpdb->query("DELETE FROM " . $wpdb->prefix.self::$table_name. " WHERE re_int = ".$id );
						echo "success";
						die();
				}
		
		}	

		/*===================================================================
		 *    name   : func_ajax_sav_menu()
		 *    desc   : ajax save on dish category
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function func_ajax_sav_menu() {
			    
				global $wpdb;
				
				if(!defined('DOING_AJAX')){
            			wp_redirect (site_url());
                } else {
						$id = $_POST['id'];
						$name = self::sanitize($_POST['name']);
						$day = self::sanitize($_POST['day']);
						$desc = self::sanitize($_POST['desc']);
						$list = $wpdb->query($wpdb->prepare("UPDATE " . $wpdb->prefix.self::$table_name. " SET re_title = %s, re_day = %s, re_desc = %s WHERE re_int = ".$id,$name,$day,$desc ));
						echo "success";
						die();
				}
		
		}			


		/*===================================================================
		 *    name   : func_ajax_sav_setting()
		 *    desc   : ajax on saving settings
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		public static function func_ajax_sav_setting() {
			    
				global $wpdb;
				
				if(!defined('DOING_AJAX')){
            			wp_redirect (site_url());
                } else {
						$postid = self::sanitize($_POST['pst']);
						$type = self::sanitize($_POST['typ']);
						$lang = self::sanitize($_POST['lan']);
						$ptype = self::sanitize($_POST['ptype']);
						
						$select = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix.self::$table_name_settings);
					
						if(!empty($select)){
							$id=null;
							foreach($select as $key => $val){
								$id = $val->rs_int;
							}
							if($id != null){
						 		$wpdb->query($wpdb->prepare("UPDATE " . $wpdb->prefix.self::$table_name_settings. " SET rs_postid = %s, rs_lang = %s, rs_type = %s, rs_posttype = %s WHERE rs_int = %d",$postid,$lang,$type,$ptype,$id ));
							}
						
						} else {
							$wpdb->query($wpdb->prepare( "INSERT INTO ".$wpdb->prefix.self::$table_name_settings ." VALUES (%d, %s, %s, %s, %s)", null, $postid,$ptype,$lang,$type ));
						}
						 
						echo get_permalink($postid);
						die();
				}
		
		}						

		/*===================================================================
		 *    name   : load_listings()
		 *    desc   : actual loading of menu list
		 *    parm   : n/a
		 *    return : $html = html code for menu list
		 *===================================================================*/
		public static function load_listings() {
			    
				global $wpdb;
				global $post;
				
				$list = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix.self::$table_name );
				
				$html = "";
				
				if(!empty($list)){
					
					foreach($list as $key => $val){
						if(strpos($val->re_day,"x") == false){
							$days = $val->re_day;
						} else {
							$days = explode("x", $val->re_day);	
						}
						$html .= "<div class='mlist'>
									<div class='mtitle'>
								  		<div class='mtle add-new-h2' style='font-size:18px; color:navyblue; border:1px solid #CCC; width:200px;float:left;'>".$val->re_title."</div>
										<input type='text' value='".$val->re_title."' class='menu_title add-new-h2' style='display:none; font-size:18px; color:red; border:1px solid #CCC; width:200px;float:left;'>
										<h2 class='menu_cap' style='display:none; margin-left:5px; font-size:14px; margin-bottom:-12px;clear:both;'>Description</h2>
										<textarea class='menu_desc' id='category_desc' style='width:390px; font-size:16px; display:none; margin-left:5px; height:100px'>".$val->re_desc." </textarea> 
										
										<div class='check_box' style='font-size:14px; float:left; margin-left:20px; width:350px; display:none'>
											<h2 style='font-size:14px; margin-bottom:5px;width:340px;'>Select days for this menu to be available:</h2>
											<div style='float:left; width:150px;'>
													<div style='float:left; margin:2px 12px 0px 15px'><label><input type='checkbox' class='days-all2' style='margin-right:12px' value='all' ".self::selected_check('all',$days).">All Days</label></div>
													<div style='float:left; margin:2px 12px 0px 15px'><label><input type='checkbox' class='days2' style='margin-right:12px' value='monday' ".self::selected_check('monday',$days).">Monday</label></div>
													<div style='float:left; margin:2px 12px 0px 15px'><label><input type='checkbox' class='days2' style='margin-right:12px' value='tuesday' ".self::selected_check('tuesday',$days).">Tuesday</label></div>
													<div style='float:left; margin:2px 12px 0px 15px'><label><input type='checkbox' class='days2' style='margin-right:12px' value='wednesday' ".self::selected_check('wednesday',$days).">Wednesday</label></div>
													<div style='float:left; margin:2px 12px 0px 15px'><label><input type='checkbox' class='days2' style='margin-right:12px' value='thursday' ".self::selected_check('thursday',$days).">Thursday</label></div>
													<div style='float:left; margin:2px 12px 0px 15px'><label><input type='checkbox' class='days2' style='margin-right:12px' value='friday' ".self::selected_check('friday',$days).">Friday</label></div>
													<div style='float:left; margin:2px 12px 0px 15px'><label><input type='checkbox' class='days2' style='margin-right:12px' value='saturday' ".self::selected_check('saturday',$days).">Saturday</label></div>
													<div style='float:left; margin:2px 12px 0px 15px'><label><input type='checkbox' class='days2' style='margin-right:12px' value='sunday' ".self::selected_check('sunday',$days).">Sunday</label></div>
											</div>
											<div style='clear:both'></div>
										</div>
										
										<div class='mbutton'>". PHP_EOL .
						         			"<input type='hidden' class='menu_id' value='".$val->re_int."'>".
								 			"<input type='button' class='edt_btn button-primary' value='Edit'> 
											<input type='button' class='sav_btn button-primary' value='Save' style='display:none'>
											<input type='button' class='can_btn button-primary' value='Cancel' style='display:none'>
											<input class='del_btn button-primary' type='button' value='Delete'>
										</div>
									</div>". PHP_EOL;
						
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
							$ctr = 1;
				
							foreach($custpost as $key => $val1){
								$price = get_post_meta($val1->ID,"post_dishprice",true);
								$price2 = get_post_meta($val1->ID,"post_dishprice2",true);
								$discount = false;
								if($price2 != "0.00" && $price2 != "" ){
									$nprice = "<div style='text-decoration:line-through;float:left; margin-right:12px; color:red;'>$".$price."</div><div style='color:green;'>$".$price2."</div>";
								} else {
									if(empty($price)){$price="0.00";}
									$nprice = "<div style='color:green;'>$".$price."</div>";
								}
								if(empty($price)){$price='0.00';}
						        $url = wp_get_attachment_url( get_post_thumbnail_id($val1->ID) );
								if(empty($url)){ $url = plugins_url()."/menu-generator/images/no-photo.jpg"; }
								$cont1 = $val1->post_content;
								if(strlen($cont1) > 180){
								$extra = "<div style='float:left; font-weight:bold; margin-left:12px;'>continued...</div>";
								} else {$extra ="";}
								//$cont1 = substr($cont1,0,180);
								$html .= "<div class='alist' style='position:relative'><div style='float:left'>". $ctr .".)</div><div style='float:left; width:335px; margin-left:12px'>". $val1->post_title . "</div><div class='m_act'>
											<a style='border:none;' href='".get_edit_post_link($val1->ID)."&menucat=".$val->re_int."'>[ edit ]</a>
										    <a style='border:none;' href='".get_delete_post_link($val1->ID)."'>[ delete ]</a>
											<a style='border:none;' class='quickv' href='javascript:;'>[ quick view ]</a>
											<div class='qview' style='position:absolute; display:none; top:-125px; left:-10px; width:400px; height:120px; border-radius:12px; background:palegoldenrod; box-shadow:1px 1px 3px 1px #333'>
												<div style='margin:10px; float:left; position:relative;'>
												    <div style='position:absolute; top:0px; left:115px; font-size:14px; width:200px; font-weight:bold;'>".$nprice."</div>
													<div style='float:left; width:100px; height:100px; overflow:hidden; border:1px solid #333'>
													<img src='".$url."' style='width:auto; height:100px'>
													</div>
													<div class ='quick_content' style='float:left; margin-left:12px; height:85px; width:260px; position:relative; overflow:hidden; '>
														<h3 style='margin-top:14px'>".$val1->post_title."</h3>
														<p style='margin-top:-10px'>".$cont1."</p>
														
													</div>".$extra."
												</div>	
											</div>
										  </div></div>";
						
								$ctr++;
							}
						}					    	
						$html .= "<div class='alist'><a style='border:none;' href='post-new.php?post_type=menucategory-post&menucat=".$val->re_int."'>Add dish here</a></div>";
						$html .= "</div>";	 
								
					}
				}
				
				return $html;
		
		}			

		/*===================================================================
		 *    name   : selected_check()
		 *    desc   : check if checkbox is selected
		 *    parm   : n/a
		 *    return : "checked" - for check box selections
		 *===================================================================*/
		public static function selected_check($string, $value ) {
				
				#check if array
				if(!is_array($value)){
					if($value == "all"){
						if($string == "all"){ return 'checked';} else { return 'disabled';}
					} else {
						if($string == $value){ return 'checked';} 
					}
				} else {
					if (in_array($string,$value)){ return 'checked'; }
				}

		}				


		/*===================================================================
		 *    name   : save_custom_post()
		 *    desc   : filter on before saving a post
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/
		 public static function save_custom_post( ) {

			global $post;	
			
			if( $_POST ) {
				update_post_meta( $post->ID, 'meta_menucat', $_POST['cat_id'] );
				$price = $_POST['dish_price'] . "." . $_POST['dish_price_cents'];
				update_post_meta( $post->ID, 'post_dishprice', $price );
				$price2 = $_POST['dish_price2'] . "." . $_POST['dish_price_cents2'];
				update_post_meta( $post->ID, 'post_dishprice2', $price2 );
				$type = $_POST['new_dish'] . "=" . $_POST['special_dish']. "=" . $_POST['hot_dish']. "=" . $_POST['spicy_dish']. "=" . $_POST['vegetarian_dish'];
				update_post_meta( $post->ID, 'post_dishtype', $type );
				$break = (isset($_POST['breakfast'])) ? "1" : "0";
				$lunch = (isset($_POST['lunch'])) ? "1" : "0";
				$dinner = (isset($_POST['dinner'])) ? "1" : "0";
				$serve = $break . "=" . $lunch . "=" . $dinner;
				update_post_meta( $post->ID, 'post_dishserve', $serve );
				
			}

		}

		/*===================================================================
		 *    name   : load_post_data()
		 *    desc   : loading of post or page for settings page selection
		 *    parm   : $type - 'post' or 'page'
		 *             $ptype - the type selected.
		 *             $pid - post/page id 
		 *    return : $html = html dropdown select
		 *===================================================================*/
		 public static function load_post_data($type,$ptype,$pid)	{					
		
			global $wpdb;
			
			if($type == "post"){
				$cond ="post_type !=  '". self::$post_type."' AND post_type != 'page'";
				$id = "id_post";
			} elseif ($type == "page") {
				$cond ="post_type !=  '". self::$post_type."' AND post_type != 'post'";
				$id = "id_page";
			}
			$dis = "";
			if($type != $ptype){$dis = "disabled";}
			
			$sql = "SELECT * 
					FROM " . $wpdb->prefix . "posts 
					WHERE ".$cond." AND post_status =  'publish'
					ORDER BY post_title ASC;";			
						
			$post_all = $wpdb->get_results($sql);
			$html = "";			
			if(!empty($post_all)){
					
					$ctr = 1;
				    $html = '<select id="'.$id.'" style="width:280px;" '.$dis.'>';
					foreach($post_all as $key => $val){ 
							$sel = "";
							if($type == $ptype && $pid == $val->ID){$sel = "selected";}
			          		$html = $html . '<option value="'.$val->ID.'" '.$sel.'>'.$val->post_title . "</option>";		
					}
					$html = $html . '</select>';
			}
			
			return $html;
		}


		/*===================================================================
		 *    name   : display_in_content()
		 *    desc   : content hook if setting is set to post.
		 *    parm   : $content - post content.
		 *    return : $content - post content with attached menu.
		 *===================================================================*/		
		public static function display_in_content($content){

				global $post;
		
				if ( is_home() ) { return; }
				
				$opt = self::get_current_options();
				
				if($opt[3] != "redirect"){
					
					if((is_single($opt[0]) && $opt[2] == "post") || (is_page($opt[0]) && $opt[2] == "page")){
							
							return $content . self::get_menu_display();
					}
				}				

		}


		/*===================================================================
		 *    name   : display_in_redirect()
		 *    desc   : redirect to page if setting is set to redirect.
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/	
		 public static function display_in_redirect() {
	
				if ( is_home() ) { return; }
				
				global $wp_query;
		
				$opt = self::get_current_options();
				
				if($opt[3] == "redirect"){
					if((is_single($opt[0]) && $opt[2] == "post") || (is_page($opt[0]) && $opt[2] == "page")){
							$file = get_bloginfo('wpurl').'/menu/';
							header("Location:".$file);
							exit;
					}
				}

	        }	


		/*===================================================================
		 *    name   : get_current_options()
		 *    desc   : redirect to page if setting is set to redirect.
		 *    parm   : n/a
		 *    return : n/a
		 *===================================================================*/	
		 public static function get_current_options(){
			
			global $wpdb;
							
			$settings = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.self::$table_name_settings); 
			
			$arr_opt = array();
			if(!empty($settings)){
					foreach($settings as $key => $val){
						$arr_opt[] = $val->rs_postid;
						$arr_opt[] = $val->rs_lang;
						$arr_opt[] = $val->rs_posttype;
						$arr_opt[] = $val->rs_type;
					}
			} else {
						$arr_opt[] = null;
						$arr_opt[] = null;
						$arr_opt[] = null;
						$arr_opt[] = null;
			}
			
			return $arr_opt;
	
		}	


		/*===================================================================
		 *    name   : get_menu()
		 *    desc   : this will get menu category for the front end
		 *    parm   : n/a
		 *    return : $htm - html code generated
		 *===================================================================*/
		public static function get_menu()
		{  
				
				global $wpdb;
				
				$dish_list = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.self::$table_name);
				
				$htm =	'<div class="left-rows" id="menu-all">All Categories</div>';	
				
				if(!empty($dish_list)){
					
					foreach($dish_list as $key => $val){
																	
							$htm .=	'<div class="left-rows" id="menu-'.$val->re_int.'">'.$val->re_title.'</div>';					
						
					}
				}
				
				return $htm;
				
		}	
		
		/*===================================================================
		 *    name   : get_dishes()
		 *    desc   : this is for the front end list of dishes
		 *    parm   : n/a
		 *    return : $htm - html code generated
		 *===================================================================*/
		public static function get_dishes()
		{  
				
				global $wpdb;
				
				$dish_list = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.self::$table_name);
				
				$language = self::get_option_setting();
				
				$htm ="";
				
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
												
												$img =  './wp-content/plugins/menu-generator/images/';
												
												$typ = explode("=",$typ);
												$icon = "";
												if(in_array("1", $typ)){
													$icon = "<div style='float:".$float.";clear:both;'>";
													if($typ[0]=="1"){
														$icon .= "<img title='New Dish' src='".$img."new.png' style='margin-left:5px; float:left;'>";
													}
													if($typ[1]=="1"){
														$icon .= "<img title='Special'  src='".$img."special.png' style='margin-left:5px; float:left;'>";
													}
													if($typ[2]=="1"){
														$icon .= "<img title='Hot'  src='".$img."hot.gif' style='margin-left:5px; float:left;'>";
													}
													if($typ[3]=="1"){
														$icon .= "<img  title='Spicy'  src='".$img."spicy.png' style='margin-left:5px; float:left;'>";
													}
													if($typ[4]=="1"){
														$icon .= "<img title='Vegetarian' src='".$img."veggie.png' style='margin-left:5px; float:left;'>";
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
																	
												$htm .=	'<div class="item_box menu-'.$val->re_int.'">
																<div class="inner_item">
																	<div class="img_cont">
																		<img src = "'.$cl[4].'" style="width:100%; height:auto">
																		<div class="price">'.$price.'</div>
																	</div>
																	<div class="itm_details">
																		<h3>'.strtoupper($cl[0]).'</h3>
																		'.$icon.'
																		<p style="clear:both">'.$cl[1].'</p>
																	</div>
																</div>
															</div>';					
										}
									}
					}
				}
				
				return $htm;
				
		}		


		/*===================================================================
		 *    name   : get_option_setting()
		 *    desc   : this will get option settings value
		 *    parm   : n/a
		 *    return : $option - array of option values from settings
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
				
		/*===================================================================
		 *    name   : get_menu_display()
		 *    desc   : this will get menu display for front end inside the
		 *             post content  
		 *    parm   : n/a
		 *    return : $htm - html code generated
		 *===================================================================*/ 		
	 	public static function get_menu_display(){
			
			$htm = '<link rel="stylesheet" href="'.plugins_url().'/menu-generator/menu-files/post-menu.css">
					<script type="text/javascript" src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
					<script type="text/javascript">
					jQuery(document).ready(function(){
						function resizing(){
							var x = jQuery("#menu-cont1").width();
								if(x <= 320){
									jQuery(".itm_details p").hide();
									jQuery(".item_box").css("min-height","100px");
									jQuery(".left-rows").css("font-size","10px");
									jQuery(".left-rows").css("margin-left","-10px");
								} else if(x <= 400){
									jQuery(".itm_details p").hide();
									jQuery(".item_box").css("min-height","120px");
									jQuery(".left-rows").css("font-size","10px");
									jQuery(".left-rows").css("margin-left","-10px");
								} else if(x <= 480){
									jQuery(".itm_details p").show();
									jQuery(".item_box").css("min-height","140px");
									jQuery(".left-rows").css("font-size","10px");
									jQuery(".left-rows").css("margin-left","-10px");
								} else if(x <= 540){
									jQuery(".itm_details p").show();
									jQuery(".item_box").css("min-height","140px");
									jQuery(".left-rows").css("font-size","12px");
									jQuery(".left-rows").css("margin-left","-10px");
								} else if (x <= 640){
									jQuery(".itm_details p").show();
									jQuery(".item_box").css("min-height","240px");
									jQuery(".left-rows").css("font-size","14px");
									jQuery(".left-rows").css("margin-left","-15px");
								} else if (x <= 768){
									jQuery(".itm_details p").show();
									jQuery(".item_box").css("min-height","240px");
									jQuery(".left-rows").css("font-size","14px");
									jQuery(".left-rows").css("margin-left","-15px");
								} else if (x >= 768){
									jQuery(".itm_details p").show();
									jQuery(".item_box").css("min-height","240px");
									jQuery(".left-rows").css("font-size","14px");
									jQuery(".left-rows").css("margin-left","-15px");
								}  
						}
				resizing();
				jQuery(window).resize(function(){
						resizing();
						var y = jQuery("#menu-cont1").width();
						jQuery("#width_").html(y +"=" + jQuery("#left-menu").width());
				});
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
		<p id="width_"></p>
		<div id="menu-cont1">
			<div id="cont">
					<div id="logo">
						<h2 style="margin-left:20px">your logo here</h2>
					</div>
					<div id="m-heading">
						<div class="mhead-inner">All Categories</div>
					</div>
					<div id="left-menu" >
						'.self::get_menu().'					
					</div>
					<div id="right-menu">
							<div id="right-cont">
								<div id="items_dish">
									'.self::get_dishes().'
								</div>
							</div>
					</div>
			</div>
		</div>';			
			
		return $htm;
	
		}			
		
		/*===================================================================
		 *    name   : sanitize()
		 *    desc   : this is just a basic sanitation of strings
		 *    parm   : n/a
		 *    return : $s - sanitized string
		 *===================================================================*/		
		public static function sanitize($input){
				
    		  return $s = preg_replace("/[^A-Z0-9a-z\w ]/u","", $input);

		}


}

/*===================================================================
 *    Instantiate menu_class()
 *===================================================================*/		
menu_class::menu_init();

?>