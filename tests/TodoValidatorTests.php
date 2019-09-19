<?php

use PHPUnit\Framework\TestCase;

final class TodoValidatorTests extends TestCase
{
    public function validateTodoInput()
    {
        $goodInput = "Pick up tomatoes";
        $badInput = "           ";

        $this->assertEquals(isTodoInputValid($goodInput) == true, "Good description");
        $this->assertEquals(isTodoInputValid($badInput)  == false, "Empty input (only white spaces)");
    }
}