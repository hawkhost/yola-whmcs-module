<?php
define("CLIENTAREA", true);
require("init.php");
$ca = new WHMCS_ClientArea();
$ca->setPageTitle("Sitebuilder Management");
$ca->addToBreadCrumb('index.php', $whmcs->get_lang('globalsystemname'));
$ca->addToBreadCrumb('sitebuilder.php?service_id=' . $_REQUEST['service_id'], 'Sitebuilder');
$ca->initPage();
$ca->requireLogin();

switch ($action) {
    case 'custom_settings':
        saveCustomSettings($ca);
        break;
    case 'set_hosting_account':
        setHostingAccount($ca);
        break;
    default:
        defaultPage($ca);

}
// Will output a template on each page
$ca->output();
/**
 * Default page when visiting the sitebuilder requires a service id
 * @param $ca
 */
function defaultPage($ca, $saved = array())
{
    if (checkServiceAccess($_REQUEST['service_id'], $ca->getUserID())) {
        if (!isTrial($_REQUEST['service_id'])) {
            $result = select_query("mod_toplineyola", "", array("serviceid" => $_REQUEST['service_id']));
            $sb = mysql_fetch_array($result);
            $sb['ftp_password'] = decrypt($sb['ftp_password']);
            $ca->assign('sb', $sb);
            // List of services if we're going to custom set it
            $join = "tblproducts ON tblhosting.packageid=tblproducts.id";
            $result = select_query("tblhosting", "tblhosting.*", array("userid" => $ca->getUserID(), "type" => "hostingaccount", "domainstatus" => "Active"), "", "", "", $join);
            while ($data = mysql_fetch_array($result)) {
                $services[] = $data;
            }
            $ca->assign('services', $services);
            $ca->assign('saved', $saved);
            $ca->setTemplate('sitebuilder_default');

        } else {
            $ca->assign('sitebuilder_error', 'You do not have permission to manage settings with a trial account');
            $ca->setTemplate('sitebuilder_error');
        }

    } else {
        $ca->assign('sitebuilder_error', 'The service you picked you do not have permissions or was not found');
        $ca->setTemplate('sitebuilder_error');
    }
}

/**
 * Saves a users custom FTP settings
 * @param $ca
 */
function saveCustomSettings($ca)
{
    if (checkServiceAccess($_POST['service_id'], $ca->getUserID()) && !isTrial($_REQUEST['service_id'])) {
        $validation = validateCustomSettings();

        if ($validation['valid']) {
            $table = "mod_toplineyola";
            $update = array(
                'ftp_address' => $_POST['ftp_address'],
                'ftp_password' => encrypt($_POST['ftp_password']),
                'ftp_wwwroot' => $_POST['ftp_wwwroot'],
                'ftp_mode' => $_POST['ftp_mode'],
                'ftp_protocol' => $_POST['ftp_protocol'],
                'ftp_username' => $_POST['ftp_username'],
                'ftp_port' => $_POST['ftp_port'],
                'domain' => $_POST['domain'],
            );
            $where = array("serviceid" => $_POST['service_id'],);
            update_query($table, $update, $where);
            if (syncwithDbTable($_POST['service_id'])) {
                $saved = array(
                    'success' => true,
                    'message' => 'Your site information has been updated successfully',

                );
                defaultPage($ca, $saved);
            } else {
                $saved = array(
                    'success' => false,
                    'message' => 'There was an issue syncing your sitebuilder data please contact our support team',

                );
                defaultPage($ca, $saved);
            }

        } else {
            $saved = array(
                'success' => false,
                'message' => $validation['errors']

            );
            defaultPage($ca, $saved);

        }

    } else {
        $ca->assign('sitebuilder_error', 'The service you picked you do not have permissions or was not found');
        $ca->setTemplate('sitebuilder_error');
    }
}

/**
 * Validates custom settings
 */
function validateCustomSettings()
{
    $validation = array
    (
        'valid' => true,
        'errors' => array()
    );
    /**
     * Basic validation
     * @ToDo Add more thorough validation
     */
    if (empty($_POST['ftp_address'])) {
        $validation['valid'] = false;
        $validation['errors'][] = 'FTP addresss cannot be empty';
    }
    if (empty($_POST['ftp_username'])) {
        $validation['valid'] = false;
        $validation['errors'][] = 'FTP username cannot be empty';
    }
    if (empty($_POST['ftp_password'])) {
        $validation['valid'] = false;
        $validation['errors'][] = 'FTP password cannot be empty';
    }
    if (empty($_POST['ftp_port'])) {
        $validation['valid'] = false;
        $validation['errors'][] = 'FTP port cannot be empty';
    }
    if (empty($_POST['ftp_mode'])) {
        $validation['valid'] = false;
        $validation['errors'][] = 'FTP mode cannot be empty';
    }
    if (empty($_POST['ftp_protocol'])) {
        $validation['valid'] = false;
        $validation['errors'][] = 'FTP protocol cannot be empty';
    }
    if (empty($_POST['domain'])) {
        $validation['valid'] = false;
        $validation['errors'][] = 'Domain cannot be empty';
    }
    return $validation;

}

/**
 * Syncs a users data based on what is in the mod_toplineyola table
 * @param $serviceId
 * @return bool
 */
function syncwithDbTable($serviceId)
{
    $topline = gettoplineYola($serviceId);
    $result = select_query("mod_toplineyola", "", array("serviceid" => $serviceId));
    $sb = mysql_fetch_array($result);
    $options = array(
        'userid' => $sb['serviceid'],
        'ftp_address' => $sb['ftp_address'],
        'ftp_userid' => $sb['ftp_username'],
        'ftp_password' => decrypt($sb['ftp_password']),
        'ftp_port' => $sb['ftp_port'],
        'ftp_wwwroot' => $sb['ftp_wwwroot'],
        'ftp_mode' => $sb['ftp_mode'],
        'ftp_protocol' => $sb['ftp_protocol'],
        'domain' => $sb['domain']
    );
    $results = $topline->modifyCustomer($options);
    if ($results['success'] == 1) {
        return true;
    }
    return false;
}

/**
 * Sets the users sitebuilder information to be that of a hosting account
 * @param $ca
 */
function setHostingAccount($ca)
{
    if (checkServiceAccess($_POST['service_id'], $ca->getUserID()) && !isTrial($_REQUEST['service_id'])) {
        // Retrieve current hosting account settings
        $result = select_query("tblhosting", "*", array("id" => $_POST['hostingid'], 'userid' => $ca->getUserID(), 'domainstatus' => 'Active'));
        $hosting = mysql_fetch_array($result);
        $table = "mod_toplineyola";
        $update = array(
            'ftp_address' => 'ftp.' . $hosting['domain'],
            'ftp_password' => $hosting['password'],
            'ftp_wwwroot' => 'public_html',
            'ftp_mode' => 'Active',
            'ftp_port' => 21,
            'ftp_userid' => $hosting['username'],
            'ftp_protocol' => 1,
            'domain' => $hosting['domain'],
        );
        $where = array("serviceid" => $_POST['service_id'],);
        update_query($table, $update, $where);
        if (syncwithDbTable($_POST['service_id'])) {
            $saved = array(
                'success' => true,
                'message' => 'Your sitebuilder website information has successfully been updated',

            );
            defaultPage($ca, $saved);
        } else {
            $saved = array(
                'success' => false,
                'message' => 'There was an issue syncing your sitebuilder data please contact our support team',

            );
            defaultPage($ca, $saved);
        }
    } else {
        $ca->assign('sitebuilder_error', 'The service you picked you do not have permissions or was not found');
        $ca->setTemplate('sitebuilder_error');
    }
}

/**
 * Checks if that specific user has access to that service
 * @param $service_id
 * @param $user_id
 * @return bool
 */
function checkServiceAccess($service_id, $user_id)
{
    $serviceResult = select_query("tblhosting", "COUNT(*) as count", array("id" => $service_id, 'userid' => $user_id));
    $service = mysql_fetch_array($serviceResult);
    if ($service['count'] == 1) {
        return true;
    }
    return false;
}

/**
 * Checks if a service is a trial or not
 * @param $service_id
 * @return bool
 */
function isTrial($service_id)
{
    $join = "tblproducts ON tblhosting.packageid=tblproducts.id";
    $result = select_query("tblhosting", "*", array("tblhosting.id" => $service_id), "", "", "", $join);
    $service = mysql_fetch_array($result);
    if ($service['configoption2'] == 'on') {
        return true;
    }
    return false;
}

/**
 * Retrieves the topline object
 * @param $serviceId
 * @return ToplineYola
 */
function gettoplineYola($serviceId)
{
    require_once('modules/servers/toplineyola/libs/toplineyola.php');
    $join = "tblservers ON tblservers.id=tblhosting.server";
    $result = select_query("tblhosting", "tblservers.*", array("tblhosting.id" => $serviceId), "", "", "", $join);
    $server = mysql_fetch_array($result);
    $apiConfig = array(
        'partnerId' => $server['username'],
        'partnerGuid' => $server['accesshash'],
    );
    return $topline = new ToplineYola($apiConfig);
}