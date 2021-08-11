<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ErrorReport_View extends Settings_Workflow2_Default_View {

    function checkPermission(Vtiger_Request $request) {
        return true;
   	}
	public function process(Vtiger_Request $request) {
        global $current_user, $vtiger_current_version;
        $adb = \PearDatabase::getInstance();

        if(!empty($_GET["stefanDebug"])) {
            ini_set("display_errors", 1);
            error_reporting(E_ALL);

            $adb->dieOnError = true;
        }

        if(!empty($_POST["send_report"])) {
            $moduleModel = Vtiger_Module_Model::getInstance("Workflow2");
            require_once("modules/Emails/class.phpmailer.php");

            require_once('modules/Emails/mail.php');

            $mailtext = "ERROR REPORT WORKFLOW EXTENSION ".$moduleModel->version." - vtiger VERSION ".$vtiger_current_version."\n\n";
            $mailtext .= "PHPINFO:\n".$_POST["system"]["phpinfo"]."\n\n";
            $mailtext .= "TABLES:\n".$_POST["system"]["table"]."\n\n";
            $mailtext .= "CurrentUser:\n".$_POST["system"]["currentUser"]."\n\n";
            $mailtext .= "FEHLERBESCHREIBUNG:\n".$_POST["errorRecognization"]."\n\n";

            $mail = new PHPMailer();
            $mail->IsSMTP();
            setMailServerProperties($mail);

            $adminUser = \Users::getActiveAdminUser();

            $mail->FromName = "Fehlerbericht";
            $mail->Sender = $adminUser->email1;
            $mail->Subject = "Workflow Designer Error Report";
            $mail->Body = $mailtext;
            $mail->AddAddress("errorreport@stefanwarnat.de", "Stefan Warnat");
            $mailReturn = MailSend($mail);
           	#setMailerProperties($mail,$subject,$contents,$from_email,$from_name,trim($to_email,","),$attachment,$emailid,$module,$logo);

            #$mail_return = send_mail("Accounts", "kontakt@stefanwarnat.de", "Fehlerbereicht", "errorreport@stefanwarnat.de","",$mailtext);
            /* ONLY DEBUG*/var_dump($mailReturn);
        }

        $extended = !empty($_GET["extend"]);

       $extendedGroups = array("PHP Variables", "HTTP Headers Information", "Apache Environment");
       # Source: http://php.net/manual/de/function.phpinfo.php (Ken)
       function phpinfo_array()
       {
           ob_start();
           phpinfo(INFO_ALL);
           $info_arr = array();
           $info_lines = explode("\n", strip_tags(ob_get_clean(), "<tr><td><h2>"));
           $cat = "General";
           foreach($info_lines as $line)
           {
               // new cat?
               preg_match("~<h2>(.*)</h2>~", $line, $title) ? $cat = trim($title[1]) : null;
               if(preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val))
               {
                   $info_arr[$cat][$val[1]] = trim($val[2]);
               }
               elseif(preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val))
               {
                   $info_arr[$cat][$val[1]] = array("local" => $val[2], "master" => $val[3]);
               }
           }
           return $info_arr;
       }

        global $dbconfig;

        $result = $adb->query('SELECT VERSION() as mysql_version');
        $data = $adb->fetchByAssoc($result);

        $debug = array("phpinfo" =>
            array(
                'Root Directory: "'.vglobal('root_directory').'"',
                'Site URL: "'.vglobal('site_URL').'"',
                'DBType: '.$dbconfig['db_type'],
                'MySQL Version: '.$data['mysql_version'],
            ),
            "table" => ""
        );
           $phpinfo = phpinfo_array();
           foreach($phpinfo as $groupKey => $group) {

               if(in_array($groupKey, $extendedGroups) && $extended == false) {
                   continue;
               }

               $debug["phpinfo"][] = "Group: ".$groupKey;

               if($groupKey == "Apache Environment" && $extended == false) {
                   continue;
               }


               foreach($group as $index => $value) {

                   if(!is_string($value) && !empty($value["local"])) {
                       $debug["phpinfo"][] = "  `".$index."` = '".$value["local"]."'";
                   } else {
                       $debug["phpinfo"][] = "  `".$index."` = '".$value."'";
                   }

               }

           }

           $tables = $adb->get_tables();

           foreach($tables as $table) {
               if(substr($table, 0, 9) == "vtiger_wf") {
                   $debug["table"][] = "Table: ".$table;
                   $cols = $adb->query("SHOW FULL COLUMNS FROM `".$table."`");
                   while($row = $adb->fetchByAssoc($cols)) {
                       $debug["table"][] = "   `".$row["field"]."` - ".$row["type"]. " - ".$row["collation"].' '.$row['extra'].' Permission: '.$row['privileges'];
                   }
               }
           }

        $sql = 'SELECT type, handlerclass, module, output, persons, text, input styleclass, version, repo_id FROM vtiger_wf_types';
        $result = $adb->query($sql, true);
        $debug["table"][] = "### Types";
        while($row = $adb->fetchByAssoc($result)) {
            //$debug["table"][] = " ".str_pad($row['type'], 20, ' ').' - Version '.$row['version'] .' - RepoID '.$row['repo_id'];
            $debug["table"][] = json_encode($row);
        }
    ?>
    <link rel="stylesheet" href="modules/Workflow2/adminStyle.css" type="text/css" media="all" />
    <div class="container-fluid" id="moduleManagerContents">

         <div class="widget_header row-fluid">
             <div class="span12">
                 <h3>
                     <b>
                         Workflow Designer - Debug
                     </b>
                 </h3>
             </div>
         </div>
         <hr>
    </div>

    <div class="settingsUI" style="width:95%;padding:10px;padding-left:30px;">
        <form method="POST" action="#">
            <?php echo getTranslatedString("LBL_DEBUG_HEAD",'Settings:Workflow2') ?>
            <p class="alert alert-danger">Please do not only send the Error Report. We won't check them, without reference in our support system. Explain your problem with an E-Mail to <strong>warnat@redoo-networks.com</strong></p>
            <textarea name="system[phpinfo]" style="width:100%;height:300px;"><?php echo implode("\n", $debug["phpinfo"]); ?></textarea><br>
            <br>
            <?php echo getTranslatedString("LBL_DEBUG_MIDDLE",'Settings:Workflow2') ?>
            <textarea name="system[table]" style="width:100%;height:300px;"><?php echo implode("\n", $debug["table"]); ?></textarea>
            <br>
            <br>
            Current User Settings: (Passwords are removed!)
            <textarea name="system[currentUser]" style="width:100%;height:300px;"><?php $cU = $current_user;
                unset($cU->db);
                unset($cU->column_fields["user_password"]);unset($cU->column_fields["confirm_password"]);unset($cU->column_fields["accesskey"]);
                unset($cU->user_password);unset($cU->confirm_password);unset($cU->accesskey);
                var_dump($cU); ?></textarea>
            <br>
            <br>
            <?php echo getTranslatedString("LBL_DEBUG_BOTTOM",'Settings:Workflow2') ?>
            <textarea name="errorRecognization" style="width:100%;height:100px;"></textarea><br>
            <br>
            <input type="submit" name="send_report" class="btn btn-primary" value="<?php echo getTranslatedString("SEND_DEBUG_REPORT",'Settings:Workflow2') ?>">
        </form>
    </div>

    <?php

	}


	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.views.resources.Workflow2",
			"modules.Settings.$moduleName.views.resources.HttpHandlerManager",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        foreach($headerScriptInstances as $obj) {
            $src = $obj->get('src');
            if(!empty($src) && strpos($src, $moduleName) !== false) {
                $obj->set('src', $src.'?v='.$moduleModel->version);
            }
        }

        return $headerScriptInstances;
	}
    function getHeaderCss(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderCss($request);
        $moduleName = $request->getModule();

        $cssFileNames = array(
            "~/modules/Settings/$moduleName/views/resources/Workflow2.css",
        );

        $cssScriptInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerStyleInstances = array_merge($headerScriptInstances, $cssScriptInstances);
        return $headerStyleInstances;
    }
}