<?php

class Xcloner_Settings
{
	
	public function get_xcloner_start_path()
	{
		if(!get_option('xcloner_start_path'))
			$path = realpath(ABSPATH);
		else
			$path = get_option('xcloner_start_path');
		
		return $path;
	}
	
	public function get_xcloner_store_path()
	{
		if(!get_option('xcloner_start_path'))
			$path = realpath(XCLONER_STORAGE_PATH);
		else
			$path = get_option('xcloner_start_path');
		
		return $path;
	}
	
	public function get_xcloner_tmp_path()
	{
		$path = sys_get_temp_dir();
		
		return $path;
	}
	
	public function get_backup_extension_name()
	{
		if(get_option('xcloner_backup_compression_level'))
			$ext = "tar.gz";
		else
			$ext = "tar";
			
		return $ext;	
	}
	
	public function get_enable_mysql_backup()
	{
		if(get_option('xcloner_enable_mysql_backup'))
			return true;
		
		return false;	
	}
	
	public function get_default_backup_name()
	{
		$data = parse_url(get_site_url());
			
		$backup_name = $data['host'].($data['port']?":".$data['port']:"").'-'.date("Y-m-d_H-i")."-".($this->get_enable_mysql_backup()?"sql":"nosql").".".$this->get_backup_extension_name();
		
		return $backup_name;
	}
	
	public function settings_init()
	{
	    global $wpdb;
	    
	    //ADDING MISSING OPTIONS
	    if( false == get_option( 'xcloner_mysql_settings_page' ) ) {  
			add_option( 'xcloner_mysql_settings_page' );
		} // end if
		
	    if( false == get_option( 'xcloner_cron_settings_page' ) ) {  
			add_option( 'xcloner_cron_settings_page' );
		} // end if
	    
	    if( false == get_option( 'xcloner_system_settings_page' ) ) {  
			add_option( 'xcloner_system_settings_page' );
		} // end if
	 
	    
	    //ADDING SETTING SECTIONS
	    //GENERAL section
	    add_settings_section(
	        'xcloner_general_settings_group',
	        __(' '),
	        array($this, 'xcloner_settings_section_cb'),
	        'xcloner_settings_page'
	    );
	    //MYSQL section
	    add_settings_section(
	        'xcloner_mysql_settings_group',
	        __(' '),
	        array($this, 'xcloner_settings_section_cb'),
	        'xcloner_mysql_settings_page'
	    );
	    
	    //SYSTEM section
	    add_settings_section(
	        'xcloner_system_settings_group',
	        __(''),
	        array($this, 'xcloner_settings_section_cb'),
	        'xcloner_system_settings_page'
	    );
	    
		
		//CRON section
	    add_settings_section(
	        'xcloner_cron_settings_group',
	        __(' '),
	        array($this, 'xcloner_settings_section_cb'),
	        'xcloner_cron_settings_page'
	    );
	    
	    
	    
		//REGISTERING THE 'GENERAL SECTION' FIELDS
		register_setting('xcloner_general_settings_group', 'xcloner_backup_compression_level', array('Xcloner_Sanitization', "sanitize_input_as_int"));
	    add_settings_field(
	        'xcloner_backup_compression_level',
	       __('Backup Compression Level'),
	        array($this, 'do_form_range_field'),
	        'xcloner_settings_page',
	        'xcloner_general_settings_group',
	        array('xcloner_backup_compression_level',
	         __('Options between [0-9]. Value 0 means no compression, while 9 is maximum compression affecting cpu load'), 
	         0,
	         9
	         )
	    );
	    
	    register_setting('xcloner_general_settings_group', 'xcloner_start_path', array('Xcloner_Sanitization', "sanitize_input_as_path"));
	    add_settings_field(
	        'xcloner_start_path',
	        __('Backup Start Location'),
	        array($this, 'do_form_text_field'),
	        'xcloner_settings_page',
	        'xcloner_general_settings_group',
	        array('xcloner_start_path',
				__('Base path location from where XCloner can start the Backup.'),
				$this->get_xcloner_start_path(),
				'disabled'
				)
	    );
	    
	    register_setting('xcloner_general_settings_group', 'xcloner_store_path', array('Xcloner_Sanitization', "sanitize_input_as_path"));
	    add_settings_field(
	        'xcloner_store_path',
	        __('Backup Storage Location'),
	        array($this, 'do_form_text_field'),
	        'xcloner_settings_page',
	        'xcloner_general_settings_group',
	        array('xcloner_store_path',
				__('Location where XCloner will store the Backup archives.'),
				$this->get_xcloner_store_path(), 
				'disabled'
				)
	    );
	    
	    register_setting('xcloner_mysql_settings_group', 'xcloner_enable_log', array('Xcloner_Sanitization', "sanitize_input_as_int"));
	    add_settings_field(
	        'xcloner_enable_log',
	        __('Enable XCloner Backup Log'),
	        array($this, 'do_form_switch_field'),
	        'xcloner_settings_page',
	        'xcloner_general_settings_group',
	        array('xcloner_enable_log',
				__('Enable the XCloner Backup log.')
				)
		);	
	 
		//REGISTERING THE 'MYSQL SECTION' FIELDS
		register_setting('xcloner_mysql_settings_group', 'xcloner_enable_mysql_backup', array('Xcloner_Sanitization', "sanitize_input_as_int"));
	    add_settings_field(
	        'xcloner_enable_mysql_backup',
	        __('Enable Mysql Backup'),
	        array($this, 'do_form_switch_field'),
	        'xcloner_mysql_settings_page',
	        'xcloner_mysql_settings_group',
	        array('xcloner_enable_mysql_backup',
				__('Enable Mysql Backup Option. If you don\'t want to backup the database, you can disable this.')
				)
	    );
	    
	    register_setting('xcloner_mysql_settings_group', 'xcloner_backup_only_wp_tables');
	    add_settings_field(
	        'xcloner_backup_only_wp_tables',
	        __('Backup only WP tables'),
	        array($this, 'do_form_switch_field'),
	        'xcloner_mysql_settings_page',
	        'xcloner_mysql_settings_group',
	        array('xcloner_backup_only_wp_tables',
				sprintf(__('Enable this if you only want to Backup only tables starting with \'%s\' prefix'), $wpdb->prefix)
				)
	    );
	    
	    register_setting('xcloner_mysql_settings_group', 'xcloner_mysql_hostname', array('Xcloner_Sanitization', "sanitize_input_as_raw"));
	    add_settings_field(
	        'xcloner_mysql_hostname',
	        __('Mysql Hostname'),
	        array($this, 'do_form_text_field'),
	        'xcloner_mysql_settings_page',
	        'xcloner_mysql_settings_group',
	        array('xcloner_mysql_hostname',
				__('Wordpress mysql hostname'),
				DB_HOST,
				'disabled'
				)
	    );

	    register_setting('xcloner_mysql_settings_group', 'xcloner_mysql_username', array('Xcloner_Sanitization', "sanitize_input_as_raw"));
	    add_settings_field(
	        'xcloner_mysql_username',
	        __('Mysql Username'),
	        array($this, 'do_form_text_field'),
	        'xcloner_mysql_settings_page',
	        'xcloner_mysql_settings_group',
	        array('xcloner_mysql_username',
				__('Wordpress mysql username'),
				DB_USER,
				'disabled'
				)
	    );
	    
	    register_setting('xcloner_mysql_settings_group', 'xcloner_mysql_database', array('Xcloner_Sanitization', "sanitize_input_as_raw"));
	    add_settings_field(
	        'xcloner_mysql_database',
	        __('Mysql Database'),
	        array($this, 'do_form_text_field'),
	        'xcloner_mysql_settings_page',
	        'xcloner_mysql_settings_group',
	        array('xcloner_mysql_database',
				__('Wordpress mysql database'),
				DB_NAME,
				'disabled'
				)
	    );
	    
	    //REGISTERING THE 'SYSTEM SECTION' FIELDS
		register_setting('xcloner_system_settings_group', 'xcloner_files_to_process_per_request', array('Xcloner_Sanitization', "sanitize_input_as_int"));
	    add_settings_field(
	        'xcloner_files_to_process_per_request',
	       __('Files To Process Per Request'),
	        array($this, 'do_form_range_field'),
	        'xcloner_system_settings_page',
	        'xcloner_system_settings_group',
	        array('xcloner_files_to_process_per_request',
	         __('Use this option to set how many files XCloner should process at one time before doing another AJAX call'), 
	         0,
	         1000
	         )
	    );
	    
		register_setting('xcloner_system_settings_group', 'xcloner_database_records_per_request', array('Xcloner_Sanitization', "sanitize_input_as_int"));
	    add_settings_field(
	        'xcloner_database_records_per_request',
	       __('Database Records Per Request'),
	        array($this, 'do_form_range_field'),
	        'xcloner_system_settings_page',
	        'xcloner_system_settings_group',
	        array('xcloner_database_records_per_request',
	         __('Use this option to set how many database table records should be fetched per AJAX request, or set to 0 to fetch all.  Limit 0 to 100000'), 
	         0,
	         100000
	         )
	    );
	    
		register_setting('xcloner_system_settings_group', 'xcloner_exclude_files_larger_than_mb', array('Xcloner_Sanitization', "sanitize_input_as_int"));
	    add_settings_field(
	        'xcloner_exclude_files_larger_than_mb',
	       __('Exclude files larger than (MB)'),
	        array($this, 'do_form_range_field'),
	        'xcloner_system_settings_page',
	        'xcloner_system_settings_group',
	        array('xcloner_exclude_files_larger_than_mb',
	         __('Use this option to automatically exclude files larger than a certain size in MB, or set to -1 to include all. Limit 0 to 10000'), 
	         0,
	         10000
	         )
	    );
	    
		register_setting('xcloner_system_settings_group', 'xcloner_split_backup_limit', array('Xcloner_Sanitization', "sanitize_input_as_int"));
	    add_settings_field(
	        'xcloner_split_backup_limit',
	       __('Split Backup Archive Limit (MB)'),
	        array($this, 'do_form_range_field'),
	        'xcloner_system_settings_page',
	        'xcloner_system_settings_group',
	        array('xcloner_split_backup_limit',
	         __('Use this option to automatically split the backup archive into smaller parts. Limit 0 to 10000'), 
	         0,
	         10000
	         )
	    );
	 
		//REGISTERING THE 'CRON SECTION' FIELDS
		register_setting('xcloner_cron_settings_group', 'xcloner_cron_frequency');
	    add_settings_field(
	        'xcloner_cron_frequency',
	        __('Cron frequency'),
	        array($this, 'do_form_text_field'),
	        'xcloner_cron_settings_page',
	        'xcloner_cron_settings_group',
	        array('xcloner_cron_frequency',
				__('Cron frequency')
			)
	    );
	}
	 
	
	
	 
	/**
	 * callback functions
	 */
	 
	// section content cb
	public function xcloner_settings_section_cb()
	{
	    //echo '<p>WPOrg Section Introduction.</p>';
	}
	 
	// field content cb
	public function do_form_text_field($params)
	{
		list($fieldname, $label, $value, $disabled) = $params;
		
		if(!$value)
			$value = get_option($fieldname);
	    // output the field
	    ?>
	    <div class="row">
	        <div class="input-field col s10 m10 l6">
	          <input <?php echo ($disabled)?"disabled":""?> name="<?php echo $fieldname?>" id="<?php echo $fieldname?>" type="text" class="validate" value="<?php echo isset($value) ? esc_attr($value) : ''; ?>">
	        </div>
	        <div class="col s2 m2 ">
				<a class="btn-floating tooltipped btn-small" data-position="left" data-delay="50" data-tooltip="<?php echo $label?>" data-tooltip-id=""><i class="material-icons">?</i></a>
	        </div>
	    </div>
		

	    <?php
	}
	
	public function do_form_range_field($params)
	{
		list($fieldname, $label, $range_start, $range_end, $disabled) = $params;
		$value = get_option($fieldname);
	?>
		<div class="row">
	        <div class="input-field col s10 m10 l6">
				<p class="range-field">
			      <input <?php echo ($disabled)?"disabled":""?> type="range" name="<?php echo $fieldname?>" id="<?php echo $fieldname?>" min="<?php echo $range_start?>" max="<?php echo $range_end?>" value="<?php echo isset($value) ? esc_attr($value) : ''; ?>" />
			    </p>
			</div>
			<div class="col s2 m2 ">
				<a class="btn-floating tooltipped btn-small" data-position="left" data-delay="50" data-tooltip="<?php echo $label?>" data-tooltip-id=""><i class="material-icons">?</i></a>
	        </div>    
		</div>	
	<?php
	}
	
	
	public function do_form_switch_field($params)
	{
		list($fieldname, $label, $disabled) = $params;
		$value = get_option($fieldname);
	?>
	<div class="row">
		<div class="input-field col s10 m10 l6">	
			<div class="switch">
				<label>
				  Off
				  <input <?php echo ($disabled)?"disabled":""?> type="checkbox" name="<?php echo $fieldname?>" id="<?php echo $fieldname?>" value="1" <?php echo ($value) ? 'checked="checked"' : ''; ?>">
				  <span class="lever"></span>
				  On
				</label>
			</div>
		</div> 
		<div class="col s2 m2">
				<a class="btn-floating tooltipped btn-small" data-position="left" data-delay="50" data-tooltip="<?php echo $label?>" data-tooltip-id=""><i class="material-icons">?</i></a>
	        </div>   
	</div>
	<?php
	}
}
