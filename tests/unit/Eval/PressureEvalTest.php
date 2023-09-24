<?php

namespace Chess\Tests\Unit\Eval;

use Chess\Eval\PressureEval;
use Chess\Play\SanPlay;
use Chess\Tests\AbstractUnitTestCase;
use Chess\Variant\Classical\Board;

class PressureEvalTest extends AbstractUnitTestCase
{
    /**
     * @test
     */
    public function start()
    {
        $pressEval = (new PressureEval(new Board()))->eval();

        $expected = [
            'w' => [],
            'b' => [],
        ];

        $this->assertSame($expected, $pressEval);
    }

    /**
     * @test
     */
    public function B25()
    {
        $B25 = file_get_contents(self::DATA_FOLDER.'/sample/B25.pgn');

        $board = (new SanPlay($B25))->validate()->getBoard();

        $pressEval = (new PressureEval($board))->eval();

        $expected = [
            'w' => [],
            'b' => ['c3'],
        ];

        $this->assertEqualsCanonicalizing($expected['w'], $pressEval['w']);
        $this->assertEqualsCanonicalizing($expected['b'], $pressEval['b']);
    }

    /**
     * @test
     */
    public function B56()
    {
        $B56 = file_get_contents(self::DATA_FOLDER.'/sample/B56.pgn');

        $board = (new SanPlay($B56))->validate()->getBoard();

        $pressEval = (new PressureEval($board))->eval();

        $expected = [
            'w' => ['c6'],
            'b' => ['d4', 'e4'],
        ];

        $this->assertEqualsCanonicalizing($expected['w'], $pressEval['w']);
        $this->assertEqualsCanonicalizing($expected['b'], $pressEval['b']);
    }

    /**
     * @test
     */
    public function C67()
    {
        $C67 = file_get_contents(self::DATA_FOLDER.'/sample/C67.pgn');

        $board = (new SanPlay($C67))->validate()->getBoard();

        $pressEval = (new PressureEval($board))->eval();

        $expected = [
            'w' => ['c6', 'e5'],
            'b' => ['d2', 'f2'],
        ];

        $this->assertEqualsCanonicalizing($expected['w'], $pressEval['w']);
        $this->assertEqualsCanonicalizing($expected['b'], $pressEval['b']);
    }
}
