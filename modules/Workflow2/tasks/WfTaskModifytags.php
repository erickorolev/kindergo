<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskModifytags extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\SimpleConfig
     */
    private $_SC = null;

    public function init()
    {
        $this->_SC = $this->addPreset("SimpleConfig", "details", array(
            'templatename' => 'mainconfig',
        ));

        if ($this->isConfiguration()) {
            $result = VtUtils::query('SELECT id, tag FROM vtiger_freetags ORDER BY tag');
            $tags = array();

            while($tag = VtUtils::fetchByAssoc($result)) {
                $tags[$tag['id']] = $tag['tag'];
            }

            $this->_SC->setColumnCount(1);

            $this->_SC->addField('recordid', 'Record ID you want to modify (Default = current record)', 'template', array('placeholder' => '$crmid'));

            $this->_SC->addField('action', 'Action', 'select', array(
                'options' => array(
                    'add' => 'Add',
                    'remove' => 'Remove',
                    'toggle' => 'Toggle'
                )
            ));
            $this->_SC->addField('tags', 'Tags', 'multiselect', array(
                'options' => $tags
            ));

            $this->_SC->addHeadline('modify record with a new tag');
            $this->_SC->addField('newtag', 'create new tag', 'template');
            $this->_SC->addField('publicvisibility', 'create public tag', 'checkbox');
            $this->_SC->addField('tagowner', 'owner of tag', 'userpicklist');
        }
    }

    private function doAction($action, $tagId, $objectId, $moduleName) {
        switch($action) {
            case 'add':
                $sql = 'INSERT IGNORE INTO vtiger_freetagged_objects SET tag_id = ?, tagger_id = ?, object_id = ?, tagged_on = ?, module = ?';
                \Workflow\VtUtils::pquery($sql, array($tagId, \Workflow\VtUtils::getCurrentUserId(), $objectId, date('Y-m-d H:i:s'), $moduleName));
                break;
            case 'remove':
                $sql = 'DELETE FROM vtiger_freetagged_objects WHERE tag_id = '.$tagId.' AND object_id = "'.$objectId.'"';
                \Workflow\VtUtils::query($sql);
                break;
        }
    }

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */

		$action = $this->_SC->get('action');
		$tags = $this->_SC->get('tags');

		if($this->_SC->has('recordid')) {
		    $recordIds = explode(',', $this->_SC->get('recordid'));
            $targetContexts = array();
		    foreach($recordIds as $recordId) {
                $targetContexts[] = \Workflow\VTEntity::getForId($recordId);
            }

        } else {
		    $recordId = $context->getId();
            $targetContexts = array($context);
        }

        if($this->_SC->has('newtag')) {
            $newTag = $this->_SC->get('newtag');
            if(!empty($newTag)) {
                $tag = new \Vtiger_Tag_Model();
                if($this->_SC->has('publicvisibility')) {
                    $tag->setType(\Vtiger_Tag_Model::PUBLIC_TYPE);
                } else {
                    $tag->setType(\Vtiger_Tag_Model::PRIVATE_TYPE);
                }
                $tag->setName($newTag);
            }
            $id = $tag->create();
            $sql = 'UPDATE vtiger_freetags SET owner = ? WHERE id = ?';
            \Workflow\VtUtils::pquery($sql, array($this->_SC->get('tagowner'), $id));

            $tags[] = $id;
        }

        foreach($targetContexts as $targetContext) {
            foreach ($tags as $tag) {
                switch ($action) {
                    case 'remove':
                        $this->doAction('remove', $tag, $targetContext->getId(), $targetContext->getModuleName());
                        break;
                    case 'add':
                        $this->doAction('add', $tag, $targetContext->getId(), $targetContext->getModuleName());
                        break;
                    case 'toggle':
                        $sql = 'SELECT * FROM vtiger_freetagged_objects WHERE tag_id = ? AND object_id = ?';
                        $result = \Workflow\VtUtils::pquery($sql, array($tag, $targetContext->getId()));

                        if (\Workflow\VtUtils::num_rows($result) > 0) {
                            $this->doAction('remove', $tag, $targetContext->getId(), $targetContext->getModuleName());
                        } else {
                            $this->doAction('add', $tag, $targetContext->getId(), $targetContext->getModuleName());
                        }
                        break;
                }
            }
        }

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
