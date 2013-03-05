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

class WPTodoList
{
	public function __construct()
	{
		register_activation_hook(__FILE__, array($this, 'todolists_register_activation'));
		add_action( 'wpmu_new_blog', array($this,'new_blog'), 10, 6);
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
	}
	
	public function todolists_register_activation($networkwide)
	{

		global $wpdb;
                 
		if (function_exists('is_multisite') && is_multisite()) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if ($networkwide)
			{
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
				foreach ($blogids as $blog_id)
				{
					switch_to_blog($blog_id);
					$this->todolists_create_tables();
				}
				switch_to_blog($old_blog);
				return;
			}   
		} 
		$this->todolists_create_tables();

	}

	public function new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta )
	{

		global $wpdb;
	 
		if (is_plugin_active_for_network('todolists/todolists.php')) {
			$old_blog = $wpdb->blogid;
			switch_to_blog($blog_id);
			todolists_create_tables();
			switch_to_blog($old_blog);
		}
	}

	public function todolists_create_tables()
	{
		
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
	
	public function todolists_init()
	{
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

	function checked_not_ontop( $args, $post_id )
	{
		if ($args['taxonomy'] == 'listcategory' )
		{
			$args['checked_ontop'] = false;
		}

		return $args;
	}
		
	public function todolists_admin_enqueue_scripts()
	{

		wp_enqueue_style('todolists_style.css',plugins_url('css/style.css',__FILE__),false,'1.0');

		wp_enqueue_script('jquery-ui-progressbar');

	}
	
	public function todolists_admin_head()
	{
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

				jQuery(".todolists_task").change(function()
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

				});
				
			});
		</script>
	<?php
	}
	
	public function todolists_wp_ajax_nopriv_updatetask()
	{
		global $wpdb;

		$taskid = $_POST["taskid"];
		$status = $_POST["status"];

		$userid = get_current_user_id();

		if($status == "true")
		{
			$sql = "INSERT IGNORE INTO " . $wpdb->prefix . "todolists_usertask SET user_id='$userid', task_id='$taskid', status='$status', completiondate=NOW()";
			$wpdb->query($sql);
		}
		else
		{
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
	
	public function todolists_wp_enqueue_scripts()
	{
		wp_enqueue_script('jquery-ui-progressbar');		
	}


	public function todolists_wp_ajax_nopriv_todolists()
	{
		echo "<div id='todolists'><div>";
		
		$terms = get_terms( 'listcategory', 'hide_empty=0&parent=0' );
		echo "<table>";

		foreach($terms as $term)
		{

			echo "<tr><td>" . $term->name . "</td><td><button class='todolists_listid' id='" . $term->term_id . "'>Insert</button></td></tr>";

		}

		echo "</table>";
		die();
	}

	function todolists_footer()
	{
		$content = '<div style="width: 100%; text-align: center; font-size: 10px; height: 15px; position: relative;">Powered by <a href="http://www.watchmanadvisors.com/to-do-list-member-wordpress-plugin/" target="_blank">"To Do List Member"</a></div>';
		echo $content;
	}
}
new WPTodoList();
require_once("code/shortcodes.php");
?>