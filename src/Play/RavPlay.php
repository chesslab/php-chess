<?php

namespace Chess\Play;

use Chess\FenToBoardFactory;
use Chess\Exception\PlayException;
use Chess\Movetext\RavMovetext;
use Chess\Movetext\SanMovetext;
use Chess\Play\SanPlay;
use Chess\Variant\Classical\Board;

/**
 * Recursive Annotation Variation.
 *
 * @author Jordi Bassagaña
 * @license MIT
 */
class RavPlay extends AbstractPlay
{
    /**
     * RAV movetext.
     *
     * @var array
     */
    protected RavMovetext $ravMovetext;

    /**
     * Resume the variations.
     *
     * @var array
     */
    protected array $resume;

    /**
     * Constructor.
     *
     * @param string $movetext
     * @param Board $board
     */
    public function __construct(string $movetext, Board $board = null)
    {
        if ($board) {
            $this->initialBoard = $board;
            $this->board = $board->clone();
        } else {
            $this->initialBoard = new Board();
            $this->board = new Board();
        }
        $this->fen = [$this->board->toFen()];
        $this->ravMovetext = new RavMovetext($this->board->move, $movetext);

        $this->ravMovetext->validate();
    }

    /**
     * Returns the RAV movetext.
     *
     * @return RavMovetext
     */
    public function getRavMovetext(): RavMovetext
    {
        return $this->ravMovetext;
    }

    /**
     * Semantically validated movetext.
     *
     * Makes the moves in the main variation of a RAV movetext.
     *
     * @throws \Chess\Exception\PlayException
     * @return \Chess\Play\RavPlay
     */
    public function validate(): RavPlay
    {
        $moves = (new SanMovetext(
            $this->ravMovetext->getMove(),
            $this->ravMovetext->main()
        ))->getMoves();

        foreach ($moves as $key => $val) {
            if (!$this->board->play($this->board->turn, $val)) {
                throw new PlayException();
            }
        }

        $this->fen();

        return $this;
    }

    /**
     * Calculates the FEN history.
     *
     * @return \Chess\Play\RavPlay
     */
    protected function fen(): RavPlay
    {
        $sanPlay = (new SanPlay(
            $this->getRavMovetext()->getBreakdown()[0],
            $this->initialBoard
        ))->validate();
        $this->fen = $sanPlay->getFen();
        $this->resume[$sanPlay->getSanMovetext()->filtered(false, false)] = $sanPlay->getBoard();
        for ($i = 1; $i < count($this->getRavMovetext()->getBreakdown()); $i++) {
            $sanMovetext = new SanMovetext(
                $this->ravMovetext->getMove(),
                $this->getRavMovetext()->getBreakdown()[$i]
            );
            foreach ($this->resume as $key => $val) {
                $sanMovetextKey = new SanMovetext($this->ravMovetext->getMove(), $key);
                if ($this->getRavMovetext()->isPrevious($sanMovetextKey, $sanMovetext)) {
                    if (
                        $this->isUndo($sanMovetextKey->getMetadata()->lastMove, $sanMovetext->getMetadata()->firstMove)
                    ) {
                        $clone = unserialize(serialize($val));
                        $undo = $clone->undo();
                        $board = FenToBoardFactory::create($undo->toFen(), $this->initialBoard);
                    } else {
                        $board = FenToBoardFactory::create($val->toFen(), $this->initialBoard);
                    }
                }
            }
            $sanPlay = (new SanPlay($this->getRavMovetext()->getBreakdown()[$i], $board))
                ->validate();
            $this->resume[$sanPlay->getSanMovetext()->filtered(false, false)] = $sanPlay->getBoard();
            $fen = $sanPlay->getFen();
            array_shift($fen);
            $this->fen = [
                ...$this->fen,
                ...$fen,
            ];
        }

        return $this;
    }

    /**
     * Finds out if a move must be undone.
     *
     * @param string $previous
     * @param string $current
     * @return bool
     */
    protected function isUndo(string $previous, string $current)
    {
        $previous = new SanMovetext($this->ravMovetext->getMove(), $previous);
        $current = new SanMovetext($this->ravMovetext->getMove(), $current);
        if ($previous->getMetadata()->turn === $current->getMetadata()->turn) {
            return true;
        }

        return false;
    }
}
