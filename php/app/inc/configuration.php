<?php

  // configuration _____________________________________________________________

  /**
   * Configuration
   * 
   * application configuration
   * 
   * @author seb
   *
   */
  
  class Configuration
  {
  	public $dbconnectionstring = 'sqlite:db.sql3';
  	
    public $default_controller = 'default';
    public $default_action = 'default';

	  public $full_project_root = '/var/www/dev/sg/';
	  public $install_directory = 'usr/';
	  public $include_directory = 'php/app/inc/';
	  public $controller_directory = 'php/app/controllers/';

    public $user_url_base = 'http://localhost/dev/sg/usr/';
	  
    // these values do not have to be changed __________________________________
    
	  public $login_key_name = 'loginkey';
    public $login_key_salt_name = 'loginkeysalt';
    public $encrypt_key_salt_name = 'encryptkeysalt';
    public $data_block_name = 'data';	  

	  public $unset_value = 'unset';
	  
	  public $full_install_directory;
	  public $full_include_directory;
	  public $full_controller_directory;
	  
	  /**
	   * Build composite configuration information
	   * @return nothing
	   */
	  public function __construct()
    {
    	$this->full_install_directory = 
        $this->full_project_root . $this->install_directory;
      $this->full_include_directory = 
        $this->full_project_root . $this->include_directory;
      $this->full_controller_directory = 
        $this->full_project_root . $this->controller_directory;
    }
    
    public function debug()
    {
    	print_r($this);
    }
  }

