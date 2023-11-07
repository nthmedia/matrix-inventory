<?php

namespace nthmedia\MatrixInventory\controllers;

use Carbon\Carbon;
use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use nthmedia\MatrixInventory\MatrixInventoryPlugin;
use nthmedia\MatrixInventory\settings\Settings;
use yii\base\Exception;
use yii\web\Response;

class MatrixInventoryController extends Controller
{
    protected array|int|bool $allowAnonymous = false;
    protected ?Settings $settings = null;

    public function beforeAction($action): bool
    {
        $this->settings = MatrixInventoryPlugin::getInstance()->settings;
        return parent::beforeAction($action);
    }

    public function actionIndex(): Response
    {
        return $this->renderTemplate('matrix-inventory/index', [
            'matrixFields' =>  $this->settings->matrixFields
        ]);
    }

    public function actionMatrix(string $key): Response
    {
        $matrixField = $this->settings->matrixFields[strtolower($key)] ?? throw new Exception("Matrix with key '${key}' not found.");

        $query = $this->getMatrixDataQuery($matrixField->fieldId, $matrixField->tableName);

        $result = collect($query->queryAll())
            ->groupBy(['handle', 'siteId', function ($item) {
                return $item['uri'] ?? "[".$item['slug']."]";
            }])
            ->map(function ($item) {
                return $item->map(function ($item) {
                    return $item->sortKeys();
                });
            })
            ->toArray();

        return $this->renderTemplate('matrix-inventory/matrix', [
            'matrixField' => $matrixField,
            'blocks' => $result,
            'sites' => $this->settings->sites
        ]);
    }

    public function actionEditEntry(int $siteId, int $id): Response
    {
        $entry = Entry::find()->id($id)->siteId($siteId)->one();

        if ($entry === null) {
            throw new Exception("Entry not found");
        }

        return $this->redirect($entry->cpEditUrl);
    }

    private function getMatrixDataQuery(int $fieldId, string $matrixTableName)
    {
        $query = Craft::$app->db->createCommand("
            SELECT
                matrixquery.entryId,
                matrixquery.siteId,
                matrixquery.handle,
                entryquery.uri,
                entryquery.slug
            FROM (
                SELECT
                    matrixblocks.primaryOwnerId AS entryId,
                    elements_sites.siteId,
                    matrixblocktypes.handle
                FROM
                    `elements` `elements`
                    INNER JOIN `matrixblocks` `matrixblocks` ON `matrixblocks`.`id` = `elements`.`id`
                        
                        
                        
                    INNER JOIN `elements_sites` `elements_sites` ON `elements_sites`.`elementId` = `elements`.`id`
                    INNER JOIN ". $matrixTableName . " as `content` ON (`content`.`elementId` = `elements`.`id`)
                        AND(`content`.`siteId` = `elements_sites`.`siteId`)
                        INNER JOIN `matrixblocktypes` `matrixblocktypes` ON `matrixblocks`.`typeId` = `matrixblocktypes`.`id`
                    WHERE (`matrixblocks`.`fieldId` = :fieldId)
                    AND(`elements`.`archived` = FALSE)
                    AND((`elements`.`enabled` = TRUE)
                    AND(`elements_sites`.`enabled` = TRUE))
                    AND(`elements`.`dateDeleted` IS NULL)
                    AND(`elements`.`draftId` IS NULL)
                    AND(`elements`.`revisionId` IS NULL)) AS `matrixquery`
                JOIN (
                    SELECT
                        `elements`.`id` AS `elementsId`, 
                        `elements_sites`.`slug`,
                        `elements_sites`.`uri`,
                        `elements_sites`.`siteId`
                    FROM
                        `elements` `elements`
                        INNER JOIN `entries` `entries` ON `entries`.`id` = `elements`.`id`
                        INNER JOIN `elements_sites` `elements_sites` ON `elements_sites`.`elementId` = `elements`.`id`
                        INNER JOIN `content` `content` ON (`content`.`elementId` = `elements`.`id`)
                            AND(`content`.`siteId` = `elements_sites`.`siteId`)
                        LEFT JOIN `structureelements` `structureelements` ON (`structureelements`.`elementId` = `elements`.`id`)
                            AND(EXISTS (
                                    SELECT
                                        * FROM `structures`
                                WHERE (`id` = `structureelements`.`structureId`)
                                AND(`dateDeleted` IS NULL)))
                        WHERE (`elements`.`archived` = FALSE)
                        AND(
                            ((`elements`.`enabled` = TRUE) AND(`elements_sites`.`enabled` = TRUE))
                            AND(`entries`.`postDate` <= :now)
                            AND((`entries`.`expiryDate` IS NULL) OR(`entries`.`expiryDate` > :now))
                        )
                        AND(`elements`.`dateDeleted` IS NULL)
                        AND(`elements`.`draftId` IS NULL)
                        AND(`elements`.`revisionId` IS NULL)) 
                AS `entryquery` ON (matrixquery.entryId = entryquery.elementsId
                    AND matrixquery.siteId = entryquery.siteId)",
            [
                "fieldId" => $fieldId,
                "now" => (string) Carbon::now()
            ]);

        return $query;
    }
}