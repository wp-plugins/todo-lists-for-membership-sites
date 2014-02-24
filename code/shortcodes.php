<?php
class WPTodoList_Shortcodes
{
	public function __construct()
	{
		add_action('init', array($this, 'add_todolists_button'));
		add_filter('tiny_mce_version', array($this, 'todolists_refresh_mce'));
		add_shortcode( 'todolists_tasklist', array($this, 'todolists_tasklist_shortcode'));
		add_shortcode( 'todolists_progressbar', array($this, 'todolists_progressbar_shortcode'));
	}
	
	public function add_todolists_button()
	{
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;
		if ( get_user_option('rich_editing') == 'true')
		{
			add_filter('mce_external_plugins', array($this, 'add_todolists_tinymce_plugin'));
			add_filter('mce_buttons', array($this, 'register_todolists_button'));
		}
	}
	
	public function register_todolists_button($buttons)
	{
		array_push($buttons, "|", "todolists-tasklist");
		array_push($buttons, "", "todolists-progressbar");
		return $buttons;
	}

	public function add_todolists_tinymce_plugin($plugin_array)
	{
		$plugin_array['todolists'] = plugins_url('js/tinymce/todolists.js',dirname(__FILE__));
		return $plugin_array;
	}
	
	public function todolists_refresh_mce($ver)
	{
		$ver += 3;
		return $ver;
	}
	
	public function todolists_tasklist_shortcode($atts)
	{
		require_once("form_todolist_user.php");
		$form = new WPTodoList_FormTodoList();

		extract(shortcode_atts(array('id' => '0'), $atts));
		return $form->get_tasklist($id);
	}
	
	public function todolists_progressbar_shortcode($atts)
	{
		require_once("form_todolist_user.php");
		$form = new WPTodoList_FormTodoList();
		
		extract(shortcode_atts(array('id' => '0'), $atts));
		return $form->get_progressbar($id);
	}
}
new WPTodoList_Shortcodes();
?>