<?php

namespace Chess\Tests\Unit\Tutor;

use Chess\FenToBoardFactory;
use Chess\Function\CompleteFunction;
use Chess\Play\SanPlay;
use Chess\Tutor\FenExplanation;
use Chess\Tests\AbstractUnitTestCase;
use Chess\Variant\Capablanca\Board as CapablancaBoard;

class FenExplanationTest extends AbstractUnitTestCase
{
    static private CompleteFunction $function;

    public static function setUpBeforeClass(): void
    {
        self::$function = new CompleteFunction();
    }

    /**
     * @test
     */
    public function A08()
    {
        $expected = [
            "Black has a slightly better control of the center.",
            "The white pieces are slightly better connected.",
            "Black has a moderate space advantage.",
        ];

        $A08 = file_get_contents(self::DATA_FOLDER.'/sample/A08.pgn');
        $board = (new SanPlay($A08))->validate()->board;

        $paragraph = (new FenExplanation(self::$function, $board))->paragraph;

        $this->assertSame($expected, $paragraph);
    }

    /**
     * @test
     */
    public function capablanca_f4()
    {
        $expected = [
            "White is totally controlling the center.",
            "The black pieces are slightly better connected.",
            "White has a total space advantage.",
            "The white player is pressuring a little bit more squares than its opponent.",
        ];

        $board = FenToBoardFactory::create(
            'rnabqkbcnr/pppppppppp/10/10/5P4/10/PPPPP1PPPP/RNABQKBCNR b KQkq f3',
            new CapablancaBoard()
        );

        $paragraph = (new FenExplanation(self::$function, $board))->paragraph;

        $this->assertSame($expected, $paragraph);
    }

    /**
     * @test
     */
    public function g3_in_check()
    {
        $expected = [
            "Black is totally controlling the center.",
            "Black has a moderate space advantage.",
            "The black player is pressuring a little bit more squares than its opponent.",
            "The white pieces are approaching the other side's king.",
            "Black has a slight protection advantage.",
            "White has a slight advanced pawn advantage.",
            "Black has a slight far advanced pawn advantage.",
            "White has a slight absolute pin advantage.",
            "White has a slight outpost advantage.",
            "Black has a checkability advantage.",
        ];

        $board = FenToBoardFactory::create('8/p4pk1/6b1/3P1PQ1/8/P1q3K1/2p3B1/8 w - -');

        $paragraph = (new FenExplanation(self::$function, $board))->paragraph;

        $this->assertSame($expected, $paragraph);
    }
}
