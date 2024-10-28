<?php

namespace Chess\Eval;

use Chess\Variant\AbstractBoard;
use Chess\Variant\Classical\PGN\AN\Color;
use Chess\Variant\Classical\PGN\AN\Piece;
use Chess\Variant\Classical\Piece\P;

/**
 * Far Advanced Pawn Evaluation
 *
 * A far-advanced pawn is a pawn that is threatening to be promoted.
 */
class FarAdvancedPawnEval extends AbstractEval implements
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
    const NAME = 'Far-advanced pawn';

    /**
     * @param \Chess\Variant\AbstractBoard $board
     */
    public function __construct(AbstractBoard $board)
    {
        $this->board = $board;

        $this->result = [
            Color::W => [],
            Color::B => [],
        ];

        $this->range = [1, 4];

        $this->subject = [
            'White',
            'Black',
        ];

        $this->observation = [
            "has a slight far advanced pawn advantage",
            "has a moderate far advanced pawn advantage",
            "has a decisive far advanced pawn advantage",
        ];

        foreach ($this->board->pieces() as $piece) {
            if ($piece->id === Piece::P && $this->isFarAdvanced($piece)) {
                $this->result[$piece->color][] = $piece->sq;
            }
        }

        $this->explain([
            Color::W => count($this->result[Color::W]),
            Color::B => count($this->result[Color::B]),
        ]);

        $this->elaborate($this->result);
    }

    /**
     * Returns true if the pawn is far advanced.
     *
     * @param \Chess\Variant\Classical\Piece\P $pawn
     * @return bool
     */
    private function isFarAdvanced(P $pawn): bool
    {
        if ($pawn->color === Color::W) {
            if ($pawn->rank() >= 6) {
                return true;
            }
        } else {
            if ($pawn->rank() <= 3) {
                return true;
            }
        }

        return false;
    }

    /**
     * Elaborate on the evaluation.
     *
     * @param array $result
     */
    private function elaborate(array $result): void
    {
        $singular = $plural = 'threatening to promote';

        $this->shorten($result, $singular, $plural);
    }
}
