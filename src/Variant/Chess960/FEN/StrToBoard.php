<?php

namespace Chess\Variant\Chess960\FEN;

use Chess\Exception\UnknownNotationException;
use Chess\Piece\PieceArray;
use Chess\Variant\Chess960\Board;
use Chess\Variant\Chess960\Rule\CastlingRule;
use Chess\Variant\Classical\FEN\Str;
use Chess\Variant\Classical\FEN\StrToBoard as ClassicalFenStrToBoard;
use Chess\Variant\Classical\PGN\AN\Square;

/**
 * StrToBoard
 *
 * Converts a FEN string to a chessboard object.
 *
 * @author Jordi Bassagaña
 * @license MIT
 */
class StrToBoard extends ClassicalFenStrToBoard
{
    private array $startPos;

    public function __construct(string $string, array $startPos)
    {
        $this->square = new Square();
        $this->fenStr = new Str();
        $this->string = $this->fenStr->validate($string);
        $this->fields = array_filter(explode(' ', $this->string));
        $this->castlingAbility = $this->fields[2];
        $this->startPos = $startPos;
        $this->castlingRule = new CastlingRule($this->startPos);
    }

    public function create(): Board
    {
        try {
            $pieces = (new PieceArray(
                $this->fenStr->toAsciiArray($this->fields[0]),
                $this->square,
                $this->castlingRule
            ))->getArray();
            $board = new Board($this->startPos, $pieces, $this->castlingAbility);
            $board->turn = $this->fields[1];
            $board->startFen = $this->string;
        } catch (\Throwable $e) {
            throw new UnknownNotationException();
        }

        return $this->enPassant($board);
    }
}
