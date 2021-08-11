<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\ConnectionProvider;

use Workflow\CommunicationPlugin;
/**
 * Class WfTaskCommunicateSMS
 *
 * @method int SMS() SMS(array $data)
 * @method int SMS_check() SMS_check(array $data)
 * @method array filterDataField(string $method, array $config)
 * @method int FAX() FAX(array $data)
 * @method int FAX_check() FAX_check(array $data)
 */
class Communicate extends \Workflow\ConnectionProvider {
    protected $_title = 'Communicate';

    protected $configFields = array (
        'provider' => array (
            'label' => 'Provider',
            'type' => 'picklist',
            'readonly' => true,
            'options' => array(),
            'description' => 'Which Communication provider do you use?'
        ),
    );

    public function getAvailableSubProvider() {
        $plugins = CommunicationPlugin::getAvailableProvider();
        return $plugins;
    }

    /**
     * @throws Exception
     */
    public function renderExtraBackend($data) { }

    public function __call($methodName, $arguments) {
        /**
         * @var $plugin CommunicationPlugin
         */
        $plugin = CommunicationPlugin::getItem($this->getSubProvider());
        $this->applyConfiguration($plugin);

        return call_user_func_array(array($plugin, $methodName), $arguments);
    }

    public function test() {
        try {
            /**
             * @var $plugin CommunicationPlugin
             */
            $plugin = CommunicationPlugin::getItem($this->getSubProvider());
            $this->applyConfiguration($plugin);
            $plugin->test();
        } catch (\Exception $exp) {
            throw new \Exception ($exp->getMessage());
        }

        return true;
    }

    public function applyConfiguration(CommunicationPlugin $provider) {
        $configFields = $this->getConfigFields();
        $keys = array_keys($configFields);
        $config = array();
        foreach($keys as $key) {
            $config[$key] = $this->get($key);
        }
        $provider->setConfiguration($config);
    }

    public function getConfigFields()
    {
        return CommunicationPlugin::getItem($this->getSubProvider())->getConfigFields();
    }

}

\Workflow\ConnectionProvider::register('communicate', '\Workflow\Plugins\ConnectionProvider\Communicate');