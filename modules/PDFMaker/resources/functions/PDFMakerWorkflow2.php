<?php

if(!function_exists("pdfmaker_recordlist")) {
    function pdfmaker_recordlist($environmentId) {
        require_once('modules/Workflow2/autoload_wf.php');
        $html = '';

        if(class_exists('\\Workflow\\ExpressionParser') && isset(\Workflow\ExpressionParser::$INSTANCE)) {
            $context = \Workflow\Main::$INSTANCE->getContext();
            $env = $context->getEnvironment($environmentId);
            $html = $env['html'];
        }

        return $html;
    }
}
if(!function_exists("record_env")) {
    function record_env($crmid, $envKey) {
        require_once('modules/Workflow2/autoload_wf.php');
        $context = \Workflow\VTEntity::getForId($crmid);
        return $context->getEnvironment($envKey);
    }
}

?>