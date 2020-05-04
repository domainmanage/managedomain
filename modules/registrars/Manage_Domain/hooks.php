<?php
require_once __DIR__ . '/vendor/autoload.php';

use ManageDomainLibs\ApiClient;
use ManageDomainLibs\Language;
use WHMCS\Database\Capsule as DB;

add_hook('AdminClientDomainsTabFields', 1, function ($vars) {

    $domainsData = DB::table("tbldomains")->where("id", $vars['id'])->first();

    if ($domainsData) {
        if (preg_match('/^([\p{L}\d\-]+)\.((?:[\p{L}\-]+\.?)+)$/ui', $domainsData->domain, $matches) || preg_match('/^(xn\-\-[\p{L}\d\-]+)\.(xn\-\-(?:[a-z\d-]+\.?1?)+)$/ui', $domainsData->domain, $matches)) {
            if ($matches[2] == "ir" || $matches[2] == "co.ir" || $matches[2] == "org.ir" || $matches[2] == "net.ir") {

                $api    = new ApiClient();
                $result = $api->getIrnicDomainsStatus($domainsData->domain);
                $data   = "";
                foreach ($result as $status) {
                    $data .= $status . "<br>";
                }

                return [
                    'IRNIC Domain Status' => $data,
                ];
            }
        }
    }
});

add_hook('ShoppingCartValidateDomainsConfig', 1, function ($vars) {

    if ($vars['a'] == "confdomains") {
        $checkNicHandleStatus = new ApiClient();
        $resultArray          = array();

        foreach ($vars['domainfield'] as $customField) {
            if (key_exists("nichandle", $customField)) {
                if (empty($customField['nichandle'])) {
                    return false;
                }

                preg_match('/^[a-z]{2}[0-9]\d+-irnic/', $customField['nichandle'], $matches);

                if (count($matches) == 1 || filter_var($customField['nichandle'], FILTER_VALIDATE_EMAIL)) {
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
