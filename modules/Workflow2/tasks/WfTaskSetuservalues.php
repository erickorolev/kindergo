<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskSetuservalues extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\FieldSetter
     */
    private $fieldSetter = false;
    protected $_envSettings = array("used_user_id");

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
        $userid = $this->get('userid', $context);
        
        if(empty($userid)) {
            $userid = $context->get('smownerid');
        }

        //      var_dump($userid);exit();
        /*$focus = CRMEntity::getInstance('Users');
        $focus->id = $userid;
        $focus->mode = 'edit';
        $focus->retrieve_entity_info($userid, 'Users');
*/
        $userModel = \Users_Record_Model::getInstanceById($userid, 'Users');

        $this->fieldSetter->apply($dummy, $setterMap, $context, $this);

        $data = $dummy->getData();

        foreach($data as $key => $value) {
            $userModel->set($key, $value);
        }

        $userModel->set('mode', 'edit');
        $userModel->save();

        $context->setEnvironment('used_user_id', $userModel->get('id'), $this);

        return "yes";
    }

    public function beforeGetTaskform($viewer) {

        if(!$this->notEmpty('userid')) {
            $this->set('userid', '$smownerid');
        }

        /* Insert here source code to create custom configurations pages */
    }
    public function beforeSave(&$values) {
        /* Insert here source code to modify the values the user submit on configuration */
    }
}
