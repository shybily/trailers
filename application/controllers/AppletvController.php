<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppletvController
 *
 * @author shybily
 */
class AppletvController extends Zend_Controller_Action {

    private $_front;

    public function init() {
//        $this->_front = Zend_Controller_Front::getInstance();
//        $this->_front->setParam("noViewRenderer", true);
    }

    public function indexAction() {
        helper::setHeader("xml");
    }

    public function applicationAction() {
        $this->_front = Zend_Controller_Front::getInstance();
        $this->_front->setParam("noViewRenderer", true);
        helper::setHeader("js");
        echo $this->view->render("appletv/application.js");
    }

    public function testAction() {
        $this->_front = Zend_Controller_Front::getInstance();
        $this->_front->setParam("noViewRenderer", true);

        echo $this->view->render("appletv/test.html");
    }

}

?>
