<?php

namespace nthmedia\MatrixInventory\settings;

class MatrixFieldSettings
{
    public string $key;
    public string $label;
    public int $fieldId;
    public string $tableName;

    public function __construct(array $options) {
        $this->key = $options['key'];
        $this->label = $options['label'];
        $this->fieldId = $options['fieldId'];
        $this->tableName = $options['tableName'];
    }
}