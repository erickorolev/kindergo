<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Workflow2_ComplexeCondition_View extends Vtiger_Index_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('ConditionPopup');
    }

    function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    public function ConditionPopup(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();

        $moduleName = $request->getModule();
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);

        $configuration = $request->get('configuration');

        if(!empty($configuration)) {
            $configuration = \Workflow\VtUtils::json_decode(base64_decode($request->get('configuration')));
        } else {
            $toModule = $request->get('toModule');
            $configuration = array(
                'module' => $toModule,
                'condition' => array()
            );
        }

        $preset = new \Workflow\ComplexeCondition('condition', array(
            'fromModule' => $request->has('fromModule') ? $request->get('fromModule') : '',
            'toModule' => $configuration['module'],
            'enableHasChanged' => false,
            'container' => 'conditionalPopupContainer',
            'enableTemplateFields' => true,
            'references' => false,
            'variables' => false,
//            'disableTemplateFields' => true,
            'disableConditionMode' => true
        ));

        $preset->setCondition($configuration['condition']);

        $preset->InitViewer(null, $viewer);

        $viewer->assign('ConditionScopeModule', $moduleName);

        $viewer->assign('toModule', $configuration['module']);
        $viewer->assign('title', getTranslatedString($request->get('title'), 'Settings:Workflow2'));

        $viewer->view('ConditionPopup.tpl', $qualifiedModuleName);
    }
}

