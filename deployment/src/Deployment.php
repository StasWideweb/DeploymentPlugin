<?php

namespace wideweb\deployment;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\App;
use craft\services\Utilities;
use wideweb\deployment\utilities\DeployUtility;
use yii\base\Event;
use wideweb\deployment\models\Settings;

/**
 * Deployment plugin
 *
 * @method static Deployment getInstance()
 * @author Stas Kaif <s.osetskyi@wideweb.pro>
 * @copyright Stas Kaif
 * @license MIT
 */
class Deployment extends Plugin
{
    public static $plugin;
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITIES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = DeployUtility::class;
            }
        );

        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function() {
            // ...
        });
    }

    protected function createSettingsModel(): ?craft\base\Model
    {
        return new Settings();
    }

    public function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('deployment/_settings', [
            'settings' => $this->getSettings(),
        ]);
    }


    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/5.x/extend/events.html to get started)
    }

    public function getRoutes(): array
    {
        return [
            'deploy/status' => 'deployment/deploy/status',
            'deploy/step' => 'deployment/deploy/step',
        ];
    }
}
