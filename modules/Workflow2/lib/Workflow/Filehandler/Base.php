<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 22.05.14 13:39
 * You must not use this file without permission.
 */
namespace Workflow\Filehandler;

abstract class Base
{
    protected $filepath = null;
    protected $position = null;
    protected $params = array();

    public function __construct($filepath, $position, $params) {
        $this->filepath = $filepath;
        $this->position = $position;
        $this->params = $params;
    }

    abstract public function init();
    abstract public function getNextRow();
    abstract public function resetPosition();
    abstract public function getTotalRows();
}

?>