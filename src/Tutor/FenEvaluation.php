<?php

namespace Chess\Tutor;

use Chess\EvalArray;
use Chess\Eval\ExplainEvalTrait;
use Chess\Eval\ElaborateEvalTrait;
use Chess\Function\AbstractFunction;
use Chess\Labeller\SumLabeller;
use Chess\Variant\AbstractBoard;

class FenEvaluation extends AbstractParagraph
{
    public function __construct(AbstractFunction $f, AbstractBoard $board)
    {
        $this->f = $f;
        $this->board = $board;

        $normd = EvalArray::normalization($this->f, $this->board);

        $this->paragraph = [
            ...$this->fenExplanation(),
            ...$this->fenElaboration(),
            ...$this->count($normd),
            ...$this->sum($normd),
        ];
    }

    private function fenExplanation(): array
    {
        $paragraph = [];

        foreach ($this->f->eval as $val) {
            $eval = new $val($this->board);
            if (in_array(ExplainEvalTrait::class, class_uses($eval))) {
                if ($phrases = $eval->explain()) {
                    $paragraph = [...$paragraph, ...$phrases];
                }
            }
        }

        return $paragraph;
    }

    private function fenElaboration(): array
    {
        $paragraph = [];

        foreach ($this->f->eval as $val) {
            $eval = new $val($this->board);
            if (in_array(ElaborateEvalTrait::class, class_uses($eval))) {
                if ($phrases = $eval->elaborate()) {
                    $paragraph = [...$paragraph, ...$phrases];
                }
            }
        }

        return $paragraph;
    }

    private function count(array $normd): array
    {
        $count = EvalArray::steinitz($normd);

        if ($count > 0) {
            $color = 'White';
        } elseif ($count < 0) {
            $color = 'Black';
            $count = abs($count);
        } else {
            $color = 'either player';
        }

        return [
            "Overall, {$count} {$this->noun($count)} {$this->verb($count)} favoring {$color}.",
        ];
    }

    private function sum(array $normd): array
    {
        $sum = round(array_sum($normd), 2);

        return [
            "The relative evaluation of this position is {$sum}.",
        ];
    }

    private function noun(int $count): string
    {
        return $count === 1 ? 'evaluation feature' : 'evaluation features';
    }

    private function verb(int $count)
    {
        return $count === 1 ? 'is' : 'are';
    }
}
