<?php

namespace nthmedia\MatrixInventory\utilities;

use Craft;
use craft\base\Utility;
use nthmedia\MatrixInventory\MatrixInventoryPlugin;

class MatrixInventoryUtility extends Utility
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('matrix-inventory', 'Matrix Inventory');
    }

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'matrix-inventory';
    }

    /**
     * @inheritdoc
     */
    public static function iconPath(): ?string
    {
        // Set the icon mask path
        $iconPath = Craft::getAlias('@vendor/nthmedia/matrix-inventory/src/icon-mask.svg');

        // If not a string, bail
        if (!is_string($iconPath)) {
            return null;
        }

        // Return the icon mask path
        return $iconPath;
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('matrix-inventory/index', [
            'matrixFields' =>  MatrixInventoryPlugin::getInstance()->settings->matrixFields
        ]);
        // Render the utility template
//        return Craft::$app->getView()->renderTemplate('matrix-inventory/index');
    }

}