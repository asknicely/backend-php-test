<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class UtilsTest extends TestCase
{

    public function testCreateJson(): void
    {
        $this->assertEquals(
            '{id: 31, user_id: 1, description: "AskNicely"}',
            Utils::createJson(['id'=>31, 'user_id'=>1, 'description'=>'AskNicely'])
        );
    }

    public function testBchexdec(): void
    {
         $this->assertEquals(
            '1024',
            Utils::bchexdec('400')
        );
        
    }


    public function testBcdechex(): void
    {
         $this->assertEquals(
            '400',
            Utils::bcdechex('1024')
        );
    }

}

