<?php

namespace Jacob\Logbook\Traits;

use Backend\Facades\BackendAuth;
use Backend\Models\User;
use Jacob\Logbook\Models\Log;
use Jacob\LogBook\Classes\Entities\Attribute;
use Jacob\LogBook\Classes\Entities\Changes;
use October\Rain\Database\Builder;

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


    /**
     * Boot log changes trait
     *
     * @return void
     */
    public static function bootLogChanges()
    {
        static::extend(function($model) {
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

    /**
     * @param Changes $changes
     */
    private function createLogBookLogItem(Changes $changes)
    {
        /** @var User $user */
        $user = BackendAuth::getUser();

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

    /**
     * Log the creation of current model
     *
     * @return void
     */
    public function logChangesAfterCreate()
    {
        $changes = new Changes([
            'type' => Changes::TYPE_CREATED,
        ]);

        $this->createLogBookLogItem($changes);
    }

    /**
     * Log the changes after update
     *
     * @return void
     */
    public function logChangesAfterUpdate()
    {
        $attributes = [];

        /** @var array $dirtyAttributes */
        $dirtyAttributes = $this->getDirty();

        /** @var array $originalAttributes */
        $originalAttributes = $this->getOriginal();

        /** @var array $ignoreFieldsLogbook */
        $ignoreFieldsLogbook = $this->ignoreFieldsLogbook ?? ['updated_at'];

        foreach ($dirtyAttributes as $column => $newValue) {
            if (in_array($column, $ignoreFieldsLogbook)) {
                continue; //ignore field
            }

            /** @var Attribute $attributeChanged */
            $attributeChanged = new Attribute([
                'column' => $column,
                'old' => $originalAttributes[$column] ?? null,
                'new' => $newValue,
            ]);

            $attributes[] = $attributeChanged->getData();
        }

        $changes = new Changes([
            'type' => Changes::TYPE_UPDATED,
            'changedAttributes' => $attributes
        ]);

        $this->createLogBookLogItem($changes);
    }

    /**
     * Log delete or delete logs when model is deleted
     *
     * @return void
     */
    public function logChangesAfterDelete()
    {
        if ($this->deleteLogbookAfterDelete ?? false) {
            /** @var Builder $query */
            $query = Log::where('model', '=', get_class($this));
            $query->where('model_key', '=', $this->getKey());
            $query->delete();
        } else {
            $changes = new Changes([
                'type' => Changes::TYPE_DELETED,
            ]);

            $this->createLogBookLogItem($changes);
        }
    }

    /**
     * @param int $limitPerPage
     * @param int $currentPage
     * @param array $relations
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLogsFromLogBook($limitPerPage = 20, $currentPage = 0, $relations = null)
    {
        /** @var Builder $query */
        $query = Log::where([
            ['model', '=', get_class($this)],
            ['model_key', '=', $this->getKey()]
        ]);

        if ($relations !== null) {
            foreach ($relations as $relation) {
                $relationClass = null;
                $relatedModels = $this->$relation;

                // no related items found
                if ($relatedModels === null ) {
                    continue;
                }

                //one item found
                if ($relatedModels instanceof \Model) {
                    $query->orWhere([
                        ['model', '=', get_class($relatedModels)],
                        ['model_key', '=', $relatedModels->getKey()]
                    ]);
                    continue;
                }

                //multiple items
                /** @var \October\Rain\Database\Model $relatedModel */
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