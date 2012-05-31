<?php
 global $PLUGINS_DIRECTORY;
 if (!defined('e107_INIT')) { exit; }
 
 // Plugin info  
 $eplug_name    = "ulogin";
 $eplug_version = "1.0";
 $eplug_author  = "uLoginTeam";
 $eplug_url = "http://ulogin.ru";
 $eplug_email = "team@ulogin.ru";
 $eplug_description="uLogin is user authorization tool.";
 $eplug_compatible  = "e107 v0.7";
 $eplug_readme      = "";        
 
 // Name of the plugin's folder
 $eplug_folder = "ulogin";
 
 // Name of menu item for plugin  
 $eplug_menu_name = "ulogin";
 
 // Name of the admin configuration file  
 $eplug_conffile = "";
 
 // List of preferences 
 $eplug_prefs       = "";
 $eplug_table_names = ""; 
 
 // Create a link in main menu (yes=TRUE, no=FALSE) 
 $eplug_module = TRUE;
 $eplug_link = FALSE;
 
 $eplug_link_name  = "ulogin";
 $eplug_link_perms = "Everyone";
 
 // Text to display after plugin successfully installed 
 $eplug_done = "Installation Successful..";
 $eplug_table_names = array("ulogin_user");

 // sql code to create those databases
 $eplug_tables = array("CREATE TABLE IF NOT EXISTS ".MPREFIX."ulogin_user (
                            `id` int(10) unsigned NOT NULL auto_increment,
                            `uid` int(10) NOT NULL,
                            `identity` text, 
                            `token` text, 
                            PRIMARY KEY (`id`)
                      )ENGINE = InnoDB;");
  ?>