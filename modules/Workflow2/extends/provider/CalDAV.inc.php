<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\ConnectionProvider;

use Workflow\VtUtils;

class CalDAV extends \Workflow\ConnectionProvider {
    protected $_title = 'CalDAV Connection';
    private $_connection = null;

    protected $configFields = array(
        /*'default' => array(
            'label' => 'Default method<br/>for all Workflow Mails',
            'type' => 'checkbox'
        )*/
    );

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) {

    }

    public function getConfigFields()
    {
        return array_merge($this->configFields, array(
            'server' => array(
                'label' => 'CalDAV URL',
                'type' => 'text',
            ),
            'validcert' => array(
                'label' => 'Validate SSL/TLS Cert',
                'type' => 'checkbox'
            ),
            'username' => array(
                'label' => 'CalDAV Auth Username',
                'type' => 'text'
            ),
            'password' => array(
                'label' => 'CalDAV Auth Password',
                'type' => 'password'
            ),
        ));
    }

    /**
     * @return \it\thecsea\simple_caldav_client\SimpleCalDAVClient
     * @throws \it\thecsea\simple_caldav_client\CalDAVException
     */
    public function getCalDAVClient() {
        $path = VtUtils::getAdditionalPath('caldav');
        require_once($path . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

        $client = new \it\thecsea\simple_caldav_client\SimpleCalDAVClient();

        $client->connect($this->get('server'), $this->get('username'), $this->get('password'));

        return $client;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function test() {
        if(extension_loaded('curl') === false) {
            throw new \Exception('php-curl Extension is required');
        }

        if(version_compare (phpversion(), '5.4.0') < 0) {
            throw new \Exception('PHP Version 5.4 is required');
        }

        try {

            $this->getCalDAVClient();

        } catch (\Exception $exp) {
            throw new \Exception ($exp->getMessage());
        }

        return true;
    }

    public function getFolderObject($folder) {
        $connection = $this->getImapConnection();

        return $connection->getMailbox($folder);
    }

    public function getFolder() {
        $connection = $this->getImapConnection();

        $mailboxes = $connection->getMailboxes();

        $return = array();
        foreach ($mailboxes as $mailbox) {
            $return[] = array(
                'name' => $mailbox->getName(),
                'messages' => $mailbox->count()
            );
        }

        return $return;
    }
}

\Workflow\ConnectionProvider::register('caldav', '\Workflow\Plugins\ConnectionProvider\CalDAV');