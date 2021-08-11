<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 08.08.14 22:02
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\Fieldtypes;

class TextOnly extends \Workflow\Fieldtype
{
    public function getFieldTypes($moduleName) {
        $fields = array();

        $fields[] = array(
            'id' => 'textonly',
            'title' => 'Readonly Text',
            'config' => array(
                'default' => array(
                    'type' => 'templatearea',
                    'label' => ''
                )
            )
        );

        return $fields;
    }

    public function renderFrontend($data, $context) {
        if(!empty($data['config']['default'])) {
            $data['config']['default'] = \Workflow\VTTemplate::parse($data['config']['default'], $context);
        }

        $html = "<div style='clear: both;min-height:26px;padding:2px 0;'><div style=''><strong>".$data['label']."</strong></div>".$data['config']['default']."</div>";

        return array('html' => $html, 'javascript' => '');
    }

    /**
     * @param $value
     * @param $name
     * @param $type
     * @param \Workflow\VTEntity $context
     * @return \type
     */
    public function getValue($value, $name, $type, $context, $allValues) {
        return '';
    }
}

\Workflow\Fieldtype::register('textonly', '\Workflow\Plugins\Fieldtypes\TextOnly');