<?php

namespace SupportMonitor\App\Models;

class SMPlugins extends BaseModel {

    /**
     *
     * @var string
     */
    protected $table = 'sm_plugins';

    /**
     * Columns that can be edited - IE not primary key or timestamps if being used
     */
    protected $fillable = [
        'slug',
    ];

    /**
     * Disable created_at and update_at columns, unless you have those.
     */
    public $timestamps = false;

    /**
     * Everything below this is best done in an abstract class that custom tables extend
     */

    /**
     * Set primary key as ID, because WordPress
     *
     * @var string
     */
    protected $primaryKey = 'ID'; // phpcs:ignore

    /**
     * Make ID guarded -- without this ID doesn't save.
     *
     * @var string
     */
    protected $guarded = [ 'ID' ];
}
