<?php

//use App\ORM\Model;

namespace Model {

    use ORM\Model;

    class User extends Model
    {
        protected static $table = 'users';
        protected static $fields = ['username', 'password'];

        public static function todos($db, $id)
        {
            return User::hasMany($db, $id, 'todos', 'user_id');
        }
    }
}
