<?php

namespace Chess;

use Chess\Variant\AbstractBoard;;
use Chess\Variant\Capablanca\Board as CapablancaBoard;
use Chess\Variant\Capablanca\FEN\StrToBoardFactory as CapablancaFenStrToBoardFactory;
use Chess\Variant\CapablancaFischer\Board as CapablancaFischerBoard;
use Chess\Variant\CapablancaFischer\FEN\StrToBoardFactory as CapablancaFischerFenStrToBoardFactory;
use Chess\Variant\Chess960\Board as Chess960Board;
use Chess\Variant\Chess960\FEN\StrToBoardFactory as Chess960FenStrToBoardFactory;
use Chess\Variant\Classical\Board as ClassicalBoard;
use Chess\Variant\Classical\FEN\StrToBoardFactory as ClassicalFenStrToBoardFactory;
use Chess\Variant\Dunsany\Board as DunsanyBoard;
use Chess\Variant\Dunsany\FEN\StrToBoardFactory as DunsanyFenStrToBoardFactory;
use Chess\Variant\Losing\Board as LosingBoard;
use Chess\Variant\Losing\FEN\StrToBoardFactory as LosingFenStrToBoardFactory;
use Chess\Variant\RacingKings\Board as RacingKingsBoard;
use Chess\Variant\RacingKings\FEN\StrToBoardFactory as RackingKingsFenStrToBoardFactory;

class FenToBoardFactory
{
    public static function create(string $fen, AbstractBoard $board = null)
    {
        $board ??= new ClassicalBoard();

        if (is_a($board, CapablancaBoard::class)) {
            return CapablancaFenStrToBoardFactory::create($fen);
        } elseif (is_a($board, CapablancaFischerBoard::class)) {
            $startPos = $board->getStartPos();
            return CapablancaFischerFenStrToBoardFactory::create($fen, $board->getStartPos());
        } elseif (is_a($board, Chess960Board::class)) {
            return Chess960FenStrToBoardFactory::create($fen, $board->getStartPos());
        } elseif (is_a($board, DunsanyBoard::class)) {
            return DunsanyFenStrToBoardFactory::create($fen);
        } elseif (is_a($board, LosingBoard::class)) {
            return LosingFenStrToBoardFactory::create($fen);
        } elseif (is_a($board, RacingKingsBoard::class)) {
            return RackingKingsFenStrToBoardFactory::create($fen);
        }

        return ClassicalFenStrToBoardFactory::create($fen);
    }
}
