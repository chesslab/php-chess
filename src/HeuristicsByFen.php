<?php

namespace Chess;

use Chess\Eval\InverseEvalInterface;
use Chess\Variant\Capablanca\Board as CapablancaBoard;
use Chess\Variant\Capablanca\FEN\StrToBoard as CapablancaFenStrToBoard;
use Chess\Variant\CapablancaFischer\Board as CapablancaFischerBoard;
use Chess\Variant\CapablancaFischer\FEN\StrToBoard as CapablancaFischerFenStrToBoard;
use Chess\Variant\Chess960\Board as Chess960Board;
use Chess\Variant\Classical\Board as ClassicalBoard;
use Chess\Variant\Classical\FEN\StrToBoard as ClassicalFenStrToBoard;
use Chess\Variant\Classical\PGN\AN\Color;

/**
 * HeuristicsByFen
 *
 * @author Jordi Bassagaña
 * @license GPL
 */
class HeuristicsByFen
{
    use HeuristicsTrait;

    protected ClassicalBoard $board;

    public function __construct(string $fen, string $variant = '')
    {
        if ($variant === Chess960Board::VARIANT) {
            $this->board = (new ClassicalFenStrToBoard($fen))->create();
        } elseif ($variant === CapablancaBoard::VARIANT) {
            $this->board = (new CapablancaFenStrToBoard($fen))->create();
        } elseif ($variant === CapablancaFischerBoard::VARIANT) {
            $this->board = (new CapablancaFischerFenStrToBoard($fen))->create();
        } else {
            $this->board = (new ClassicalFenStrToBoard($fen))->create();
        }

        $this->calc();
    }

    /**
     * Returns the current evaluation.
     *
     * @return array
     */
    public function eval(): array
    {
        $result = [
            Color::W => 0,
            Color::B => 0,
        ];

        $weights = array_values($this->getEval());

        $pic = $this->getResult();

        for ($i = 0; $i < count($this->getEval()); $i++) {
            $result[Color::W] += $weights[$i] * $pic[Color::W][$i];
            $result[Color::B] += $weights[$i] * $pic[Color::B][$i];
        }

        $result[Color::W] = round($result[Color::W], 2);
        $result[Color::B] = round($result[Color::B], 2);

        return $result;
    }

    /**
     * Returns the resized balance given a new range of values.
     *
     * @param float $newMin
     * @param float $newMax
     * @return array
     */
    public function getResizedBalance(float $newMin, float $newMax): array
    {
        $oldMin = -1;
        $oldMax = 1;
        $resize = [];
        foreach ($this->balance as $val) {
            $resized = (($val - $oldMin) / ($oldMax - $oldMin)) *
                ($newMax - $newMin) + $newMin;
            $resize[] = round($resized, 2);
        }

        return $resize;
    }

    /**
     * Heristics calc.
     *
     * @return HeuristicsByFen
     */
    protected function calc(): HeuristicsByFen
    {
        $item = [];
        foreach ($this->eval as $className => $weight) {
            $heuristic = new $className($this->board);
            $eval = $heuristic->eval();
            if (is_array($eval[Color::W])) {
                if ($heuristic instanceof InverseEvalInterface) {
                    $item[] = [
                        Color::W => count($eval[Color::B]),
                        Color::B => count($eval[Color::W]),
                    ];
                } else {
                    $item[] = [
                        Color::W => count($eval[Color::W]),
                        Color::B => count($eval[Color::B]),
                    ];
                }
            } else {
                if ($heuristic instanceof InverseEvalInterface) {
                    $item[] = [
                        Color::W => $eval[Color::B],
                        Color::B => $eval[Color::W],
                    ];
                } else {
                    $item[] = $eval;
                }
            }
        }

        $this->result[Color::W] = array_column($item, Color::W);
        $this->result[Color::B] = array_column($item, Color::B);

        $this->normalize()->balance();

        return $this;
    }

    protected function normalize(): HeuristicsByFen
    {
        $normalization = [];

        $values = [
            ...$this->result[Color::W],
            ...$this->result[Color::B]
        ];

        $min = min($values);
        $max = max($values);

        for ($i = 0; $i < count($this->eval); $i++) {
            if ($max - $min > 0) {
                $normalization[Color::W][$i] =
                    round(($this->result[Color::W][$i] - $min) / ($max - $min), 2);
                $normalization[Color::B][$i] =
                    round(($this->result[Color::B][$i] - $min) / ($max - $min), 2);
            } elseif ($max == $min) {
                $normalization[Color::W][$i] = round(1 / count($values), 2);
                $normalization[Color::B][$i] = round(1 / count($values), 2);
            }
        }

        $this->result = $normalization;

        return $this;
    }

    protected function balance(): HeuristicsByFen
    {
        foreach ($this->result[Color::W] as $key => $val) {
            $this->balance[$key] =
                round($this->result[Color::W][$key] - $this->result[Color::B][$key], 2);
        }

        return $this;
    }
}
