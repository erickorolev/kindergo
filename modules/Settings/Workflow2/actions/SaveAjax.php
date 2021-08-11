<?php
global $root_directory;
require_once($root_directory."/modules/Colorizer/autoloader.php");
sw_autoload_register("SWExtension", "~/modules/Colorizer/libs");

class Settings_Colorizer_SaveAjax_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $db = PearDatabase::getInstance();
        $moduleModel = Vtiger_Module_Model::getInstance("Colorizer");

        $tabid = intval($request->get("newEntry"));
        $newEntry_field = ($request->get("newEntry_field"));
        $settings = ($request->get("settings"));

        $condition = $request->get("condition");
        $actionSetting = $request->get("actionSetting");

        $summaryview = $request->get("summaryview")==1;
        $detailview = $request->get("detailview")==1;
        $listview = $request->get("listview")==1;

        $editID = $request->get("edit_id");
        unset($condition["##CONDID##"]);
        unset($actionSetting["##ACTIONID##"]);

        $sql = "SELECT id FROM vtiger_colorizer WHERE tabid = '".$tabid."' AND field = '".$newEntry_field."'";
        $result = $db->query($sql);
        if($db->num_rows($result) > 0) {
            $editID = $db->query_result($result, 0, "id");
        }

        $response = new Vtiger_Response();

        if(empty($editID)) {
            $className = "S"."WE"."xt"."ension_"."Colorizer_"."721d6ffafc464e72f7e"."afca66f949ed76486afbf";
            $asdf = new $className("Colorizer", $moduleModel->version);
            if(!$asdf->ge2055887bb4f19d2c67775b32b574553()) {
                $sql = "SELECT * FROM vtiger_colorizer LIMIT 1";
                $result = $db->query($sql);
                if($db->num_rows($result) > 1) {
                    throw new Exception("License don't allow more configurations!");
                }
            }
        }

        $additional = array(
            "bbcode" => ($request->get("enable_bbcode") == "1"?1:0),
            "listviewrow" => ($request->get("check_enable_listviewrow") == "1"?1:0),
        );


            try{
                if(empty($editID)) {
                    $sql = "INSERT INTO vtiger_colorizer SET `additional` = ?, `actions` = ?, `condition` = ?, field = ?, tabid = ?, settings = ?, labelSettings = ?, summaryview = ?, detailview = ?, listview = ?";
                } else {
                    $sql = "UPDATE vtiger_colorizer SET `additional` = ?, `actions` = ?, `condition` = ?, field = ?, tabid = ?, settings = ?, labelSettings = ?, summaryview = ?, detailview = ?, listview = ? WHERE id = ".intval($editID);
                }
                $db->pquery($sql, array(json_encode($additional), json_encode($_POST["actionSetting"]), json_encode($condition), $newEntry_field, $tabid, $settings["field"], $settings["label"], $summaryview?1:0, $detailview?1:0, $listview?1:0), true);

                if(empty($editID)) {
                    $response->setResult(array("id" => \Workflow\VtUtils::LastDBInsertID()));
                } else {
                    $response->setResult(array("id" => $editID));
                }
            }catch (Exception $e) {
                $response->setError($e->getCode(), $e->getMessage());
            }

            Vtiger_Link::addLink($tabid, "DETAILVIEWSIDEBARWIDGET", "Colorizer", "module=Colorizer&view=SidebarWidget&mode=showSidebar&viewtype=detail", "", "999", "");
            Vtiger_Link::addLink($tabid, "LISTVIEWSIDEBARWIDGET", "Colorizer", "module=Colorizer&view=SidebarWidget&mode=showSidebar&viewtype=detail&tabid=".$tabid, "", "999", "");
            $response->emit();
            return;

            /*
            $sql = "SELECT linkid FROM vtiger_links WHERE tabid = ".$tabid." AND linklabel = 'Colorizer'";
            $result = $adb->query($sql);
            if($adb->num_rows($result) == 0) {
                $sql = "INSERT INTO vtiger_links SET linkid = ".$adb->getUniqueID('vtiger_links').", tabid = ".$tabid.", linklabel = 'Colorizer', linktype = 'DETAILVIEWWIDGET', linkurl = 'block://Colorizer:modules/Colorizer/Colorizer.php', sequence = 0";
                $adb->query($sql);

                $sql = "UPDATE vtiger_links_seq SET id = id + 1";
            }

            // Recheck Record
            $sql = "SELECT linkid FROM vtiger_links WHERE tabid = ".$tabid." AND linklabel = 'Colorizer'";
            $result = $adb->query($sql);
            if($adb->num_rows($result) == 0) {
                echo "<p class='error'>Link couldn't setup. Please check vtiger_links Sequence and resave record.</p>";
            }
*/



        #$response->

        $record = $request->get('record');
        if(empty($record)) {
			//get instance from currency name, Aleady deleted and adding again same currency case 
            $recordModel = Settings_Currency_Record_Model::getInstance($request->get('currency_name'));
            if(empty($recordModel)) {
				$recordModel = new Settings_Currency_Record_Model();
			}
		} else {
            $recordModel = Settings_Currency_Record_Model::getInstance($record);
        }
        
        $fieldList = array('currency_name','conversion_rate','currency_status','currency_code','currency_symbol');
        
        foreach ($fieldList as $fieldName) {
            if($request->has($fieldName)) {
                $recordModel->set($fieldName,$request->get($fieldName));
            }
        }
		//To make sure we are saving record as non deleted. This is useful if we are adding deleted currency
		$recordModel->set('deleted',0);
        $response = new Vtiger_Response();

            if($request->get('currency_status') == 'Inactive' && !empty($record)) {
                $transforCurrencyToId = $request->get('transform_to_id');
                if(empty($transforCurrencyToId)) {
                    throw new Exception('Transfer currency id cannot be empty');
                }
                Settings_Currency_Module_Model::tranformCurrency($record, $transforCurrencyToId);
            }
            $id = $recordModel->save();
            $recordModel = Settings_Currency_Record_Model::getInstance($id);


        $response->emit();
    }
}