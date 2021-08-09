<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_RecordList_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $id = (int)$request->get('objectID');

        ?>
    <div class="modelContainer" style="width:550px;">
        <form method="POST" id="WorkflowImportForm" action="index.php?module=Workflow2&parent=Settings&action=WorkflowImport" enctype="multipart/form-data">
    <div class="modal-header contentsBackground">
    	<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
        <h3>Workflow Import</h3>
    </div>
            <input type="hidden" name="workflowObjectId"id="workflowObjectId" value="<? echo $id ?>" />
        <div style="padding: 10px;">

            <?
            echo "<select id='recordSelector'>";
            $sql = "SELECT user_name, id, first_name, last_name FROM vtiger_users WHERE deleted = 0 AND status = 'Active'";
            $result = $adb->query($sql, $db);
            $users = array();

            while($row = $adb->fetch_array($result)) {
                echo "<option value='".$row["id"]."'>".$row["user_name"]." [".$row["last_name"].", ". $row["first_name"]."]</option>";
            }

            echo "</select>";
            ?>
        </div>
        <div class="modal-footer quickCreateActions">
                <a class="cancelLink cancelLinkContainer pull-right" type="reset" data-dismiss="modal"><? echo getTranslatedString('LBL_CLOSE', 'Settings:Workflow2') ?></a>
            <button class="btn btn-success" type="submit" id="modalSubmitButton" ><strong>Set</strong></button>
       	</div>
        </form>
    </div>
    <?
        exit();

   }
}