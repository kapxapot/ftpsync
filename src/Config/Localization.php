<?php

namespace App\Config;

use Plasticode\Config\Localization as LocalizationBase;

class Localization extends LocalizationBase
{
    protected function ru()
    {
        return array_merge(
            parent::ru(),
            [
                'primary_id' => 'Источник',
                'replica_id' => 'Копия',
            ]
        );
    }
}
