<?php

namespace Chess\Tests\Unit\Variant\Chess960;

use Chess\Tests\AbstractUnitTestCase;
use Chess\Variant\Chess960\StartingPosition;

class StartingPositionTest extends AbstractUnitTestCase
{
    /**
     * @test
     */
    public function create()
    {
        $arr = (new StartingPosition())->create();

        $this->assertNotEmpty($arr);
    }
}
