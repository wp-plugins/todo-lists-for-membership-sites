<?php

class WPTodoList_FormTodoList

{



	



	public function get_tasklist($listid)

	{

		global $wpdb,$ip_id,$userid,$todolist_task_disappear;

		

		ob_start();	 
		



		$userid = get_current_user_id();

		if ( is_user_logged_in() ) { 



			$sql = "SELECT task_id FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true'";



		}else{



			$sql = "SELECT task_id FROM " . $wpdb->prefix . "todolists_iptask WHERE ip_id='$ip_id' AND status='true'";



		}

		

		$taskids = $wpdb->get_results($sql, ARRAY_A);



	

			

		$result_task = $wpdb->get_results($sql);

		$save_task_list = array();	

		foreach( $result_task as $result_taskid ){

			$save_task_list[] = $result_taskid->task_id;

			

		}



		

			

		



		$usertasks = array();

		if(is_array($taskids) && count($taskids) > 0)

		{

			foreach($taskids as $taskid)

			{

				$usertasks[$taskid["task_id"]] = true;

			}

		}				

		

		$args = array(

						'numberposts' 	=> -1,

						'post_type' 	=> 'task',

						'post_status' 	=> 'publish',

					);

		$tasks = get_posts ($args);



		$term_tasks = array();







		



			foreach($tasks as $task)

			{

				$terms   = wp_get_post_terms($task->ID ,"listcategory");



				if($todolist_task_disappear == "on" && is_super_admin( $userid ) )

				{	

				

					if(!in_array($task->ID, $save_task_list) ){

						if(count($terms) == 1)

						{

							$term = $terms[0];

							$term_tasks[$term->term_id][] = $task;

						}

						elseif(count($terms) > 1)

						{

							foreach($terms as $term)

							{

								if($term->parent != 0)

								{

									$term_tasks[$term->term_id][] = $task;

									break;

								}

							}

						}



					}

				}

				else{



					if(count($terms) == 1)

					{

						$term = $terms[0];

						$term_tasks[$term->term_id][] = $task;

					}

					elseif(count($terms) > 1)

					{

						foreach($terms as $term)

						{

							if($term->parent != 0)

							{

								$term_tasks[$term->term_id][] = $task;

								break;

							}

						}

					}





				}

			}



			if($listid != 0)

			{

				$tempterm = get_term($listid,'listcategory');

				$terms = array($tempterm);

			}

			else

			{

				$terms = get_terms( 'listcategory', 'hide_empty=0&parent=0' );

			}



					

					















		



		







		$taskcount = 0;





		foreach($terms as $term)

		{

			echo "<div style='font-size: 1.5em; font-weight: bold; margin: 5px 0px;'>" . $term->name . "</div>";



			$id = $term->term_id;



			if(!empty($term_tasks[$id]))

			{

				

				foreach($term_tasks[$id] as $task)

				{

					



						$checked = "";



						if(!empty($usertasks[$task->ID])) $checked = " checked='checked' ";



						echo "<div style='position: relative; float: left;' id='all_todolist_task_id[".$task->ID."]'><input type='checkbox'  name='todolists_task_id".rand()."' class='todolists_task_sdc".$listid."' id='todolists_task_id[".$task->ID."]' $checked /><label for='todolists_task_id[" . $task->ID . "]'>" . $task->post_title . "</label></div>";



						?>

						

						<div class='todolists_heading' id='todolists_heading[<?php echo $task->ID?>]' style='margin-left: 20px; margin-top: 5px; float: left; cursor: pointer; border: solid 1px #aaaaaa; width: 10px; height: 10px; position: relative;'><div style='margin: 4px 2px; height: 2px; background-color: #aaaaaa;'></div><div style='position: absolute; top: 2px; bottom: 2px; left: 4px; right: 4px; background-color: #aaaaaa;' class='todolists_plus'></div></div>



						<div style='float: none; clear: both;' class="todolists_content" id="todolists_content[<?php echo $task->ID?>]"><?php echo $task->post_content; ?></div>

						<div style='float: none; clear: both;'></div>

						

						<?php

						

						$taskcount++;

						

										

					

				}





			}



			$sub_terms = get_terms( 'listcategory', 'hide_empty=0&parent=' . $id );





			if(is_array($sub_terms) && count($sub_terms) > 0)

			{

			

				foreach($sub_terms as $sub_term)

				{



					

					



						echo "<div style='font-size: 1.2em; font-weight: bold; margin: 5px 0px;'>" . $sub_term->name . "</div>";



						$id = $sub_term->term_id;



						if(!empty($term_tasks[$id]))

						{



							foreach($term_tasks[$id] as $task)

							{

								$checked = "";

								if(!empty($usertasks[$task->ID])) $checked = " checked='checked' ";

								echo "<div style='position: relative; float: left;' id='all_todolist_task_id[".$task->ID."]'><input type='checkbox'  name='todolists_task_id".rand()."' class='todolists_task_sdc".$listid."' id='todolists_task_id[" . $task->ID . "]' $checked /><label for='todolists_task_id[" . $task->ID . "]'>" . $task->post_title . "</label></div>";

								?>

								

								<div class='todolists_heading' id='todolists_heading[<?php echo $task->ID?>]' style='margin-left: 20px; margin-top: 5px; float: left; cursor: pointer; border: solid 1px #aaaaaa; width: 10px; height: 10px; position: relative;'><div style='margin: 4px 2px; height: 2px; background-color: #aaaaaa;'></div><div style='position: absolute; top: 2px; bottom: 2px; left: 4px; right: 4px; background-color: #aaaaaa;' class='todolists_plus'></div></div>



								<div style='float: none; clear: both;' class="todolists_content" id="todolists_content[<?php echo $task->ID?>]"><?php echo $task->post_content; ?></div>

								<div style='float: none; clear: both;'></div>



								



								<?php

								$taskcount++;

							}



						}





					

						

				}



			}



		}

	?>

			<script>

				jQuery(document).ready(function()

				{

					/*jQuery("#progressbar_sdc<?php echo $listid; ?>").progressbar(

					{

						value: <?php echo round($percent,0); ?>

					});



					jQuery(".todolists_task_sdc<?php echo $listid; ?>").change(function()

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

							jQuery("#progressbar_sdc<?php echo $listid; ?>").progressbar('option','value',parseInt(taskcountdetails[2]));

							jQuery("#todolist_progressbar_header_sdc<?php echo $listid; ?>").html("<div>Completed " + taskcountdetails[0] + " out of " + taskcountdetails[1] + " tasks, " + taskcountdetails[2] + "%</div>");



						});



					});*/

					jQuery(".todolists_task_sdc<?php echo $listid; ?>").change(function()

					{

						var _taskid = jQuery(this).attr("id").replace("todolists_task_id[","").replace("]","");

						var _status = jQuery(this).is(":checked");

						var main_task_id = 'all_todolist_task_id\\[' + _taskid + '\\]';

						var main_task_heading = 'todolists_heading\\['+ _taskid + '\\]';

						var main_task_desc = 'todolists_content\\['+ _taskid + '\\]';

						

						var widget_main_task_id = 'all_widget_todolists_task_id\\[' + _taskid + '\\]';

						var widget_main_task_heading = 'widget_todolists_heading\\['+ _taskid + '\\]';

						var widget_main_task_desc = 'widget_todolists_content\\['+ _taskid + '\\]';						

						

						

						var taskdata = {

									action: "completetask",

									taskid: _taskid,

									status: _status

								};



						jQuery.post("<?php echo admin_url("admin-ajax.php"); ?>", taskdata, function(data)

						{
							<?php
								if($todolist_task_disappear == "on" && is_super_admin( $userid ) )
								{	?>		
									if(data == "on")

									{

										jQuery('#'+ main_task_id).remove();

										jQuery('#'+ main_task_heading).remove();

										jQuery('#'+ main_task_desc).remove();

										jQuery('#'+ widget_main_task_id).remove();

										jQuery('#'+ widget_main_task_heading).remove();

										jQuery('#'+ widget_main_task_desc).remove();

									}

									
							<?php 	}									

							?>

							
							//var taskcountdetails = data.split("_");

							//jQuery("#progressbar_sdc<?php echo $listid; ?>").progressbar('option','value',parseInt(taskcountdetails[2]));

							//jQuery("#todolist_progressbar_header_sdc<?php echo $listid; ?>").html("<div>Completed " + taskcountdetails[0] + " out of " + taskcountdetails[1] + " tasks, " + taskcountdetails[2] + "%</div>");



						});						

						

					});

				});

			</script>

	<?php	

		return ob_get_clean();

	}

	

	public function get_progressbar($listid)

	{

	

		global $wpdb,$ip_id,$todolist_task_disappear;

			

		ob_start();



		$userid = get_current_user_id();



		

			if ( is_user_logged_in() ) { 



				$sql = "SELECT task_id FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true'";



			}else{



				$sql = "SELECT task_id FROM " . $wpdb->prefix . "todolists_iptask WHERE ip_id=".$ip_id." AND status='true'";



			}

	

		$taskids = $wpdb->get_results($sql, ARRAY_A);



		$result_task = $wpdb->get_results($sql);





		foreach( $result_task as $result_taskid ){

			$save_task_list[] = $result_taskid->task_id;

			

		}

		



		$usertasks = array();

		if(is_array($taskids) && count($taskids) > 0)

		{

			foreach($taskids as $taskid)

			{

				$usertasks[$taskid["task_id"]] = true;

			}

		}



		$args = array(

			'numberposts' 	=> -1,

			'post_type' 	=> 'task',

			'post_status' 	=> 'publish'

		);

		$tasks = get_posts ($args);



		$term_tasks = array();

		

		foreach($tasks as $task)

		{



			$terms = wp_get_post_terms($task->ID ,"listcategory");

			

				if(count($terms) == 1)

				{

					$term = $terms[0];

					$term_tasks[$term->term_id][] = $task;

				}

				elseif(count($terms) > 1)

				{

					foreach($terms as $term)

					{

						if($term->parent != 0)

						{

							$term_tasks[$term->term_id][] = $task;

							break;

						}

					}

				}

			

			

		}



		if($listid != 0)

		{

			$tempterm = get_term($listid,'listcategory');

			$terms = array($tempterm);

		}

		else

		{

			$terms = get_terms( 'listcategory', 'hide_empty=0&parent=0' );

		}



		$taskcount = 0;

		$taskids = array();



		foreach($terms as $term)

		{

			$id = $term->term_id;



			if(!empty($term_tasks[$id]))

			{

				foreach($term_tasks[$id] as $task)

				{

					$taskcount++;

					$taskids[] = $task->ID;

				}

			}



			$sub_terms = get_terms( 'listcategory', 'hide_empty=0&parent=' . $id );

			if(is_array($sub_terms) && count($sub_terms) > 0)

			{		

				foreach($sub_terms as $sub_term)

				{

					$id = $sub_term->term_id;



					if(!empty($term_tasks[$id]))

					{

						foreach($term_tasks[$id] as $task)

						{

							$taskcount++;

							$taskids[] = $task->ID;

						}

					}

				}

			}

		}



		$taskidlist = implode(",",$taskids);



		

		$total = $taskcount;



		$userid = get_current_user_id();

		

		$taskidlist_count = explode(",",$taskidlist);

		if(isset($taskidlist) && $taskidlist!= "")

		{

			if(count($taskidlist_count) > 0)

			{	

				if ( is_user_logged_in() ) { 



					$sql = "SELECT COUNT(task_id) AS completed FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true' AND task_id IN ($taskidlist)";



				}else{



					$sql = "SELECT COUNT(task_id) AS completed FROM " . $wpdb->prefix . "todolists_iptask WHERE ip_id='$ip_id' AND status='true' AND task_id IN ($taskidlist)";



				}				

				

				$row = $wpdb->get_row($sql, ARRAY_A);

				$completed = $row["completed"];

			}

			else

			{

				$completed = 0;			

			}



			if($total != 0) $percent = ($completed * 100 / $total);

			else $percent = 0;



			$percent = round($percent,1);

		



		//echo "<div id='todolist_progressbar_header'><h2>{$completed}/{$total}, {$percent}%</h2></div>";



		?>	

		<style>

			.ui-progressbar

			{

				width: 100%;

				background-color: #aaaaaa;

				border: solid 1px black;

				-webkit-border-radius: 3px;

				-moz-border-radius: 3px;

				border-radius: 3px;

				min-height: 20px;

			}

			.ui-progressbar .ui-progressbar-value

			{

				height: 20px;

				background-image: url("<?php echo plugins_url('img/pbar-ani.gif',dirname(__FILE__)); ?>");

			}

			#progressbar_sdc<?php echo $listid; ?>

			{

				margin-top: 10px;

				margin-bottom: 10px;

				position: relative;

			}

			#todolist_progressbar_header_sdc<?php echo $listid; ?>

			{

				position: absolute;

				top: 3px;

				left: 5px;

				font-size: 12px;

			}

			</style>

			<?php 	

					if ($todolist_task_disappear == "on" && is_super_admin( $userid ) ) {



							if ($total != $completed )  {  ?>



								<div id="progressbar_sdc<?php echo $listid; ?>"><?php echo "<div id='todolist_progressbar_header_sdc".$listid."'><div>Completed {$completed} out of {$total} tasks, {$percent}%</div></div>"; ?></div>



					<?php	}

						}else{ 



							if ($total != $completed )  {  ?>



								<div id="progressbar_sdc<?php echo $listid; ?>"><?php echo "<div id='todolist_progressbar_header_sdc".$listid."'><div>Completed {$completed} out of {$total} tasks, {$percent}%</div></div>"; ?></div>



					<?php	}else{ ?>

							

								<div id="progressbar_sdc<?php echo $listid; ?>"><?php echo "<div id='todolist_progressbar_header_sdc".$listid."'><div>All Task Completed</div></div>"; ?></div>

							

					<?php 	}

						

						}

		  			?>

					

		  	

			<script>

				jQuery(document).ready(function()

				{

					jQuery("#progressbar_sdc<?php echo $listid; ?>").progressbar(

					{

						value: <?php echo round($percent,0); ?>

					});



					jQuery(".todolists_task_sdc<?php echo $listid; ?>").change(function()

					{

						var _taskid = jQuery(this).attr("id").replace("todolists_task_id[","").replace("]","");

						var _status = jQuery(this).is(":checked");

						if(_status == true)

						{

							var widget_task_id = 'widget_todolists_task_id\\[' + _taskid + '\\]';							

							jQuery('#'+ widget_task_id).prop("checked", true);

						}

						else

						{							

							var widget_task_id = 'widget_todolists_task_id\\[' + _taskid + '\\]';							

							jQuery('#'+ widget_task_id).prop("checked", false);

						}



						var taskdata = {

									action: "updatetask",

									taskid: _taskid,

									status: _status

								};



						jQuery.post("<?php echo admin_url("admin-ajax.php"); ?>", taskdata, function(data)

						{

							

							var taskcountdetails = data.split("_");

							//alert(taskcountdetails);//return false;

							jQuery("#progressbar_sdc<?php echo $listid; ?>").progressbar('option','value',parseInt(taskcountdetails[2]));

							if(taskcountdetails[1] == taskcountdetails[0])

							{		

								jQuery("#todolist_progressbar_header_sdc<?php echo $listid; ?>").html("<div>All Task Completed</div>");

							}

							else

							{

								jQuery("#todolist_progressbar_header_sdc<?php echo $listid; ?>").html("<div>Completed " + taskcountdetails[0] + " out of " + taskcountdetails[1] + " tasks, " + taskcountdetails[2] + "%</div>");		

							}

							

							if(jQuery("#progressbar<?php echo $listid; ?>").length)

							{

								jQuery("#progressbar<?php echo $listid; ?>").progressbar('option','value',parseInt(taskcountdetails[2]));

								if(jQuery("#todolist_progressbar_header<?php echo $listid; ?>").length)

								{

									if(taskcountdetails[1] == taskcountdetails[0])

									{		

										jQuery("#todolist_progressbar_header<?php echo $listid; ?>").html("<div>All Task Completed</div>");

									}

									else

									{

										jQuery("#todolist_progressbar_header<?php echo $listid; ?>").html("<div>Completed " + taskcountdetails[0] + " out of" + taskcountdetails[1] + " tasks, " + taskcountdetails[2] + "%</div>");		

									}	

								}

							}							



						});



					});

				});

			</script>

		<?php

		}

		return ob_get_clean();

	}

	

	public function get_taskcountdetails($listid)

	{

		global $wpdb,$ip_id ;

		

		ob_start();



		$userid = get_current_user_id();

		if ( is_user_logged_in() ) { 



			$sql = "SELECT task_id FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true'";



		}else{



			$sql = "SELECT task_id FROM " . $wpdb->prefix . "todolists_iptask WHERE ip_id='$ip_id' AND status='true'";



		}

		

		$taskids = $wpdb->get_results($sql, ARRAY_A);



		$result_task = $wpdb->get_results($sql);





		foreach( $result_task as $result_taskid ){

			$save_task_list[] = $result_taskid->task_id;

			

		}



		$usertasks = array();

		if(is_array($taskids) && count($taskids) > 0)

		{

			foreach($taskids as $taskid)

			{

				$usertasks[$taskid["task_id"]] = true;

			}

		}



		$args = array(

			'numberposts' 	=> -1,

			'post_type' 	=> 'task',

			'post_status' 	=> 'publish'

		);

		$tasks = get_posts ($args);



		$term_tasks = array();



		foreach($tasks as $task)

		{



			$terms = wp_get_post_terms($task->ID ,"listcategory");

			



				if(count($terms) == 1)

				{

					$term = $terms[0];

					$term_tasks[$term->term_id][] = $task;

				}

				elseif(count($terms) > 1)

				{

					foreach($terms as $term)

					{

						if($term->parent != 0)

						{

							$term_tasks[$term->term_id][] = $task;

							break;

						}

					}

				}



		}



		if($listid != 0)

		{

			$tempterm = get_term($listid,'listcategory');

			$terms = array($tempterm);

		}

		else

		{

			$terms = get_terms( 'listcategory', 'hide_empty=0&parent=0' );

		}



		$taskcount = 0;

		$taskids = array();



		foreach($terms as $term)

		{

			$id = $term->term_id;



			if(!empty($term_tasks[$id]))

			{

				foreach($term_tasks[$id] as $task)

				{

					$taskcount++;

					$taskids[] = $task->ID;

				}

			}



			$sub_terms = get_terms( 'listcategory', 'hide_empty=0&parent=' . $id );

			if(is_array($sub_terms) && count($sub_terms) > 0)

			{		

				foreach($sub_terms as $sub_term)

				{

					$id = $sub_term->term_id;



					if(!empty($term_tasks[$id]))

					{

						foreach($term_tasks[$id] as $task)

						{

							$taskcount++;

							$taskids[] = $task->ID;

						}

					}

				}

			}

		}



		$taskidlist = implode(",",$taskids);



		$total = $taskcount;



		$userid = get_current_user_id();

		

		$taskidlist_count = explode(",",$taskidlist);

		if(count($taskidlist_count) > 0)

		{	

			if ( is_user_logged_in() ) { 



				$sql = "SELECT COUNT(task_id) AS completed FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true' AND task_id IN ($taskidlist)";



			}else{



				$sql = "SELECT COUNT(task_id) AS completed FROM " . $wpdb->prefix . "todolists_iptask WHERE ip_id='$ip_id' AND status='true' AND task_id IN ($taskidlist)";



			}

			

			$row = $wpdb->get_row($sql, ARRAY_A);

			$completed = $row["completed"];

		}

		else

		{

			$completed = 0;			

		}



		if($total != 0) $percent = ($completed * 100 / $total);

		else $percent = 0;



		$percent = round($percent,1);



		$taskcountdetails["total"] = $total;

		$taskcountdetails["completed"] = $completed;

		$taskcountdetails["percent"] = $percent;



		return $taskcountdetails;

	}

	

	public function get_tasklistid($taskid)

	{

		$terms = wp_get_post_terms($taskid ,"listcategory");

		

		$listid = 0;

		foreach($terms as $term)

		{

			if($term->parent == 0)

			{

				$listid = $term->term_id;

			}

			else

			{

				$listid = $term->parent;

			}

		}

		return $listid;

	}

}

new WPTodoList_FormTodoList();

?>