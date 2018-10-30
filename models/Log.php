<?php namespace Jacob\Logbook\Models;

use Backend\Models\User;
use Model;

/**
 * Log Model
 */
class Log extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'jacob_logbook_logs';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'model',
        'model_key',
        'changes',
        'backend_user_id',
    ];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    protected $jsonable = [
        'changes',
    ];

    /**
     * @var array $belongsTo relations
     */
    public $belongsTo = [
        'backendUser' => [
            User::class
        ],
    ];
}
