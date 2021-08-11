<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskGenerateRecordlist extends \Workflow\Task
{
    public function init() {
        if(!empty($_POST['changeModule'])) {
            $this->set('fields', array());
        }
    }

    public function handleTask(&$context) {
		$envId = $this->get('envId');

        if(empty($envId)) {
            throw new \Exception('You must configure the generate Recordlist Block and set a Environment ID.');
        }

        $fields = $this->get('fields');
        $env = $context->getEnvironment($envId);

        $search_module = $this->get("search_module");

        if(!is_array($env) && is_string($env) && !empty($env)) {
            $parts = explode("#~#", $search_module);
            $env = array(
                'moduleName' => $parts[0],
                'ids' => explode(',', $env)
            );
        }

        $moduleName = $env['moduleName'];
        $ids = $env['ids'];
        $tabid = getTabid($moduleName);
        $showTotalRow = $this->get('totalrow') == '1';
        $translated = array();

        $total = array();
        foreach($fields as $index => $field) {
            $matches = array();
            $return = preg_match('/{?\\$(\w+|(\[([a-zA-Z0-9]*)((,(.*?))?)\])|({(.*?)}}>)|\((\w+) ?: \(([_\w]+)\) (\w+)\))}?/', $field['field'], $matches);

            if(count($matches) == 2) {
                $fieldModuleName = $moduleName;
                $fieldname = $matches['1'];
            } else {
                $fieldModuleName = $matches[10];
                $fieldname = $matches[11];
            }

            $fieldData = \Workflow\VtUtils::getFieldInfo($fieldname, getTabId($fieldModuleName));
            $fieldType = \Workflow\VtUtils::getFieldTypeName($fieldData['uitype'], $fieldData['typeofdata']);

            if($fieldType == 'decimal' || $fieldType == 'currency' || $fieldType == 'integer' || $fieldType == 'number') {
                $total[$index] = 0;
            } else {
                $total[$index] = false;
            }

            if($fieldType == 'picklist' && $this->notEmpty('translated')) {
                $translated[$index] = $this->get('translated');
            }

        }

        if(!empty($search_module)) {
            if($search_module != -1) {
                $parts = explode("#~#", $search_module);
            }
        }

        if($moduleName != $parts[0]) {
            throw new \Exception('The generate RecordList Block use the wrong Module. You must set '.$moduleName.' or create another block.');
        }

        $adb = \PearDatabase::getInstance();
        $html = '<table border=1 cellpadding=2 cellspacing=0>';
            $html .= '<thead><tr>';
                foreach($fields as $field) {
                    $html .= '<th style="width:'.$field['width'].';text-align:left;background-color:#ccc;">'.$field['label'].'</th>';
                }
            $html .= '</tr></thead>';
            foreach($ids as $id) {
                $html .= '<tr>';
                $record = \Workflow\VTEntity::getForId($id, $moduleName);
                $contextDummy = \Workflow\VTEntity::getDummy();
                foreach($fields as $index => $field) {
                    if($field['field'] == 'link') {
                        $value = '<a href="'.vglobal('site_URL').'/index.php?module='.$record->getModuleName().'&view=Detail&record='.$id.'">Link</a>';
                    } else {
                        $value = \Workflow\VTTemplate::parse($field['field'], $record);
                    }

                    if(isset($translated[$index])) {
                        $value = \Vtiger_Language_Handler::getTranslatedString($value, $moduleName, $translated[$index]);
                    }
                    if($showTotalRow === true && $total[$index] !== false) {
                        $total[$index] += floatval($value);
                    }

                    if(!empty($field['value']) && $field['value'] != '$value') {
                        $contextDummy->set('value', $value);
                        $value = \Workflow\VTTemplate::parse($field['value'], $contextDummy);
                    }

                    $html .= '<td>'.$value.'</td>';
                }
                $html .= '</tr>';
            }

            if($showTotalRow === true) {
                $html .=  '<tr>';
                    foreach($fields as $index => $field) {
                        if($total[$index] !== false) {
                            $html .= '<td style="text-align:left;background-color:#ccc;font-weight:bold;">' . floatval($total[$index]) . '</td>';
                        } else {
                            $html .= '<td style="text-align:left;background-color:#ccc;font-weight:bold;"></td>';
                        }
                    }
                $html .=  '</tr>';
            }
        $html .= '</table>';

		$env['html'] = $html;
        $context->setEnvironment($envId, $env);

		return "yes";
    }

    public function getFromFields() {
        if($this->_fromFields === null) {
            $search_module = $this->get("search_module");

            if(!empty($search_module)) {
                if($search_module != -1) {
                    $parts = explode("#~#", $search_module);
                }
            } else {
                return;
            }


            $this->_fromFields = VtUtils::getFieldsWithBlocksForModule($parts[0], true);
        }

        return $this->_fromFields;
    }

    public function beforeGetTaskform($viewer) {
        $fields = $this->get('fields');
        if(empty($fields) || $fields == -1) {
            $fields = array();
        }

        $languages = \Vtiger_Language_Handler::getAllLanguages();

        $viewer->assign("languages", $languages);
        $viewer->assign("StaticFieldsField", 'fields');
        $viewer->assign("fields", $fields);
        $viewer->assign("fromFields", $this->getFromFields());

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
            $viewer->assign("related_tabid", $parts[1]);
        }

    }	
    public function beforeSave(&$values) {
        unset($values['fields']['##SETID##']);
        return $values;
		/* Insert here source code to modify the values the user submit on configuration */
    }	
}
