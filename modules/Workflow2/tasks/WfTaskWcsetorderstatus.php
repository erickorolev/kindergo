<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskWcsetorderstatus extends \Workflow\Task
{
    /**
     * @var \Workflow\Preset\SimpleConfig
     */
    private $_SC = null;

    /**
     * @var \Workflow\Preset\ValueList
     */
    private $mainValueList = null;

    public function init()
    {
        $this->_SC = $this->addPreset("SimpleConfig", "details", array(
            'templatename' => 'mainconfig',
        ));

        if($this->isConfiguration()) {
            $this->_SC->setColumnCount(1);

            $this->_SC->addFields('providerid', 'Woocommerce Provider', 'provider', array(
                'provider' => 'woocommerce-rest'
            ));

            if($this->_SC->has('providerid')) {
                $this->_SC->addFields('post_id', 'Product ID to update', 'template');

                $select = array(
                    'pending' => 'Zahlung ausstehend',
                    'processing' => 'In Bearbeitung',
                    'on-hold' => 'In Wartestellung',
                    'completed' => 'Fertiggestellt',
                    'cancelled' => 'Storniert/Abgebrochen',
                    'refunded ' => 'Rückerstattet',
                    'failed ' => 'Fehlgeschlagen',
                );

                $this->_SC->addFields('post_status', 'Post Status', 'select', array(
                    'options' => $select
                ));

            }
            /*            $this->_SC->addFields('url', 'URL Wordpress System', 'template', array(
                            //'description' => '(optional)'
                        ));
                        $this->_SC->addFields('password', 'Password', 'template', array(
                            //'description' => '(optional)'
                        ));*/
        }

    }

    public function handleTask(&$context) {
        /* Insert here source code to execute the task */

        if($this->_SC->has('providerid')) {
            /**
             * @var $provider \Workflow\Plugins\ConnectionProvider\Woocommerce
             */
            $provider = \Workflow\ConnectionProvider::getConnection($this->_SC->get('providerid'));

            $data = array(
                'status' => $this->_SC->get('post_status'),
            );

            $post = $provider->putPost($this->_SC->get('post_id'), $data);
        }

        return "yes";
    }

    public function beforeGetTaskform($viewer) {
        if($this->_SC->has('post_type')) {
            $viewer->assign('ShowMetaTags', true);
        } else {
            $viewer->assign('ShowMetaTags', true);
        }
        /* Insert here source code to create custom configurations pages */
    }
    public function beforeSave(&$values) {
        /* Insert here source code to modify the values the user submit on configuration */
    }
}
