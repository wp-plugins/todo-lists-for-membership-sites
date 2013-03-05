<?php
class WPTodoList_FormTodoList
{
	public function get_tasklist($listid)
	{
		global $wpdb;

		ob_start();

		$userid = get_current_user_id();
		$sql = "SELECT task_id FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true'";
		$taskids = $wpdb->get_results($sql, ARRAY_A);

		$usertasks = array();
		if(is_array($taskids) && count($taskids) > 0)
		{
			foreach($taskids as $taskid)
			{
				$usertasks[$taskid["task_id"]] = true;
			}
		}

		$args = array(
			'numberposts' => -1,
			'post_type' => 'task',
			'post_status' => 'publish'
		);
		$tasks = get_posts ($args);

		$term_tasks = array();

		foreach($tasks as $task)
		{
			$terms   = wp_get_post_terms($task->ID ,"listcategory");

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
					echo "<div style='position: relative; float: left;'><input type='checkbox'  name='todolists_task_id[" . $task->ID . "]' class='todolists_task' id='todolists_task_id[" . $task->ID . "]' $checked /><label for='todolists_task_id[" . $task->ID . "]'>" . $task->post_title . "</label></div>";
					?>
					
					<div class='todolists_heading' style='margin-left: 20px; margin-top: 5px; float: left; cursor: pointer; border: solid 1px #aaaaaa; width: 10px; height: 10px; position: relative;'><div style='margin: 4px 2px; height: 2px; background-color: #aaaaaa;'></div><div style='position: absolute; top: 2px; bottom: 2px; left: 4px; right: 4px; background-color: #aaaaaa;' class='todolists_plus'></div></div>

					<div style='float: none; clear: both;' class="todolists_content"><?php echo $task->post_content; ?></div>
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
							echo "<div style='position: relative; float: left;'><input type='checkbox'  name='todolists_task_id[" . $task->ID . "]' class='todolists_task' id='todolists_task_id[" . $task->ID . "]' $checked /><label for='todolists_task_id[" . $task->ID . "]'>" . $task->post_title . "</label></div>";
							?>
							
							<div class='todolists_heading' style='margin-left: 20px; margin-top: 5px; float: left; cursor: pointer; border: solid 1px #aaaaaa; width: 10px; height: 10px; position: relative;'><div style='margin: 4px 2px; height: 2px; background-color: #aaaaaa;'></div><div style='position: absolute; top: 2px; bottom: 2px; left: 4px; right: 4px; background-color: #aaaaaa;' class='todolists_plus'></div></div>

							<div style='float: none; clear: both;' class="todolists_content"><?php echo $task->post_content; ?></div>
							<div style='float: none; clear: both;'></div>

							

							<?php
							$taskcount++;
						}

					}

				}

			}

		}
		
		return ob_get_clean();
	}
	
	public function get_progressbar($listid)
	{
		global $wpdb;

		ob_start();

		$userid = get_current_user_id();
		$sql = "SELECT task_id FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true'";
		$taskids = $wpdb->get_results($sql, ARRAY_A);

		$usertasks = array();
		if(is_array($taskids) && count($taskids) > 0)
		{
			foreach($taskids as $taskid)
			{
				$usertasks[$taskid["task_id"]] = true;
			}
		}

		$args = array(
			'numberposts' => -1,
			'post_type' => 'task',
			'post_status' => 'publish'
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
		$sql = "SELECT COUNT(task_id) AS completed FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true' AND task_id IN ($taskidlist)";
		$row = $wpdb->get_row($sql, ARRAY_A);
		$completed = $row["completed"];

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
			#progressbar
			{
				margin-top: 10px;
				margin-bottom: 10px;
				position: relative;
			}
			#todolist_progressbar_header
			{
				position: absolute;
				top: 3px;
				left: 5px;
				font-size: 12px;
			}
			</style>
			<div id="progressbar"><?php echo "<div id='todolist_progressbar_header'><div>Completed {$completed} out of {$total} tasks, {$percent}%</div></div>"; ?></div>
			<script>
				jQuery(document).ready(function()
				{
					jQuery("#progressbar").progressbar(
					{
						value: <?php echo round($percent,0); ?>
					});
				});
			</script>
		<?php

		return ob_get_clean();
	}
	
	public function get_taskcountdetails($listid)
	{
		global $wpdb;

		ob_start();

		$userid = get_current_user_id();
		$sql = "SELECT task_id FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true'";
		$taskids = $wpdb->get_results($sql, ARRAY_A);

		$usertasks = array();
		if(is_array($taskids) && count($taskids) > 0)
		{
			foreach($taskids as $taskid)
			{
				$usertasks[$taskid["task_id"]] = true;
			}
		}

		$args = array(
			'numberposts' => -1,
			'post_type' => 'task',
			'post_status' => 'publish'
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
		$sql = "SELECT COUNT(task_id) AS completed FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true' AND task_id IN ($taskidlist)";
		$row = $wpdb->get_row($sql, ARRAY_A);
		$completed = $row["completed"];

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