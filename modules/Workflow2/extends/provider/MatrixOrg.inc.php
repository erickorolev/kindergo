<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\ConnectionProvider;

use Workflow\VtUtils;

class MatrixOrg extends \Workflow\ConnectionProvider {
    protected $_title = 'Matrix Server';
    private $_connection = null;

    protected $configFields = array(
        /*'default' => array(
            'label' => 'Default method<br/>for all Workflow Mails',
            'type' => 'checkbox'
        )*/
    );

    protected $js4Editor = '';

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) {

    }

    public function getConfigFields()
    {
        return array_merge($this->configFields, array(
            'server' => array(
                'label' => 'Hostname',
                'type' => 'text',
            ),
            'username' => array(
                'label' => 'Username',
                'type' => 'text'
            ),
            'password' => array(
                'label' => 'Password',
                'type' => 'password'
            ),
        ));
    }

    /**
     * @return \MatrixOrg\Client
     */
    public function getClient() {
        return new \MatrixOrg\Client($this->get('server'), $this->get('username'), $this->get('password'));
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function test() {
        $client = $this->getClient();

        $client->connect();
        return true;
    }

    public function getCurrentRooms() {
        $client = $this->getClient();

        return $client->getCurrentRooms();
    }

}


\Workflow2\Autoload::register('MatrixOrg', realpath(VtUtils::getAdditionalPath('matrix_org')));

\Workflow\ConnectionProvider::register('matrixserver', '\Workflow\Plugins\ConnectionProvider\MatrixOrg');