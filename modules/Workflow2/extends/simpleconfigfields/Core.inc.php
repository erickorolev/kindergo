<?php
namespace Workflow\Plugin\SimpleConfigFields;

use Workflow\VtUtils;

class Core {
    public static function text($field) {
        return '<input type="text" name="'.$field['name'].'" autocomplete="off" value="'.$field['value'].'" style="width:90%;" />';
    }
    public static function hidden($field) {
        return '<input type="hidden" name="'.$field['name'].'" autocomplete="off" value="'.$field['value'].'" style="width:90%;" />';
    }
    public static function template($field) {
        $options = array();
        $options['width'] = '600px';

        return '<div class="insertTextfield" data-name="'.$field['name'].'" data-placeholder="'.$field['placeholder'].'" data-id="id'.md5(microtime().$field['name']).'" data-options=\'{"width":"'.$options['width'].'"}\' style="width:99%;">'.$field['value'].'</div>';
    }
    public static function password($field) {
        return '<input type="password" class="form-control" name="'.$field['name'].'" autocomplete="off" value="'.$field['value'].'" style="width:90%;" />';
    }
    public static function textarea($field) {
        //$options = array();
        //$options['width'] = '600px';

        return '<div class="insertTextarea" data-name="'.$field['name'].'" data-placeholder="'.$field['placeholder'].'" data-id="id'.md5(microtime().$field['name']).'" data-options=\'{}\' style="width:90%;">'.$field['value'].'</div>';
    }
    public static function customconfigfield($field) {
        // Custom Config field only usable for CustomValue Switch!
        $options = array();
        $options['width'] = '600px';

        $options['disabled'] = $field['disabled'] == true;

        return '<div class="insertTextfield" data-name="'.$field['name'].'" data-placeholder="'.$field['placeholder'].'" data-id="id'.md5(microtime().$field['name']).'" data-options=\'{"width":"'.$options['width'].'","disabled":'.($options['disabled']?'true':'false').'}\' style="width:99%;">'.$field['value'].'</div>';
    }
    public static function checkbox($field) {
        return '<input type="checkbox" name="'.$field['name'].'" autocomplete="off" value="1" '.($field['value'] == '1' ? 'checked="checked"':'').' />';
    }
    public static function readonly($field) {
        return '<td class="SCLabel" colspan="2"><span>'.$field['label'].'</span></td>';
    }
    public static function timezone($field) {
        if(empty($field['value'])) {
            $currentUser = \Users_Record_Model::getCurrentUserModel();
            $field['value'] = $currentUser->get('time_zone');
        }
        $userModuleModel = \Users_Module_Model::getInstance('Users');
        $timezones = $userModuleModel->getTimeZonesList();

        $html = '<select name="'.$field['name'].'" class="select2 SCSingleFieldWidth">"';
        foreach($timezones as $timezone) {
            $html .= '<option value="'.$timezone.'" '.($field['value'] == $timezone ? 'selected="selected"' : '').'>'.$timezone.'</option>';
        }
        $html .= '</select>';

        return $html;
    }
    public static function provider($field) {
        $availableProvider = \Workflow\ConnectionProvider::getAvailableConfigurations($field['provider']);
        $html = '<select name="'.$field['name'].'" class="select2 SCSingleFieldWidth">"';
        foreach($availableProvider as $id => $label) {
            $html .= '<option value="'.$id.'" '.($field['value'] == $id ? 'selected="selected"' : '').'>'.$label.'</option>';
        }
        $html .= '</select>';

        return $html;
    }
    public static function select($field) {
        $html1 = '<select name="'.$field['name'].'" class="select2 SCSingleFieldWidth">';

        foreach($field['options'] as $id => $label) {
            $html1 .= '<option value="'.$id.'" '.($field['value'] == $id ? 'selected="selected"' : '').'>'.$label.'</option>';
        }
        $html1 .= '</select>';

        return $html1;
    }
    public static function multiselect($field) {
        $html1 = '<select multiple="multiple" name="'.$field['name'].'[]" class="select2 SCSingleFieldWidth">';

        foreach($field['options'] as $id => $label) {
            $html1 .= '<option value="'.$id.'" '.(in_array($id, $field['value']) ? 'selected="selected"' : '').'>'.$label.'</option>';
        }
        $html1 .= '</select>';

        return $html1;
    }

    public static function fields($parameters) {
        $moduleName = $parameters['modulename'];
        $uiTypes = isset($parameters['uitypes']) ? $parameters['uitypes'] : false;

        $fields = VtUtils::getFieldsForModule($moduleName, $uiTypes);

        $html1 = '<select multiple="multiple" name="'.$parameters['name'].'[]" class="select2 SCSingleFieldWidth">';

        foreach($fields as $fieldname => $fielddata) {
            $html1 .= '<option value="'.$fielddata->name.'" '.(in_array($fielddata->name, $parameters['value']) ? 'selected="selected"' : '').'>'.$fielddata->label.'</option>';
        }
        $html1 .= '</select>';

        return $html1;
    }

    public static function expressionfield($field) {
        $options = array();
        $options['width'] = '600px';

       //$field['value'] = htmlentities($field['value']);
        //var_dump($field);
        return '<div class="insertTextfield" data-name="'.$field['name'].'" data-mode="expression" data-placeholder="'.$field['placeholder'].'" data-id="id'.md5(microtime().$field['name']).'" data-options=\'{"width":"'.$options['width'].'"}\'>'.$field['value'].'</div>';
    }

    public static function expressionarea($field) {
        $options = array();
        $options['width'] = '600px';

        //$field['value'] = htmlentities($field['value']);

        return '<div class="insertTextarea" data-name="'.$field['name'].'" data-mode="expression" data-placeholder="'.$field['placeholder'].'" data-id="id'.md5(microtime().$field['name']).'" data-options=\'{"width":"'.$options['width'].'"}\'>'.$field['value'].'</div>';
    }

    public static function user($field) {
        $currentUser = \Users_Record_Model::getCurrentUserModel();
        $users = $currentUser->getAccessibleUsers();
        $groups = $currentUser->getAccessibleGroups();
        $assignedToValues = array();
        $assignedToValues[vtranslate('LBL_USERS', 'Vtiger')] = $users;
        if(empty($field['onlyuser'])) {
            $assignedToValues[vtranslate('LBL_GROUPS', 'Vtiger')] = $groups;
        }

        $options  = '';
        $options .= '<option value="$current_user_id" '.($field['value'] == '$current_user_id'?'selected="selected"':'').'>current User</option>';
        $options .= '<option value="$assigned_user_id" '.($field['value'] == '$assigned_user_id'?'selected="selected"':'').'>assigned User/Group</option>';

        foreach($assignedToValues as $groupLabel => $objs) {
            $options .= '<optgroup label="'.$groupLabel.'">';
            foreach($objs as $objId => $obj) {
                $options .= '<option value="'.$objId.'" '.($field['value'] == $objId?'selected="selected"':'').'>'.$obj.'</option>';
            }
        }

        $html1 = '<select name="'.$field['name'].'" class="select2 SCSingleFieldWidth">' . $options . '</select>';

        return $html1;
    }

}

\Workflow\SimpleConfigFields::register('userpicklist', array('\Workflow\Plugin\SimpleConfigFields\Core', 'user'));
\Workflow\SimpleConfigFields::register('password', array('\Workflow\Plugin\SimpleConfigFields\Core', 'password'));

\Workflow\SimpleConfigFields::register('hidden', array('\Workflow\Plugin\SimpleConfigFields\Core', 'hidden'), array(
    'decorated' => true
));
\Workflow\SimpleConfigFields::register('text', array('\Workflow\Plugin\SimpleConfigFields\Core', 'text'));
\Workflow\SimpleConfigFields::register('textarea', array('\Workflow\Plugin\SimpleConfigFields\Core', 'textarea'));
\Workflow\SimpleConfigFields::register('expressionfield', array('\Workflow\Plugin\SimpleConfigFields\Core', 'expressionfield'));
\Workflow\SimpleConfigFields::register('expressionarea', array('\Workflow\Plugin\SimpleConfigFields\Core', 'expressionarea'));
\Workflow\SimpleConfigFields::register('select', array('\Workflow\Plugin\SimpleConfigFields\Core', 'select'), array(
    'customvalue' => true,
));
\Workflow\SimpleConfigFields::register('multiselect', array('\Workflow\Plugin\SimpleConfigFields\Core', 'multiselect'), array(
    'customvalue' => true,
));
\Workflow\SimpleConfigFields::register('picklist', array('\Workflow\Plugin\SimpleConfigFields\Core', 'select'), array(
    'customvalue' => true,
));
\Workflow\SimpleConfigFields::register('fields', array('\Workflow\Plugin\SimpleConfigFields\Core', 'fields'), array(
    'customvalue' => true,
));
\Workflow\SimpleConfigFields::register('multipicklist', array('\Workflow\Plugin\SimpleConfigFields\Core', 'multiselect'), array(
    'customvalue' => true,
));
\Workflow\SimpleConfigFields::register('template', array('\Workflow\Plugin\SimpleConfigFields\Core', 'template'));

\Workflow\SimpleConfigFields::register('customconfigfield', array('\Workflow\Plugin\SimpleConfigFields\Core', 'customconfigfield'));

\Workflow\SimpleConfigFields::register('checkbox', array('\Workflow\Plugin\SimpleConfigFields\Core', 'checkbox'), array(
    'customvalue' => true,
));
\Workflow\SimpleConfigFields::register('timezone', array('\Workflow\Plugin\SimpleConfigFields\Core', 'timezone'));
\Workflow\SimpleConfigFields::register('provider', array('\Workflow\Plugin\SimpleConfigFields\Core', 'provider'));
\Workflow\SimpleConfigFields::register('readonly', array('\Workflow\Plugin\SimpleConfigFields\Core', 'readonly'), array(
    'decorated' => true
));