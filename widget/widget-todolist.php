<?php
class todolist_widget extends WP_Widget 
{
	/** constructor */
    function todolist_widget() 
	{
		$widget_options = array(
			'classname' 	=> 'todolist_widget',
			'description' 	=> 'ToDo List Tasks'
		);
        parent::WP_Widget('todolist_base', __('ToDo List Tasks', 'todolist'), $widget_options);
    }

	/** @see WP_Widget::widget */
    function widget($args, $instance) 
	{
		global $wpdb;
        extract($args);
        echo $before_widget;
        // <h3 class="widget-title">Calendar</h3>

        $listid				= esc_attr($instance['listcategory']);
        $displayprogressbar = esc_attr($instance['displayprogressbar']);
        if(empty($displayprogressbar)) $displayprogressbar = 'off';

        /** TASK LIST **/
		$userid 	= get_current_user_id();
		$sql 		= "SELECT task_id FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true'";
		$taskids 	= $wpdb->get_results($sql, ARRAY_A);

		$usertasks 	= array();
		if(is_array($taskids) && count($taskids) > 0){
			foreach($taskids as $taskid){
				$usertasks[$taskid["task_id"]] = true;
			}
		}

		$pargs = array(
			'numberposts' 	=> -1,
			'post_type' 	=> 'task',
			'post_status' 	=> 'publish'
		);
		$tasks = get_posts($pargs);

		$term_tasks = array();
		foreach($tasks as $task){
			$terms   = wp_get_post_terms($task->ID ,"listcategory");
			if(count($terms) == 1){
				$term = $terms[0];
				$term_tasks[$term->term_id][] = $task;
			}
			elseif(count($terms) > 1){
				foreach($terms as $term){
					if($term->parent != 0){
						$term_tasks[$term->term_id][] = $task;
						break;
					}
				}
			}
		}

		if($listid != 0){
			$tempterm 	= get_term($listid, 'listcategory');
			$terms 		= array($tempterm);
		}else{
			$terms = get_terms( 'listcategory', 'hide_empty=0&parent=0' );
		}

		$taskcount = 0;

		foreach($terms as $term){
			echo "<div style='font-size: 1.5em; font-weight: bold; margin: 5px 0px;'>" . $term->name . "</div>";
			/** PROGRESS BAR **/

			if($displayprogressbar == 'on'){
				$userid 	= get_current_user_id();
				$sql 		= "SELECT task_id FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true'";
				$taskids 	= $wpdb->get_results($sql, ARRAY_A);

				$usertasks 	= array();
				if(is_array($taskids) && count($taskids) > 0){
					foreach($taskids as $taskid){
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

				foreach($tasks as $task){
					$terms = wp_get_post_terms($task->ID ,"listcategory");

					if(count($terms) == 1){
						$term = $terms[0];
						$term_tasks[$term->term_id][] = $task;
					}
					elseif(count($terms) > 1){
						foreach($terms as $term){
							if($term->parent != 0){
								$term_tasks[$term->term_id][] = $task;
								break;
							}
						}
					}
				}

				if($listid != 0){
					$tempterm = get_term($listid,'listcategory');
					$terms = array($tempterm);
				}else{
					$terms = get_terms( 'listcategory', 'hide_empty=0&parent=0' );
				}

				$taskcount 	= 0;
				$taskids 	= array();

				foreach($terms as $term){
					$id = $term->term_id;

					if(!empty($term_tasks[$id])){
						foreach($term_tasks[$id] as $task){
							$taskcount++;
							$taskids[] = $task->ID;
						}
					}

					$sub_terms = get_terms( 'listcategory', 'hide_empty=0&parent=' . $id );
					if(is_array($sub_terms) && count($sub_terms) > 0){
						foreach($sub_terms as $sub_term){
							$id = $sub_term->term_id;

							if(!empty($term_tasks[$id])){
								foreach($term_tasks[$id] as $task){
									$taskcount++;
									$taskids[] = $task->ID;
								}
							}
						}
					}
				}

				$taskidlist = implode(",",$taskids);
				$total 		= $taskcount;
				$userid 	= get_current_user_id();
				$sql = "SELECT COUNT(task_id) AS completed FROM " . $wpdb->prefix . "todolists_usertask WHERE user_id='$userid' AND status='true' AND task_id IN ($taskidlist)";
				$row = $wpdb->get_row($sql, ARRAY_A);
				$completed 	= $row["completed"];

				if($total != 0) $percent = ($completed * 100 / $total);
				else $percent = 0;
				$percent = round($percent,1);
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
						background-image: url("<?php echo plugins_url().'/todo-lists-for-membership-sites/img/pbar-ani.gif'; ?>");
					}
					#progressbar<?php echo $listid; ?>
					{
						margin-top: 10px;
						margin-bottom: 10px;
						position: relative;
					}
					#todolist_progressbar_header<?php echo $listid; ?>
					{
						position: absolute;
						top: 3px;
						left: 5px;
						font-size: 12px;
					}
					</style>
					<div id="progressbar<?php echo $listid; ?>"><?php echo "<div id='todolist_progressbar_header".$listid."'><div>Completed {$completed} out of {$total} tasks, {$percent}%</div></div>"; ?></div>
					<script>
						jQuery(document).ready(function()
						{
							jQuery("#progressbar<?php echo $listid; ?>").progressbar(
							{
								value: <?php echo round($percent,0); ?>
							});
						});
					</script>
				<?php
			}

			$id = $term->term_id;

			if(!empty($term_tasks[$id])){
				foreach($term_tasks[$id] as $task){
					$checked = "";
					if(!empty($usertasks[$task->ID])) $checked = " checked='checked' ";
					echo "<div style='position: relative; float: left;'><input type='checkbox'  name='todolists_task_id[" . $task->ID . "]' class='todolists_task".$listid."' id='todolists_task_id[" . $task->ID . "]' $checked /><label for='todolists_task_id[" . $task->ID . "]'>" . $task->post_title . "</label></div>";
					?>
					
					<div class='todolists_heading' style='margin-left: 20px; margin-top: 5px; float: left; cursor: pointer; border: solid 1px #aaaaaa; width: 10px; height: 10px; position: relative;'><div style='margin: 4px 2px; height: 2px; background-color: #aaaaaa;'></div><div style='position: absolute; top: 2px; bottom: 2px; left: 4px; right: 4px; background-color: #aaaaaa;' class='todolists_plus'></div></div>

					<div style='float: none; clear: both;' class="todolists_content"><?php echo $task->post_content; ?></div>
					<div style='float: none; clear: both;'></div>
					<?php
					$taskcount++;
				}
			}

			$sub_terms = get_terms('listcategory', 'hide_empty=0&parent=' . $id);
			if(is_array($sub_terms) && count($sub_terms) > 0){
				foreach($sub_terms as $sub_term){
					echo "<div style='font-size: 1.2em; font-weight: bold; margin: 5px 0px;'>" . $sub_term->name . "</div>";

					$id = $sub_term->term_id;

					if(!empty($term_tasks[$id])){
						foreach($term_tasks[$id] as $task){
							$checked = "";
							if(!empty($usertasks[$task->ID])) $checked = " checked='checked' ";
							echo "<div style='position: relative; float: left;'><input type='checkbox'  name='todolists_task_id[" . $task->ID . "]' class='todolists_task".$listid."' id='todolists_task_id[" . $task->ID . "]' $checked /><label for='todolists_task_id[" . $task->ID . "]'>" . $task->post_title . "</label></div>";
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

			?>
			<script type="text/javascript">
			jQuery(".todolists_task<?php echo $listid; ?>").change(function()
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
					var taskcountdetails = data.split("_");
					jQuery("#progressbar<?php echo $listid; ?>").progressbar('option','value',parseInt(taskcountdetails[2]));
					jQuery("#todolist_progressbar_header<?php echo $listid; ?>").html("<div>Completed " + taskcountdetails[0] + " out of " + taskcountdetails[1] + " tasks, " + taskcountdetails[2] + "%</div>");

				});

			});
			</script>
			<?php
		}

        echo $after_widget;
    }
	
	/** @see WP_Widget::update */
    function update($new_instance, $old_instance) 
	{
		$instance = $old_instance;
		$instance['listcategory'] 		= strip_tags($new_instance['listcategory']);
		$instance['displayprogressbar'] = strip_tags($new_instance['displayprogressbar']);
		return $instance;
    }
	
	 /** @see WP_Widget::form */
	function form($instance) 
	{	
		global $themename;
		$listcategory 		= esc_attr($instance['listcategory']);
		$displayprogressbar	= esc_attr($instance['displayprogressbar']);
		?>
		<table>
			<tr>
				<td>List Category :</td>
			</tr>
			<tr>
				<td>
					<select name="<?php echo $this->get_field_name('listcategory'); ?>" id="<?php echo $this->get_field_id('listcategory'); ?>">
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
								$option .= '<option value="'.$cat->cat_ID.'" '.selected($cat->cat_ID, $listcategory).'>'.$cat->name.'</option>';
							}
							echo $option;
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td><input style="margin-left:1px" type="checkbox" id="<?php echo $this->get_field_id('displayprogressbar'); ?>" name="<?php echo $this->get_field_name('displayprogressbar'); ?>" <?php checked($displayprogressbar, 'on'); ?> />Display progress bar below task name</td>
			</tr>
			</table>
		<?php
	}
}
?>