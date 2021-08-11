<?php
/**
 This File was developed by Stefan Warnat <vtiger@stefanwarnat.de>

 It belongs to the Workflow Designer and must not be distributed without complete extension
**/

require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));
/* vt6 ready */
class WfTaskExpcond extends \Workflow\Task {

    public function init() {
        $this->addPreset("SyntaxHighlighter", "syntax");
    }
    public function handleTask(&$context) {
        $conditions = $this->get("condition");

        $parser = new \Workflow\ExpressionParser($conditions, $context, false); # Last Parameter = DEBUG

        try {
            $parser->run();
        } catch(\Workflow\ExpressionException $exp) {
            \Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), "", "");
        } catch (\Exception $exp) {
            throw $exp;
        }

        $return = $parser->getReturn();

        if($return != "yes" && $return != "no") {
            $return = "no";
        }

        return $return;

    }

    public function beforeSave(&$values) {
        $parser = new \Workflow\ExpressionParser($values['condition'], \Workflow\VTEntity::getDummy(), false, false); # Last Parameter = DEBUG
        $SyntaxCheck = $parser->checkSyntax();

        if($SyntaxCheck !== false) {
            $this->addConfigHint("<b>Found some syntax errors in custom Expressions:</b><br>" . $SyntaxCheck[0] . " [Line " . $SyntaxCheck[1] . "]");
        }
    }
    public function beforeGetTaskform($viewer) {

    }

}