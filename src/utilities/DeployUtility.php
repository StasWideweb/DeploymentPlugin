<?php

namespace wideweb\deployment\utilities;

use Craft;
use craft\base\Utility;
use wideweb\deployment\DeploymentBundle;

class DeployUtility extends Utility
{
    public static function displayName(): string
    {
        return Craft::t('app', 'Deployment');
    }

    public static function id(): string
    {
        return 'deploy-utility';
    }

    public static function iconPath(): ?string
    {
        return Craft::getAlias('@deploy/icon.svg');
    }

    public static function contentHtml(): string
    {
        Craft::$app->getView()->registerAssetBundle(DeploymentBundle::class);

        return Craft::$app->view->renderTemplate('deployment/_deploy');
    }
}
