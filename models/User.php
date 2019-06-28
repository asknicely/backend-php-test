<?php

namespace Model {
    class User
    {
        public $id;
        public $name;
        public $password;

        public function __construct($id, $name, $password)
        {
            $this->id = $id;
            $this->name = $name;
            $this->password = $password;
        }
    }
}
