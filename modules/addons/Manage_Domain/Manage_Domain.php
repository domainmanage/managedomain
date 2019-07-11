<?php

include(__DIR__ . "/vendor/autoload.php");
$lang = dirname(__FILE__) . DS . '/libs/ApiClient.php';
include($lang);

use ManageDomain\Entity\Manager;
use ManageDomain\ApiClient;
use WHMCS\Database\Capsule as DB;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if (!defined("DS")) {
    define('DS', DIRECTORY_SEPARATOR);
}

function Manage_Domain_config()
{
    $configarray = array(
        "name" => "Manage Domain",
        "description" => "Manage domain Addond",
        "version" => "1.0.1",
        "author" => "Great world Lovers",
    );
    return $configarray;
}

function Manage_Domain_activate()
{
    try {
        DB::schema()->create(
            'mod_MD_transactions',
            function ($table) {
                $table->increments('id');
                $table->string('amount');
                $table->string('type');
                $table->integer('userid');
                $table->integer('invoiceid');
                $table->string('description');
                $table->string('status');
                $table->timestamps();
            }
        );
        DB::schema()->create(
            'mod_MD_configs',
            function ($table) {
                $table->increments('id');
                $table->string('key');
                $table->string('value');
            }
        );

        DB::table("mod_MD_configs")->insert([
            "key" => "extrapercent",
            "value" => "1"
        ]);
        DB::table("mod_MD_configs")->insert([
            "key" => "convertrate",
            "value" => "10"
        ]);
        $defaultCurrency = DB::table("tblcurrencies")->where("default", 1)->first();
        DB::table("mod_MD_configs")->insert([
            "key" => "defaultcurrency",
            "value" => $defaultCurrency->id
        ]);

    } catch (\Exception $e) {
        return array('status' => 'error', 'description' => 'error in create table for module');
    }
    return array('status' => 'success', 'description' => 'Manage_domain active successfully');
}

function Manage_Domain_deactivate()
{
    try {
        DB::schema()->dropIfExists('mod_MD_transactions');
        DB::schema()->dropIfExists('mod_MD_configs');
    } catch (Exception $exception) {
        return array('status' => 'error', 'description' => $exception->getMessage());
    }
    return array('status' => 'success', 'description' => 'Manage_domain deactive successfully');

}

function Manage_Domain_clientarea($vars)
{
    $language = strtolower($_SESSION['Language']);
    $lang = dirname(__FILE__) . DS . 'lang' . DS . $language . '.php';
    if (!file_exists($lang))
        $lang = dirname(__FILE__) . DS . 'lang' . DS . 'farsi.php';
    include($lang);
    $client = Manage_Domain_GetClientsDetails($_SESSION["uid"]);
    $configs = DB::table("mod_MD_configs")->pluck("value", 'key');

    if (!isset($_GET["type"]) || $_GET["type"] != "pricetype") {
        if (!isset($configs["pricetype"]) && $configs["pricetype"] == '') {
            header('Location: index.php?m=Manage_Domain&Status=error&type=pricetype');
            die;
        }
    }

    if (isset($_GET["amount"])) {
        $_GET["amount"] = str_replace(',', '', $_GET["amount"]);

        if (!intval($_GET['amount'])) {
            header('Location: index.php?m=Manage_Domain&Status=error&balance=intval');
            die;
        }
        $credit = $client["credit"];
        if ($credit < $_GET["amount"]) {
            header('Location: index.php?m=Manage_Domain&Status=error&balance=low');
            die;
        }
        $api = DB::table("tblregistrars")->where('registrar', 'Manage_Domain')->pluck('value', 'setting');
        $configs = DB::table("mod_MD_configs")->pluck('value', 'key');
        $parameters = [
            'amount' => $_GET["amount"],
            'email' => $_GET["email"],
            'pricetype' => $configs["pricetype"],
            'ApiUrl' => Manage_Domain_DecryptPassword($api["ApiUrl"])["password"],
            'ApiKey' => Manage_Domain_DecryptPassword($api["ApiKey"])["password"],
            'client' => $client
        ];
        Manage_Domain_ChargeAccount($parameters);
    }

    return array(
        'pagetitle' => $_LANG["title"],
        'breadcrumb' => array('index.php?m=Manage_Domain' => $_LANG["domain"]),
        'templatefile' => 'clienthome',
        'requirelogin' => true,
        'forcessl' => false,
        'vars' => array(
            'status' => $_GET["Status"],
            'balance' => $_GET["balance"],
            'type' => $_GET['type'],
            "lang" => $_LANG,
            'email' => $client["email"],
            'userBalance' => $client["credit"],
            'currency_code' => $client["currency_code"]
        ),
    );
}

function Manage_Domain_getBalance()
{

    $result = DB::table("tblregistrars")->where("registrar", "Manage_Domain")->get();
    return $result;
}

function Manage_Domain_DecryptPassword($password)
{
    $command = 'DecryptPassword';
    $postData = array(
        'password2' => $password,
    );
    $results = localAPI($command, $postData);
    return $results;
}

function Manage_Domain_applycredit($params)
{
    $command = 'CreateInvoice';
    $postData = array(
        'userid' => $_SESSION["uid"],
        'itemdescription1' => 'invoice for charge domain account',
        'itemamount1' => $params["amount"],
        'autoapplycredit' => '1',
    );
    $results = localAPI($command, $postData);
    if ($results["result"] == "success") {
        DB::table("mod_MD_transactions")->insert([
            'amount' => $params["amount"],
            'userid' => $params['client']['userid'],
            'type' => '+',
            'status' => 'payed',
            'invoiceid' => $results["invoiceid"],
            'description' => 'پرداخت برای فروش دامنه',
        ]);
    }
    return $results;
}

function Manage_Domain_GetClientsDetails($id)
{
    $command = 'GetClientsDetails';
    $postData = array(
        'clientid' => $id,
        'stats' => true,
    );
    $results = localAPI($command, $postData);
    return $results;
}

function Manage_Domain_ChargeAccount($params)
{
    $minesAccount = Manage_Domain_applycredit($params);
    $api = new ApiDomain();
    $result = $api->post()->call("chargeAccount", $params);
    if ($result["result"] == "success") {
        header('Location: index.php?m=Manage_Domain&Status=success');
        die;
    } else {
        $command = 'AddCredit';
        $postData = array(
            'clientid' => $_SESSION["uid"],
            'description' => 'refund price off domain invoic : ' . $minesAccount["invoiceid"],
            'amount' => $params['amount'],
        );
        try {
            DB::table("mod_MD_transactions")
                ->where('invoiceid', $minesAccount["invoiceid"])
                ->update([
                    'status' => 'refund',
                    'description' => 'بازگشت موجودی به حساب کاربر'
                ]);
        } catch
        (Exception $ex) {
            echo $ex->getMessage();
            die;
        }
        $results = localAPI($command, $postData);
        if ($results["result"] == "success") {
            header('Location: index.php?m=Manage_Domain&Status=error&balance=notrefund');
            die;
        }
    }
}

function Manage_Domain_output($vars)
{
    $whmcsDate = [
        "1" => "msetupfee",
        "2" => "qsetupfee",
        "3" => "ssetupfee",
        "4" => "asetupfee",
        "5" => "bsetupfee",
        "6" => "monthly",
        "7" => "quarterly",
        "8" => "semiannually",
        "9" => "annually",
        "10" => "biennially",
    ];
    $versionManager = new Manager();


    if (isset($_REQUEST["savefastimporter"])) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $addedPercent = $_POST['changepercent'];
            $defaultSite = $_POST['defaultcurrency'];
            $convertToCurrency = $_POST['convertcurrency'];
            try {


                DB::table("mod_MD_configs")->updateorInsert(
                    [
                        "key" => "extrapercent"
                    ]
                    , [
                    "key" => "extrapercent",
                    "value" => $addedPercent
                ]);
                DB::table("mod_MD_configs")->updateorInsert([
                        "key" => "convertrate"
                    ]
                    , [
                        "key" => "convertrate",
                        "value" => $convertToCurrency
                    ]);

                DB::table("mod_MD_configs")->updateorInsert([
                        "key" => "defaultcurrency"
                    ]
                    , [
                        "key" => "defaultcurrency",
                        "value" => $defaultSite
                    ]);
                echo "<div class='alert alert-success text-center'> <strong>Saved successfully</strong></div>";

            } catch (Exception $e) {
                echo "<div class='alert alert-danger text-center'> <strong>ooooooppppps something wrong</strong></div>";

            }
        }
    }
    if (isset($_REQUEST['saveform'])) {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $domainPrices = Manage_Domain_getprice();
            $importSetting = DB::table("mod_MD_configs")->pluck("value", "key");
            foreach ($_POST as $key => $value) {
                if (isset($domainPrices[$value])) {
                    $terms = explode(",", $domainPrices[$value]['term']);
                    $registerPrice = [];
                    $transferPrice = [];
                    $renewPrice = [];

                    switch ($importSetting['convertrate']) {
                        case "1":
                        case "3":
                            $convertRate = 1;
                            break;
                        case "2":
                            $convertRate = 10;
                            break;
                    }

                    foreach ($terms as $term) {

                        if ($importSetting['convertrate'] != 3) {

                            if ($domainPrices[$value]['exchange'] == "usd") {
                                $registerPrice[$whmcsDate[$term]] = abs(round(((($domainPrices[$value]['register'] * $term) * $domainPrices['usd']) * ($importSetting['extrapercent'] + 100) / 100) + 500, -3)) / $convertRate;
                                $transferPrice[$whmcsDate[$term]] = abs(round(((($domainPrices[$value]['transfer'] * $term) * $domainPrices['usd']) * ($importSetting['extrapercent'] + 100) / 100) + 500, -3)) / $convertRate;
                                $renewPrice[$whmcsDate[$term]] = abs(round(((($domainPrices[$value]['renew'] * $term) * $domainPrices['usd']) * ($importSetting['extrapercent'] + 100) / 100) + 500, -3)) / $convertRate;
                            } else {
                                $registerPrice[$whmcsDate[$term]] = abs(round((($domainPrices[$value]['register'] * $term) * ($importSetting['extrapercent'] + 100) / 100) + 500, -3)) / $convertRate;
                                $transferPrice[$whmcsDate[$term]] = abs(round((($domainPrices[$value]['transfer'] * $term) * ($importSetting['extrapercent'] + 100) / 100) + 500, -3)) / $convertRate;
                                $renewPrice[$whmcsDate[$term]] = abs(round((($domainPrices[$value]['renew'] * $term) * ($importSetting['extrapercent'] + 100) / 100) + 500, -3)) / $convertRate;
                            }
                        } else {
                            $registerPrice[$whmcsDate[$term]] = $domainPrices[$value]['register'] * $term;
                            $transferPrice[$whmcsDate[$term]] = $domainPrices[$value]['transfer'] * $term;
                            $renewPrice[$whmcsDate[$term]] = $domainPrices[$value]['renew'] * $term;
                        }
                    }

                    $tldDomain = DB::table("tbldomainpricing")->where("extension", "=", "." . $value)->first();
                    if ($tldDomain) {
                        $tldID = $tldDomain->id;
                        DB::table("mod_MD_tlds")->updateOrInsert([
                            "tld" => $value,
                            "systemid" => $tldID,
                        ], [
                            "tld" => $value,
                            "systemid" => $tldID,
                            "lastupdate" => time(),
                        ]);
                    } else {
                        DB::table("tbldomainpricing")->insert([
                            "extension" => "." . $value,
                            "dnsmanagement" => "1",
                            "emailforwarding" => "0",
                            "idprotection" => "0",
                            "eppcode" => "1",
                            "autoreg" => "Manage_Domain",
                            "group" => "",
                            "grace_period" => "-1",
                            "grace_period_fee" => "0.00",
                            "redemption_grace_period" => "-1",
                            "redemption_grace_period_fee" => "-1.00"
                        ]);

                        $tldID = DB::getPdo()->lastInsertId();
                        DB::table("mod_MD_tlds")->insert([
                            "tld" => $value,
                            "systemid" => $tldID,
                            "lastupdate" => time(),
                        ]);
                    }
                    $registerPrice['type'] = 'domainregister';
                    $transferPrice['type'] = 'domaintransfer';
                    $renewPrice['type'] = 'domainrenew';
                    $renewPrice['relid'] = $transferPrice['relid'] = $registerPrice['relid'] = $tldID;
                    $renewPrice['currency'] = $transferPrice['currency'] = $registerPrice['currency'] = $importSetting['defaultcurrency'];
                    DB::table('tblpricing')->updateOrInsert([
                        'relid' => $tldID,
                        'type' => 'domainregister'
                    ], $registerPrice
                    );
                    DB::table('tblpricing')->updateOrInsert([
                        'relid' => $tldID,
                        'type' => 'domaintransfer'
                    ], $transferPrice
                    );
                    DB::table('tblpricing')->updateOrInsert([
                        'relid' => $tldID,
                        'type' => 'domainrenew'
                    ], $renewPrice
                    );
                }
            }
            echo "<div class='alert alert-success text-center'> <strong>successfully Import</strong></div>";
        }
    }

    if (isset($_REQUEST["update"])) {
        if ($_REQUEST['update'] == '1') {
            $versionManager->ManageDomain_update();
            header("Location: addonmodules.php?module=Manage_Domain&update=2");
        } else if ($_REQUEST['update'] == '2') {
            echo "updated successfully.";
            $output = '<div class="alert alert-success text-center">';
            $output .= "SuccessFully Updated &nbsp;";
            $output .= '</div>';
            echo $output;
        }
    }

    $version = $vars['version'];
    $remoteVersion = $versionManager->getLatestVersion();

    if (version_compare($version, $remoteVersion)) {
        $output = '<div class="alert alert-info text-center">';
        $output .= "New update is available (Current version: $version, Latest version: $remoteVersion)&nbsp;";
        $output .= 'click <a href="addonmodules.php?module=Manage_Domain&update=1"><strong>here</strong></a> to update.';
        $output .= '</div>';
        echo $output;

    }

    if (isset($_GET["pricetype"])) {
        Manage_Domain_configs();
    }
    $smarty = new Smarty();
    $configs = DB::table("mod_MD_configs")->pluck('value', 'key');
    $smarty->assign('pricetype', $configs["pricetype"]);
    $smarty->template_dir = __DIR__ . '/templates';


    if (isset($_GET["page"])) {
        $page = $_GET["page"];
    } else {
        $page = 1;
    };
    $limit = 10;
    if ($page < 1) {
        $page = 1;
    }

    $smarty->assign('currentpage', $page);
    $start_from = ($page - 1) * $limit;
    $count = DB::table("mod_MD_transactions")->join('tblclients', 'mod_MD_transactions.userid', '=', 'tblclients.id')
        ->select('mod_MD_transactions.created_at as date', 'mod_MD_transactions.*', 'tblclients.firstname', 'tblclients.lastname')
        ->limit($limit)->count();
    $pagecount = $count / $limit;
    if ($page > $pagecount) {
        $page = $pagecount;
    }

    $smarty->assign("pagenumber", ceil($pagecount));
    if ($pagecount < 1) {
        $transactions = DB::table("mod_MD_transactions")->join('tblclients', 'mod_MD_transactions.userid', '=', 'tblclients.id')
            ->select('mod_MD_transactions.created_at as date', 'mod_MD_transactions.*', 'tblclients.firstname', 'tblclients.lastname')
            ->limit($limit)->get();
    } else {
        $transactions = DB::table("mod_MD_transactions")->join('tblclients', 'mod_MD_transactions.userid', '=', 'tblclients.id')
            ->select('mod_MD_transactions.created_at as date', 'mod_MD_transactions.*', 'tblclients.firstname', 'tblclients.lastname')
            ->offset($start_from)->limit($limit)->get();
    }


    $pricelist = Manage_Domain_getprice();

    $tldsHistory = DB::table("mod_MD_tlds")->select("tld")->pluck("tld");

    $siteCurrencies = DB::table("tblcurrencies")->get();

    $fastImporterSetting = DB::table("mod_MD_configs")->pluck("value", "key");

    $smarty->assign("pricelist", $pricelist);
    $smarty->assign("tldsHistory", $tldsHistory);
    $smarty->assign("transactions", $transactions);
    $smarty->assign("currencies", $siteCurrencies);
    $smarty->assign("fastimportersetting", $fastImporterSetting);
    $smarty->display("admin.tpl");

}

function Manage_Domain_configs()
{
    $config = DB::table("mod_MD_configs")->where("key", "pricetype")->first();
    if ($config) {
        DB::table('mod_MD_configs')->where("key", "pricetype")->update([
            'value' => $_GET["pricetype"]
        ]);
    } else {
        DB::table("mod_MD_configs")->insert([
            'key' => 'pricetype',
            'value' => $_GET["pricetype"]
        ]);
    }
}

function Manage_Domain_getprice()
{
    try {
        $api = new ApiClient();
        $api->get()->call('getprices');
    } catch (Exception $exception) {
        return json_encode(array(
            'error' => $exception->getMessage(),
        ));
    }
    return $api->results;
}
