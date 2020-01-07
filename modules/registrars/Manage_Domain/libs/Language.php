<?php
namespace ManageDomainLibs;

use WHMCS\Config\Setting;

class Language
{
    public static $languageVariable;

    function Constructor()
    {
        $LanguageSession = isset($_SESSION['Language']) ? $_SESSION['Language'] : Setting::getValue("language");
        $languageFile = __DIR__ . '/../lang/' . $LanguageSession . '.php';

        if (file_exists($languageFile))
            require_once $languageFile;
        elseif (file_exists(__DIR__ . '/lang/' . Setting::getValue("language") . '.php'))
            require_once __DIR__ . '/../lang/' . Setting::getValue("language") . '.php';
        else
            require_once __DIR__ . '/../lang/farsi.php';

        self::$languageVariable = $languageVar;
    }

    public static function lang($key)
    {
        self::Constructor();
        return key_exists($key, self::$languageVariable) ? self::$languageVariable[$key] : $key;
    }
}