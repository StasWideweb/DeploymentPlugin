<?php

namespace wideweb\deployment;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class DeploymentBundle extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@wideweb/deployment/resources';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/deployment.js',
        ];
        $this->css = [
            'css/deployment.css',
        ];

        parent::init();
    }
}