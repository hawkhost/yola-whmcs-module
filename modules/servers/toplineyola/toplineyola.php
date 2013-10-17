<?php

/**
 * Configuration options of the Topline Yola product
 * @return array
 */
function toplineyola_ConfigOptions()
{
    // WHMCS has a maximum of 24 options
    $configarray = array(
        "Topline Yola Bundle ID" => array("Type" => "text", "Size" => "25",),
        "Trial" => array("Type" => "yesno", "Description" => "Tick to be trial account"),
        "Trial Duration" => array("Type" => "text", "Description" => "Duration 1-30 Days"),
        "Trial FTP Address" => array("Type" => "text", "Size" => "25",),
        "Trial FTP Username" => array("Type" => "text", "Size" => "25",),
        "Trial FTP Password" => array("Type" => "text", "Size" => "25",),
        "Trial FTP Mode" => array(
            "FriendlyName" => "Trial FTP Protocol",
            "Type" => "dropdown", # Dropdown Choice of Options
            "Options" => "Active,Passive",
            "Description" => "",
            "Default" => "Active",
        ),
        "Trial FTP Port" => array("Type" => "text", "Size" => "25",),
        "Trial FTP Protocol" => array("Type" => "text", "Size" => "25",),
        "Trial Document Root" => array("Type" => "text", "Size" => "25", "Description" => "Document root before randomly generated folder for example public_html/{username}"),
        "Trial URL" => array("Type" => "text", "Size" => "25", "Description" => "URL before randomly generated username folder http://hawkhosttrial.com/{username}"),
    );
    return $configarray;
}

/**
 * Retrieves the topline class initializes it
 * @param $partnerId
 * @param $partnerGuid
 * @return ToplineYola
 */
function getTopline($partnerId, $partnerGuid)
{
    require_once('libs/toplineyola.php');
    $apiConfig = array(
        'partnerId' => $partnerId,
        'partnerGuid' => $partnerGuid,
    );
    return new ToplineYola($apiConfig);
}

/**
 * Generates a random string of lower case characters
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Creates a sitebuilder account
 * @param $params
 * @return string
 */
function toplineyola_CreateAccount($params)
{
    $topline = getTopline($params['serverusername'], $params['serveraccesshash']);
    // User details
    $options = array(
        'userid' => $params['serviceid'],
        'password' => $params['password'],
        'first_name' => $params['clientsdetails']['firstname'],
        'last_name' => $params['clientsdetails']['lastname'],
        'email' => $params['clientsdetails']['email'],
        'phone' => $params['clientsdetails']['phone'],
        'bundle_id' => $params['configoption1'],
        'language' => '',
    );
    // Trial account
    if ($params['configoption2'] == 'on') {
        $userFolder = generateRandomString();
        $options['status'] = 2;
        $options['ftp_address'] = $params['configoption4'];
        $options['ftp_userid'] = $params['configoption5'];
        $options['ftp_password'] = $params['configoption6'];
        $options['ftp_port'] = $params['configoption8'];
        $options['ftp_protocol'] = $params['configoption7'];
        $options['ftp_mode'] = $params['configoption9'];
        $options['ftp_wwwroot'] = str_replace('{username}',$userFolder,$params['configoption10']);
        $options['domain'] = str_replace('{username}',$userFolder,$params['configoption11']);
        $options['trial_duration'] = $params['configoption3'];

        if (!createTrialFtpFolder($options['ftp_address'],$options['ftp_userid'],$options['ftp_password'],str_replace('{username}','',$params['configoption10']),$userFolder))
        {
            return 'Unable to create trial FTP folder';
        }
    } else {
        $options['status'] = 1;
        /*
         * Logic for setting up a sitebuilder on a hosting account
         */
        $join = "tblproducts ON tblhosting.packageid=tblproducts.id";
        $result = select_query("tblhosting", "tblhosting.*", array("userid" => $params['clientsdetails']['id'], "type" => "hostingaccount", "domainstatus" => "Active"), "id", "ASC", "1", $join);
        $service = mysql_fetch_array($result);
        if (isset($service['id'])) {
            $options['ftp_address'] = 'ftp.' . $service['domain'];
            $options['ftp_userid'] = $service['username'];
            $options['ftp_password'] = decrypt($service['password']);
            $options['ftp_port'] = 21;
            $options['ftp_protocol'] = 1;
            $options['ftp_mode'] = 'Active';
            $options['ftp_wwwroot'] = 'public_html';
            $options['domain'] = $service['domain'];

        } else {
            // The user has no active service but has a sitebuilder so we'll randomly generate it
            $options['ftp_address'] = 'ftp.' . 'no-domain-' . $params['id'] . '.com';
            $options['ftp_userid'] = $params['id'];
            $options['ftp_password'] = substr(md5(rand()), 0, 7);
            $options['ftp_port'] = 21;
            $options['ftp_protocol'] = 1;
            $options['ftp_mode'] = 'Active';
            $options['ftp_wwwroot'] = 'public_html';
            $options['domain'] = 'no-domain-' . $params['id'] . '.com';
        }
    }
    $result = $topline->addCustomer($options);
    insert_query("mod_toplineyola", array(
        "serviceid" => $params['serviceid'],
        "ftp_address" => $_POST['modulefields'][0],
        "ftp_username" => $_POST['modulefields'][1],
        "ftp_password" => encryptToplineYola($_POST['modulefields'][2]),
        'ftp_port' => $_POST['modulefields'][3],
        'ftp_wwwroot' => $_POST['modulefields'][4],
        'ftp_mode' => $_POST['modulefields'][5],
        'ftp_protocol' => $_POST['modulefields'][6],
        'domain' => $_POST['modulefields'][7],
    ));
    if ($result['success'] == 1) {
        return "success";
    } else {
        logModuleCall('toplineyola','create',$options,$result,null,null);
        return $result['error'];
    }
}

/**
 * Terminates a sitebuilder account
 * @param $params
 * @return string
 */
function toplineyola_TerminateAccount($params)
{

    $topline = getTopline($params['serverusername'], $params['serveraccesshash']);
    $options = array(
        'userid' => $params['serviceid'],
    );
    $result = $topline->deleteCustomer($options);
    if ($result['success'] == 1) {
        return "success";
    } else {
        logModuleCall('toplineyola','terminate',$options,$result,null,null);
        return $result['error'];

    }

}

/**
 * Suspends a sitebuilder account
 * @param $params
 * @return string
 */
function toplineyola_SuspendAccount($params)
{

    $topline = getTopline($params['serverusername'], $params['serveraccesshash']);
    $options = array(
        'userid' => $params['serviceid'],
        'status' => 3,
    );
    $result = $topline->modifyCustomer($options);
    if ($result['success'] == 1) {
        return "success";
    } else {
        logModuleCall('toplineyola','suspend',$options,$result,null,null);
        return $result['error'];

    }

}

/**
 * Unsuspends a sitebuilder account
 * @param $params
 * @return string
 */
function toplineyola_UnsuspendAccount($params)
{

    $topline = getTopline($params['serverusername'], $params['serveraccesshash']);
    $options = array(
        'userid' => $params['serviceid'],
    );
    if (1 == 1) // Trial Code)
    {
        $options['status'] = 2;
    } else {
        $options['status'] = 1;
    }
    $result = $topline->modifyCustomer($options);
    if ($result['success'] == 1) {
        return "success";
    } else {
        logModuleCall('toplineyola','unsuspend',$options,$result,null,null);
        return $result['error'];

    }
}

/**
 * Change password which will never do anything
 * @param $params
 * @return string
 */
function toplineyola_ChangePassword($params)
{
    return 'success';

}

/**
 * Upgrade/Downgrades a package
 * @param $params
 * @return string
 */
function toplineyola_ChangePackage($params)
{
    $topline = getTopline($params['serverusername'], $params['serveraccesshash']);
    $options = array(
        'userid' => $params['serviceid'],
        'bundle_id' => $params['configoption1'],
    );
    $result = $topline->modifyCustomer($options);
    if ($result['success'] == 1) {
        return "success";
    } else {
        logModuleCall('toplineyola','changepackage',$options,$result,null,null);
        return $result['error'];
    }
}

/**
 * Client area additional options
 * @param $params
 * @return string
 */
function toplineyola_ClientArea($params)
{

    $topline = getTopline($params['serverusername'], $params['serveraccesshash']);
    $options = array('userid' => $params['serviceid']);
    $result = $topline->requestToken($options);
    $code = '
    <div align="center">
    <form action="http://login.sitebuilderservice.com/sitebuilder_session.php" method="POST" target="_blank">
    <input type="hidden" name="sbstkn" value="' . $result['token'] . '"/>
        <input type="submit" value="Login to Sitebuilder">
    </form>
    <form action="sitebuilder.php">
    <input type="hidden" name="service_id" value="' . $params['serviceid'] . '">
        <input type="submit" value="Manage Settings">
    </form>
    </div>';
    return $code;
}

/**
 * Admin login link which is not used
 * @param $params
 * @return string
 */
function toplineyola_AdminLink($params)
{
    $code = '';
    return $code;
}

/**
 * Login link which is not used we use our custom button to get to the sitebuilder
 * @param $params
 */
function toplineyola_LoginLink($params)
{

}

/**
 * Client area custom buttons which are not necessary
 * @return array
 */
function toplineyola_ClientAreaCustomButtonArray()
{
    $buttonarray = array();
    return $buttonarray;
}

/**
 * Admin area custom buttons which are not necessary
 * @return array
 */
function toplineyola_AdminCustomButtonArray()
{
    $buttonarray = array();
    return $buttonarray;
}

/**
 * Updates usage which for this module is not necessary
 * @param $params
 * @return string
 */
function toplineyola_UsageUpdate($params)
{
    return 'success';

}

/**
 * The additional sitebuilder fields displayed to an admin
 * @param $params
 * @return array
 */
function toplineyola_AdminServicesTabFields($params)
{
    $result = select_query("mod_toplineyola", "", array("serviceid" => $params['serviceid']));
    $data = mysql_fetch_array($result);
    $fieldsarray = array
    (
        'FTP Address' => '<input type="text" name="modulefields[0]" size="30" value="' . $data['ftp_address'] . '" />',
        'FTP Username' => '<input type="text" name="modulefields[1]" size="30" value="' . $data['ftp_username'] . '" />',
        'FTP Password' => '<input type="text" name="modulefields[2]" size="30" value="' . decryptToplineYola($data['ftp_password']) . '" />',
        'FTP Port' => '<input type="text" name="modulefields[3]" size="30" value="' . $data['ftp_port'] . '" />',
        'FTP WWW Root' => '<input type="text" name="modulefields[4]" size="30" value="' . $data['ftp_wwwroot'] . '" />',
        'FTP Mode' => '<input type="text" name="modulefields[5]" size="30" value="' . $data['ftp_mode'] . '" />',
        'FTP Protocol' => '<input type="text" name="modulefields[6]" size="30" value="' . $data['ftp_protocol'] . '" />',
        'Domain' => '<input type="text" name="modulefields[7]" size="30" value="' . $data['domain'] . '" />',
    );
    return $fieldsarray;

}

/**
 * Saves the extra fields on the admin page
 * @param $params
 */
function toplineyola_AdminServicesTabFieldsSave($params)
{
    update_query("mod_toplineyola", array(
        "ftp_address" => $_POST['modulefields'][0],
        "ftp_username" => $_POST['modulefields'][1],
        "ftp_password" => encryptToplineYola($_POST['modulefields'][2]),
        'ftp_port' => $_POST['modulefields'][3],
        'ftp_wwwroot' => $_POST['modulefields'][4],
        'ftp_mode' => $_POST['modulefields'][5],
        'ftp_protocol' => $_POST['modulefields'][6],
        'domain' => $_POST['modulefields'][7],
    ), array("serviceid" => $params['serviceid']));
}

/**
 * Encrypts the ftp password used by Yola (Wrapper in case we elect to change this from the WHMCS function)
 * @param $string
 * @return mixed
 */
function encryptToplineYola($string)
{
    return encrypt($string);
}

/**
 * Decrypts the password used by Yola (Wrapper in case we elect to change this from the WHMCS function)
 * @param $string
 * @return mixed
 */
function decryptToplineYola($string)
{
    return decrypt($string);
}

/**
 * Creates the trial FTP login
 * @param $ftp_address
 * @param $ftp_password
 * @param $ftp_wwwroot
 */
function createTrialFtpFolder($ftp_address,$ftp_username,$ftp_password,$ftp_wwwroot,$userfolder)
{
    // Connect
    $connection = ftp_connect($ftp_address);
    // Authenticate
    $login = ftp_login($connection, $ftp_username,$ftp_password);
    if (ftp_chdir($connection, $ftp_wwwroot)) {
        if (!ftp_mkdir($connection, $userfolder))
            {
                return false;
            }
    } else {
        ftp_close($connection);
        return false;
    }
    ftp_close($connection);
    return true;
}

?>