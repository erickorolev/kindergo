<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskDecisiontable extends \Workflow\Task
{
    public function handleTask(&$context) {
		/* Insert here source code to execute the task */
		$structure = $this->get('structure');
		$dataRows = $this->get('data');

		$decisionValues = array();
		foreach($structure['_ColType']['decision'] as $col) {
            $matchValue = $structure['_Columns'][$col]['value'];
            $decisionValues[$col] = \Workflow\VTTemplate::parse($matchValue, $context);
        }

        foreach($dataRows as $index => $data) {
            $match = false;


            foreach($structure['_ColType']['decision'] as $col) {
//                var_dump('run check',$col);
                $matchValue = $decisionValues[$col];
                $checkValue = $data[$col];

                switch ($checkValue['type']) {
                    case 'equal':
//                        var_dump($checkValue['value'].' == '.$matchValue);

                        $match = ($checkValue['value'] == $matchValue);

                        break;
                    case 'contain':
//                        var_dump($checkValue['value'].' ~= '.$matchValue);
                        if (strpos($matchValue, $checkValue['value']) !== false) {
                            $match = true;
                        } else {
                            $match = false;
                        }

                        break;
                    case 'expression':
                        $parser = new \Workflow\ExpressionParser($checkValue['value'], $context, false); # Last Parameter = DEBUG

                        try {
                            $parser->run();
                        } catch(\Workflow\ExpressionException $exp) {
                            Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), "", "");
                        }
                        $returnValue = $parser->getReturn();

                        if($returnValue == 'on' || $returnValue == 'true' || $returnValue == 'yes' || $returnValue == 1 || $returnValue == '1' || $returnValue === true) {
                            $match = true;
                        } else {
                            $match = false;
                        }

                        break;
                }

                if($match === false) {
                    break;
                }
            }


            if($match === false) {
                continue;
            }

            if($match === true) {
                foreach($structure['_ColType']['setter'] as $col) {
                    $setConfig = $structure['_Columns'][$col];
                    $content = \Workflow\VTTemplate::parse($data[$col]['value'], $context);

                    switch($setConfig['type']) {
                        case 'envvar':

                            $code = '<?php '.$setConfig['value'].' = "'.addslashes($content).'"; ?>';
                            \Workflow\VTTemplate::parse($code, $context);
                            break;
                        case 'field':
                            $context->set($setConfig['value'], $content);
                            break;
                    }
                }

                break;
            }
        }

		return "yes";
    }
	
    public function beforeGetTaskform($viewer) {
        if($this->notEmpty('data') === false) {
            $this->set('data', array());
        }
        if($this->notEmpty('structure') === false) {
            $this->set('structure', array());
        }

        $viewer->assign('fields', \Workflow\VtUtils::getFieldsWithBlocksForModule($this->getModuleName()));
		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values) {
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
