<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskFileStore extends \Workflow\Task
{
    public function init() {

        $this->addPreset("FileActions", "resultaction", array(
            'module' => $this->getModuleName(),
        ));

    }

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */

        $fileid = $this->get('fileid', $context);
        $filestore = $context->getTempFiles($fileid);

        if(empty($filestore)) {
            $this->addStat('No file on filestoreid "'.$fileid.'" available.');
            return 'yes';
        }

        \Workflow\FileAction::doActions($this->get('resultaction'), $filestore['path'], $filestore['name'], $context, array(), $this->getWorkflow());

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
