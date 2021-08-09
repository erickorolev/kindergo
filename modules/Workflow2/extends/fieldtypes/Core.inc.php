<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 08.08.14 22:02
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\Fieldtypes;

use Workflow\VTEntity;
use Workflow\VtUtils;

class Core extends \Workflow\Fieldtype
{
    public function decorated($data) {
        if($data['type'] == 'hidden') {
            return false;
        }
        if($data['type'] == 'checkbox') {
            return false;
        }

        return true;
    }

    public function getFieldTypes($moduleName) {
        $fields = array();

        $fields[] = array(
            'compatible_version' => 2,
            'id' => 'text',
            'title' => 'Text',
            'config' => array(
                'default' => array(
                    'type' => 'templatefield',
                    'label' => 'Default Value'
                ),
                'mandatory' => array(
                    'type' => 'checkbox',
                    'label' => 'Field is Mandatory',
                    'value' => '1',
                ),
            )
        );
        $fields[] = array(
            'compatible_version' => 2,
            'id' => 'email',
            'title' => 'E-Mail Adresse Selectbox',
            'config' => array(
                'default' => array(
                    'type' => 'templatefield',
                    'label' => 'Default Value'
                ),
                'multiple' => array(
                    'type' => 'checkbox',
                    'label' => 'Allow multiple values',
                    'value' => '1',
                ),
                'mandatory' => array(
                    'type' => 'checkbox',
                    'label' => 'Field is Mandatory',
                    'value' => '1',
                ),
            )
        );
        $fields[] = array(
            'compatible_version' => 2,
            'id' => 'email-text',
            'title' => 'E-Mail Adresse Textfield',
            'config' => array(
                'default' => array(
                    'type' => 'templatefield',
                    'label' => 'Default Value'
                ),
                'mandatory' => array(
                    'type' => 'checkbox',
                    'label' => 'Field is Mandatory',
                    'value' => '1',
                ),
            )
        );
        $fields[] = array(
            'compatible_version' => 2,
            'id' => 'hidden',
            'title' => 'Hidden value',
            'config' => array(
                'default' => array(
                    'type' => 'templatefield',
                    'label' => 'Value'
                ),
            )
        );
        $fields[] = array(
            'compatible_version' => 2,
            'id' => 'textarea',
            'title' => 'Textarea',
            'config' => array(
                'default' => array(
                    'type' => 'templatearea',
                    'label' => 'Default Value'
                ),
                'mandatory' => array(
                    'type' => 'checkbox',
                    'label' => 'Field is Mandatory',
                    'value' => '1',
                ),
            )
        );
        $fields[] = array(
            'compatible_version' => 2,
            'id' => 'htmleditor',
            'title' => 'HTML Editor',
            'config' => array(
                'default' => array(
                    'type' => 'templatearea',
                    'label' => 'Default Value'
                ),
                'mandatory' => array(
                    'type' => 'checkbox',
                    'label' => 'Field is Mandatory',
                    'value' => '1',
                ),
            )
        );
        $fields[] = array(
            'compatible_version' => 2,
            'id' => 'checkbox',
            'title' => 'Checkbox',
            'config' => array(
                'default' => array(
                    'type' => 'checkbox',
                    'label' => 'Default Value',
                    'value' => 'On'
                ),
                'mandatory' => array(
                    'type' => 'checkbox',
                    'label' => 'Field is Mandatory',
                    'value' => '1',
                ),
            )
        );
        $languages = array(
            '' => 'default User language'
        );
        $sql = 'SELECT prefix, label FROM vtiger_language ORDER BY label';
        $result = VtUtils::query($sql);
        while($row = VtUtils::fetchByAssoc($result)) {
            $languages[$row['prefix']] = $row['label'];
        }
        $fields[] = array(
            'compatible_version' => 2,
            'id' => 'picklist',
            'title' => 'Picklist',
            'config' => array(
                'default' => array(
                    'type' => 'templatearea',
                    'label' => 'Available Custom Options',
                    'description' => 'You could use "Visible Value#~#ValueToSend" to show Userfriendly values',
                ),
                'defval' => array(
                    'type' => 'templatefield',
                    'label' => 'Default value',
                ),
/*                'dmylabel' => array(
                    'type' => 'label',
                    'label' => 'You could use "Visible Value#~#ValueToSend" to show Userfriendly values'
                ),*/
                'srcpicklist' => array(
                    'type' => 'custom',
                ),
                'language' => array(
                    'type' => 'picklist',
                    'label' => 'show this picklist values in this language',
                    'options' => $languages
                ),
                'multiple' => array(
                    'type' => 'checkbox',
                    'label' => 'Allow multiple selections',
                    'value' => '1'
                ),
                'mandatory' => array(
                    'type' => 'checkbox',
                    'label' => 'Field is Mandatory',
                    'value' => '1',
                ),
            )
        );
        $fields[] = array(
            'compatible_version' => 2,
            'id' => 'date',
            'title' => 'Date',
            'config' => array(
                'default' => array(
                    'type' => 'templatefield',
                    'label' => 'Default'
                ),
                'mandatory' => array(
                    'type' => 'checkbox',
                    'label' => 'Field is Mandatory',
                    'value' => '1',
                ),
            )
        );
        $fields[] = array(
            'compatible_version' => 2,
            'id' => 'file',
            'title' => 'Fileupload',
            'config' => array(
                'default' => array(
                    'type' => 'templatefield',
                    'label' => 'Write file to FileStoreID'
                ),
                'mandatory' => array(
                    'type' => 'checkbox',
                    'label' => 'Field is Mandatory',
                    'value' => '1',
                ),
            )
        );

        return $fields;
    }

    public function getFieldConfig($data) {
        $field = '';
        $script = '';

        switch($data['type']) {
            case 'file':
                $field = '<input type="file" '.(!empty($data['config']['mandatory'])?'required="required"':'').' class="FixedUsed" id="reqfield_' . $data['name'] . '" data-filestoreid="' . $data['config']['default'] . '" style="width:100%" name="' . $data['name'] . '" value="' . $data['config']["default"] . '">';
                break;
            case 'checkbox':
                $field = '<input type="checkbox" style="vertical-align:middle;" '.(!empty($data['config']['mandatory'])?'required="required"':'').' name="' . $data['name'] . '" ' . ($data["config"]["default"] == 'On'?"checked='checked'":"") . ' value="on">';

                if(self::$Version == 1) {
                    $field = "<label style='width:100%;'><div style='min-height:26px;padding:2px 0;margin:0 !important;' class='row'><div class='col-lg-4'>&nbsp;</div><div style='text-align:left;' class='col-lg-8'>" . $field . "&nbsp;&nbsp;&nbsp;<strong style='vertical-align:middle;'>" . $data['label'] . "</strong></div></div></label>";
                } else {
                    $field = "<label style='width:100%;'><div style='min-height:26px;padding:2px 0;margin:0 !important;' class='row'><div style='text-align:left;' class='col-lg-8'>" . $field . "&nbsp;&nbsp;&nbsp;<strong style='vertical-align:middle;'>" . $data['label'] . "</strong></div></div></label>";
                }

                break;
            case 'textarea':
                $field = '<textarea id="reqfield_' . $data['name'] . '" '.(!empty($data['config']['mandatory'])?'required="required"':'').' style="width:100%;height:100px;" name="' . $data['name'] . '">' . $data['config']["default"] . '</textarea>';
                break;
            case 'htmleditor':
                $field = '<textarea class="fixedUsed used '.(!empty($data['config']['mandatory'])?'RequiredCheck':'').'" id="reqfield_' . $data['name'] . '" '.(!empty($data['config']['mandatory'])?'required="required"':'').' style="width:100%;height:100px;" name="' . $data['name'] . '">' . $data['config']["default"] . '</textarea>';
                $script = 'window.CKEDITOR = null; var CKEDITOR_BASEPATH = \'modules/Workflow2/views/resources/js/ckeditor_4.9.1/\'; FlexUtils("Workflow2").loadScript("'.PATH_CKEDITOR.'/ckeditor.js").then(function() {
                                jQuery("#reqfield_' . $data['name'] . '").ckeditor({
                                    //basePath: \'modules/Workflow2/views/resources/js/ckeditor_4.9.1/\',
                                    skin: \'moono-lisa\',
                                    toolbar: [
                        { name: \'document\', items: [ \'Source\' ] },
                        { name: \'clipboard\', items: [ \'Undo\', \'Redo\' ] },

                        { name: \'basicstyles\', items: [ \'Bold\', \'Italic\', \'Underline\', \'Strike\', \'RemoveFormat\' ] },
                        { name: \'paragraph\', items: [ \'NumberedList\', \'BulletedList\', \'-\', \'Outdent\', \'Indent\', \'-\', \'Blockquote\', \'-\', \'JustifyLeft\', \'JustifyCenter\', \'JustifyRight\', \'JustifyBlock\' ] },
                        { name: \'links\', items: [ \'Link\', \'Unlink\' ] },
                        { name: \'insert\', items: [ \'Image\', \'Table\', \'HorizontalRule\' ] },
                        { name: \'colors\', items: [ \'TextColor\', \'BGColor\' ] }
                    ]
                                });

                });';
                break;
            case 'custom':
                if(is_callable($data['backend'])) {

                }
                break;
            case 'picklist':
                $options = explode("\n", $data['config']['default']);
                $field = '<select style="width:100%;" '.(!empty($data['config']['mandatory'])?'required="required"':'').' '.(!empty($data['config']['multiple'])?'multiple="multiple"':'').' name="' . $data['name'] . ''.(!empty($data['config']['multiple'])?'[]':'').'" class="MakeSelect2 '.(!empty($data['config']['mandatory'])?'RequiredCheck':'').'">';
                $defValue = !empty($data['config']['defval'])?$data['config']['defval']:'';

                if(!empty($data['config']['srcpicklist'])) {
                    if($data['config']['srcpicklist'] == 'Userlist') {
                        $currentUser = \Users_Record_Model::getCurrentUserModel();
                        $users = $currentUser->getAccessibleUsers();
                        $groups = $currentUser->getAccessibleGroups();
                        $assignedToValues = array();
                        $assignedToValues[vtranslate('LBL_USERS', 'Vtiger')] = $users;
                        $assignedToValues[vtranslate('LBL_GROUPS', 'Vtiger')] = $groups;

                        foreach($assignedToValues as $groupLabel => $objs) {
                            $field .= '<optgroup label="'.$groupLabel.'">';
                            foreach($objs as $objId => $obj) {
                                $field .= '<option value="'.$objId.'" '.(!empty($defValue) && $defValue == $objId ? 'selected="selected"' : '').'>'.$obj.'</option>';
                            }
                        }
                    } else {
                        $split = explode('-', $data['config']['srcpicklist']);

                        $language = $data['config']['language'];
                        if(empty($language)) {
                            $language = \Vtiger_Language_Handler::getLanguage();
                        }

                        $language1 = \Vtiger_Language_Handler::getModuleStringsFromFile($language, 'Vtiger');
                        $language2 = \Vtiger_Language_Handler::getModuleStringsFromFile($language, $split[0]);
                        $moduleStrings = array_merge(
                            $language1['languageStrings'],
                            $language2['languageStrings']
                        );

                        $picklistValues = getAllPickListValues($split[1], $moduleStrings);

                        foreach ($picklistValues as $picklistKey => $picklistValue) {
                            $field .= '<option value="' . $picklistKey . '" '.(!empty($defValue) && $defValue == $picklistKey ? 'selected="selected"' : '').'>' . $picklistValue . '</option>';
                        }
                    }
                }

                foreach($options as $option) {
                    $option = trim($option);
                    if(strpos($option, '#~#') !== false) {
                        $parts = explode('#~#',$option);
                        $fieldValue = $parts[1];
                        $fieldLabel = $parts[0];

                    } else {
                        $fieldValue = $option;
                        $fieldLabel = $option;
                    }

                    $field .= '<option value="'.$fieldValue.'" '.(!empty($defValue) && $defValue == $fieldValue ? 'selected="selected"' : '').'>'.$fieldLabel.'</option>';
                }
                $field .= '</select>';
                break;
            case 'date':
                $current_user = \Users_Record_Model::getCurrentUserModel();

                if(self::$Version == 1) {

                    $field = '<div class="input-append pull-right" style="width:100%;">';
                    if(!empty($data['config']["default"])) {
                        $preset = \DateTimeField::convertToUserFormat($data['config']["default"]);
                    } else {
                        $preset = '';
                    }
                    $field .= '<input type="text" '.(!empty($data['config']['mandatory'])?'required="required"':'').' class="dateField span2" data-date-format="'.$current_user->date_format.'"id="reqfield_' . $data['name'] . '"name="' . $data['name'] . '" value="' . $preset . '">';
                    $field .= '<span class="add-on"><i class="icon-calendar"></i></span>';
                    $field .= '</div>';

                } else {

                    $field = '';
                    if(!empty($data['config']["default"])) {
                        $preset = \DateTimeField::convertToUserFormat($data['config']["default"]);
                    } else {
                        $preset = '';
                    }
                    $field .= '<input type="text" '.(!empty($data['config']['mandatory'])?'required="required"':'').' class="dateField span2" data-date-format="'.$current_user->date_format.'"id="reqfield_' . $data['name'] . '"name="' . $data['name'] . '" value="' . $preset . '">';
                    $field .= '<i style="position:absolute;top:10px;right:5px;" class="fa fa-calendar" aria-hidden="true"></i>';
                    $field .= '';

                }
                break;
            case 'text':
            default:
                $field = '<input type="text" '.(!empty($data['config']['mandatory'])?'required="required"':'').' class="form-control" id="reqfield_' . $data['name'] . '" style="width:100%;" name="' . $data['name'] . '" value="' . $data['config']["default"] . '">';
                break;
            case 'hidden':
                $field = '<input type="hidden" id="reqfield_' . $data['name'] . '" style="width:100%;" name="' . $data['name'] . '" value="' . $data['config']["default"] . '">';
                break;
            case 'email-text':
                $field = '<input type="email" '.(!empty($data['config']['mandatory'])?'required="required"':'').' class="form-control" id="reqfield_' . $data['name'] . '" style="width:100%;" name="' . $data['name'] . '" value="' . $data['config']["default"] . '">';
                break;
            case 'email':
                $field = '<input type="hidden" data-type="email" '.(!empty($data['config']['mandatory'])?'required="required"':'').' class="form-control" id="reqfield_' . $data['name'] . '" style="width:100%;" name="' . $data['name'] . '" value="' . $data['config']["default"] . '">';
                $script = '
            var input = $("#reqfield_' . $data['name'] . '");
            input.select2({
                placeholder: "",
                width:\'100%\',
                minimumInputLength: 1,
                multiple:'.($data['config']['multiple'] == '1' ? 'true' : 'false').',
                separator: ";#;",
                initSelection: function (element, callback) {
                    var parts = jQuery(element).val().split(\',\');
                    jQuery(element).val("");                    
                    var data = [];

                    jQuery.each(parts, function(index, id) {
                        if(id != "") {
                            data.push({
                                id: id,
                                text: id
                            });
                        }
                    });

                    if(data.length > 0) callback(data);
                },
                query: function (query) {
                    var data = {
                        query: query.term,
                        page: query.page,
                        pageLimit: 25,
                        fieldtype:"email"
                    };

                    jQuery.post("index.php?module=Workflow2&action=Autocompleter", data, function (results) {
                        query.callback(results);
                    }, \'json\');

                }
            });';
                break;
        }

        return array(
            'html' => $field,
            'javascript' => $script
        );
    }
    /**
     * @param $data
     * @param VTEntity $context
     * @return array
     */
    public function renderFrontend($data, $context) {
        if(!empty($data['config']['default'])) {
            $data['config']['default'] = \Workflow\VTTemplate::parse($data['config']['default'], $context);
        }

        $fieldCode = $this->getFieldConfig($data);


        switch($data['type']) {
            case 'checkbox':
                return $fieldCode;
                break;
        }

        $html = "<label style='width:100%;'><div style='min-height:26px;padding:2px 0;margin:0 !important;' class='row'><div class='col-lg-4'><strong>".$data['label']."</strong></div><div style='text-align:right;' class='col-lg-8'>".$fieldCode['html']."</div></div></label>";

        return array('html' => $html, 'javascript' => $fieldCode['javascript']);
    }

    public function renderFrontendV2($data, $context) {
        if(!empty($data['config']['default'])) {
            $data['config']['default'] = \Workflow\VTTemplate::parse($data['config']['default'], $context);
        }

        $fieldCode = $this->getFieldConfig($data);

        $fieldCode['js'] = $fieldCode['javascript'];
        unset($fieldCode['javascript']);

        return $fieldCode;
    }

    public function getConfigData($moduleName, $item, $key, $configdata) {
        if($item['id'] == 'picklist' && $key == 'srcpicklist') {
            $return = array(
                'type' => 'picklist',
                'label' => 'Values from this Picklist',
                'options' => array('' => '- use custom options only')
            );

            $field['config'][$key]['type'] = 'picklist';
            $adb = \PearDatabase::getInstance();
            $sql = 'SELECT fieldname, fieldlabel, tabid FROM vtiger_field WHERE uitype IN ('.implode(',', array(117,115,15, 16, 33, 98)).') ORDER BY tabid';
            $result = $adb->query($sql);

            $return['options']['Userlist'] = vtranslate('List of Users/Groups', 'Settings:Workflow2');

            while($row = $adb->fetchByAssoc($result)) {
                $fieldModuleName = VtUtils::getModuleName($row['tabid']);
                $key = $fieldModuleName.'-'.$row['fieldname'];
                $return['options'][$key] = vtranslate($fieldModuleName, $fieldModuleName) . ' - '.vtranslate($row['fieldlabel'], $fieldModuleName);
            }


            return $return;
        }

    }

    /**
     * @param $value
     * @param $name
     * @param $type
     * @param \Workflow\VTEntity $context
     * @return \type
     */
    public function getValue($value, $name, $type, $context, $allValues) {
        if($type == 'date') {
            $value = \DateTimeField::convertToDBFormat($value);
        }

        if($type == 'file') {
            if(!empty($_FILES['fileUpload'])) {
                $context->addTempFile($_FILES['fileUpload']['tmp_name'][$name], $value, $_FILES['fileUpload']['name'][$name]);
            }
            return '1';
        }
        return $value;
    }
}

\Workflow\Fieldtype::register('core', '\Workflow\Plugins\Fieldtypes\Core');