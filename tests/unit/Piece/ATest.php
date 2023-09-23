<?php

namespace Chess\Tests\Unit\Piece;

use Chess\Tests\AbstractUnitTestCase;
use Chess\Piece\A;

class ATest extends AbstractUnitTestCase
{
    static private $size;

    public static function setUpBeforeClass(): void
    {
        self::$size = [
            'files' => 10,
            'ranks' => 8,
        ];
    }

    /**
     * @test
     */
    public function mobility_a1()
    {
        $archbishop = new A('w', 'a1', self::$size);

        $mobility = (object) [
            'upLeft' => [],
            'upRight' => ['b2', 'c3', 'd4', 'e5', 'f6', 'g7', 'h8'],
            'downLeft' => [],
            'downRight' => [],
            'knight' => ['c2', 'b3']
        ];

        $this->assertEquals($mobility, $archbishop->getMobility());
    }

    /**
     * @test
     */
    public function mobility_e4()
    {
        $archbishop = new A('w', 'e4', self::$size);

        $mobility = (object) [
            'upLeft' => ['d5', 'c6', 'b7', 'a8'],
            'upRight' => ['f5', 'g6', 'h7', 'i8'],
            'downLeft' => ['d3', 'c2', 'b1'],
            'downRight' => ['f3', 'g2', 'h1'],
            'knight' => ['d6', 'c5', 'c3', 'd2', 'f2', 'g3', 'g5', 'f6']
        ];

        $this->assertEquals($mobility, $archbishop->getMobility());

    }

    /**
     * @test
     */
    public function mobility_d4()
    {
        $archbishop = new A('w', 'd4', self::$size);

        $mobility = (object) [
            'upLeft' => ['c5', 'b6', 'a7'],
            'upRight' => ['e5', 'f6', 'g7', 'h8'],
            'downLeft' => ['c3', 'b2', 'a1'],
            'downRight' => ['e3', 'f2', 'g1'],
            'knight' => ['c6', 'b5', 'b3', 'c2', 'e2', 'f3', 'f5', 'e6']
        ];

        $this->assertEquals($mobility, $archbishop->getMobility());

    }
}
