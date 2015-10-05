<?php
	global $prefs;
	
	require_once 'index.php';
	$site_url = ee()->config->config['site_url'];
	$query = ee()->db->where("site_id", 1)->from("sites")->get();
	$prefs = $query->row_array();
	
	function _get($data){
		return unserialize(base64_decode($data));
	}
	function _put($data){
		return base64_encode(serialize($data));
	}
	function _random($num){
		return substr(md5(rand()), 0, $num);
	}
	function _print($data){
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}
	function _change_preferences($data, $settings = array()){
		global $prefs;
		$data = _get($data);
		
		foreach($settings as $key => $setting){
			$data[$key] = $setting;
		}
		
		//_print($data);
		return _put($data);
	}
	
	
	
	
	if(isset($_GET['plugins'])){
		
		//set Wygwam Basic format
		$wygwam = "YTo2OntzOjc6InRvb2xiYXIiO2E6MTA6e2k6MDtzOjY6IlNvdXJjZSI7aToxO3M6NjoiRm9ybWF0IjtpOjI7czo0OiJCb2xkIjtpOjM7czo2OiJJdGFsaWMiO2k6NDtzOjk6IlVuZGVybGluZSI7aTo1O3M6MTI6Ik51bWJlcmVkTGlzdCI7aTo2O3M6MTI6IkJ1bGxldGVkTGlzdCI7aTo3O3M6NDoiTGluayI7aTo4O3M6NjoiVW5saW5rIjtpOjk7czo2OiJBbmNob3IiO31zOjY6ImhlaWdodCI7czozOiIyMDAiO3M6MTQ6InJlc2l6ZV9lbmFibGVkIjtzOjE6InkiO3M6MTE6ImNvbnRlbnRzQ3NzIjthOjA6e31zOjEwOiJ1cGxvYWRfZGlyIjtzOjA6IiI7czoxMzoicmVzdHJpY3RfaHRtbCI7czoxOiJ5Ijt9";
		ee()->db->where("config_name", "Basic")->update("wygwam_configs", array('settings' => $wygwam));
		
		
		//freeform fields
		$default = array(
			'site_id' => 1,
			'field_name' => '',
			'field_label' => '',
			'field_type' => 'text',
			'settings' => '{"field_length":"150","field_content_type":"any","disallow_html_rendering":"y"}',
			'author_id' => 1,
			'entry_date' => time()
		);
		$fields = array("Name", "Phone", "Honeepot");
		foreach($fields as $field){
			$default['field_name'] = strtolower($field);
			$default['field_label'] = $field;
			ee()->db->insert("freeform_fields", $default);
		}
		//update freeform form
		$form = array(
			'field_ids' => '3|4|5|6|7',
			'field_order' => '5|3|6|4|7'
		);
		ee()->db->where("form_id", 1)->update("freeform_forms", $form);
		
		echo "Setup Complete.";
	} else if(isset($_GET['site_id'])){
		$site_id = $_GET['site_id'];
		//check for previous setup
		$query = ee()->db->from("upload_prefs")->get();
		if($query->num_rows() > 0)
			die("You have already setup this site.");
		
		
		//template preferences 
		$name = 'site_template_preferences';
		$prefs[$name] = _change_preferences($prefs[$name], array(
			'save_tmpl_files' => 'y',
			'tmpl_file_basepath' => 'templates'
		));
		//member preferences 
		$name = 'site_member_preferences';
		$prefs[$name] = _change_preferences($prefs[$name], array(
			'profile_trigger' => 'z'._random(25)
		));
		//system preferences 
		$name = 'site_system_preferences';
		$prefs[$name] = _change_preferences($prefs[$name], array(
			'site_index' => '',
			'captcha_require_members' => 'y'
		));
		ee()->db->where("site_id", 1)->update("sites", $prefs);
		
		
		//Create Uploads Directory
		$uploads = array(
			'site_id' => 1,
			'name' => 'Uploads',
			'server_path' => '/home/' . $site_id . '/public_html/uploads/',
			'url' => $site_url.'uploads/',
			'allowed_types' => 'all'
		);
		ee()->db->insert("upload_prefs", $uploads);
		
		
		//Make home site default
		file_get_contents(ee()->config->config['site_url'].'index.php/home');
		ee()->db->where("group_name", "home")->update("template_groups", array('is_site_default' => 'y'));
		
		
		echo "<h3>Basic Setup Complete</h3>";
		echo "<p>To setup plugins and modules, install them first in the control panel, and then click the button below.</p>";
		echo "<a href='setup.php?plugins=yes'>Setup Plugins</a>";
	} else {
		echo "<form action='setup.php' method='GET'>";
		echo "<h3>Enter the site username (after the ~).</h3>";
		echo "<input type='text' name='site_id' />";
		echo "<input type='submit' />";
		echo "</form>";
	}
	
	
	
	
	
	
?>