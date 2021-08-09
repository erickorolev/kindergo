<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 14.12.14 19:54
 * You must not use this file without permission.
 */
namespace Workflow;

interface UserQueueInterface
{
    /**
     * Return the Config, which could be used to build the frontend
     * @param $data
     * @param $context
     * @return mixed
     */
    public function exportUserQueue($data, $context);

    /**
     * Should Return the HTML Form, which will be displayed to the User
     *
     * @param $data
     * @param $context
     * @return mixed
     */
    public static function generateUserQueueHTML($config, $context);
}

?>