<?php

namespace Jacob\Logbook;

use Jacob\LogBook\FormWidgets\LogBook;
use Jacob\Logbook\ReportWidgets\LogBookModelChanges;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name' => 'Logbook',
            'description' => 'Creates a logbook based on changes in a model',
            'author' => 'Jacob',
            'icon' => 'icon-leaf'
        ];
    }

    public function registerFormWidgets(): array
    {
        return [
            LogBook::class => 'jacob_logbook_log',
        ];
    }

    public function registerReportWidgets(): array
    {
        return [
            LogBookModelChanges::class => [
                'label' => 'Logbook of changes in a model'
            ],
        ];
    }
}
