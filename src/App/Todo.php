<?php

namespace App;

class Todo extends \Illuminate\Database\Eloquent\Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description',
        'status',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the user that owns todos.
     */
    public function user()
    {
        return $this->belongsTo('App\User')->withDefault();
    }
}
