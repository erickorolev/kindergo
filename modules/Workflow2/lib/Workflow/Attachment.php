<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */
namespace Workflow;
abstract class Attachment extends Extendable
{
    /**
     * Store new Files as Attachments Record
     */
    const MODE_ADD_NEW_ATTACHMENTS = 'MODE_ADD_NEW_ATTACHMENTS';

    /**
     * Only store files temporarily if not already stored as attachments
     */
    const MODE_NOT_ADD_NEW_ATTACHMENTS = 'MODE_NOT_ADD_NEW_ATTACHMENTS';

    protected $_mode = Attachment::MODE_ADD_NEW_ATTACHMENTS;
    private $attachments = array();

    public static function init() {
        self::_init(dirname(__FILE__).'/../../extends/attachments/');
    }
    public function isActive($moduleName) {
        return true;
    }
    public static function getAvailableOptions($moduleName) {
        $items = self::getItems();

        $return = array();
        foreach($items as $item) {
            if($item->isActive($moduleName) !== true) continue;
            /**
             * @var Attachment $item
             */
            $configs = $item->getConfigurations($moduleName);

            foreach($configs as $file) {
                $return[] = $file;
            }
        }

        return $return;
    }

    /**
     * @param $context
     * @param $key
     * @param $value
     *
     * return array(array('ID|PATH', 'ID or path to file', ['filename' => '']))
     */
    public static function getAttachments($key, $value, $context, $mode = self::MODE_ADD_NEW_ATTACHMENTS) {

        $tmpParts = explode('#', $key);
        /**
         * @var $item Attachment
         */
        $item = self::getItem($tmpParts[0]);

        $item->setMode($mode);

        if($item === false) {
            return array();
        }

        $item->clearAttachmentRecords();

        $item->generateAttachments($tmpParts[1], $value, $context, $mode);

        $attachments = $item->getAttachmentRecords();

        return $attachments;
    }

    // return array(array('ID|PATH', 'ID or path to file', ['filename', 'filetype', ...]))
    abstract public function generateAttachments($context, $key, $value);

    /**
     * return array(array('<html>','<script>'), array('<html>','<script>'))
     * @param $moduleName
     * @return mixed
     */
    abstract public function getConfigurations($moduleName);

    public function isAvailable($moduleName) {
        return true;
    }
    public function setMode($mode) {
        $this->_mode = $mode;
    }
    public function clearAttachmentRecords() {
        $this->attachments = array();
    }
    public function addAttachmentRecord($mode, $value, $filename = null) {
        $this->attachments[] = array($mode, $value, array('filename' => $filename));
    }
    public function getAttachmentRecords() {
        return $this->attachments;
    }

}

?>