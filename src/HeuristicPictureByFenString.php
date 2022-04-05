<?php

namespace Chess;

use Chess\Evaluation\InverseEvaluationInterface;
use Chess\FEN\StrToBoard;
use Chess\PGN\Symbol;

class HeuristicPictureByFenString
{
    use HeuristicPictureTrait;

    protected $board;

    public function __construct(string $fen)
    {
        $this->board = (new StrToBoard($fen))->create();
    }

    /**
     * Returns the current evaluation of $this->board.
     *
     * The result obtained suggests which player is probably better.
     *
     * @return array
     */
    public function eval(): array
    {
        $result = [
            Symbol::WHITE => 0,
            Symbol::BLACK => 0,
        ];

        $weights = array_values($this->getDimensions());

        $pic = $this->take()->getPicture();

        for ($i = 0; $i < count($this->getDimensions()); $i++) {
            $result[Symbol::WHITE] += $weights[$i] * $pic[Symbol::WHITE][$i];
            $result[Symbol::BLACK] += $weights[$i] * $pic[Symbol::BLACK][$i];
        }

        $result[Symbol::WHITE] = round($result[Symbol::WHITE], 2);
        $result[Symbol::BLACK] = round($result[Symbol::BLACK], 2);

        return $result;
    }

    /**
     * Takes a normalized, balanced heuristic picture.
     *
     * @return \Chess\Heuristic\HeuristicPictureByFenString
     */
    public function take(): HeuristicPictureByFenString
    {
        $item = [];
        foreach ($this->dimensions as $dimension => $w) {
            $eval = (new $dimension($this->board))->eval();
            if (is_array($eval[Symbol::WHITE])) {
                if ($dimension instanceof InverseEvaluationInterface) {
                    $item[] = [
                        Symbol::WHITE => count($eval[Symbol::BLACK]),
                        Symbol::BLACK => count($eval[Symbol::WHITE]),
                    ];
                } else {
                    $item[] = [
                        Symbol::WHITE => count($eval[Symbol::WHITE]),
                        Symbol::BLACK => count($eval[Symbol::BLACK]),
                    ];
                }
            } else {
                if ($dimension instanceof InverseEvaluationInterface) {
                    $item[] = [
                        Symbol::WHITE => $eval[Symbol::BLACK],
                        Symbol::BLACK => $eval[Symbol::WHITE],
                    ];
                } else {
                    $item[] = $eval;
                }
            }
        }

        $this->picture[Symbol::WHITE] = array_column($item, Symbol::WHITE);
        $this->picture[Symbol::BLACK] = array_column($item, Symbol::BLACK);

        $this->normalize()->balance();

        return $this;
    }

    protected function normalize(): HeuristicPictureByFenString
    {
        $normalization = [];

        $values = array_merge(
            $this->picture[Symbol::WHITE],
            $this->picture[Symbol::BLACK]
        );

        $min = min($values);
        $max = max($values);

        for ($i = 0; $i < count($this->dimensions); $i++) {
            if ($max - $min > 0) {
                $normalization[Symbol::WHITE][$i] =
                    round(($this->picture[Symbol::WHITE][$i] - $min) / ($max - $min), 2);
                $normalization[Symbol::BLACK][$i] =
                    round(($this->picture[Symbol::BLACK][$i] - $min) / ($max - $min), 2);
            } elseif ($max == $min) {
                $normalization[Symbol::WHITE][$i] = round(1 / count($values), 2);
                $normalization[Symbol::BLACK][$i] = round(1 / count($values), 2);
            }
        }

        $this->picture = $normalization;

        return $this;
    }

    protected function balance(): HeuristicPictureByFenString
    {
        foreach ($this->picture[Symbol::WHITE] as $key => $val) {
            $this->balance[$key] = $this->picture[Symbol::WHITE][$key] - $this->picture[Symbol::BLACK][$key];
        }

        return $this;
    }
}
