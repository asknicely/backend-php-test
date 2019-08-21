<?php
namespace Src\Model;

use Illuminate\Database\Eloquent\Model;

class Todos extends Model
{
    protected $table = 'todos';

    protected $fillable = ['user_id', 'description'];

    public $timestamps = false;
}