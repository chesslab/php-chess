<?php

namespace Chess\Computer;

use Chess\Variant\Classical\Board;

/**
 * RandomMove
 *
 * @author Jordi Bassagaña
 * @license MIT
 */
class RandomMove
{
    /**
     * Chess board.
     *
     * @var \Chess\Variant\Classical\Board
     */
    protected Board $board;

    /**
     * Constructor.
     *
     * @param \Chess\Variant\Classical\Board $board
     */
    public function __construct(Board $board)
    {
        $this->board = unserialize(serialize($board));
    }

    /**
     * Returns a chess move.
     *
     * @return null|object
     */
    public function move(): ?object
    {
        $legal = [];
        foreach ($this->board->getPieces($this->board->getTurn()) as $piece) {
            if ($sqs = $piece->sqs()) {
                $legal[$piece->getSq()] = $sqs;
            }
        }

        $from = array_rand($legal);
        shuffle($legal[$from]);
        $to = $legal[$from][0];

        $lan = "{$from}{$to}";

        if ($this->board->playLan($this->board->getTurn(), $lan)) {
            $last = array_slice($this->board->getHistory(), -1)[0];
            return (object) [
                'pgn' => $last['move']['pgn'],
                'lan' => $lan,
            ];
        }

        return null;
    }
}
