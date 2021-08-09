<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_ConditionPopup_Action extends Settings_Vtiger_Basic_Action {

    public function process(Vtiger_Request $request) {
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

        $preset = new \Workflow\Preset\Condition('condition', null, array(
            'fromModule' => $request->get('fromModule'),
            'toModule' => $configuration['module'],
            'enableHasChanged' => false,
            'container' => 'conditionalPopupContainer'
        ));

        $preset->beforeGetTaskform(array(
            array(
                'condition' => $configuration['condition']
            ),
            $viewer
            )
        );

        $viewer->assign('show_calculation', $request->get('calculator') == 'true');

        $viewer->assign('toModule', $configuration['module']);
        $viewer->assign('javascript', $preset->getInlineJS());
        $viewer->assign('title', getTranslatedString($request->get('title'), 'Settings:Workflow2'));

        $viewer->view('ConditionPopup.tpl', $qualifiedModuleName);
    }
}

