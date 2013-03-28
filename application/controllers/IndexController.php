<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        // action body
    }
    
    public function testAction(){
        $front = Zend_Controller_Front::getInstance();
        $front->setParam("noViewRenderer", true);
        echo "test";
        
    }

}

