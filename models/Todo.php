<?php

namespace Model {

    class Todo
    {
        public $id;
        public $user_id;
        public $description;
        public $completed = False;
        function __construct($id, $uid, $description)
        {
            $this->id = $id;
            $this->user_id = $uid;
            $this->description = $description;
        }
    }
}
