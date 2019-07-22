<?php


namespace App\Controllers;


/**
 * Class Common
 * @package App\Controllers
 */
class Common
{
    protected $_app = null;
    protected $_em = null;
    protected $_user = null;

    /**
     * Common constructor.
     *
     * Stores global app variable for easy child access. Some objects are extracted from app for ease of use.
     *
     * @param $app
     */
    public function __construct($app)
    {
        $this->_app = $app;
        $this->_em = $app['orm.em'];
        $this->_user = $app['session']->get('user');
    }
}