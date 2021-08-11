<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
/* vt6 ready 2014/04/12 */
class WfTaskParseString extends \Workflow\Task {

    /**
     * @param $context \Workflow\VTEntity
     * @return string
     */
    public function handleTask(&$context) {
        $regex = $this->get("regex", $context);
        $source = $this->get("source", $context);

        $index = $this->get("targetindex");
        $envVar = $this->get("env_var");

        $this->addStat("Source:".$source);

        if(!empty($regex)) {
            try {
                @preg_match($regex, $source, $matches);
            } catch (Exception $exp) {
                Workflow2::error_handler(E_NONBREAK_ERROR, $exp->getMessage());
                return "no";
            }

            if(count($matches) >= $index) {
                $testresult = $matches[$index];
            } else {
                $testresult = "";
            }
        } else {
            $testresult = getTranslatedString("LBL_BEFORE_TEST_SET_REGEX", "Workflow2");
        }
        $context->setEnvironment($envVar, $testresult);

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        $testresult = "";
        if(!empty($_POST["testtext"])) {
            $regex = $this->get("regex");
            $index = $this->get("targetindex");

            if(!empty($regex)) {
                try {
                    @preg_match($regex, $_POST["testtext"], $matches);
                } catch (Exception $exp) {
                    $matches = array();
                }
                if(count($matches) >= $index) {
                    $testresult = $matches[$index];
                } else {
                    $testresult = "";
                }
            } else {
                $testresult = getTranslatedString("LBL_BEFORE_TEST_SET_REGEX", "Workflow2");
            }

            $viewer->assign("testtext", htmlentities($_POST["testtext"], ENT_QUOTES, "UTF-8"));
        }

        $viewer->assign("testresult", $testresult);
    }

    public function beforeSave(&$data) {

    }

}