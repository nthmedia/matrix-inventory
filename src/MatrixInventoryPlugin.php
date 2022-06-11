<?php

namespace nthmedia\MatrixInventory;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\View;
use nthmedia\MatrixInventory\settings\Settings;
use yii\base\Event;
use yii\base\Exception;

class MatrixInventoryPlugin extends Plugin
{
    /**
     * @var MatrixInventoryPlugin
     */
    public static $plugin;

    /** @var ?Settings */
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

        // Base template directory
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
            if (is_dir($baseDir = $this->getBasePath() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'templates')) {
                $e->roots[$this->id] = $baseDir;
            }
        });

        $this->registerCPRules();

        try {
            $this->settings = (new Settings())->loadSettings();
        } catch (\Throwable $exception) {
            throw new Exception("Invalid matrix-inventory configuration file structure");
        }
    }

    private function registerCPRules(): void
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

        foreach ($this->settings->matrixFields as $matrixField) {
            $item['subnav']['matrix-inventory-' . $matrixField->key] = [
                'label' => $matrixField->label,
                'url' => 'matrix-inventory/view/' . $matrixField->key,
            ];
        }

        return $item;
    }
}
