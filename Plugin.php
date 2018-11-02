<?php namespace Jacob\Logbook;

use Jacob\LogBook\FormWidgets\LogBook;
use Jacob\Logbook\ReportWidgets\LogBookModelChanges;
use System\Classes\PluginBase;

/**
 * Logbook Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Logbook',
            'description' => 'Creates a logbook based on changes in a model',
            'author'      => 'Jacob',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register form widgets
     *
     * @return array
     */
    public function registerFormWidgets()
    {
        return [
            LogBook::class => 'jacob_logbook_log',
        ];
    }

    /**
     * Register report widgets
     * @return array
     */
    public function registerReportWidgets()
    {
        return [
            LogBookModelChanges::class => [
                'label'   => 'Logbook of changes in a model'
            ],
        ];
    }
}
