<?php

namespace nthmedia\MatrixInventory\settings;

use Craft;
use yii\helpers\ArrayHelper;

class Settings
{
    /** @var SiteSettings[] */
    public array $sites;

    /** @var MatrixFieldSettings[] */
    public array $matrixFields;

    public function loadSettings(): self
    {
        $configFileContents = Craft::$app->config->getConfigFromFile('matrix-inventory');

        $this->sites = array_map(fn($item) => new SiteSettings($item), $configFileContents['sites'] ?? []);
        $this->matrixFields = array_map(fn($item) => new MatrixFieldSettings($item), $configFileContents['matrixFields'] ?? []);
        $this->matrixFields = ArrayHelper::index($this->matrixFields, 'key');

        return $this;
    }
}
