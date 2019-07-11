<?php

use WHMCS\View\Menu\Item as MenuItem;


add_hook('ClientAreaPrimaryNavbar', 1, function (MenuItem $primaryNavbar) {
    $langDir = ROOTDIR . "/modules/addons/Manage_Domain/lang/";
    include $langDir . $_SESSION["Language"] . ".php";

    if (!is_null($primaryNavbar->getChild('Billing'))) {
        $primaryNavbar->getChild('Billing')
            ->addChild('divider', array(
                'order' => '100',
            ))->setClass('nav-divider');
        $primaryNavbar->getChild('Billing')
            ->addChild('Charge Domainpanel', array(
                'label' => $_LANG["domain"],
                'uri' =>
                    'index.php?m=Manage_Domain',
                'order' => '101',
            ));


    }
});


add_hook('ClientAreaHeadOutput', 1, function ($vars) {
    if ($_GET["m"] == "Manage_Domain") {
        $js = \WHMCS\Utility\Environment\WebHelper::getBaseUrl();
        return <<<HTML
<script type="text/javascript" src="$js/modules/addons/Manage_Domain/js/jquery.inputmask.min.js"></script>
<script >
        $(window).on('load', function () {
                       $('.currency').inputmask("numeric", {
                        radixPoint: ".",
                        groupSeparator: ",",
                        digits: 2,
                        autoGroup: true,
                        rightAlign: false,
                        oncleared: function () {
                            self.Value('');
                        }
                    });
                })
</script>
HTML;
    }
});
//<script> var whmcsBaseUrl = "{\WHMCS\Utility\Environment\WebHelper::getBaseUrl()}"; </script>
