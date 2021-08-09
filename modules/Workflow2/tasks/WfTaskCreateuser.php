<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskCreateuser extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\FieldSetter
     */
    private $fieldSetter = false;
    protected $_envSettings = array("created_user_id");

    public function init() {
        $this->fieldSetter = $this->addPreset("FieldSetter", "setter", array(
            'fromModule' => $this->getModuleName(),
            'toModule' => 'Users',
            'refFields' => false
        ));
    }

    public function handleTask(&$context) {
        /* Insert here source code to execute the task */

        $dummy = \Workflow\VTEntity::getDummy();

        $setterMap = $this->get("setter");

        //      var_dump($userid);exit();
        /*$focus = CRMEntity::getInstance('Users');
        $focus->id = $userid;
        $focus->mode = 'edit';
        $focus->retrieve_entity_info($userid, 'Users');
*/

        $userModel = \Users_Record_Model::getCleanInstance('Users');

        $this->fieldSetter->apply($dummy, $setterMap, $context, $this);

        $data = $dummy->getData();

        foreach($data as $key => $value) {
            $userModel->set($key, $value);
        }

        $userModel->set('mode', '');
        $userModel->save();

        $context->setEnvironment('created_user_id', $userModel->get('id'), $this);

        return "yes";
    }

    public function beforeGetTaskform($viewer) {

        $setter = $this->get('setter');

        if($setter == -1) {
            $mandatoryFields = VtUtils::getMandatoryFields('Users');
            $startFields = array();
            $counter = 1;
            $mandatoryFields = array('user_name', 'email1', 'last_name', 'roleid', 'user_password', 'confirm_password');
            foreach($mandatoryFields as $field) {
                $startFields["".$counter] = array("field" => $field, "mode" => "value", "value" => "", "fixed" => true);
                $counter++;
            }


            $this->set("setter", $startFields);
        }

        /* Insert here source code to create custom configurations pages */
    }
    public function beforeSave(&$values) {
        /* Insert here source code to modify the values the user submit on configuration */
    }
}
