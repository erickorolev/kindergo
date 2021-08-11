<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskComplexrecordsource extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\RecordSources
     */
    private $RecordSources = null;

    public function init() {
        if(-1 != $this->get("search_module") || !empty($_POST["task"]["search_module"])) {
            $module = !empty($_POST["task"]["search_module"]) ? $_POST["task"]["search_module"] : $this->get("search_module");
            $parts = explode('#~#', $module);
            /*$this->addPreset("Condition", "condition", array(
                'toModule' => VtUtils::getModuleName($parts[1]),
                'mode' => 'mysql',
            ));*/

            $this->RecordSources = $this->addPreset("RecordSources", "recordsource", array(
                'module' => VtUtils::getModuleName($parts[1]),
                'default' => 'condition',
                //'ignorechain' => true
            ));
        }

    }

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
		$chainid = $this->get('chainid');
        $chainid = md5($chainid);
        $environmentId = '__chain_'.$chainid;

        $this->addStat('EnvID: '.$environmentId);

        $chain = $context->getEnvironment($environmentId);
        if(empty($chain)) {
            $chain = array();
            $this->addStat('Create Chain');
        }

        $query = $this->RecordSources->getQuery(
            $context,
            $this->notEmpty("sort_field")?$this->get('sort_field'):null,
            $this->notEmpty("found_rows")?$this->get('found_rows'):null,
            false
        );
        $query = str_replace('/* Insert Fields */', '', $query);

        $logical = 'AND';
        if($this->get('combine') == 'OR') {
            $logical = 'OR';
        }

        if($this->get('includemode') == 'include') {
            $chain[] = $logical.' vtiger_crmentity.crmid IN ('.$query.')';
        }
        if($this->get('includemode') == 'exclude') {
            $chain[] = $logical.' vtiger_crmentity.crmid NOT IN ('.$query.')';
        }

        $this->addStat('Result chain');
        $this->addStat($chain);

        $context->setEnvironment($environmentId, $chain);

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {

        $viewer->assign("related_modules", VtUtils::getEntityModules(true));

        $search_module = $this->get("search_module");

        if(!empty($_POST["task"]["search_module"])) {
            $parts = explode("#~#", $_POST["task"]["search_module"]);
        } elseif(!empty($search_module)) {
            if($search_module != -1) {
                $parts = explode("#~#", $search_module);
            }
        } else {
            return;
        }

        if(!empty($parts)) {
            $search_module_name = VtUtils::getModuleName($parts[1]);

            $viewer->assign("related_tabid", $parts[1]);

            $fields = VtUtils::getFieldsWithBlocksForModule($search_module_name);
            $viewer->assign("sort_fields", $fields);

        }

    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
