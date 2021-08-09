<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskWppushrecord extends \Workflow\Task
{
    protected $_envSettings = array("post_id", "post_url");
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
            $this->_SC->setColumnCount(2);

            $this->_SC->addFields('providerid', 'Wordpress Provider', 'provider', array(
                'provider' => 'wordpress-rest'
            ));

            if($this->_SC->has('providerid')) {
                /**
                 * @var $provider \Workflow\Plugins\ConnectionProvider\Wordpress
                 */
                $provider = \Workflow\ConnectionProvider::getConnection($this->_SC->get('providerid'));

                $postTypes = $provider->getAvailablePostTypes();

                $select = array();
                foreach($postTypes as $restBase => $type) {
                    $select[$restBase] = $type['name'];
                }

                $this->_SC->addFields('post_type', 'Post Type', 'select', array(
                    'options' => $select
                ));

                $this->_SC->addFields('post_id', 'Post ID to update', 'template');

                if($this->_SC->has('post_type')) {
                    $postType = $this->_SC->get('post_type');
                    $postData = $postTypes[$postType];

                    $this->_SC->addHeadline('Post Type Settings - General');
                    $this->_SC->addFields('post_title', 'Post title', 'template');

                    $status = $provider->getPostStatus();
                    $select = array();
                    foreach($status as $restBase => $type) {
                        $select[$restBase] = $type['name'];
                    }

                    $this->_SC->addFields('post_status', 'Post Status', 'select', array(
                        'options' => $select
                    ));

                    $this->_SC->addFields('post_name', 'Permalink', 'template');
                    $this->_SC->addField('hint', 'Only lowercase chars, numbers, and dashes.<br/>Your must make sure, this postname isunique in Wordpress Otherwise an index will append', 'readonly');

                    $this->_SC->addFields('post_excerpt', 'Post Excerpt', 'textarea');
                    $this->_SC->addFields('post_content', 'Post Content', 'textarea');

                    $this->_SC->addHeadline('Post Type Settings - Taxonomies');

                    $taxes = array();
                    foreach($postData['taxonomies'] as $taxSlug) {
                        $tax = $provider->getTaxonomy($taxSlug);

                        $taxes[] = $tax['slug'];
                        $this->_SC->addFields('tax_'.$tax['slug'].'', $tax['name'], 'template');
                        
                    }

                    $this->_SC->addFields('taxlist', '', 'hidden', array('default' => implode('###', $taxes)));
                }
            }
/*            $this->_SC->addFields('url', 'URL Wordpress System', 'template', array(
                //'description' => '(optional)'
            ));
            $this->_SC->addFields('password', 'Password', 'template', array(
                //'description' => '(optional)'
            ));*/
        }

        $this->mainValueList = $this->addPreset('ValueList', 'mainvaluelist', array(
            'module' => $this->getModuleName(),
            'placeholder_key' => 'Meta Key',
            'placeholder_value' => 'Meta Value',
        ));

        if(!empty($_POST['loadpostmeta'])) {
            $valueList = $this->get('mainvaluelist');
            $metaKeys = $provider->getMetaKeys($postType);

            $given = array();
            foreach($valueList as $data) {
                $given[$data['label']] = $data;
            }
            $set = array_unique(array_merge(array_keys($given), $metaKeys, array_keys($given)));

            $init = array();
            foreach($set as $key) {
                if(isset($given[$key])) {
                    $init[] = $given[$key];
                } else {
                    $init[] = array(
                        'label' => $key,
                        'mode' => 'value',
                        'value' => '',
                    );
                }
            }
            $this->set('mainvaluelist', $init);
            //$this->mainValueList->setParameter();
        }

    }

    public function handleTask(&$context) {
		/* Insert here source code to execute the task */

        if($this->_SC->has('providerid') && $this->_SC->has('post_type')) {
            /**
             * @var $provider \Workflow\Plugins\ConnectionProvider\Wordpress
             */
            $provider = \Workflow\ConnectionProvider::getConnection($this->_SC->get('providerid'));

            $data = array(
                'post_id' => $this->_SC->get('post_id'),
                'post_type' => $this->_SC->get('post_type'),
                'post_title' => $this->_SC->get('post_title'),
                'post_status' => $this->_SC->get('post_status'),
                'post_name' => $this->_SC->get('post_name'),
                'post_content' => $this->_SC->get('post_content'),
                'post_excerpt' => $this->_SC->get('post_excerpt'),
                'post_meta' => array()
            );

            $valueList = $this->mainValueList->getList();

            foreach($valueList as $field) {

                switch($field['mode']) {
                    case 'function':
                        $parser = new \Workflow\ExpressionParser($field['value'], $context, false); # Last Parameter = DEBUG

                        try {
                            $parser->run();
                        } catch(\Workflow\ExpressionException $exp) {
                            Workflow2::error_handler(E_EXPRESSION_ERROR, $exp->getMessage(), "", "");
                        }

                        $data['post_meta'][$field['label']] = $parser->getReturn();
                        break;
                    default:
                        $data['post_meta'][$field['label']] = \Workflow\VTTemplate::parse($field['value'], $context);
                        break;
                }
            }

            $taxList = $this->_SC->get('taxlist');
            if(!empty($taxList)) {
                $parts = explode('###', $taxList);
                foreach($parts as $taxonomy) {
                    $data['taxonomy'][$taxonomy] = $this->_SC->get('tax_'.$taxonomy);
                }
            }

            $post = $provider->pushPost($data);

            $context->setEnvironment('post_id', $post['id'], $this);
            $context->setEnvironment('post_url', $post['permalink'], $this);
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
