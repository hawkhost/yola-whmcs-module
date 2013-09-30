<?php
class ToplineYola
{
    protected $apiAddCustomer;
    protected $apiModifyCustomer;
    protected $apiDeleteCustomer;
    protected $apiQueryCustomer;
    protected $apiRequestToken;
    protected $apiBundleInfo;
    protected $apiLogin;
    protected $format;

    /**
     * @param $options Various configurations options
     */
    public function __construct($options)
    {
        if (isset($options['partnerId'])) {
            $this->setPartnerId($options['partnerId']);
        }
        if (isset($options['partnerGuid'])) {
            $this->setPartnerGuid($options['partnerGuid']);
        }
        $this->apiAddCustomer = 'https://api.sitebuilderservice.com/addcustomer.php';
        $this->apiModifyCustomer = 'https://api.sitebuilderservice.com/modifycustomer.php';
        $this->apiDeleteCustomer = 'https://api.sitebuilderservice.com/deletecustomer.php';
        $this->apiQueryCustomer = 'https://api.sitebuilderservice.com/querycustomer.php';
        $this->apiRequestToken = 'https://api.sitebuilderservice.com/request_token.php';
        $this->apiBundleInfo = 'https://api.sitebuilderservice.com/bundleinfo.php';
        $this->apiLogin = 'http://login.sitebuilderservice.com/sitebuilder_session.php';
        $this->apiBundleInfo = 'https://api.sitebuilderservice.com/bundleinfo.php';
    }

    /**
     * Sets partner ID for all API Calls
     * @param $partnerId
     */
    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
    }

    /**
     * Sets partner Guid for all API calls
     * @param $partnerGuid
     */
    public function setPartnerGuid($partnerGuid)
    {
        $this->partnerGuid = $partnerGuid;
    }

    /**
     * Adds Customer
     * @param $options
     * @return array
     */
    public function addCustomer($options)
    {
        if (!isset($options['partner_id'])) {
            $options['partner_id'] = $this->partnerId;
            $options['partner_guid'] = $this->partnerGuid;
        }
        $result = $this->call($this->apiAddCustomer, $options);
        $status = array();
        $status['success'] = true;
        $status['error'] = '';
        if ($result->attributes()->code == 406) {
            $status['success'] = false;
            $status['error'] = 'Missing required fields: ' . $result->required;
        } else if ($result->attributes()->code == 200) {
            // No message 200 at all

        }
        return $status;
    }

    /**
     * Modify a current customer
     * @param $options
     * @return array
     */
    public function modifyCustomer($options)
    {
        if (!isset($options['partner_id'])) {
            $options['partner_id'] = $this->partnerId;
            $options['partner_guid'] = $this->partnerGuid;
        }
        $status = array();
        $status['success'] = true;
        $status['error'] = '';
        if (!isset($options['userid'])) {
            $status['success'] = false;
            $status['error'] = 'You need a userid in order to modify a customer';
            return $status;
        }
        $result = $this->call($this->apiModifyCustomer, $options);
        if ($result->attributes()->code == 406) {
            $status['success'] = false;
            $status['error'] = 'Missing required fields: ' . $result->required;
        } else if ($result->attributes()->code == 200) {

        }
        return $status;


    }

    /**
     * Query customer
     * @param $options
     * @return array
     */
    public function queryCustomer($options)
    {
        $status = array();
        $status['success'] = true;
        $status['error'] = '';
        if (!isset($options['partner_id'])) {
            $options['partner_id'] = $this->partnerId;
            $options['partner_guid'] = $this->partnerGuid;
        }
        if (!isset($options['domain'])) {
            $status['success'] = false;
            $status['error'] = 'Domain is a required field';
            return $status;
        }

        $xml = $this->call($this->apiQueryCustomer, $options);
        $result = $this->createArray($xml);
        return $result;

    }

    /**
     * Delete customer (not yet implemented)
     * @param $options
     */
    public function deleteCustomer($options)
    {
        if (!isset($options['partner_id'])) {
                    $options['partner_id'] = $this->partnerId;
                    $options['partner_guid'] = $this->partnerGuid;
                }
        $status = array();
                $status['success'] = true;
                $status['error'] = '';
                if (!isset($options['userid'])) {
                    $status['success'] = false;
                    $status['error'] = 'You need a userid in order to remove a customer';
                    return $status;
                }
                $result = $this->call($this->apiDeleteCustomer, $options);
                if ($result->attributes()->code == 406) {
                    $status['success'] = false;
                    $status['error'] = 'Missing required fields: ' . $result->required;
                } else if ($result->attributes()->code == 200) {

                }
                return $status;

    }

    /**
     * Request a token
     * @param $options
     * @return array
     */
    public function requestToken($options)
    {
        if (!isset($options['partner_id'])) {
            $options['partner_id'] = $this->partnerId;
            $options['partner_guid'] = $this->partnerGuid;
        }
        $status = array();
        $status['success'] = true;
        $status['error'] = '';
        if (!isset($options['userid'])) {
            $status['success'] = false;
            $status['error'] = 'You need a userid in order to request a token';
            return $status;
        }
        $result = $this->call($this->apiRequestToken, $options);
        if ($result->attributes()->code == 406) {
            $status['success'] = false;
            $status['error'] = 'Missing required fields: ' . $result->required;
        } else if ($result->attributes()->code == 201) {
            $status['token'] = (string)$result->token;
            $status['userid'] = (string)$result->userid;

        }
        return $status;

    }

    /**
     * Login option does not actually do anything
     * @param $options
     */
    public function login($options)
    {
        /*
        if (!isset($options['sbstkn']))
                        {
                            $status['success'] = false;
                            $status['error'] = 'You need a token to login';
                            return $status;
                        }
        $result = $this->call($this->apiLogin,$options);
        echo '<pre>';
        print_r($result);
        exit;
        */
    }

    /**
     * Adds reseller option ETA v1.1
     * @param $options
     */
    public function addReseller($options)
    {

    }

    /**
     * Modify reseller option ETA v1.1
     * @param $options
     */
    public function modifyReseller($options)
    {

    }

    /**
     * Verify partner option ETA v1.1
     * @param $options
     */
    public function verifyPartner($options)
    {

    }

    /**
     * Retrieves a list of available bundles
     * @param array $options
     * @return array
     */
    public function bundleInfo($options = array())
    {
        if (isset($options['partnerId'])) {
            $partnerId = $options['partnerId'];
            $partnerGuid = $options['partnerGuid'];
        } else {
            $partnerId = $this->partnerId;
            $partnerGuid = $this->partnerGuid;
        }
        $params = array(
            'partner_id' => $partnerId,
            'partner_guid' => $partnerGuid,
        );
        $xml = $this->call($this->apiBundleInfo, $params);
        return $this->createArray($xml);
    }

    /**
     * Execute API calls
     * @param $apiUrl
     * @param $params
     * @return SimpleXMLElement
     */
    protected function call($apiUrl, $params)
    {
        $curl = curl_init($apiUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($curl);
        curl_close($curl);
        $xml = new SimpleXMLElement($response);
        return $xml;

    }

    /**
     * Converts our XML into a pretty array
     * @param $xml
     */
    public function createArray($xml)
    {
        $data = array();
        foreach ($xml as $entry) {
            $row = array();
            foreach ($entry->attributes() as $k => $v) {
                // Attributes only if they don't already exist below
                if (!isset($entry['$k'])) {
                    $row[$k] = (string)$v;
                }
            }
            // Fill other fields
            foreach ($entry as $k => $v) {
                $row[$k] = (string)$v;
            }
            $data[] = $row;
        }
        return $data;
    }
}