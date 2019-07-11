<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if (!defined('DS'))
    define('DS', DIRECTORY_SEPARATOR);

require_once(dirname(dirname(dirname(__FILE__))) . DS . "registrars" . DS . "Manage_Domain" . DS . 'libs' . DS . 'ApiClient.php');

use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domains\DomainLookup\SearchResult;
use WHMCS\Module\Registrar\Registrarmodule\ApiClient;


function Manage_Domain_MetaData()
{
    return array(
        'DisplayName' => 'Manage Domain Registeration',
        'APIVersion' => '1.0',
    );
}

function Manage_Domain_getConfigArray()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Manage Domain Registration Module.',
        ),

        'ApiUrl' => array(
            'Type' => 'text',
            'Size' => '500',
            'Default' => '',
            'Description' => 'Enter Api URL',
        ),

        'ApiKey' => array(
            'Type' => 'text',
            'Size' => '500',
            'Default' => '',
            'Description' => 'Enter Api Key',
        ),
    );
}


function Manage_Domain_RegisterDomain($params)
{
    try {
        $api = new ApiClient();
        $api->post()->call('RegisterDomain', $params);

        if ($api->status) {
            return array(
                'success' => true,
            );
        } else {
            return array(
                'error' => $api->message,
            );
        }
    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

function Manage_Domain_TransferDomain($params)
{

    try {
        $api = new ApiClient();

        $api->post()->call('TransferDomain', $params);


        if ($api->status) {
            return array(
                'success' => true,
            );
        } else {
            return array(
                'error' => $api->message,
            );
        }

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }


}

function Manage_Domain_RenewDomain($params)
{

    try {
        $api = new ApiClient();
        $api->post()->call('RenewDomain', $params);

        if ($api->status) {
            return array(
                'success' => true,
            );
        } else {
            return array(
                'error' => $api->message,
            );
        }

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}


function Manage_Domain_GetNameservers($params)
{
    $values = array();
    try {
        $api = new  ApiClient();

        $api->get()->call("GetNameservers", $params);
        if ($api->status) {
            $result = objectToArray($api);
            if (is_array($result["results"]["response"])) {

                if (isset($result["results"]["response"]['ns1'])) {
                    $values["ns1"] = $result["results"]["response"]['ns1'];
                }
                if (isset($result["results"]["response"]['ns2'])) {
                    $values["ns2"] = $result["results"]["response"]['ns2'];
                }
                if (isset($result["results"]["response"]['ns3'])) {
                    $values["ns3"] = $result["results"]["response"]['ns3'];
                }
                if (isset($result["results"]["response"]['ns4'])) {
                    $values["ns4"] = $result["results"]["response"]['ns4'];
                }
                if (isset($result["results"]["response"]['ns5'])) {
                    $values["ns5"] = $result["results"]["response"]['ns5'];
                }
            }
        } else {
            if (isset($api->results['result']) && $api->results['result'] == "error") {
                $values["error"] = json_encode($api->results["message"]);

            }
        }
        return ($values);
    } catch (Exception $exception) {
        die($exception->getMessage());
    }
    return $values;
}

function Manage_Domain_RegisterNameserver($params)
{
    try {
        $api = new ApiClient();
        $api->call('RegisterNameServer', $params);

        if ($api->status) {
            return array(
                'success' => true,
            );
        } else {
            return array(
                'error' => $api->message,
            );
        }
    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}


function Manage_Domain_ModifyNameserver($params)
{

    try {
        $api = new ApiClient();
        $api->post()->call('ModifyPrivateNameServer', $params);
        if ($api->status) {
            return array(
                'success' => true,
            );
        } else {
            return array(
                'error' => $api->message,
            );
        }
    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}


function Manage_Domain_DeleteNameserver($params)
{

    try {
        $api = new ApiClient();
        $api->post()->call('RemoveNameServer', $params);

        if ($api->status) {
            return array(
                'success' => true,
            );
        } else {
            return array(
                'error' => $api->message,
            );
        }
    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

function Manage_Domain_SaveNameservers($params)
{
    try {
        $api = new ApiClient();
        $api->call('ModifyNameserver', $params);
        if ($api->status) {
            return array(
                'success' => true,
            );
        } else {
            return array(
                'error' => $api->message,
            );
        }
    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

function Manage_Domain_GetEPPCode($params)
{

    try {
        $api = new ApiClient();
        $api->post()->call('GetEPPCode', $params);
        if ($api->status) {

            return array(
                "eppcode" => $api->results["message"],
            );
        } else {
            return array(
                'error' => $api->message,
            );
        }
    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}


function Manage_Domain_GetContactDetails($params)
{
    try {
        $api = new ApiClient();
        $api->get()->call('GetContacts', $params);
        if ($api->status) {
            $Registrant = $api->results["response"];

            return array(
                'Registrant' => array(
                    "FirstName" => $Registrant["FirstName"],
                    "LastName" => utf8_encode($Registrant["LastName"]),
                    "Company" => utf8_encode($Registrant["Company"]),
                    "EMail" => $Registrant["EMail"],
                    "AddressLine1" => utf8_encode($Registrant["AddressLine1"]),
                    "State" => $Registrant["State"],
                    "City" => $Registrant["City"],
                    "Country" => $Registrant["Country"],
                    "Fax" => $Registrant["Fax"],
                    "FaxCountryCode" => $Registrant["FaxCountryCode"],
                    "Phone" => $Registrant["Phone"],

                    "ZipCode" => $Registrant["ZipCode"],
                ),
            );
        } else {
            return array(
                'error' => $api->message,
            );
        }
    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }


}

function Manage_Domain_SaveContactDetails($params)
{
    try {

        $api = new ApiClient();
        $api->call('SaveContactDetails', $params);


        return array(
            'success' => true,
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

function Manage_Domain_CheckAvailability($params)
{
    try {
        $api = new ApiClient();
        $api->get()->call('CheckAvailability', $params);

        $results = new ResultsList();
        foreach ($api->results['response']['domains'] as $domain) {

            $searchResult = new SearchResult($domain['sld'], $domain['tld']);

            if ($domain['status'] == 'available') {
                $status = SearchResult::STATUS_NOT_REGISTERED;
            } elseif ($domain['status'] == 'registered') {
                $status = SearchResult::STATUS_REGISTERED;
            } elseif ($domain['status'] == 'reserved') {
                $status = SearchResult::STATUS_RESERVED;
            } else {
                $status = SearchResult::STATUS_TLD_NOT_SUPPORTED;
            }
            $searchResult->setStatus($status);

            if ($domain['isPremiumName']) {
                $searchResult->setPremiumDomain(true);
                $searchResult->setPremiumCostPricing(
                    array(
                        'register' => $domain['price'],
                        'renew' => $domain['price'],
                        'CurrencyCode' => 'تومان',
                    )
                );
            }

            $results->append($searchResult);
        }

        return $results;

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

function Manage_Domain_TransferSync($params)
{
    $userIdentifier = $params['API Username'];
    $apiKey = $params['API Key'];
    $testMode = $params['Test Mode'];
    $accountMode = $params['Account Mode'];
    $emailPreference = $params['Email Preference'];
    $additionalInfo = $params['Additional Information'];

    $sld = $params['sld'];
    $tld = $params['tld'];

    $postfields = array(
        'username' => $userIdentifier,
        'password' => $apiKey,
        'testmode' => $testMode,
        'domain' => $sld . '.' . $tld,
    );

    try {
        $api = new ApiClient();
        $api->call('CheckDomainTransfer', $postfields);

        if ($api->getFromResponse('transfercomplete')) {
            return array(
                'completed' => true,
                'expirydate' => $api->getFromResponse('expirydate'), // Format: YYYY-MM-DD
            );
        } elseif ($api->getFromResponse('transferfailed')) {
            return array(
                'failed' => true,
                'reason' => $api->getFromResponse('failurereason'), // Reason for the transfer failure if available
            );
        } else {
            return array();
        }

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}


function Manage_Domain_GetRegistrarLock($params)
{
    $api = new ApiClient();
    $api->get()->call('GetLockStatus', $params);
    if ($api->status) {
        if (isset($api->results['response']['LockStatus'])) {
            if ($api->results['response']['LockStatus'] === true || $api->results['response']['LockStatus'] == "true") {
                $values = "locked";
            } else {
                $values = "unlocked";
            }

        }
    } else {
        $values["error"] = $api->results['response']["Message"] . "<br />" . $api->results["response"]["Details"];
    }

    return $values;
}

function Manage_Domain_GetDNS($params)
{

    try {
        $api = new ApiClient();
        $api->call('GetDNSHostRecords', $params);

        $hostRecords = array();
        foreach ($api->getFromResponse('records') as $record) {
            $hostRecords[] = array(
                "hostname" => $record['name'],
                "type" => $record['type'],
                "address" => $record['address'],
                "priority" => $record['mxpref'],
            );
        }
        return $hostRecords;

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

function Manage_Domain_SaveDNS($params)
{
    $userIdentifier = $params['API Username'];
    $apiKey = $params['API Key'];
    $testMode = $params['Test Mode'];
    $accountMode = $params['Account Mode'];
    $emailPreference = $params['Email Preference'];
    $additionalInfo = $params['Additional Information'];

    $sld = $params['sld'];
    $tld = $params['tld'];

    $dnsrecords = $params['dnsrecords'];

    $postfields = array(
        'username' => $userIdentifier,
        'password' => $apiKey,
        'testmode' => $testMode,
        'domain' => $sld . '.' . $tld,
        'records' => $dnsrecords,
    );

    try {
        $api = new ApiClient();
        $api->call('GetDNSHostRecords', $postfields);

        return array(
            'success' => 'success',
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}


function Manage_Domain_SaveRegistrarLock($params)
{


    try {

        $api = new ApiClient();
        $api->get()->call('GetLockStatus', $params);

        if ($api->status) {
            if (isset($api->results['response']['LockStatus'])) {
                if ($api->results['response']['LockStatus'] === true || $api->results['response']['LockStatus'] == "true") {
                    $kilit = "locked";
                } else {
                    $kilit = "unlocked";
                }
                if ($kilit == "unlocked") {
                    $api = new ApiClient();
                    $api->put()->call('EnableTheftProtectionLock', $params);

                } else {
                    $api = new ApiClient();
                    $api->put()->call('DisableTheftProtectionLock', $params);
                }

                if ($api->status) {
                    $values = array("success" => true);
                } else {
                    return array(
                        'error' => $api->message,
                    );
                }

            }
        } else {
            $values["error"] = $api->results['response']["Message"] . "<br />" . $api->results['response']["Details"];
        }
        return $values;
    } catch (\Exception $e) {
        return array(
            "error" => $e->getMessage()
        );
    }
}


function nameservervalidation($params)
{
    try {
        $api = new  ApiClient();
        $api->call("nameservervalidation", $params);
        if ($api->status) {
            return array(
                'success' => true,
            );
        } else {
            return array(
                'error' => $api->message,
            );
        }
    } catch (Exception $exception) {
        die($exception->getMessage());
    }

}

function objectToArray($object)
{
    try {
        $object = json_decode(json_encode($object), true);
    } catch (Exception $ex) {
    }
    return $object;
}
