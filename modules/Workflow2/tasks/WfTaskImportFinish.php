<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension

 * Last Change: 2012-12-06 1.6 swarnat
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

require_once(realpath(dirname(__FILE__).'/../lib/Workflow/Importer.php'));

class WfTaskImportFinish extends \Workflow\Task
{
    protected $_envSettings = array();
    protected $_javascriptFile = "WfTaskCSVnextLine.js";
    protected $_ConfigTemplate = false;

    /**
     * @param $context \Workflow\VTEntity
     */
    public function handleTask(&$context) {
        $importHash = $context->getEnvironment('_import_hash');
        $importer = \Workflow\Importer::getInstance($importHash);
        $importer->set('finish', true);

//        $importState = $context->getEnvironment("_internal");
//
//        $importState["finish"] = true;
//
//        $context->setEnvironment("_internal", $importState);

        $result = array(
            "done" => $importer->get('seek'),
            "ready" => true,
            'text' => sprintf(vtranslate('Import process with %s rows successfullly finished', 'Settings:Workflow2'), $importer->getTotalRows())
        );

        if(vglobal('_ImportTriggered') === true) {
            return 'yes';
        }

        if(!wfIsCli()) {
            echo json_encode($result);
            exit();
        }
    }

}
