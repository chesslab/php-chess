<?php

namespace Chess\Eval;

use Chess\Variant\AbstractBoard;
use Chess\Variant\Classical\PGN\AN\Color;
use Chess\Variant\Classical\PGN\AN\Piece;

/**
 * Square Outpost Evaluation
 *
 * A square protected by a pawn that cannot be attacked by an opponent's pawn.
 */
class SqOutpostEval extends AbstractEval implements
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
    const NAME = 'Outpost square';

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
            "has a slight outpost advantage",
            "has a moderate outpost advantage",
            "has a total outpost advantage",
        ];

        foreach ($this->board->pieces() as $piece) {
            if ($piece->id === Piece::P) {
                $captureSqs = $piece->captureSqs;
                if ($piece->ranks['end'] !== (int) substr($captureSqs[0], 1)) {
                    $left = chr(ord($captureSqs[0]) - 1);
                    $right = chr(ord($captureSqs[0]) + 1);
                    if (
                        !$this->isFileAttacked($piece->color, $captureSqs[0], $left) &&
                        !$this->isFileAttacked($piece->color, $captureSqs[0], $right)
                    ) {
                        $this->result[$piece->color][] = $captureSqs[0];
                    }
                    if (isset($captureSqs[1])) {
                        $left = chr(ord($captureSqs[1]) - 1);
                        $right = chr(ord($captureSqs[1]) + 1);
                        if (
                            !$this->isFileAttacked($piece->color, $captureSqs[1], $left) &&
                            !$this->isFileAttacked($piece->color, $captureSqs[1], $right)
                        ) {
                            $this->result[$piece->color][] = $captureSqs[1];
                        }
                    }
                }
            }
        }

        $this->result[Color::W] = array_unique($this->result[Color::W]);
        $this->result[Color::B] = array_unique($this->result[Color::B]);

        $this->explain([
            Color::W => count($this->result[Color::W]),
            Color::B => count($this->result[Color::B]),
        ]);

        $this->elaborate($this->result);
    }

    /**
     * Returns true if the given file can be attacked by an opponent's pawn.
     *
     * @param string $color
     * @param string $sq
     * @param string $file
     * @return bool
     */
    private function isFileAttacked(string $color, string $sq, string $file): bool
    {
        $rank = substr($sq, 1);
        for ($i = 2; $i < 8; $i++) {
            if ($piece = $this->board->pieceBySq($file . $i)) {
                if (
                    $piece->id === Piece::P &&
                    $piece->color === $this->board->color->opp($color)
                ) {
                    if ($color === Color::W) {
                        if ($i > $rank) {
                            return true;
                        }
                    } else {
                        if ($i < $rank) {
                            return true;
                        }
                    }
                }
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
        $singular = mb_strtolower('an ' . self::NAME);
        $plural = mb_strtolower(self::NAME . 's');

        $this->shorten($result, $singular, $plural);
    }
}
