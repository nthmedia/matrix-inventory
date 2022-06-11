<?php

namespace nthmedia\MatrixInventory\settings;

class SiteSettings
{
    public string $label;
    public int $siteId;

    public function __construct(array $options) {
        $this->label = $options['label'];
        $this->siteId = $options['siteId'];
    }
}