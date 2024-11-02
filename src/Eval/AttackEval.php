<?php

namespace Chess\Eval;

use Chess\Variant\AbstractBoard;
use Chess\Variant\AbstractPiece;
use Chess\Variant\Classical\PGN\AN\Color;
use Chess\Variant\Classical\PGN\AN\Piece;

/**
 * Attack Evaluation
 *
 * A piece is under threat of being attacked if it can be taken by an
 * opponent's piece. The player with the greater number of material points under
 * threat of attack has a disadvantage.
 */
class AttackEval extends AbstractEval implements
    ElaborateEvalInterface,
    ExplainEvalInterface
{
    use ElaborateEvalTrait;
    use ExplainEvalTrait;

    /**
     * The name of the heuristic.
     *
     * @var string
     */
    const NAME = 'Attack';

    /**
     * @param \Chess\Variant\AbstractBoard $board
     */
    public function __construct(AbstractBoard $board)
    {
        $this->board = $board;

        $this->range = [0.8, 5];

        $this->subject = [
            'White',
            'Black',
        ];

        $this->observation = [
            "has a slight attack advantage",
            "has a moderate attack advantage",
            "has a total attack advantage",
        ];

        if (
            !$this->board->isCheck() &&
            !$this->board->isMate() &&
            !$this->board->isStalemate()
        ) {
            foreach ($this->board->pieces() as $piece) {
                if ($piece->id !== Piece::K) {
                    $clone = $this->board->clone();
                    $clone->turn = $piece->oppColor();
                    $attack = [
                        Color::W => 0,
                        Color::B => 0,
                    ];
                    foreach ($piece->attacking() as $attacking) {
                        $capturedPiece = $clone->pieceBySq($piece->sq);
                        if ($clone->playLan($clone->turn, $attacking->sq . $piece->sq)) {
                            $attack[$attacking->color] += self::$value[$capturedPiece->id];
                            foreach ($piece->defending() as $defending) {
                                $capturedPiece = $clone->pieceBySq($piece->sq);
                                if ($clone->playLan($clone->turn, $defending->sq . $piece->sq)) {
                                    $attack[$defending->color] += self::$value[$capturedPiece->id];
                                }
                            }
                        }
                    }
                    $diff = $attack[Color::W] - $attack[Color::B];
                    if ($piece->oppColor() === Color::W) {
                        if ($diff > 0) {
                            $this->result[Color::W] += $diff;
                            $this->elaborate($piece);
                        }
                    } else {
                        if ($diff < 0) {
                            $this->result[Color::B] += abs($diff);
                            $this->elaborate($piece);
                        }
                    }
                }
            }
        }

        $this->explain($this->result);
    }

    /**
     * Elaborate on the evaluation.
     *
     * @param \Chess\Variant\AbstractPiece $piece
     */
    private function elaborate(AbstractPiece $piece): void
    {
        $this->elaboration[] = "The {$piece->sq}-square is under threat of being attacked.";
    }
}
