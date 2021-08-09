<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskMessage extends \Workflow\Task
{
    /**
     * @param $context \Workflow\VTEntity
     */
    public function handleTask(&$context) {
        $adb = PearDatabase::getInstance();

        $targetId = $this->get('targetId', $context);
        if(empty($targetId)) {
            $targetId = $context->getId();
        }

        $target = $this->get('target');
        if($target == -1) {
            $target = 'record';
        }

        $targetIds = array();

        if($target == 'record') {
            if(strpos($targetId, 'x') !== false) {
                $parts = explode('x', $targetId);
                $targetId = $parts[1];
            }
            $targetIds[] = $targetId;
        } else {
            $targetUser = $this->get('targetUser', $context);
            if(!empty($targetUser)) {
                if($targetUser == 'assigned') $targetUser = 'assigned_user_id';
                if($targetUser == 'modified') $targetUser = 'modifiedby';

                $targetuserId = \Workflow\VTTemplate::parse('$'.$targetUser, $context);

                if(strpos($targetuserId, 'x') !== false) {
                    $parts = explode('x', $targetuserId);
                    $targetuserId = $parts[1];
                }

                $targetIds[] = $targetuserId;
            }
        }

/*        if($this->get('target') == 'user') {
            if(strpos($idString, "x") !== false) {
                $idParts = explode("x", $idString);
                return $idParts[1];
            }

            $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();
            $target = 'user';

            $targetId = $current_user->id;
        }*/


        $type = $this->get('type', $context);
        $subject = $this->get('subject', $context);
        $message = $this->get('message', $context);

        foreach($targetIds as $targetId) {
            $this->addStat('Add Message to '.$type.' '.$targetId);

            $sql = 'INSERT INTO vtiger_wf_messages SET crmid = ?, type = ?, subject = ?, message = ?, show_once = ?, show_until = ?, position = ?, created = NOW(), target = ?';
            $adb->pquery($sql, array($targetId, $type, $subject, $message, $this->get('show_once', $context), $this->get('show_until', $context), $this->get('position', $context), $target));
        }

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        $viewer->assign('isFrontendWorkflow', $this->getWorkflow()->isFrontendWorkflow());
    }

    public function beforeSave(&$values) {


    }
}
