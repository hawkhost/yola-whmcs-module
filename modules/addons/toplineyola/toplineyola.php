<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function toplineyola_config() {
    $configarray = array(
    "name" => "Topline - Yola",
    "description" => "This module allows you to manage the Topline Yola module",
    "version" => "1.0",
    "author" => "Hawk Host Inc.",
    "language" => "english",
   );
    /*
     *  "fields" => array(
             "option1" => array ("FriendlyName" => "Option1", "Type" => "text", "Size" => "25", "Description" => "Textbox", "Default" => "Example", ),
             "option2" => array ("FriendlyName" => "Option2", "Type" => "password", "Size" => "25", "Description" => "Password", ),
             "option3" => array ("FriendlyName" => "Option3", "Type" => "yesno", "Size" => "25", "Description" => "Sample Check Box", ),
             "option4" => array ("FriendlyName" => "Option4", "Type" => "dropdown", "Options" => "1,2,3,4,5", "Description" => "Sample Dropdown", "Default" => "3", ),
             "option5" => array ("FriendlyName" => "Option5", "Type" => "radio", "Options" => "Demo1,Demo2,Demo3", "Description" => "Radio Options Demo", ),
             "option6" => array ("FriendlyName" => "Option6", "Type" => "textarea", "Rows" => "3", "Cols" => "50", "Description" => "Description goes here", "Default" => "Test", ),
         )
     */
    return $configarray;
}

function toplineyola_activate() {

    # Create Custom DB Table
    $query = "
    CREATE TABLE IF NOT EXISTS `mod_toplineyola` (
      `serviceid` int(11) NOT NULL,
      `ftp_address` varchar(255) NOT NULL,
      `ftp_username` varchar(255) NOT NULL,
      `ftp_password` text NOT NULL,
      `ftp_port` int(11) NOT NULL,
      `ftp_wwwroot` varchar(255) NOT NULL,
      `ftp_mode` varchar(255) NOT NULL,
      `ftp_protocol` varchar(255) NOT NULL,
      `domain` varchar(255) NOT NULL,
      UNIQUE KEY `relid` (`serviceid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    ";
    $result = full_query($query);

    if (!$result)
    {
        return array('status'=>'error','description'=>'There was an error activating the module');
    }
    else
    {
        return array('status'=>'success','description'=>'The Topline Yola module has been installed');
    }
}

function toplineyola_deactivate() {

    # Remove Custom DB Table
    //$query = "DROP TABLE `mod_toplineyola`";
	//$result = full_query($query);
    return array('status'=>'success','description'=>'The Topline Yola module has been deactivated to remove the database run "DROP TABLE `mod_toplineyola"');

}

function toplineyola_upgrade($vars) {

    // $version['version']
    /**
     * This is a placeholder for future versions where you may need to upgrade
     */

}

function toplineyola_output($vars)
{
    echo '<p>'.$vars['_lang']['dashboard_intro'].'</p>';

}

function toplineyola_sidebar($vars)
{
    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $option1 = $vars['option1'];
    $option2 = $vars['option2'];
    $option3 = $vars['option3'];
    $option4 = $vars['option4'];
    $option5 = $vars['option5'];
    $LANG = $vars['_lang'];

    $sidebar = '<span class="header"><img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16" />Topline - Yola</span>
<ul class="menu">
        <li><a href="'.$vars['modulelink'].'">Dashboard</a></li>
        <li><a target="_blank" href="http://github.com/">Github</a></li>
        <li><a href="#">Version: '.$version.'</a></li>
    </ul>';
    return $sidebar;
}