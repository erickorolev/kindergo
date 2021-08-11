<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskPDFMaker extends \Workflow\Task
{
    protected $_javascriptFile = 'WfTaskPdfmaker.js';

    public function init() {
        $record = $this->get('recordChooser');
        if($record == 'current') {
            $moduleName = $this->getModuleName();
        }

        if($_POST['changeModule'] == '1') {
            $this->set('condition', array());
            $this->set('templates', array());
        }

        if(-1 != $this->get("search_module") || !empty($_POST["task"]["search_module"])) {
            $module = !empty($_POST["task"]["search_module"]) ? $_POST["task"]["search_module"] : $this->get("search_module");
            $parts = explode('#~#', $module);

            $moduleName = VtUtils::getModuleName($parts[1]);
        } else {
            $moduleName = $this->getModuleName();
        }

        $this->addPreset("Condition", "condition", array(
            'toModule' => $moduleName,
            'fromModule' => $this->getModuleName(),
            'mode' => 'mysql',
        ));

        $this->addPreset("FileActions", "resultaction", array(
            'module' => $moduleName,
        ));

        if($this->get('templateids') == -1 && $this->notEmpty('template')) {
            $this->set('templateids', implode(',', $this->get('template')));
        }
    }

    public function handleTask(&$context) {
        $adb = \PearDatabase::getInstance();

        if(!getTabid('PDFMaker') || !vtlib_isModuleActive('PDFMaker')) {
            throw new \Exception('PDFMaker Extension not found!');
        }

        /* Insert here source code to execute the task */
        $recordChooser = $this->get('recordChooser');

        if(empty($recordChooser) || $recordChooser == -1) {
            throw new \Exception('You need to configure the PDFMaker Integraion.');
        }

        $recordIds = array();
        if($recordChooser === 'current') {
            $recordIds = array($context->getId());
            $moduleName = $this->getModuleName();
        } else {
            $currentTime = microtime(true);
            $benchmark = array();

            $parts = explode("#~#", $this->get("search_module"));

            $related_module = VtUtils::getModuleName($parts[1]);
            $moduleName = $related_module;

            $objMySQL = new \Workflow\ConditionMysql($related_module, $context);

            $main_module = \CRMEntity::getInstance($related_module);

            $sqlCondition = $objMySQL->parse($this->get("condition"));

            $newTime = microtime(true);
            $benchmark[] = round(($newTime - $currentTime), 3);$currentTime = $newTime;

            $sqlTables = $objMySQL->generateTables();
            if(strlen($sqlCondition) > 3) {
                $sqlCondition .= " AND vtiger_crmentity.deleted = 0";
            } else {
                $sqlCondition .= " vtiger_crmentity.deleted = 0";
            }

            $sqlCondition .= " GROUP BY vtiger_crmentity.crmid ";
            $idColumn = $main_module->table_name.".".$main_module->table_index;
            $sqlQuery = "SELECT $idColumn as `idCol` ".$sqlTables." WHERE ".(strlen($sqlCondition) > 3?$sqlCondition:"");
            $sortField = $this->get("sort_field");
            if(!empty($sortField) && $sortField != -1) {
                $sortField = VtUtils::getColumnName($sortField);
                $sortDirection = $this->get("sortDirection");

                $sqlQuery .= " ORDER BY ".$sortField." ".$sortDirection;
            }

            $this->addStat("MySQL Query: ".$sqlQuery);

            $result = $adb->query($sqlQuery, true);

            $newTime = microtime(true);
            $benchmark[] = round(($newTime - $currentTime), 3);

            $this->addStat("num Rows: ".$adb->num_rows($result));

            # If no records are found, fo other way
            if($adb->num_rows($result) == 0) {
                return "yes";
            }

            $this->addStat("Benchmark: " . implode("/", $benchmark));

            while($row = $adb->fetchByAssoc($result)) {
                $recordIds[] = $row['idcol'];
            }
        }

        $context->save();

        $useUser = Users::getActiveAdminUser();
        $oldUser = vglobal('current_user'); vglobal('current_user', $useUser);

        $PDFMaker = new PDFMaker_PDFMaker_Model();

        $mpdf = "";

        $copies = $this->get('copies');
        if($copies == -1 || empty($copies)) {
            $copies = 1;
        }

        $templateids = array();
        for($i = 0; $i < $copies;$i++) {
            $templateids = array_merge($templateids, explode(',', $this->get('templateids')));
        }

        if($moduleName == 'Events') $moduleName = 'Calendar';
//        $filename = $PDFMaker->GetPreparedMPDF($mpdf, $recordIds, $templateids, $moduleName, $dl, '');

        $language = '';
        if($this->notEmpty('language')) {
            $language = $this->get('language');
        }
        if(empty($language)) {
            $language = Vtiger_Language_Handler::getLanguage();
        }

        if($PDFMaker->GetVersionType() == 'Free') {
            $filename = $PDFMaker->GetPreparedMPDF($mpdf, $recordIds, $moduleName, $language, '');
        } else {
            $filename = $PDFMaker->GetPreparedMPDF($mpdf, $recordIds, $templateids, $moduleName, $language, '');
        }

        if(strpos($filename, '.pdf') === false) {
            $filename .= '.pdf';
        }
//        $filename = $PDFMaker->generate_cool_uri($filename);

        $tmpfile = tempnam(sys_get_temp_dir(), 'WfTmp');
        @unlink($tmpfile);

        $mpdf->Output($tmpfile);

        $overwriteFilename = $this->get("filename", $context);
        if($overwriteFilename != -1 && !empty($overwriteFilename)) {
            $filename = $overwriteFilename;
        }

        \Workflow\FileAction::doActions($this->get('resultaction'), $tmpfile, $filename, $context, $recordIds, $this->getWorkflow());

        vglobal('current_user', $oldUser);

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        global $adb, $current_language, $mod_strings;

        if (!getTabid('PDFMaker') || !vtlib_isModuleActive('PDFMaker')) {
            $viewer->assign('disable', true);
            $this->addConfigHint(getTranslatedString('LBL_FOR_THIS_YOU_NEED_PDFMAKER','Settings:Workflow2'));
            return;
        }

        $copies = $this->get('copies');
        if($copies === -1 || empty($copies)) {
            $this->set('copies', 1);
        }

        $viewer->assign("related_modules", VtUtils::getEntityModules(true));
        $search_module = $this->get("search_module");

        $parts = false;
        if(!empty($_POST["task"]["search_module"])) {
            $parts = explode("#~#", $_POST["task"]["search_module"]);
        } elseif(!empty($search_module)) {
            if($search_module != -1) {
                $parts = explode("#~#", $search_module);
                $tabid = $parts[1];
            }
        }

        if(!empty($parts)) {
            $moduleName = $parts[0];
            $tabid = $parts[1];
        } else {
            $moduleName = $this->getModuleName();
            $tabid = getTabid($moduleName);
        }

        $viewer->assign("related_tabid", $tabid);

        $fields = VtUtils::getFieldsWithBlocksForModule($moduleName);
        $viewer->assign("sort_fields", $fields);

        require_once('modules/PDFMaker/helpers/Version.php');
        $viewer->assign('PDFMAKER_VERSION', PDFMaker_Version_Helper::$version);

        $sql = "SELECT folderid, foldername FROM vtiger_attachmentsfolder ORDER BY sequence";
        $result = $adb->query($sql);

        $folders = array();
        while($row = $adb->fetch_array($result)) {
            $folders[] = $row;
        }

        $viewer->assign("folders", $folders);

        if($moduleName == 'Events') $moduleName = 'Calendar';
        $sql = "SELECT templateid, filename, description FROM vtiger_pdfmaker WHERE module = '".$moduleName."'";
        $result = $adb->query($sql);

        $templates = array();
        while($row = $adb->fetch_array($result)) {
            $templates[] = array(
                'id' => $row['templateid'],
                'text' => $row['filename'],
            );
            $templateData[$row['templateid']] = $row['filename'];
        }

        $languages = array(
            '' => 'default language'
        );
        $sql = 'SELECT prefix, label FROM vtiger_language ORDER BY label';
        $result = VtUtils::query($sql);
        while($row = VtUtils::fetchByAssoc($result)) {
            $languages[$row['prefix']] = $row['label'];
        }

        $viewer->assign('LANGUAGES', $languages);

        $viewer->assign("templates", $templates);
        $viewer->assign("templateData", $templateData);

        $workflows = \Workflow2::getWorkflowsForModule("Documents", 1);
        $viewer->assign("workflows", $workflows);
    }

    public function beforeSave(&$values) {
        /* Insert here source code to modify the values the user submit on configuration */
    }
}
