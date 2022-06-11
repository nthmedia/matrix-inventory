<?php

namespace nthmedia\MatrixInventory;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;

class MatrixInventoryPlugin extends Plugin
{
    /**
     * @var MatrixInventoryPlugin
     */
    public static $plugin;

    /**
     * @var array
     */
    public $settings;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * @var bool
     */
    public $hasCpSection = true;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        $this->settings = Craft::$app->config->getConfigFromFile('matrix-inventory');

        $this->registerCPRules();
    }

    private function registerCPRules()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['GET matrix-inventory'] = 'matrix-inventory/matrix-inventory/index';
                $event->rules['GET matrix-inventory\/view\/<key:[A-Za-z]+>'] = 'matrix-inventory/matrix-inventory/matrix';
                $event->rules['GET matrix-inventory\/redirect\/<siteId:[0-9]+>\/<id:[0-9]+>'] = 'matrix-inventory/matrix-inventory/edit-entry';
            }
        );
    }

    public function getCpNavItem()
    {
        $item = parent::getCpNavItem();
        $item['label'] = 'Matrix Inventory';

        $item['subnav'] = [
            'main' => [
                'label' => 'Dashboard',
                'url' => 'matrix-inventory',
            ],
        ];

        foreach ($this->settings['matrixFields'] as $key => $option) {
            $item['subnav']['matrix-inventory-' . $key] = [
                'label' => $option['label'],
                'url' => 'matrix-inventory/view/' . $key,
            ];
        }

        return $item;
    }
}
