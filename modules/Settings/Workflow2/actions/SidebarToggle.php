<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_SidebarToggle_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $tabid = getTabId($request->get('workflowModule'));
        $adb = \PearDatabase::getInstance();

        if($request->get('hidden') == true) {
            ob_start();
        }
        if(!empty($tabid)) {
            $sql = "SELECT linkid FROM vtiger_links WHERE linktype = 'DETAILVIEWSIDEBARWIDGET' AND linklabel = 'Workflows' AND tabid = ".$tabid;
            $result = $adb->query($sql);
            $mode = $request->get('MODE');

            if($mode == 'ADD' && $adb->num_rows($result) > 1) {
                $adb->query("DELETE FROM vtiger_links WHERE (linktype = 'DETAILVIEWSIDEBARWIDGET' OR linktype = 'LISTVIEWSIDEBARWIDGET') AND linklabel = 'Workflows' AND tabid = ".$tabid, true);

                $sql = "SELECT linkid FROM vtiger_links WHERE linktype = 'DETAILVIEWSIDEBARWIDGET' AND linklabel = 'Workflows' AND tabid = ".$tabid;
                $result = $adb->query($sql);
            }

            if($mode == 'DEL' || (empty($mode) && $adb->num_rows($result) > 0)) {
                $mode = 'DEL';
                $adb->query("DELETE FROM vtiger_links WHERE (linktype = 'DETAILVIEWSIDEBARWIDGET' OR linktype = 'LISTVIEWSIDEBARWIDGET') AND linklabel = 'Workflows' AND tabid = ".$tabid, true);

                echo getTranslatedString('LBL_ACTIVATE_SIDEBAR', 'Settings:Workflow2');
            } elseif($adb->num_rows($result) == 0) {
                $mode = 'ADD';
                $linkid = $adb->getUniqueID("vtiger_links");
                $adb->query("INSERT INTO vtiger_links SET linkid = '".$linkid."',linktype = 'DETAILVIEWSIDEBARWIDGET', linklabel = 'Workflows', tabid = ".$tabid.",linkurl='".'module=Workflow2&view=SidebarWidget'."'", true);

                $linkid = $adb->getUniqueID("vtiger_links");
                $adb->query("INSERT INTO vtiger_links SET linkid = '".$linkid."',linktype = 'LISTVIEWSIDEBARWIDGET', linklabel = 'Workflows', tabid = ".$tabid.",linkurl='".'module=Workflow2&src_module='.$request->get('workflowModule').'&view=SidebarListWidget'."'", true);

                echo getTranslatedString('LBL_DEACTIVATE_SIDEBAR', 'Settings:Workflow2');
            }
        }

        if($request->get('hidden') == true) {
            ob_end_clean();
        }
        if($request->get('workflowModule') == 'Calendar') {
            $request2 = $request;

            $request->set('MODE', $mode);
            $request->set('workflowModule', "Events");
            $request->set('hidden', true);
            $this->process($request);
        }

    }
}