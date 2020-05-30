Logbook plugin for OctoberCMS
=

This plugin creates a logbook for changes in an Eloquent model.<br/>
The plugin also provides a Form Widget and Report Widget to display the log changes. <br/>
The Form Widget can also show logs of related models, so all changes can be visible. <br/>
See the documentation how to use the logbook Form Widget and trait. <br/>

Usage
=

### Model
Use the LogChanges trait in your model.
This trait will automatically save changes from the model in the database.

```php
use Jacob\Logbook\Traits\LogChanges;
```

If you want to ignore fields that don't need the be logged, add this to your model.
By default the updated_at field is ignored.
```php
/** @var array $ignoreFields fields to ignore */
protected $ignoreFieldsLogbook = [
    'updated_at',
];
```

If you want to provide a custom log book model display name you can add this to your model:
```php
/**
 * Here you can override the model name that is displayed in the log files.
 * The name is going to be translated when possible.
 */
public $logBookModelName = 'MyModelName'
```

If you want to delete the logs when the model is deleted you can add the following code to your model.

```php
/**
 * Delete log book items after model is deleted
 *
 * If true -> log items are deleted when the model is deleted
 * If false -> a new log item will be created with status deleted.
 *
 * @var bool
 */
protected $deleteLogbookAfterDelete = false;
```

If you want to change the displayed column name, you can add the following code to your model.
This can be usefull if you want to give a translation string for the column

```php
/**
 * If you override this function you can change the column name that is displayed in the log book
 * The returned column will be translated if it is possible
 *
 * @param string $column
 * @return string
 */
public static function changeLogBookDisplayColumn($column)
{
    return 'example.plugin::lang.' . $column;
}
```

You can also override the displayed value. You have to add the following function to your model.

```php
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
    if ($column) {
        // do something
    }

    return $value;
}
```

If you want to hide the undo button for this model specifically, you can add $logBookUndoable to your model.
You can also disable the undo button in the log book form widget.

```php
/**
 * Hides or shows undo button for current field
 *
 * @var bool $logBookLogUndoable
 */
public $logBookLogUndoable = false
```

# Form Widget

You can use the formwidget as follows: <br/>
**MAKE** **SURE** TO SET AN UNDERSCORE IN FRONT OF THE FORM FIELD NAME. FOR EXAMPLE _logbook,
because the Form Widget doesn't has a save value.

Options: <br/>

| Option            | Default       | type      |
| ----------------- |:-------------:| ---------:|
| limitPerPage      | 20            | int       |
| startPage         | 1             | int       |
| showLogRelations  | null          | array or string |
| showSoftDeletedRelations  | null  | array or string |
| showUndoChangesButton| true       | bool      |
| refreshFormAfterUndo | true       | bool      |

Example:
```yaml
_logbook@update:
    type: jacob_logbook_log
    limitPerPage: 10 #optional
    startPage: 1 #optional
    showLogRelations: #optional (contains the name(s) of the relations)
        - customer
        - anotherRelationName
    showSoftDeleteRelations: #optional (contains the name(s) of the relations with soft delete support)
        - relationNameWithSoftDeletes
    showUndoChangesButton: true #optional
    refreshFormAfterUndo: false #optional
```
