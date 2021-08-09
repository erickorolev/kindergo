<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskSendPortalLogin extends \Workflow\Task
{
    public function handleTask(&$context) {

        $context->save();

        require_once 'include/Webservices/Retrieve.php';
        require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
        require_once('modules/Contacts/ContactsHandler.php');

        $adb = \PearDatabase::getInstance();
        $sql = "DELETE FROM vtiger_portalinfo WHERE id = ?";
        $adb->pquery($sql, array($context->getId()), true);

        $entity = new \VTWorkflowEntity($context->getUser(), $context->getWsId());


        Contacts_sendCustomerPortalLoginDetails($entity);;

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        /* Insert here source code to create custom configurations pages */
    }
    public function beforeSave(&$values) {
        /* Insert here source code to modify the values the user submit on configuration */
    }
}
