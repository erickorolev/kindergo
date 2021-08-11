<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskWcpushproduct extends \Workflow\Task
{
    protected $_envSettings = array("post_id", 'post_url');
    /**
     * @var \Workflow\Preset\SimpleConfig
     */
    private $_SC = null;

    /**
     * @var \Workflow\Preset\ValueList
     */
    private $mainValueList = null;

    /**
     * @var \Workflow\Preset\ValueList
     */
    private $metaValueList = null;

    public function init()
    {
        $this->_SC = $this->addPreset("SimpleConfig", "details", array(
            'templatename' => 'mainconfig',
        ));

        if($this->isConfiguration()) {
            $this->_SC->setColumnCount(2);

            $this->_SC->addFields('providerid', 'Woocommerce Provider', 'provider', array(
                'provider' => 'woocommerce-rest'
            ));

            if($this->_SC->has('providerid')) {
                /**
                 * @var $provider \Workflow\Plugins\ConnectionProvider\Wordpress
                 */
                $provider = \Workflow\ConnectionProvider::getConnection($this->_SC->get('providerid'));

                $this->_SC->addFields('post_type', 'Product Type', 'select', array(
                    'options' => array('simple' => 'Simple Product')
                ));

                $this->_SC->addFields('post_id', 'Product ID to update', 'template');

                $postType = $this->_SC->get('post_type');

                $this->_SC->addHeadline('Post Type Settings - General');
                $this->_SC->addFields('post_title', 'Product name', 'template');

                $select = array(
                    'draft' => 'Draft',
                    'public' => 'Public',
                );
                $this->_SC->addFields('post_status', 'Post Status', 'select', array(
                    'options' => $select
                ));

                $this->_SC->addFields('post_name', 'Permalink', 'template');
                $this->_SC->addField('hint', 'Only lowercase chars, numbers, and dashes.<br/>Your must make sure, this postname isunique in Wordpress Otherwise an index will append', 'readonly');

                $this->_SC->addFields('post_excerpt', 'Product Excerpt', 'textarea');
                $this->_SC->addFields('post_content', 'Product Content', 'textarea');

               // $this->_SC->addHeadline('Post Type Settings - Taxonomies');

                $this->_SC->addHeadline('Product Settings');

                $categories = $provider->getProductCategories();
                $select = array();
                Foreach($categories as $category) {
                    $select[$category['id']] = $category['name'];
                }
                $this->_SC->addFields('categories', 'Product category', 'multiselect', array(
                    'options' => $select
                ));

                $this->_SC->addFields('price', 'Price', 'template');
                $this->_SC->addFields('sku', 'Article Nr.', 'template');
                $this->_SC->addFields('lager', 'Storage Quantity', 'template');


                //$this->_SC->addFields('taxlist', '', 'hidden', array('default' => implode('###', $taxes)));
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
            'placeholder_key' => 'Key',
            'placeholder_value' => 'Value',
        ));

        $this->metaValueList = $this->addPreset('ValueList', 'metavaluelist', array(
            'module' => $this->getModuleName(),
            'placeholder_key' => 'Meta Key',
            'placeholder_value' => 'Meta Value',
        ));

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
                'categories' => $this->_SC->get('categories'),
                'post_excerpt' => $this->_SC->get('post_excerpt'),
                'price' => $this->_SC->get('price'),
                'sku' => $this->_SC->get('sku'),
                'lager' => $this->_SC->get('lager'),
                'post_meta' => array(),
                'additional' => array(),
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

                        $data['additional'][$field['label']] = $parser->getReturn();
                        break;
                    default:
                        $data['additional'][$field['label']] = \Workflow\VTTemplate::parse($field['value'], $context);
                        break;
                }
            }

            $valueList = $this->metaValueList->getList();
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
