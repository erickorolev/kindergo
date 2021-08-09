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

class Settings_Workflow2_Check_View extends Vtiger_Popup_View {

    public function dirToArray($dir) {
       $result = array();

       $cdir = scandir($dir);
       foreach ($cdir as $key => $value)
       {
          if (!in_array($value,array(".","..")))
          {
             if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
             {
                $result[$value] = $this->dirToArray($dir . DIRECTORY_SEPARATOR . $value);
             }
             else
             {
                if(!is_writeable($dir . DIRECTORY_SEPARATOR . $value)) {
                    echo '<p style="margin:0;color:red;">File: '.$dir . DIRECTORY_SEPARATOR . $value.' not writable!</p>';
                } else {
                    echo '<p style="margin:0;color:green;">File: '.$dir . DIRECTORY_SEPARATOR . $value.' OK!</p>';
                }
             }
          }
       }

       return $result;
    }
    public function process(Vtiger_Request $request) {
        $this->dirToArray(vglobal('root_directory').'modules/Workflow2/');
        $this->dirToArray(vglobal('root_directory').'modules/Settings/Workflow2/');
        $this->dirToArray(vglobal('root_directory').'layouts/vlayout/modules/Workflow2/');
        $this->dirToArray(vglobal('root_directory').'layouts/vlayout/modules/Settings/Workflow2/');

        echo '<p><strong>FINISHED</strong></p>';
   	}

}