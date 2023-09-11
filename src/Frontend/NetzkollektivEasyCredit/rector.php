<?php
//declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeLevelSetList;

return static function (RectorConfig $rectorConfig) {
    $rectorConfig->sets([
        DowngradeLevelSetList::DOWN_TO_PHP_56
    ]);
    $rectorConfig->skip([
        __DIR__ . '/vendor/*',
    ]);
};
