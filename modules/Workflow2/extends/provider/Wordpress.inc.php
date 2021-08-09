<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Stefan Warnat <support@stefanwarnat.de>
 * Date: 20.09.14 23:15
 * You must not use this file without permission.
 */
namespace Workflow\Plugins\ConnectionProvider;

use Workflow\OAuth;
use Workflow\VtUtils;

/**
 * Class WfTaskCommunicateSMS
 *
 * @method int SMS() SMS(array $data)
 * @method int SMS_check() SMS_check(array $data)
 * @method array filterDataField(string $method, array $config)
 * @method int FAX() FAX(array $data)
 * @method int FAX_check() FAX_check(array $data)
 */
class Wordpress extends \Workflow\ConnectionProvider {
    protected $_title = 'Wordpress REST';

    protected $OAuthEnabled = false;


    /*
        protected $configFields = array (
            'provider' => array (
                'label' => 'Provider',
                'type' => 'picklist',
                'readonly' => true,
                'options' => array(),
                'description' => 'Which Communication provider do you use?'
            ),
        );
    */

    /**
     * @throws \Exception
     */
    public function renderExtraBackend($data) {

    }

    private function getCurl($endpoint, $namespace = 'wp/v2') {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->getEndpoint($namespace) . $endpoint);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $header = array();

        //$header[] = 'Content-Type: application/json';
//        $header[] = 'Content-Type: multipart/form-data';
        $header[] = 'Authorization: Basic ' . base64_encode($this->get('username') . ':' . $this->get('password'));

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        return $ch;
    }
    public function request($method = "GET", $endpoint, $params = array(), $namespace = 'wp/v2') {
        $ch = $this->getCurl($endpoint, $namespace);

        if($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);

            if(!empty($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
        }

        $response = curl_exec($ch);
        $responseJSON = json_decode($response, true);

        if(empty($responseJSON) && !empty($response)) {
            return $response;
        }
        if(!empty($responseJSON['code'])) {
            throw new \Exception($responseJSON['message']);
        }

        return $responseJSON;
    }

    public function test() {
        $response = $this->request("GET", "settings");

        return true;
    }

    public function getAvailablePostTypes() {
        $response = $this->request("GET", "types");

        $return = array();
        foreach($response as $postType => $postData) {
            $return[$postData['rest_base']] = array(
                'name' => $postData['name'],
                'slug' => $postData['slug'],
                'taxonomies' => $postData['taxonomies']
            );
        }

        return $return;
    }

    public function pushPost($post) {
        $parameters = array(
            'title' => $post['post_title'],
            'status' => $post['post_status'],
            'content' => $post['post_content'],
            'excerpt' => $post['post_excerpt'],
            'slug' => $post['post_name'],
            'meta' => array(),
        );

        if(!empty($post['taxonomy'])) {
            foreach ($post['taxonomy'] as $slug => $value) {
                if(preg_match('/[0-9]+/', $value)) $value = intval($value);
                $parameters[$slug] = $value;
            }
        }

        if(!empty($post['post_meta'])) {
            foreach ($post['post_meta'] as $meta_key => $meta_value) {
                $parameters['custom_fields'][$meta_key] = $meta_value;
            }
        }

        if(!empty($post['post_id'])) {
            $response = $this->request('POST', $post['post_type'] . '/' . $post['post_id'], $parameters);
        } else {
            $response = $this->request('POST', $post['post_type'], $parameters);
        }

        return $response;
    }

    public function getPostStatus() {
        $response = $this->request("GET", "statuses");

        $return = array();
        foreach($response as $postType => $postData) {
            $return[$postData['slug']] = array(
                'name' => $postData['name'],
                'slug' => $postData['slug'],
            );
        }

        return $return;
    }

    public function getMetaKeys($postType) {
        $response = $this->request("GET", "restapi/".$postType.'/metakeys');

        return $response;
    }

    public function getTaxonomy($taxonomy) {
        $response = $this->request("GET", "taxonomies/".$taxonomy);

        return array(
            'slug' => $taxonomy,
            'name' => $response['name'],
        );
    }

    public function applyConfiguration(CommunicationPlugin $provider) {

    }

    public function getConfigFields()
    {
        return array_merge($this->configFields, array(
            'server' => array(
                'label' => 'URL to Wordpress',
                'type' => 'text',
            ),
            'username' => array(
                'label' => 'Username to login',
                'type' => 'text',
            ),
            'password' => array(
                'label' => 'Application password',
                'type' => 'password',
            ),
        ));
    }

    public function getEndpoint($namespace = 'wp/v2') {
        $url = trim($this->get('server'), '/') . '/wp-json/'.$namespace.'/';

        return $url;
    }

}

\Workflow\ConnectionProvider::register('wordpress-rest', '\Workflow\Plugins\ConnectionProvider\Wordpress');