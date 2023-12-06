<?php

namespace Chess\Tests\Unit\Eval;

use Chess\Eval\DoubledPawnEval;
use Chess\Piece\AsciiArray;
use Chess\Tests\AbstractUnitTestCase;
use Chess\Variant\Classical\PGN\AN\Square;
use Chess\Variant\Classical\Rule\CastlingRule;

class DoubledPawnEvalTest extends AbstractUnitTestCase
{
    static private $size;

    static private $castlingRule;

    public static function setUpBeforeClass(): void
    {
        self::$size = Square::SIZE;

        self::$castlingRule = (new CastlingRule())->getRule();
    }

    /**
     * @test
     */
    public function kaufman_16()
    {
        $expectedEval = [
            'w' => 1,
            'b' => 0,
        ];

        $expectedPhrase = [
            "The white pieces are slightly better in terms of doubled pawns.",
        ];

        $position = [
            7 => [ ' . ', ' . ', ' . ', ' . ', ' . ', ' . ', ' . ', ' . ' ],
            6 => [ ' . ', ' . ', ' . ', ' . ', ' p ', ' . ', ' . ', ' . ' ],
            5 => [ ' p ', ' . ', ' . ', ' p ', ' . ', ' . ', ' . ', ' . ' ],
            4 => [ ' . ', ' . ', ' p ', ' P ', ' . ', ' . ', ' . ', ' . ' ],
            3 => [ ' . ', ' . ', ' P ', ' . ', ' P ', ' . ', ' . ', ' . ' ],
            2 => [ ' . ', ' P ', ' . ', ' . ', ' . ', ' . ', ' k ', ' . ' ],
            1 => [ ' . ', ' P ', ' . ', ' K ', ' . ', ' . ', ' . ', ' . ' ],
            0 => [ ' . ', ' . ', ' . ', ' . ', ' . ', ' . ', ' . ', ' . ' ],
        ];

        $board = (new AsciiArray($position, self::$size, self::$castlingRule))
            ->toClassicalBoard('\Chess\Variant\Classical\Board', 'w');

        $doubledPawnEval = new DoubledPawnEval($board);

        $this->assertSame($expectedEval, $doubledPawnEval->getResult());
        $this->assertSame($expectedPhrase, $doubledPawnEval->getPhrases());
    }

    /**
     * @test
     */
    public function kaufman_17()
    {
        $expectedEval = [
            'w' => 0,
            'b' => 1,
        ];

        $expectedPhrase = [
            "The black pieces are slightly better in terms of doubled pawns.",
        ];

        $position = [
            7 => [ ' . ', ' r ', ' . ', ' q ', ' . ', ' r ', ' k ', ' . ' ],
            6 => [ ' p ', ' . ', ' p ', ' . ', ' . ', ' p ', ' b ', ' p ' ],
            5 => [ ' . ', ' . ', ' p ', ' p ', ' . ', ' k ', ' p ', ' . ' ],
            4 => [ ' . ', ' . ', ' . ', ' . ', ' . ', ' . ', ' B ', ' . ' ],
            3 => [ ' . ', ' . ', ' . ', ' . ', ' P ', ' . ', ' . ', ' . ' ],
            2 => [ ' . ', ' . ', ' N ', ' Q ', ' . ', ' . ', ' . ', ' . ' ],
            1 => [ ' P ', ' P ', ' P ', ' . ', ' . ', ' P ', ' P ', ' P ' ],
            0 => [ ' . ', ' . ', ' . ', ' R ', ' . ', ' R ', ' K ', ' . ' ],
        ];

        $board = (new AsciiArray($position, self::$size, self::$castlingRule))
            ->toClassicalBoard('\Chess\Variant\Classical\Board', 'w');

        $doubledPawnEval = new DoubledPawnEval($board);

        $this->assertSame($expectedEval, $doubledPawnEval->getResult());
        $this->assertSame($expectedPhrase, $doubledPawnEval->getPhrases());
    }
}
