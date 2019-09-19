<?php

class TodoValidator
{
    static function isTodoInputValid($input)
    {
        return trim($input) != "";
    }
}
