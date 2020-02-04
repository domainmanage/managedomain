<?php
require_once __DIR__ . '/vendor/autoload.php';

use ManageDomainLibs\ApiClient;
use ManageDomainLibs\Language;
use WHMCS\Config\Setting;

add_hook('ShoppingCartValidateDomainsConfig', 1, function ($vars) {

    if ($vars['a'] == "confdomains") {
        $checkNicHandleStatus = new ApiClient();
        $resultArray = array();

        foreach ($vars['domainfield'] as $customField) {
            if (key_exists("nichandle", $customField)) {
                if (empty($customField['nichandle'])) {
                    return false;
                }
                
                
                preg_match('/^[a-z]{2}[0-9]\d+-irnic/', $customField['nichandle'], $matches);
                
                
                if (count($matches) == 1 || filter_var($customField['nichandle'],FILTER_VALIDATE_EMAIL)) {
                    $status = $checkNicHandleStatus->checkNicHandle($customField['nichandle']);

                    if (!$status['valid']) {
                        switch (key($status['code'])) {
                            case 300:
                                $resultArray[] = str_replace("[nic]", "[{$customField['nichandle']}]", Language::lang("invalidNichandle"));
                                break;
                            case 400:
                                $resultArray[] = str_replace("[nic]", "[{$customField['nichandle']}]", Language::lang("invalidPermission"));
                                break;
                            case 500:
                                $resultArray[] = str_replace("[nic]", "[{$customField['nichandle']}]", Language::lang("someError"));
                                break;
                            default:
                                $resultArray[] = str_replace("[nic]", "[{$customField['nichandle']}]", Language::lang("error"));
                                break;
                        }
                    }
                } else {
                    $resultArray[] = str_replace("[nic]", "[{$customField['nichandle']}]", Language::lang("invalidNichandle"));
                }
            }
        }
        if (count($resultArray) > 0) {
            return $resultArray;
        }
    }
});