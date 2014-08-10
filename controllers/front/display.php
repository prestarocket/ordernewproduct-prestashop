<?php

class ordernewproductdisplayModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->setTemplate('display.tpl');
    }
}