<?php namespace Jacob\LogBook\FormWidgets;

use Backend\Classes\FormWidgetBase;

/**
 * LogBook Form Widget
 */
class LogBook extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'jacob_logbook_log';

    /**
     * @var int $limitPerPage The amount of log items per page
     */
    public $limitPerPage = 20;

    /** @var int $startPage The page number to start to show log items */
    public $startPage = 1;

    /** @var array|string|null  */
    public $showLogRelations = null;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->fillFromConfig([
            'limitPerPage',
            'startPage',
            'showLogRelations',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        $this->prepareLogs();

        return $this->makePartial('default');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        if ($this->showLogRelations !== null) {
            if (!is_array($this->showLogRelations)) {
                $relation = $this->showLogRelations;
                $this->showLogRelations = [$relation];
            }
        }

        $this->vars['name'] = $this->formField->getName();
        $this->vars['model'] = $this->model;
    }

    /**
     * Prepare the log items
     */
    public function prepareLogs()
    {
        $this->vars['logs'] = $this->model->getLogsFromLogBook(
            $this->limitPerPage,
            $this->startPage,
            $this->showLogRelations
        );
    }

    /**
     * Change log book page
     *
     * @return array
     */
    public function onLogBookChangePage()
    {
        $page = (int)post('page');
        $this->prepareVars();
        $this->vars['logs'] = $this->model->getLogsFromLogBook(
            $this->limitPerPage,
            $page,
            $this->showLogRelations
        );

        return [
            '#jacob-logbook' => $this->makePartial('logbook'),
        ];
    }

    /**
     * @param mixed $value
     * @return null|string
     */
    public function getSaveValue($value)
    {
        return null;
    }
}
