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

    /**
     * Scope a query to only include active users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $page
     * @param  int  $perPage
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePage($query, $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        return $query->skip($offset)
            ->take($perPage);
    }

    public function toggleStatus()
    {
        $this->status = !$this->status;
    }
}
