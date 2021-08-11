<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
/* vt6 ready 2014/04/23 */
class WfTaskRedirectUser extends \Workflow\Task
{
    /**
     * @param $context \Workflow\VTEntity
     */
    public function handleTask(&$context) {

        $redirect_type = $this->get("redirect_type");

        if($redirect_type == -1 || $redirect_type == "url" || empty($redirect_type)) {
            $url = $this->get("redirection", $context);
        } else if($redirect_type == "pdfmaker") {
            $template = $this->get("pdftemplate");
            global $current_language, $site_URL;
            $url = "index.php?module=PDFMaker&relmodule=".$context->getModuleName()."&action=CreatePDFFromTemplate&record=".$context->getId()."&commontemplateid=".intval($template)."&language=".$current_language;
        } else if($redirect_type == "editview") {
            $crmid = $this->get('crmid_editview', $context);
            if($crmid == $context->getId()) {
                $url = 'index.php?module='.$context->getModuleName().'&view=Edit&record='.$crmid;
            } else {
                $url = 'index.php?module='.Vtiger_Functions::getCRMRecordType($crmid).'&view=Edit&record='.$crmid;
            }
        } else if($redirect_type == "reload") {
            $url = '_internal_reload';
        } else if($redirect_type == 'none') {
            $url = '';
        }

        $this->addStat('Current Record: '.$context->getId());

        if(!empty($url)) {
            $target = $this->get("target");

            $this->getWorkflow()->setSuccessRedirection($url, $target);
            //$this->getWorkflow()->setSuccessRedirectionTarget();
        } else {
            $this->getWorkflow()->clearRedirection();
        }


        return "yes";
    }

    public function beforeGetTaskform($viewer) {

        $viewer->assign("ENABLE_PDFMAKER", false);
        $viewer->assign("pdfmaker_templates", array());
        if(getTabid('PDFMaker') && vtlib_isModuleActive('PDFMaker')) {
        require_once('modules/PDFMaker/PDFMaker.php');
            if(class_exists("PDFMaker")) {
                $PDFMaker = $PDFMaker = new PDFMaker_PDFMaker_Model();

                if(method_exists($PDFMaker, "GetAvailableTemplates")) {
                    $viewer->assign("ENABLE_PDFMAKER", true);

                    $templates = $PDFMaker->GetAvailableTemplates($this->getModuleName());
                    foreach($templates as $index => $value) {
                        $pdftemplates[$index] = $value["templatename"];
                    }
                    $viewer->assign("pdfmaker_templates", $pdftemplates);

                    $templateid = $this->get("template");

                    if(!empty($templateid) && $templateid != -1 && $this->get("attachments") == -1) {
                        $this->set("attachments", '{"pdfmaker#'.$templateid.'":"title"}');
                    } else {
                        if($this->get("attachments") == -1) {
                            $this->set("attachments", '{}');
                        }
                    }
                }
            }
        }
    }

    public function beforeSave(&$values) {
        if($values["redirect_type"] == "pdfmaker" && empty($values["pdftemplate"])) {
            $values["redirect_type"] = "url";
        }
    }
}
