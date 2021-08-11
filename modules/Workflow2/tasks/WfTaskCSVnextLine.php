<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension

 * Last Change: 2012-12-06 1.6 swarnat
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskCSVnextLine extends \Workflow\Task
{
    protected $_envSettings = array();

    /**
     * @param $context \Workflow\VTEntity
     */
    public function handleTask(&$context) {
//        $importState = $context->getEnvironment("_internal");
        $pause_rows = $this->get("pause_rows");

        $importHash = $context->getEnvironment('_import_hash');
        $importer = \Workflow\Importer::getInstance($importHash);

        if($pause_rows !== -1 && !empty($pause_rows)) {
            if(wfIsCli() === false) {
                header('Seek' . $importer->get("seek") . ':1');
            }

//            var_dump('Import Hash: '.$importHash);
//            var_dump('Debug: Seek: '.$importer->get('seek').' LastPause: '.$importer->get("lastPause").' Total: '.$importer->get("total"));

			if($importer->get("seek") -  $importer->get("lastPause")  >= $pause_rows && $importer->get("total") > $importer->get("seek")) {
                $debugValue = $importer->get("seek");

                $importer->set("lastPause", $importer->get('seek'));

//                $context->setEnvironment("_internal", $importState);

                \Workflow\Queue::addEntry($this, $this->getWorkflow()->getUser(), $context, "static", time() + (864000 * 3), 1);

                $totalRows = $importer->get("total");
                $seek = $importer->get("seek");
                $importParams = $importer->get("importParams");
                if(!empty($importParams['skipfirst'])) {
                    $totalRows -= 1;
                    $seek -= 1;
                }

                $result = array(
                    "done" => $seek,
                    'total' => $totalRows,
                    "ready" => false,
                    'debug' => $debugValue,
                    'text' => sprintf(vtranslate('%s of %s rows done in Import process', 'Settings:Workflow2'), $seek, $totalRows)
                );

                $importer->set("execID", $this->getWorkflow()->getLastExecID());

                if(!wfIsCli()) {
                    echo json_encode($result);
                    exit();
                } else {
                    return;
                }
            }
        }

        do {
            $row = $importer->getNextRow();
//var_dump($row);
            $importer->set("seek", $importer->get("seek") + 1);
//            $importer->get("seek")++;

            if($row == false) {
                return "no";
            }
        } while(count($row) == 1 && empty($row[0]));

        // leere Zeilen am Ende der Datei werden nicht wieder in das Environment geschrieben und somit nicht mitgezählt!
//        $context->setEnvironment("_internal", $importState);

        #$importState["pos"]++;
        #$context->setEnvironment("_internal", $importState);

        $cols = $this->get("cols");

        $csv = array();
        foreach($cols as $index => $colKey) {
            $csv[$colKey] = $row[$index];
        }
        $context->setEnvironment("csv", $csv);

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        $pause_rows = $this->get("pause_rows");
        if($pause_rows == -1) {
            $this->set("pause_rows", 50);
        }

        $cols = $this->get("cols");

        if($cols == -1) {
            $cols = array();
        }

        $viewer->assign("cols", $cols);
    }

    public function beforeSave(&$values) {

    }

    public function getEnvironmentVariables() {
        $variables = array();

        $fields = $this->get('cols');
        if(!empty($fields) && $fields !== -1) {
            foreach($fields as $value) {
                $variables[] = "['csv']['".$value."'";
            }
            return $variables;
        }

        return array();
    }

}
