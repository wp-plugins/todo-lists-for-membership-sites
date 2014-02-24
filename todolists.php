<?php
/*
Plugin Name: To Do List Member
Plugin URI: http://www.watchmanadvisors.com/to-do-list-member-wordpress-plugin/
Description: Todo list for membership sites
Version: 1.3
Author: Trent Jessee
Author URI:  http://prosperfi.com
License: GPLv2 or later
*/

class WPTodoList{
	public function __construct(){
		register_activation_hook(__FILE__, array($this, 'todolists_register_activation'));
		add_action('wpmu_new_blog', array($this,'new_blog'), 10, 6);
		add_action('init', array($this, 'todolists_init'));
		add_action('admin_enqueue_scripts', array($this, 'todolists_admin_enqueue_scripts'));
		add_action('admin_head', array($this, 'todolists_admin_head'));
		add_action('wp_head', array($this, 'todolists_admin_head'));
		add_action('wp_ajax_updatetask', array($this, 'todolists_wp_ajax_nopriv_updatetask'));
		add_action('wp_ajax_nopriv_updatetask', array($this, 'todolists_wp_ajax_nopriv_updatetask'));
		add_action('wp_enqueue_scripts', array($this, 'todolists_wp_enqueue_scripts'));
		add_action('wp_ajax_todolists', array($this, 'todolists_wp_ajax_nopriv_todolists'));
		add_action('wp_ajax_nopriv_todolists', array($this, 'todolists_wp_ajax_nopriv_todolists'));
		add_filter('wp_terms_checklist_args', array($this, 'checked_not_ontop'), 1, 2 );
		add_action('wp_footer', array($this,'todolists_footer'));

		add_action('admin_menu', array($this, 'todolists_register_submenu'));
		add_filter('views_edit-task', array($this, 'tdl_button'));
		add_action('admin_head-edit.php', array($this, 'tdl_button_moved'));
		add_action('admin_init', array($this, 'tdl_download_xml'));
		add_action('wp_ajax_todolists_import', array($this, 'todolists_import_wp_ajax_nopriv_todolists'));
		add_action('wp_ajax_nopriv_todolists_import', array($this, 'todolists_import_wp_ajax_nopriv_todolists'));
		require_once("widget/widget-todolist.php");
		add_action('widgets_init', array($this, 'tdl_register_widget'));
	}

	public function tdl_register_widget(){
		register_widget('todolist_widget');
	}

	private function tdl_get_last_taxonomy_number($catname){
		global $wpdb;
		$table_terms	= $wpdb->prefix.'terms';
		$table_txn		= $wpdb->prefix.'term_taxonomy';

		$get = $wpdb->get_row("SELECT term.term_id, term.name FROM $table_terms AS term INNER JOIN $table_txn AS txn ON term.term_id=txn.term_id 
WHERE txn.taxonomy='listcategory'
AND term.name LIKE '$catname%' 
ORDER BY term.term_id DESC
LIMIT 1");
		$foundcatname	= $get->name;
		$returnfcatname	= (int)filter_var($foundcatname, FILTER_SANITIZE_NUMBER_INT);
		return $returnfcatname;
	}

	public function todolists_import_wp_ajax_nopriv_todolists(){
		$arraygenxml	= $_POST['genxml'];

		$args = array(
		    'orderby'       => 'name', 
		    'order'         => 'ASC',
		    'hide_empty'    => false,
		); 

		$get = get_terms('listcategory', $args);
		if(!empty($get)){
			foreach($get as $txn){
				$farrtxnid[] = $txn->term_id;
			}
		}

		/*$i = 1;
		foreach($arraygenxml as $catnametoadd){
			$gterm 	= get_term_by('name', $catnametoadd, 'listcategory');
			$termid	= !empty($gterm->term_id) ? $termid = $gterm->term_id : $termid = -1;

			if(in_array($termid, $arrtxnid)){
				$sign = $i;
			}else{
				$sign = '';
			}

			$obj 	= wp_insert_term($catnametoadd.$sign, 'listcategory');
			$arrcattotxn[] = $obj['term_id'];

			$i++;
		}
		var_dump($arrcattotxn);
		exit();*/
		if(is_null($farrtxnid)){
			$arrtxnid = array();
		}else{
			$arrtxnid = $farrtxnid;
		}

		$xmlfileurl 	= $_POST['xmlfileurl'];
		$xmlstring 		= @file_get_contents($xmlfileurl);
		$xml 	= simplexml_load_string($xmlstring);
		$json 	= json_encode($xml);
		$array 	= json_decode($json, TRUE);
		$arrayxml 	= $array['category'];

		if(!empty($arrayxml)){
			$carxml = $arrayxml['@attributes']['name'];		
			
			if(is_null($carxml)){
				foreach($arrayxml as $k => $v){
					$fcat 			= $v['@attributes']['name'];
					$arrpost 		= $v['post'];
					krsort($arrpost);
					$arrayf[$fcat] 	= $arrpost;

					/*$i = 1;
					foreach($arrpost as $post){
						$ptitle[] 	= $post['post_title'];
						$pcontent	= $post['post_content'];
						
						$date 		= date('Y-m-d H:i:s');
						$dateinsec 	= strtotime($date)-60;
						$newdate	= $dateinsec+$i;

						$xmlargs 	= array(
							'post_type'		=> 'task',
							'post_title'	=> $ptitle,
							'post_status'	=> 'publish',
							'post_content'	=> $pcontent,
							'post_date'		=> date('Y-m-d H:i:s', $newdate),
							'post_date_gmt' => date('Y-m-d H:i:s', $newdate)
						);
						$postid = wp_insert_post($xmlargs);
						wp_set_post_terms($postid, $arraygenxml, 'listcategory', true);
						
						$i+=2;
					}*/
				}
			}else{
				$fcat 			= $arrayxml['@attributes']['name'];
				$arrpost 		= $arrayxml['post'];
				krsort($arrpost);
				$arrayf[$fcat] 	= $arrpost;
			}
		}

		if(!empty($arrayf)){
			foreach($arrayf as $k => $v){
				if(!in_array($k, $arraygenxml)){
					unset($arrayf[$k]);
				}
			}
		}

		if(!empty($arrayf)){
			$j = 1;
			foreach($arrayf as $key => $value){
				$foundcat = $key;
				$i = 1;
				$gterm 	= get_term_by('name', $foundcat, 'listcategory');
				$termid	= !empty($gterm->term_id) ? $termid = $gterm->term_id : $termid = -1;
				
				$getexistingidcat = $this->tdl_get_last_taxonomy_number($foundcat);

				if(in_array($termid, $arrtxnid)){
					$sign = $getexistingidcat+1;
				}else{
					$sign = '';
				}
				
				$obj 	= wp_insert_term($foundcat.$sign, 'listcategory');
				$arrcattotxn = array($obj['term_id']);

				foreach($value as $postfound){
					$pftitle	= $postfound['post_title'];
					$pfcontent	= $postfound['post_content'];

					$date 		= date('Y-m-d H:i:s');
					$dateinsec 	= strtotime($date)-250;
					$newdate	= $dateinsec+$i;

					$xmlargs 	= array(
						'post_type'		=> 'task',
						'post_title'	=> $pftitle,
						'post_status'	=> 'publish',
						'post_content'	=> $pfcontent,
						'post_date'		=> date('Y-m-d H:i:s', $newdate),
						'post_date_gmt' => date('Y-m-d H:i:s', $newdate)
					);
					$postid = wp_insert_post($xmlargs);

					/** SET TERM **/
					wp_set_post_terms($postid, $arrcattotxn, 'listcategory', true);
					
					$i+=5;
				}
				$j++;
			}
		}

		exit();
		/*echo json_encode($_POST); exit();

		if(!empty($arraygenxml)){
			$i = 1;
			foreach($arraygenxml as $genxml){
				$date 		= date('Y-m-d H:i:s');
				$dateinsec 	= strtotime($date)-60;
				$newdate	= $dateinsec+$i;
					
				$arraygenxmln 	= explode("|", $genxml);
				$title 			= $arraygenxmln[0];
				$content 		= $arraygenxmln[1];
				$xmlargs 	= array(
					'post_type'		=> 'task',
					'post_title'	=> $title,
					'post_status'	=> 'publish',
					'post_content'	=> $content,
					'post_date'		=> date('Y-m-d H:i:s', $newdate),
					'post_date_gmt' => date('Y-m-d H:i:s', $newdate)
				);
				$postid = wp_insert_post($xmlargs);
				wp_set_post_terms($postid, $arraycatxml, 'listcategory', true);
				$i+=2;
			}
		}
		exit();*/
	}

	public function xmlsafe($value, $inquotes=false){
		if ($inquotes) return str_replace(array('&','>','<','"'), array('&amp;','&gt;','&lt;','&quot;'), $value);
		else return str_replace(array('&','>','<'), array('&amp;','&gt;','&lt;'), $value);
	}

	public function downloadtextasfile($text, $filename){
		$fsize 	= strlen($text);
		header('Set-Cookie: fileDownload=true; path=/');
		header("HTTP/1.1 200 OK");
		header("Pragma: public");
		header("Expires: 0"); 
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Cache-Control: private",false);
		header("Content-type: application/force-download");  
		header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";" ); 
		header("Content-Transfer-Encoding: binary"); 
		header("Content-Length: " . $fsize); 
		ob_clean();
		flush(); 
		echo $text;
		exit();
	}

	public function tdl_download_xml(){
		if(isset($_GET['export']) && $_GET['export'] == 'XML'){
			$arraycatlist 	= explode(",", $_GET['catids']);
			$filename 		= "todolist_" . date("YmdHis") . ".xml";
			
			$text  = "";
			$text .= "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
			$text .= "<todolist>\n";
			foreach($arraycatlist as $cid){
				$gterm 	= get_term_by('term_id', $cid, 'listcategory');
				$term 	= $gterm->name;
				$params = array(
					'posts_per_page'=> -1,
					'post_type' 	=> 'task',
					'post_status' 	=> 'publish',
					'orderby' 		=> 'date',
					'order'			=> 'DESC',
					'tax_query' 	=> array(
				        array(
				        'taxonomy' 	=> 'listcategory',
				        'field' 	=> 'term_id',
				        'terms' 	=> $cid
				        )
				    )
				);

				$tasks 	= query_posts($params);

				$text .= "<category name='".$term."'>\n";
				foreach($tasks as $task){
					$text .= "<post>\n";
					$text .= "<post_title>".$this->xmlsafe($task->post_title)."</post_title>\n";
					$text .= "<post_content>".$this->xmlsafe($task->post_content)."</post_content>\n";
					$text .= "</post>\n";
				}
				$text .= "</category>\n";
			}
			$text .= "</todolist>";

			$this->downloadtextasfile($text, $filename);
			exit();
		}
	}

	public function tdl_button($views){
		$views['my-button'] = '<span id="tdl_button_extra"><button id="tdl_button_exportxml" class="button button-primary" style="margin: 1px 8px 0 0;">Export to XML</button><button id="tdl_button_importxml" class="button button-primary" style="margin: 1px 8px 0 0;">Import from XML</button></span>';
   	 	return $views;
	}

	public function tdl_button_moved(){
		global $current_screen;
	    if('task' != $current_screen->post_type)
	        return;
	    $handleurl	= plugins_url('res/', __FILE__);
	    ?>
	    <script>
		jQuery(function($){
		    'use strict';
		    var url = '<?php echo $handleurl; ?>';
		    jQuery('#tdl_fileupload').fileupload({
		        url: url,
		        dataType: 'json',
		        done: function (e, data) {
		            jQuery.each(data.result.files, function (index, file) {
		                var xmlfileurl = file.url;
		                if(xmlfileurl == undefined){
		                	alert('Only XML file are allowed!'); return false;
		                }else{
		                	jQuery.ajax({
							    type 	: "GET",
							    url 	: xmlfileurl,
							    dataType: "xml",
							    success : function (xml) {
							    	jQuery('#tdl_select_cat_xml').prop('disabled', false);
									jQuery('#tdl_button_importxml_execute').prop('disabled', false);

									/*var array 	= [];
									var title 	= [];
									var content = [];
									var cat 	= [];

									$(xml).find('post').each(function(i, v){
								      	var ftitle 	= $(this).find('post_title').text();
								      	var fcontent = $(this).find('post_content').text();
								      	
								      	title[i] 	= ftitle;
								      	content[i] 	= fcontent;
								      	$(xml).find('category').each(function(idx, j){
								      		cat[idx] = $(j).attr('name');
								      	});

								      	array[cat] = [];
								    });

									console.log(cat);
									console.log(title);
									console.log(content);
									console.log(array);
									return false;

									$(xml).find('category').each(function(idx, j){
								      	cat 		= $(j).attr('name');
								      	array[cat]	= [];
								      	var ftitle 	= $(this).find('post_title').text();
								      	var fcontent = $(this).find('post_content').text();
								      	$(j).find("post").each(function(i , vi){
								      		title = $(j).find('post_title').text();
								      		content = $(j).find('post_content').text();

								      		array[cat].push(title);
								      	});
								    });

								    console.log(array);*/

									var option;
							    	$(xml).find('category').each(function(idx, j){
							    		var cat 	= $(j).attr('name');
								      	/*var title 	= $(this).find('post_title').text();
								      	var content = $(this).find('post_content').text();
								      	
								      	if(title == '' || content == ''){
								      		alert('Invalid XML format!');
								      		option = '';
								      		return false;
								      	}else{
								      		option += '<option value="'+cat+'">'+cat+'</option>';
								      	}*/
								      	option += '<option value="'+cat+'">'+cat+'</option>';
								    });

								    if(option != ''){
								    	jQuery('#tdl_generic_parsedxml').append('<label for="tdl_generic_parsedxml">Select Categories</label><br><select name="tdl_generic_xml[]" id="tdl_generic_xml" style="width:50%" multiple>'+option+'</select>');
								    	jQuery('#tdl_xmlfileurl').val(xmlfileurl);
								    }
							    }
							});
		                }
		            });
		        }
		    }).prop('disabled', !jQuery.support.fileInput)
		        .parent().addClass(jQuery.support.fileInput ? undefined : 'disabled');
		});
		</script>
	    <script type="text/javascript">
	        jQuery(document).ready(function($) 
	        {
	        	jQuery('#tdl_button_importxml_execute').click(function(e){
	        		e.preventDefault();
	        		jQuery(this).prop('disabled', true);
	        		//var catxml = jQuery('#tdl_select_cat_xml').val();
	        		var genxml = jQuery('#tdl_generic_xml').val();
	        		var xmlfileurl = jQuery('#tdl_xmlfileurl').val();

	        		if(genxml == null){
	        			alert('Please select the Parsed XML');
	        			return false;
	        		}

	        		//if(catxml == null){
	        			//alert('Please select the Category!');
	        			//return false;
	        		//}
	        		var data = {action : 'todolists_import', genxml : genxml, xmlfileurl : xmlfileurl};
	        		jQuery.post(window.todolists_ajaxurl, data, function (response){
	        			//console.log(response);
	        			jQuery('#tdl_button_importxml_execute').prop('disabled', false);
	        			//return false;
	        			$.colorbox.remove();
	        			window.location.reload();
	        		});
	        	});

	        	jQuery('#tdl_button_importxml').click(function(e){
	        		e.preventDefault();
	        		jQuery.colorbox(
					{
						inline: true,
						href: '#tdl_inline_import',
						width:'50%',
						height: '60%',
						scrolling:true,
						fixed:true,
						escKey: false,
						overlayClose: false,
						closeButton: true
					});
					jQuery('#tdl_select_cat_xml').prop('disabled', true);
					jQuery('#tdl_button_importxml_execute').prop('disabled', true);
	        	});

	            jQuery('#tdl_button_extra').insertAfter('input#post-query-submit');
	            jQuery('#tdl_button_exportxml').click(function(e){
	            	e.preventDefault();

	            	jQuery.colorbox(
					{
						inline: true,
						href: '#tdl_inline_export',
						width:'30%',  
						scrolling:true,
						fixed:true,
						escKey: false,
						overlayClose: false,
						closeButton: true
					});
	            });

	            jQuery('#tdl_select_cat_popup').click(function(){
	            	var catids 	= jQuery(this).val();
	            	var dlurl 	= '?post_type=task&export=XML&catids='+catids;
	            	jQuery('#tdl_button_downloadxml').attr('href', dlurl);
	            });

	            jQuery('#tdl_button_downloadxml').click(function(e){
	            	var check = jQuery('#tdl_select_cat_popup').val();
	            	if(check == null)
	            	{
	            		alert('Please select the category!'); return false;
	            	}
	            });
	        });     
	    </script>

	    <div style='display:none;'>
	    	<div id="tdl_inline_export" style="text-align:center">
	    		<form action="" method="POST" id="tdl_form_exportxml">
				    <select name="tdl_select_cat_popup[]" id="tdl_select_cat_popup" multiple style="width:50%;">
						<?php
						$sera = array(
							'type'      	=> 'post',
							'orderby'  	 	=> 'name',
							'order'  		=> 'ASC',
							'taxonomy'  	=> 'listcategory',
							'hide_empty'	=> 0 
						);
						$categories = get_categories($sera);
						if(!empty($categories)){
							foreach($categories as $cat){
								$option .= '<option value="'.$cat->cat_ID.'">'.$cat->name.'</option>';
							}
							echo $option;
						}
						?>
					</select><p/>
					<a href="" class="button button-primary" id="tdl_button_downloadxml">Download</a>
				</form>
			</div>
		</div>

		<div style='display:none;'>
	    	<div id="tdl_inline_import">
	    		<span class="button button-primary fileinput-button">
			        <span>Select files...</span>
			        <input id="tdl_fileupload" type="file" name="files">
			    </span>
			    <form action="" method="POST">
			    	<br>
			    	<div id="tdl_generic_parsedxml"></div><br>
			    	<input type="hidden" name="tdl_xmlfileurl" id="tdl_xmlfileurl"/>
			    	<!--<label for="tdl_select_cat_xml">List Category</label><br>
			    	<select name="tdl_select_cat_xml[]" id="tdl_select_cat_xml" multiple style="width:50%;">
						<?php
						/*$sera = array(
							'type'      	=> 'post',
							'orderby'  	 	=> 'name',
							'order'  		=> 'ASC',
							'taxonomy'  	=> 'listcategory',
							'hide_empty'	=> 0 
						);
						$categories = get_categories($sera);
						if(!empty($categories)){
							foreach($categories as $cat){
								$options .= '<option value="'.$cat->cat_ID.'">'.$cat->name.'</option>';
							}
							echo $options;
						}*/
						?>
					</select>--><p/>
					<button class="button button-primary" id="tdl_button_importxml_execute">Import</button>
			    </form>
			</div>
		</div>
	    <?php 
	}
	
	public function todolists_register_submenu(){
		add_submenu_page(
		    'edit.php?post_type=task',
		    'Order Tasks',
		    'Order Tasks',
		    'manage_options',
		    'todolists_ordertask',
		    array($this, 'todolists_ordertask_callback')
		);
	}

	public function todolists_ordertask_callback(){
		if($_POST['submit'] == 'Save'){

			foreach($_POST['tdl_order'] as $key => $null){
				$array[] = $key;
			}

			krsort($array);
			$i = 1;
			foreach($array as $pid){
				$date 		= date('Y-m-d H:i:s');
				$dateinsec 	= strtotime($date)-250;
				$newdate	= $dateinsec+$i;

				$prm 	= array(
					'ID'		=> $pid,
					'post_date'	=> date('Y-m-d H:i:s', $newdate),
					'post_date_gmt' => date('Y-m-d H:i:s', $newdate),
				);
				wp_update_post($prm);
				$i+=5;
			}
			echo '<div class="updated"><p>Task List order saved</p></div>';
		}
		?>
		<div class="wrap nosubsub">
			<h2>Order Tasks</h2><p/>

			<select name="tdl_select_cat" id="tdl_select_cat" onChange="viewTasks(this.value)">
				<option>Select List Category</option>
				<?php
				$sera = array(
					'type'      	=> 'post',
					'orderby'  	 	=> 'name',
					'order'  		=> 'ASC',
					'taxonomy'  	=> 'listcategory',
					'hide_empty'	=> 0 
				);
				$categories = get_categories($sera);
				if(!empty($categories)){
					foreach($categories as $cat){
						$option .= '<option value="'.$cat->cat_ID.'">'.$cat->name.'</option>';
					}
					echo $option;
				}
				?>
			</select><br/>

			<form id="todolist_order_form" action="" method="POST">
				<table id="todolists_order_table" class="wp-list-table widefat plugins" cellspacing="0">
					<thead>
						<th>Title</th>
					</thead>
					<tbody>
						<?php
							if(!isset($_GET['cid'])){
								echo '<tr>';
								echo '<td>Please select list category!</td>';
								echo '</tr>';
							}else{
								$params = array(
									'posts_per_page'=> -1,
									'post_type' 	=> 'task',
									'post_status' 	=> 'publish',
									'orderby' 		=> 'date',
									'order'			=> 'DESC',
									'tax_query' 	=> array(
								        array(
								        'taxonomy' 	=> 'listcategory', 
								        'field' 	=> 'term_id', 
								        'terms' 	=> $_GET['cid'])
								    )
								);

								$tasks 	= query_posts($params);			
								if(!empty($tasks)){
									foreach($tasks as $t){
										echo '<tr>';
										echo '<td class="todolists_order" style="cursor:move;">'.$t->post_title.'<input type="hidden" name="tdl_order['.$t->ID.']"/></td>';
										echo '</tr>';
									}
								}
							}
						?>
					</tbody>
				</table>
				<?php if(isset($_GET['cid'])){submit_button("Save");} ?>
			</form>
		</div>
		<script type="text/javascript">
		function viewTasks(id){
			location.href='?post_type=task&page=todolists_ordertask&cid='+id;
		}
		</script>
		<?php
	}

	public function todolists_register_activation($networkwide){
		global $wpdb;
                 
		if (function_exists('is_multisite') && is_multisite()) {
			if ($networkwide){
				$old_blog 	= $wpdb->blogid;
				$blogids 	= $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
				foreach ($blogids as $blog_id){
					switch_to_blog($blog_id);
					$this->todolists_create_tables();
				}
				switch_to_blog($old_blog);
				return;
			}   
		} 
		$this->todolists_create_tables();
	}

	public function new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ){
		global $wpdb;
	 
		if (is_plugin_active_for_network('todolists/todolists.php')) {
			$old_blog = $wpdb->blogid;
			switch_to_blog($blog_id);
			todolists_create_tables();
			switch_to_blog($old_blog);
		}
	}

	public function todolists_create_tables(){	
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;

		$sql = "CREATE TABLE " . $wpdb->prefix . "todolists_usertask (
				  user_id int(11) NOT NULL,
				  task_id int(11) NOT NULL,
				  status varchar(10) NOT NULL,
				  completiondate datetime NOT NULL,
				  PRIMARY KEY (user_id,task_id)
				);";

		dbDelta($sql);
	}
	
	public function todolists_init(){
		register_post_type( 'task',
		array(
				'labels' => array(
					'name' => 'Tasks',
					'singular_name' => 'Task',
					'add_new' => 'Add New Task',
					'add_new_item' => 'Add New Task',
					'edit' => 'Edit',
					'edit_item' => 'Edit Task',
					'new_item' => 'New Task',
					'view' => 'View',
					'view_item' => 'View Task',
					'search_items' => 'Search Tasks',
					'not_found' => 'No Tasks found',
					'not_found_in_trash' => 'No Tasks found in Trash',
					'parent' => 'Parent Task'
				),
				'public' => true,
				'menu_position' => 100,
				'supports' => array( 'title', 'editor'),
				'taxonomies' => array( '' ),
				'menu_icon' => plugins_url( 'img/wp-icon.png', __FILE__ ),
				'has_archive' => true
			)
		);

		  $labels = array(
			'name' => _x( 'List Categories', 'taxonomy general name' ),
			'singular_name' => _x( 'List Category', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search List Categories' ),
			'all_items' => __( 'All List Categories' ),
			'parent_item' => __( 'Parent List Category' ),
			'parent_item_colon' => __( 'Parent List Category:' ),
			'edit_item' => __( 'Edit List Category' ), 
			'update_item' => __( 'Update List Category' ),
			'add_new_item' => __( 'Add New List Category' ),
			'new_item_name' => __( 'New List Category Name' ),
			'menu_name' => __( 'List Categories' ),
		  ); 	

		  register_taxonomy('listcategory',array('task'), array(
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'listcategory' ),
		  ));
	}

	function checked_not_ontop( $args, $post_id ){
		if ($args['taxonomy'] == 'listcategory' )
		{
			$args['checked_ontop'] = false;
		}
		return $args;
	}
		
	public function todolists_admin_enqueue_scripts(){
		wp_enqueue_style('colorbox.css', plugins_url('css/colorbox.css', __FILE__), false, 'screen');
		wp_enqueue_style('todolists_style.css',plugins_url('css/style.css',__FILE__),false, '1.0');
		wp_enqueue_style('jquery.fileupload', plugins_url('css/jquery.fileupload.css', __FILE__), false, 'screen');

		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-progressbar');
		wp_enqueue_script('jquery.ui.widget', plugins_url('js/jquery.ui.widget.js', __FILE__), array(), true);
		wp_enqueue_script('jquery.colorbox-min', plugins_url('js/jquery.colorbox-min.js', __FILE__), array(), true);
		wp_enqueue_script('jquery.fileupload', plugins_url('js/jquery.fileupload.js', __FILE__), array(), true);
	}
	
	public function todolists_admin_head(){
		?>
		<style type="text/css">
			.layer1 {
			margin: 0;
			padding: 0;
			width: 100%;
			}
			 
			.heading {
			margin: 1px;
			color: #fff;
			padding: 3px 10px;
			cursor: pointer;
			position: relative;
			background-color:#B0ABAD;
			}
			.content {
			padding: 5px 10px;
			background-color:#fafafa;
			}
			p { padding: 5px 0; }
		</style>
		
		<script type="text/javascript">
			jQuery(document).ready(function() {
			  jQuery(".todolists_content").hide();
			  jQuery(".todolists_heading").click(function()
			  {
   			    jQuery(this).children(".todolists_plus").toggle();
				jQuery(this).next(".todolists_content").slideToggle(500);
			  });
			});
		</script>
		
		<script>
			jQuery(document).ready(function(){
				window.todolists_ajaxurl = "<?php echo admin_url("admin-ajax.php"); ?>";

				var fixHelper = function(e, ui)
				{
					ui.children().each(function()
					{
						jQuery(this).width(jQuery(this).width());
					});
					return ui;
				};

				jQuery("#todolists_order_table tbody").sortable({disabled: true, helper: fixHelper});
				jQuery(".todolists_order").hover(function ()
				{
					jQuery("#todolists_order_table tbody").sortable("enable");
					jQuery(this).css('background-color', '#818080');
				}, 
				 function ()
				{
					jQuery("#todolists_order_table tbody").sortable("disable");
					jQuery(this).css('background-color', 'white');
				});

				/*jQuery(".todolists_task").change(function()
				{
					var _taskid = jQuery(this).attr("id").replace("todolists_task_id[","").replace("]","");
					var _status = jQuery(this).is(":checked");

					var taskdata = {
								action: "updatetask",
								taskid: _taskid,
								status: _status
							};

					jQuery.post("<?php echo admin_url("admin-ajax.php"); ?>", taskdata, function(data)
					{
						//alert(data);
						var taskcountdetails = data.split("_");
						jQuery("#progressbar").progressbar('option','value',parseInt(taskcountdetails[2]));
						jQuery("#todolist_progressbar_header").html("<div>Completed " + taskcountdetails[0] + " out of " + taskcountdetails[1] + ", " + taskcountdetails[2] + "%</div>");

					});

				});*/
				
			});
		</script>
	<?php
	}
	
	public function todolists_wp_ajax_nopriv_updatetask(){
		global $wpdb;

		$taskid = $_POST["taskid"];
		$status = $_POST["status"];

		$userid = get_current_user_id();

		if($status == "true"){
			$sql = "INSERT IGNORE INTO " . $wpdb->prefix . "todolists_usertask SET user_id='$userid', task_id='$taskid', status='$status', completiondate=NOW()";
			$wpdb->query($sql);
		}else{
			$sql = "DELETE FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND task_id='$taskid'";
			$wpdb->query($sql);
		}

		require_once("code/form_todolist_user.php");
		$form 	= new WPTodoList_FormTodoList();
		$listid = $form->get_tasklistid($taskid);
		
		$taskcountdetails = $form->get_taskcountdetails($listid);
		echo $taskcountdetails["completed"] . "_" . $taskcountdetails["total"] . "_" . $taskcountdetails["percent"] . "_" . $listid;
		die();
	}
	
	public function todolists_wp_enqueue_scripts(){
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-progressbar');		
	}


	public function todolists_wp_ajax_nopriv_todolists(){
		echo "<div id='todolists'><div>";
		$terms = get_terms( 'listcategory', 'hide_empty=0&parent=0' );
		echo "<table>";
		foreach($terms as $term){
			echo "<tr><td>" . $term->name . "</td><td><button class='todolists_listid' id='" . $term->term_id . "'>Insert</button></td></tr>";
		}
		echo "</table>";
		die();
	}

	function todolists_footer(){
		$content = '<div style="width: 100%; text-align: center; font-size: 10px; height: 15px; position: relative;">Powered by <a href="http://www.watchmanadvisors.com/to-do-list-member-wordpress-plugin/" target="_blank">"To Do List Member"</a></div>';
		echo $content;
	}
}
new WPTodoList();
require_once("code/shortcodes.php");
?>