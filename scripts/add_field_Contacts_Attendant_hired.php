<?php

/*
 * Скрипт добавляет поле в модуль через API тигры. 
 * Удобно тем, что можно задать понятное название поле вместо стандартного cf_3443
 * Также используется для создания одинаковых полей между девом и продом. Один и тот же скрипт запускается на деве и проде.
 * Скрипты для удобство класть в vtiger/scripts/
 * Перед запуском проверить, чтобы у файла были полномочия на запуск от сервере апаче
 * Перед запуском проверить путь к файлам, которые добавлены через require_once
 * Для правильного пути добавлена функция chdir('../')
 */


$Vtiger_Utils_Log = true;
chdir('../');
require_once('vtlib/Vtiger/Menu.php');
require_once('vtlib/Vtiger/Module.php');
require_once('vtlib/Vtiger/Block.php');
require_once('vtlib/Vtiger/Field.php');
$module = Vtiger_Module::getInstance('Contacts'); // Имя модуля из таблицы vtiger_tab
if ($module) {
    $block = Vtiger_Block::getInstance('LBL_CONTACT_INFORMATION', $module); // Название блока из таблицы vtiger_blocks
    if ($block) {
        $field = Vtiger_Field::getInstance('attendant_hired', $module); // Название поля без пробелов через нижнее подчеркивание
        if (!$field) {
            $field               = new Vtiger_Field();
            $field->name         = 'attendant_hired';  // Название поля без пробелов через нижнее подчеркивание
            $field->table        = $module->basetable;
            $field->label        = 'LBL_ATTENDANT_HIRED';  // Лейбл на английском. Переводить на русский через файлы-переводов.
            $field->column       = 'attendant_hired';  // Название поля без пробелов через нижнее подчеркивание
            $field->columntype   = 'DATE';  // Посмотреть тип у похожих полей в таблице vtiger_навание-модуля
            
            // Посмотреть тип у похожих полей в таблице vtiger_field 
            // 1 - текстовое поле
            // 15 - поле-список)
            $field->uitype = 5;  

            $field->displaytype = 1;  // Посмотреть тип у похожих полей в таблице vtiger_field
            
            // Посмотреть тип у похожих полей в таблице vtiger_field
            // V~O~LE~100 - текстовое поле
            // V~O - поле список
            $field->typeofdata = 'D~O';  

            $field->presence = 2; // Посмотреть тип у похожих полей в таблице vtiger_field
            $field->quickcreate = 1; // Посмотреть тип у похожих полей в таблице vtiger_field
            $field->generatedtype = 2; // Посмотреть тип у похожих полей в таблице vtiger_field
            $block->addField($field);

            //Для создания поля-списка раскомментировать эти две строки и прописать значения на английском. Переводить на русский через файлы-переводов.
            //$pickListValues = array('Active', 'Inactive', 'Standby');
            //$field->setPicklistValues($pickListValues);

            echo "Поле успешно добавлено.";
        }
    } else {
        echo "Не найден блок. Сверьте название блока в таблице vtiger_blocks.";
    }
} else {
    echo "Не найден модуль. Сверьте название модуля в таблице vtiger_tab.";
}
​
?>