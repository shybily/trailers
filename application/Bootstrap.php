<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initView() {
        $options = $this->getOptions();
        $viewOptions = $options['resources']['view'];
        $view = new Zend_View($viewOptions);

        if (!empty($viewOptions['params'])) {
            foreach ($viewOptions['params'] as $key => $value) {
                $view->$key = $value;
            }
        }

        $view->env = $this->getEnvironment();
        $view->doctype('XHTML1_TRANSITIONAL');
        $view->bootstrap = $this;

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setViewSuffix("xml");
        $viewRenderer->setView($view);

        return $view;
    }

    protected function _initRouter() {

        $this->bootstrap('frontcontroller');
        $front = $this->getResource('frontcontroller');
        $router = $front->getRouter();
        $config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/router.ini", "production");
        $router->addConfig($config, 'routes');

        return $router;
    }

}