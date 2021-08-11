<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskFormatComments extends \Workflow\Task
{
    public function handleTask(&$context) {
        $adb = \PearDatabase::getInstance();

        $envid = $this->get('envid');
        if(empty($envid) || $envid == -1) {
            return 'yes';
        }

        if($this->notEmpty('relatedto')) {
            $crmid = $this->get('relatedto', $context);
        } else {
            $crmid = $context->getId();
        }

        if($this->notEmpty('count')) {
            $limit = $this->get('count', $context);
        } else {
            $limit = null;
        }

        if($this->notEmpty('sort')) {
            $orderBy = 'createdtime DESC';
        } else {
            switch($this->get('sort')) {
                case 'date#asc':
                    $orderBy = 'createdtime ASC';
                    break;
                case 'date#desc':
                    $orderBy = 'createdtime DESC';
                    break;
            }
        }

        $extraWhere = '';
        switch($this->get('src')) {
            case 'users':
                $extraWhere .= ' AND customer = 0';
                break;
            case 'customers':
                $extraWhere .= ' AND customer != 0';
                break;
        }


        $sql = "SELECT crmid
FROM vtiger_modcomments
INNER JOIN `vtiger_crmentity` ON (`vtiger_crmentity`.`crmid` = `vtiger_modcomments`.`modcommentsid`)
LEFT JOIN `vtiger_modcommentscf` ON (`vtiger_modcommentscf`.`modcommentsid` = `vtiger_modcomments`.`modcommentsid`)
   LEFT JOIN vtiger_users
       ON (vtiger_users.id = vtiger_crmentity.smownerid)
WHERE ( ((`vtiger_modcomments`.`related_to` = '".$crmid."')) ".$extraWhere." ) AND vtiger_crmentity.deleted = 0 GROUP BY vtiger_crmentity.crmid  ORDER BY ".$orderBy." ".(!empty($limit)?' LIMIT '.$limit:'');
        $result = $adb->query($sql, true);

        $content = '';
        $example = array();
        if($adb->num_rows($result) > 0) {
            while($row = $adb->fetchByAssoc($result)) {
                $modComment = \Workflow\VTEntity::getForId($row['crmid'], 'ModComments');
                $modComment->setEnvironment('commentAuthor', $this->getCommentAuthor($modComment));

                try {
                    $example[] = $this->get('text', $modComment);
                } catch (\Exception $exp) {}
            }

            $divider = $this->get('divider', $context);
            if(empty($divider)) {
                $divider = '<br /><br />';
            }
            $divider = str_replace('\n', PHP_EOL, $divider);

            $content = implode($divider, $example);

            $context->setEnvironment($envid, $content);

        }


		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
        // text is empty
        if($this->notEmpty('text') == false) {
            $this->set('text', '$env["commentAuthor"] - $[DATEFORMAT,$createdtime,\'d.m.Y\']<br>
---------------<br>
$[NL2BR,$commentcontent]');
        }
        if($this->notEmpty('relatedto') == false) {
            $this->set('relatedto', '$crmid');
        }

        if($this->notEmpty('text')) {
            $dummy = \Workflow\VTEntity::getDummy();
            $example = array();

            $orderBy = 'createdtime DESC';

            switch($this->get('sort')) {
                case 'date#asc':
                    $orderBy = 'createdtime ASC';
                    break;
                case 'date#desc':
                    $orderBy = 'createdtime DESC';
                    break;
            }

            $extraWhere = '';
            switch($this->get('src')) {
                case 'users':
                    $extraWhere .= ' AND customer = 0';
                    break;
                case 'customers':
                    $extraWhere .= ' AND customer != 0';
                    break;
            }

            if($this->notEmpty('count')) {
                $limit = $this->get('count', $dummy);
            }

            if(empty($limit)) {
                $limit = 3;
            }

            $sql = "SELECT vtiger_crmentity.crmid
FROM vtiger_modcomments
INNER JOIN `vtiger_crmentity` ON (`vtiger_crmentity`.`crmid` = `vtiger_modcomments`.`modcommentsid`)
LEFT JOIN `vtiger_modcommentscf` ON (`vtiger_modcommentscf`.`modcommentsid` = `vtiger_modcomments`.`modcommentsid`)
   LEFT JOIN vtiger_users
       ON (vtiger_users.id = vtiger_crmentity.smownerid)
WHERE vtiger_crmentity.deleted = 0 ".$extraWhere." GROUP BY vtiger_crmentity.crmid ORDER BY ".$orderBy." LIMIT ".$limit;
            $adb = \PearDatabase::getInstance();
            $result = $adb->query($sql, true);

            if($adb->num_rows($result) > 0) {
                while($row = $adb->fetchByAssoc($result)) {
                    $modComment = \Workflow\VTEntity::getForId($row['crmid'], 'ModComments');
                    $modComment->setEnvironment('commentAuthor', $this->getCommentAuthor($modComment));

                    try {
                        $example[] = $this->get('text', $modComment);
                    } catch (\Exception $exp) {}
                }

                $divider = $this->get('divider', $dummy);
                if(empty($divider)) {
                    $divider = '<br /><br />';
                }
                $divider = str_replace('\n', PHP_EOL, $divider);

                $example = implode($divider, $example);
            }

            if(!empty($example)) {
                $viewer->assign('example', $example);
            }

        }
		/* Insert here source code to create custom configurations pages */
    }

    public function getCommentAuthor(\Workflow\VTEntity $modCommentObj) {
        $return = '';

        if($modCommentObj->get('customer') != '' && $modCommentObj->get('customer') != '0') {
            $return = \Vtiger_Functions::getCRMRecordLabel($modCommentObj->get('customer'));
        } else {
            $return = \Vtiger_Functions::getUserRecordLabel($modCommentObj->get('userid'));
        }

        return $return;
    }
    public function getEnvironmentVariables()
    {
        $return = parent::getEnvironmentVariables(); // TODO: Change the autogenerated stub
        if($this->notEmpty('envid')) {
            $return[] = $this->get('envid');
        }

        return $return;
    }

    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
