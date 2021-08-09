<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 08.08.14 22:02
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\Fieldtypes;

use Workflow\VTEntity;

class Contact extends \Workflow\Fieldtype
{
    public function getFieldTypes($moduleName) {
        $fields = array();

        $fields[] = array(
            'id' => 'contact',
            'title' => 'Organization/Contact',
            'config' => array(
                'orgaid' => array(
                    'type' => 'templatefield',
                    'label' => 'OrgaID. to $env[',
                ),
                'contactid' => array(
                    'type' => 'label',
                    'label' => 'ContactID goes to $env["value"][...]',
                ),
            )
        );

        return $fields;
    }


    /**
     * @param $data     - Config Array of this Input with the following Structure
     *                      array(
     *                          'label' => 'Label the Function should use',
     *                          'name' => 'The Fieldname, which should submit the value, the Workflow will be write to Environment',
     *                          'config' => Key-Value Array with all configurations, done by admin
     *                      )
     * @param \Workflow\VTEntity $context - Current Record, which is assigned to the Workflow
     * @return array - The rendered content, shown to the user with the following structure
     *                  array(
     *                      'html' => '<htmlContentOfThisInputField>',
     *                      'javascript' => 'A Javascript executed after html is shown'
     *                  )
     *
     */
    public function renderFrontend($data, $context) {
        $adb = \PearDatabase::getInstance();

        /**
         * @var \Vtiger_Viewer $viewer
         */
        $viewer = \Vtiger_Viewer::getInstance();

        $html = '';
        $script = '';

        $fieldId = 'field_'.preg_replace('/[^a-zA-Z0-9_]/','_', $data['name']);

        $field1 = '<div class="insertReferencefield" style="float:right;" data-name="'.$data['name'].'][accountid" data-module="Accounts"></div>';
        $field2 = '<div class="insertReferencefield" style="float:right;" data-name="'.$data['name'].'][contactid" data-module="Contacts" data-parentfield="'.$data['name'].'][accountid"></div>';
        $html .= "<div style='min-height:26px;padding:2px 0;'><div class='col-lg-4'><strong>".$data['label']."</strong></div><div style='text-align:right;' class='col-lg-8'><div style='overflow:hidden;width:100%;'><strong>Organization</strong><br/>".$field1."</div><div style='overflow:hidden;width:100%;'><strong>Contact</strong><br/>".$field2."</div></div></div>";

        $script = '';
        if(!empty($data['config']['nullable'])) {
            $script .= 'jQuery("#' . $fieldId . '").select2("val", "");';
        }

        $script .= 'jQuery(function() { jQuery("#contactid_contactid_display").attr("readonly", "readonly"); });';
        return array('html' => $html, 'javascript' => $script);
    }

    /**
     * @param $value
     * @param $name
     * @param $type
     * @param $context VTEntity
     * @param $allValues
     * @param $fieldConfig
     * @return mixed
     */
    public function getValue($value, $name, $type, $context, $allValues, $fieldConfig) {
        $orgaField = $fieldConfig['orgaid'];
        $context->setEnvironment($orgaField, $value['accountid']);

        return $value['contactid'];
    }

}

// The class neeeds to be registered
\Workflow\Fieldtype::register('contact', '\Workflow\Plugins\Fieldtypes\Contact');