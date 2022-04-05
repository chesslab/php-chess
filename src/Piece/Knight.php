<?php

namespace Chess\Piece;

use Chess\Exception\UnknownNotationException;
use Chess\PGN\Symbol;
use Chess\PGN\Validate;
use Chess\Piece\AbstractPiece;

/**
 * Knight class.
 *
 * @author Jordi Bassagañas
 * @license GPL
 */
class Knight extends AbstractPiece
{
    /**
     * Constructor.
     *
     * @param string $color
     * @param string $sq
     */
    public function __construct(string $color, string $sq)
    {
        parent::__construct($color, $sq, Symbol::KNIGHT);

        $this->travel = (object)[
            'jumps' => []
        ];

        $this->travel();
    }

    /**
     * Calculates the knight's travel.
     */
    protected function travel(): void
    {
        try {
            $file = chr(ord($this->sq[0]) - 1);
            $rank = (int)$this->sq[1] + 2;
            if (Validate::sq($file.$rank)) {
                $this->travel->jumps[] = $file . $rank;
            }
        } catch (UnknownNotationException $e) {

        }

        try {
            $file = chr(ord($this->sq[0]) - 2);
            $rank = (int)$this->sq[1] + 1;
            if (Validate::sq($file.$rank)) {
                $this->travel->jumps[] = $file . $rank;
            }
        } catch (UnknownNotationException $e) {

        }

        try {
            $file = chr(ord($this->sq[0]) - 2);
            $rank = (int)$this->sq[1] - 1;
            if (Validate::sq($file.$rank)) {
                $this->travel->jumps[] = $file . $rank;
            }
        } catch (UnknownNotationException $e) {

        }

        try {
            $file = chr(ord($this->sq[0]) - 1);
            $rank = (int)$this->sq[1] - 2;
            if (Validate::sq($file.$rank)) {
                $this->travel->jumps[] = $file . $rank;
            }
        } catch (UnknownNotationException $e) {

        }

        try {
            $file = chr(ord($this->sq[0]) + 1);
            $rank = (int)$this->sq[1] - 2;
            if (Validate::sq($file.$rank)) {
                $this->travel->jumps[] = $file . $rank;
            }
        } catch (UnknownNotationException $e) {

        }

        try {

            $file = chr(ord($this->sq[0]) + 2);
            $rank = (int)$this->sq[1] - 1;
            if (Validate::sq($file.$rank)) {
                $this->travel->jumps[] = $file . $rank;
            }
        } catch (UnknownNotationException $e) {

        }

        try {
            $file = chr(ord($this->sq[0]) + 2);
            $rank = (int)$this->sq[1] + 1;
            if (Validate::sq($file.$rank)) {
                $this->travel->jumps[] = $file . $rank;
            }
        } catch (UnknownNotationException $e) {

        }

        try {
            $file = chr(ord($this->sq[0]) + 1);
            $rank = (int)$this->sq[1] + 2;
            if (Validate::sq($file.$rank)) {
                $this->travel->jumps[] = $file . $rank;
            }
        } catch (UnknownNotationException $e) {

        }

    }

    public function getSquares(): array
    {
        $moves = [];
        foreach ($this->travel->jumps as $sq) {
            if (in_array($sq, $this->board->getSquares()->free)) {
                $moves[] = $sq;
            } elseif (in_array($sq, $this->board->getSquares()->used->{$this->getOppColor()})) {
                $moves[] = $sq;
            }
        }

        return $moves;
    }

    public function getDefendedSquares(): array
    {
        $sqs = [];
        foreach ($this->travel->jumps as $sq) {
            if (in_array($sq, $this->board->getSquares()->used->{$this->getColor()})) {
                $sqs[] = $sq;
            }
        }

        return $sqs;
    }
}
