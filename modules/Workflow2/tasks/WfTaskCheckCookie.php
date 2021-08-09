<?php
require_once(realpath(dirname(__FILE__).'/../autoload_wf.php'));

class WfTaskCheckCookie extends \Workflow\Task
{
    public function handleTask(&$context) {
        $cookiename = $this->get('cookiename');
        $cookievalue = $this->get('cookievalue');

        if(!empty($_COOKIE[$cookiename]) && $_COOKIE[$cookiename] == $cookievalue) {
            return 'yes';
        } else {
            if(!empty($_COOKIE[$cookiename])) {
                $this->addStat('Required cookie value: '.$cookievalue);
                $this->addStat('Existing cookie value: '.$_COOKIE[$cookiename]);
            } else {
                $this->addStat('Cookie do not existing');
            }
            return 'no';
        }
    }
	
    public function beforeGetTaskform($viewer) {
        $cookiename = $this->get('cookiename');
        $cookievalue = $this->get('cookievalue');

        if(empty($cookiename) || $cookiename == -1) {
            $this->set('cookiename', $cookiename = 'cookie_'.$this->getBlockId());
        }
        if(empty($cookievalue) || $cookievalue == -1) {
            $this->set('cookievalue', $cookievalue = '1');
        }

        if(!empty($_COOKIE[$cookiename]) && $_COOKIE[$cookiename] == $cookievalue) {
            $viewer->assign('cookie_exists', true);
        }

		/* Insert here source code to create custom configurations pages */
    }	
    public function beforeSave(&$values)
    {
        /* Insert here source code to modify the values the user submit on configuration */
    }
    public function afterSave() {
        if(!empty($_REQUEST['setCookieAction'])) {
            $cookiename = $this->get('cookiename');
            $cookievalue = $this->get('cookievalue');

            if($_REQUEST['setCookieAction'] == '1') {
                setcookie($cookiename, $cookievalue, time() + 3600, '/');
                $_COOKIE[$cookiename] = $cookievalue;
            }
            if($_REQUEST['setCookieAction'] == '2') {
                setcookie($cookiename, '', time() - 3600, '/');
                unset($_COOKIE[$cookiename]);
            }
        }
    }
}
