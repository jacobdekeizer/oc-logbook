<?php

namespace Jacob\LogBook\FormWidgets;

use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Jacob\LogBook\Classes\LoadRelation;
use Jacob\Logbook\Models\Log;
use Jacob\Logbook\Traits\LogChanges;
use October\Rain\Database\Model;
use Throwable;

/**
 * @property LogChanges|Model $model
 */
class LogBook extends FormWidgetBase
{
    /** @var int $limitPerPage The amount of log items per page */
    public $limitPerPage = 20;

    /** @var int $startPage The page number to start to show log items */
    public $startPage = 1;

    /** @var array|string|null  */
    public $showLogRelations = null;

    /** @var array|string|null */
    public $showSoftDeleteRelations = null;

    /** @var bool $showUndoChangeButton */
    public $showUndoChangesButton = true;

    /** @var bool $refreshFromAfterUndo */
    public $refreshFormAfterUndo = true;

    /** @var LengthAwarePaginator */
    public $logs;

    protected $defaultAlias = 'jacob_logbook_log';

    public function init(): void
    {
        $this->fillFromConfig([
            'limitPerPage',
            'startPage',
            'showLogRelations',
            'showSoftDeleteRelations',
            'relationsWithTrashed',
            'showUndoChangesButton',
            'refreshFormAfterUndo',
        ]);
    }

    public function render(): string
    {
        $this->prepareVars();

        $this->logs = $this->model->getLogsFromLogBook(
            $this->limitPerPage,
            $this->startPage,
            $this->getRelationsToLoad()
        );

        return $this->makePartial('default');
    }

    public function prepareVars(): void
    {
        if ($this->showLogRelations !== null) {
            $this->showLogRelations = (array) $this->showLogRelations;
        }

        if ($this->showSoftDeleteRelations !== null) {
            $this->showSoftDeleteRelations = (array) $this->showSoftDeleteRelations;
        }
    }

    public function onLogBookChangePage(): array
    {
        $page = (int) post('page', 1);

        $this->prepareVars();

        $this->logs = $this->model->getLogsFromLogBook(
            $this->limitPerPage,
            $page,
            $this->getRelationsToLoad()
        );

        return [
            '#jacob-logbook' => $this->makePartial('logbook'),
        ];
    }

    /**
     * @return RedirectResponse|array
     * @throws \Exception
     */
    public function onLogBookUndoChange()
    {
        $id = post('id', null);

        /** @var Log $log */
        $log = Log::query()->find($id);

        if (!$log || !$log->getMutation()->isTypeUpdated()) {
            return $this->onLogBookChangePage();
        }

        /** @var Model $modelInstance */
        $modelInstance = resolve($log->getAttribute('model'));

        $modelQuery = $modelInstance->newQuery();

        try {
            $modelQuery->withTrashed();
        } catch (Throwable $throwable) {
            // this model doesn't support soft deleting
        }

        $changedModel = $modelQuery->find($log->getModelKey());

        foreach ($log->getMutation()->getChangedAttributes() as $changedAttribute) {
            $changedModel->setAttribute($changedAttribute->getColumn(), $changedAttribute->getOld());
        }

        $changedModel->save();

        if ($this->refreshFormAfterUndo) {
            /** @var Redirector $redirect */
            $redirect = resolve(Redirector::class);
            return $redirect->refresh();
        }

        return $this->onLogBookChangePage();
    }

    public function getSaveValue($value): int
    {
        return FormField::NO_SAVE_DATA;
    }

    /**
     * @return LoadRelation[]
     */
    private function getRelationsToLoad(): array
    {
        $data = [];

        foreach ($this->showLogRelations ?? [] as $relation) {
            $data[] = new LoadRelation($relation, false);
        }

        foreach ($this->showSoftDeleteRelations ?? [] as $relation) {
            $data[] = new LoadRelation($relation, true);
        }

        return $data;
    }
}
