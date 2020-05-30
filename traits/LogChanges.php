<?php

namespace Jacob\Logbook\Traits;

use Backend\Classes\AuthManager;
use Backend\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Jacob\LogBook\Classes\LoadRelation;
use Jacob\Logbook\Models\Log;
use Jacob\LogBook\Classes\Entities\Attribute;
use Jacob\LogBook\Classes\Entities\Changes;
use October\Rain\Database\Builder;
use October\Rain\Database\Model;

trait LogChanges
{
    /**
     * ===========================================================
     * You can override these properties in your model
     * ===========================================================
     */

    /**
     * @var array $ignoreFields fields to ignore
     *
     * protected $ignoreFieldsLogbook = [
     *      'updated_at'
     * ];
     */

    /**
     * Delete log book items after model is deleted
     *
     * If true -> log items are deleted when the model is deleted
     * If false -> a new log item will be created with status deleted.
     *
     * @var bool $deleteLogbookAfterDelete
     *
     * protected $deleteLogbookAfterDelete = false;
     */

    /**
     * Here you can override the model name that is displayed in the log files.
     * The name is going to be translated when possible.
     *
     * string $logBookModelName
     *
     * public $logBookModelName = 'MyModelName'
     */

    /**
     * Hides or shows undo button for current field
     *
     * @var bool $logBookLogUndoable
     *
     * public $logBookLogUndoable = false
     */

    /**
     * If you override this function you can change the value that is displayed in the log book
     * This can be useful for example with a dropdown
     *
     * @param $column
     * @param $value
     * @return string
     */
    public static function changeLogBookDisplayValue($column, $value)
    {
        return $value;
    }

    /**
     * If you override this function you can change the column name that is displayed in the log book
     * The returned column will be translated if it is possible
     *
     * @param string $column
     * @return string
     */
    public static function changeLogBookDisplayColumn($column)
    {
        return $column;
    }

    public static function bootLogChanges(): void
    {
        static::extend(function(Model $model) {
            /** @var Model|self $model */
            $model->bindEvent('model.afterCreate', function() use ($model) {
                $model->logChangesAfterCreate();
            });

            $model->bindEvent('model.afterUpdate', function() use ($model) {
                $model->logChangesAfterUpdate();
            });

            $model->bindEvent('model.afterDelete', function() use ($model) {
                $model->logChangesAfterDelete();
            });
        });
    }

    private function createLogBookLogItem(Changes $changes): void
    {
        /** @var User $user */
        $user = AuthManager::instance()->getUser();

        if (!$user) {
            $backendUserId = null;
        } else {
            $backendUserId = $user->getKey();
        }

        Log::create([
            'model' => get_class($this),
            'model_key' => $this->getKey(),
            'changes' => $changes->getData(),
            'backend_user_id' => $backendUserId,
        ]);
    }

    public function logChangesAfterCreate(): void
    {
        $changes = new Changes(Changes::TYPE_CREATED);

        $this->createLogBookLogItem($changes);
    }

    public function logChangesAfterUpdate(): void
    {
        $attributes = [];

        $originalAttributes = $this->getOriginal();

        $ignoreFieldsLogbook = $this->ignoreFieldsLogbook ?? ['updated_at'];

        foreach ($this->getDirty() as $column => $newValue) {
            if (in_array($column, $ignoreFieldsLogbook)) {
                continue; //ignore field
            }

            $attributes[] = new Attribute($column,$originalAttributes[$column] ?? null, $newValue);
        }

        if (count($attributes) === 0) {
            // no changes to log
            return;
        }

        $this->createLogBookLogItem(new Changes(Changes::TYPE_UPDATED, $attributes));
    }

    public function logChangesAfterDelete(): void
    {
        if ($this->deleteLogbookAfterDelete ?? false) {
            /** @var Builder $query */
            $query = Log::query()->where('model', '=', get_class($this));
            $query->where('model_key', '=', $this->getKey());
            $query->delete();
        } else {
            $changes = new Changes(Changes::TYPE_DELETED);

            $this->createLogBookLogItem($changes);
        }
    }

    /**
     * @param int $limitPerPage
     * @param int $currentPage
     * @param LoadRelation[]|null $relations
     * @return LengthAwarePaginator
     */
    public function getLogsFromLogBook(int $limitPerPage = 20, int $currentPage = 0, array $relations = null): LengthAwarePaginator
    {
        /** @var Builder $query */
        $query = Log::query()->where([
            ['model', '=', get_class($this)],
            ['model_key', '=', $this->getKey()]
        ]);

        if ($relations !== null) {
            foreach ($relations as $relation) {
                $relationClass = null;

                $relationName = $relation->getName();

                if ($relation->isWithTrashed()) {
                    $relatedModels = $this->$relationName()->withTrashed()->get();
                } else {
                    $relatedModels = $this->$relationName;
                }

                // no related items found
                if ($relatedModels === null ) {
                    continue;
                }

                // one related item found
                if ($relatedModels instanceof Model) {
                    $query->orWhere([
                        ['model', '=', get_class($relatedModels)],
                        ['model_key', '=', $relatedModels->getKey()]
                    ]);
                    continue;
                }

                // multiple related items found
                /** @var Model $relatedModel */
                foreach ($relatedModels as $relatedModel) {
                    if ($relationClass === null) {
                        $relationClass = get_class($relatedModel);
                    }

                    $query->orWhere([
                        ['model', '=', $relationClass],
                        ['model_key', '=', $relatedModel->getKey()]
                    ]);
                }
            }
        }

        $query->orderBy('updated_at', 'desc');
        $query->with('backendUser');

        return $query->paginate($limitPerPage, $currentPage);
    }
}
