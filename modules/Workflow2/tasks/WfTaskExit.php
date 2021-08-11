<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
/* vt6 ready 2014/04/12 */
class WfTaskExit extends \Workflow\Task
{
    public function handleTask(&$context) {
        $context->save();

        if(!wfIsCli()) {
            $settings = $this->getWorkflow()->getSettings();
            $title = $this->getTitle();
            if(empty($title)) $title = "no title";
            echo "".sprintf(getTranslatedString("TXT_EXIT_INFO", "Workflow2"), $settings["title"], $title)."";
            exit();
        } else {
            return false;
        }
    }
}
