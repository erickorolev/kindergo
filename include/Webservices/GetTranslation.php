<?php

function vtws_gettranslation($portal_language, $module, $totranslate, $user)
{
    global $log, $default_language, $current_language;
    $log->debug('> vtws_gettranslation');
    foreach ($totranslate as $key => $str) {
        $translated[$str] = Vtiger_Functions::getTranslatedString($str, $module, $portal_language);
    }
    return $translated;
}

?>