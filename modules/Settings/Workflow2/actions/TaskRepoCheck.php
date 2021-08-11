<?php
global $root_directory;
require_once($root_directory."/modules/Workflow2/autoload_wf.php");

class Settings_Workflow2_TaskRepoCheck_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        $adb = PearDatabase::getInstance();
        $response = new Vtiger_Response();
        $url = $request->get('url');

        try {
            if(!filter_var($url, FILTER_VALIDATE_URL))
            {
                throw new Exception("URL is not valid");
            }

            $mod = new \Workflow2();
            $params = array(
                'module' => 'Workflow2',
                'mod_version' => $mod->getVersion(),
                'releasepath' => 'stable',
                'licensehash' => '1',
            );

            $content = VtUtils::getContentFromUrl($url, $params, 'post');

            try {
                $root = new SimpleXMLElement($content);
            } catch(Exception $exp) {
                throw new Exception('no task repository');
            }

            if(isset($root->automatedLicense)) {
                $licenseKey = (string)$root->automatedLicense;
            } else {
                $licenseKey =  '';
            }

            if(empty($root->title)) {
                throw new Exception('no task repository (title missing)');
            }
            $needLicense = (string)$root->needLicense == "1";
            $response->setResult(array("success" => true, '_nonce' => sha1(vglobal('site_URL').$url.'0s-f,mäp'.(string)$root->title),'licenseKey' => $licenseKey, 'title' => (string)$root->title, 'license' => $needLicense));
        } catch(Exception $exp) {
            $response->setResult(array("success" => false, "error" => $exp->getMessage()));
        }

        $response->emit();
    }

    # Source: http://sansiba.com/tut_farbverlauf.htm
    private function create_gradient($steps, $color1, $color2)
    {
        if($steps == 1) {
            return array($color1);
        }
        if($steps == 2) {
            return array($color1, $color2);
        }
        $r1=hexdec(substr($color1,1,2));
        $g1=hexdec(substr($color1,3,2));
        $b1=hexdec(substr($color1,5,2));

        $r2=hexdec(substr($color2,1,2));
        $g2=hexdec(substr($color2,3,2));
        $b2=hexdec(substr($color2,5,2));

        $diff_r=$r2-$r1;
        $diff_g=$g2-$g1;
        $diff_b=$b2-$b1;

        $colors = array();
        for ($i=0; $i<$steps; $i++)
        {
            $factor=$i / $steps;

            $r=round($r1 + $diff_r * $factor);
            $g=round($g1 + $diff_g * $factor);
            $b=round($b1 + $diff_b * $factor);

            $color="#" . sprintf("%02X",$r) . sprintf("%02X",$g) . sprintf("%02X",$b);
            $colors[] = $color;
        }

        return $colors;
    }

}