<?php namespace Jacob\LogBook\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Illuminate\Routing\Redirector;
use Jacob\LogBook\Classes\Entities\Changes;
use Jacob\Logbook\Models\Log;

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

    /** @var bool $showUndoChangeButton */
    public $showUndoChangesButton = true;

    /** @var bool $refreshFromAfterUndo */
    public $refreshFormAfterUndo = true;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->fillFromConfig([
            'limitPerPage',
            'startPage',
            'showLogRelations',
            'showUndoChangesButton',
            'refreshFormAfterUndo',
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
            $this->showLogRelations = (array) $this->showLogRelations;
        }

        $this->vars['name'] = $this->formField->getName();
        $this->vars['model'] = $this->model;
        $this->vars['showUndoChangesButton'] = $this->showUndoChangesButton;
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
        $page = (int)post('page', 1);
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
     * Undo change from logbook
     *
     * @return mixed
     * @throws \Exception
     */
    public function onLogBookUndoChange()
    {
        $id = post('id', null);

        /** @var Log $log */
        $log = Log::find($id);

        if (!$log || ($log->getAttribute('changes')['type'] ?? null) !== Changes::TYPE_UPDATED) {
            return $this->onLogBookChangePage();
        }

        /** @var \Model $modelInstance */
        $modelInstance = app($log->getAttribute('model'));

        /** @var \Model $changedModel */
        $changedModel =  $modelInstance->find($log->getAttribute('model_key'));

        /** @var array $changedAttribute */
        foreach ($log->getAttribute('changes')['changedAttributes'] ?? [] as $changedAttribute) {
            $changedModel->setAttribute($changedAttribute['column'], $changedAttribute['old']);
        }

        $changedModel->save();

        if ($this->refreshFormAfterUndo) {
            /** @var Redirector $redirect */
            $redirect = resolve(Redirector::class);
            return $redirect->refresh();
        }

        return $this->onLogBookChangePage();
    }

    /**
     * @param mixed $value
     * @return null|string
     */
    public function getSaveValue($value)
    {
        return \Backend\Classes\FormField::NO_SAVE_DATA;
    }
}
