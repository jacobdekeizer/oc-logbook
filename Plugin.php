<?php namespace Jacob\Logbook;

use Jacob\LogBook\FormWidgets\LogBook;
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

    public function registerFormWidgets()
    {
        return [
            LogBook::class => 'jacob_logbook_log',
        ];
    }
}
