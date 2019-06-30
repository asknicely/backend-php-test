<?php

namespace Model {

    use ORM\Model;

    class Todo extends Model
    {
        protected static $table = 'todos';
        protected static $fields = ['user_id', 'description', 'completed'];

        public static function user()
        {
            return Todo::belongsTo('users', 'user_id');
        }
    }
}
