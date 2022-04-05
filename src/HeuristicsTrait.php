<?php

namespace Chess;

use Chess\Evaluation\AttackEvaluation;
use Chess\Evaluation\BackwardPawnEvaluation;
use Chess\Evaluation\CenterEvaluation;
use Chess\Evaluation\ConnectivityEvaluation;
use Chess\Evaluation\IsolatedPawnEvaluation;
use Chess\Evaluation\KingSafetyEvaluation;
use Chess\Evaluation\MaterialEvaluation;
use Chess\Evaluation\PressureEvaluation;
use Chess\Evaluation\SpaceEvaluation;
use Chess\Evaluation\TacticsEvaluation;
use Chess\Evaluation\DoubledPawnEvaluation;
use Chess\Evaluation\PassedPawnEvaluation;
use Chess\Evaluation\InverseEvaluationInterface;
use Chess\Evaluation\AbsolutePinEvaluation;
use Chess\Evaluation\RelativePinEvaluation;
use Chess\Evaluation\AbsoluteForkEvaluation;
use Chess\Evaluation\RelativeForkEvaluation;
use Chess\Evaluation\SqOutpostEvaluation;
use Chess\Evaluation\KnightOutpostEvaluation;
use Chess\Evaluation\BishopOutpostEvaluation;
use Chess\PGN\Symbol;

/**
 * HeuristicsTrait
 *
 * A chess game can be thought of in terms of snapshots describing what's going on
 * the board as reported by a number of evaluation features. Thus, it can be plotted
 * in terms of balance. +1 is the best possible evaluation for White and -1 the
 * best possible evaluation for Black. Both forces being set to 0 means they're
 * actually offset and, therefore, balanced.
 */
trait HeuristicsTrait
{
    /**
     * The evaluation features that make up a heuristic picture.
     *
     * The sum of the weights equals to 100 as per a multiple-criteria decision analysis
     * (MCDA) based on the point allocation method. This allows to label input vectors
     * for further machine learning purposes.
     *
     * The order in which the different chess evaluation features are arranged as
     * a dimension doesn't really matter. The first permutation is used to somehow
     * highlight that a particular dimension is a restricted permutation actually.
     *
     * Let the grandmasters label chess positions. Once a particular position is
     * successfully transformed into an input vector of numbers, then it can be
     * labeled on the assumption that the best possible move that could be made
     * was made — by a chess grandmaster.
     *
     * @var array
     */
    protected $dimensions = [
        MaterialEvaluation::class => 28,
        CenterEvaluation::class => 4,
        ConnectivityEvaluation::class => 4,
        SpaceEvaluation::class => 4,
        PressureEvaluation::class => 4,
        KingSafetyEvaluation::class => 4,
        TacticsEvaluation::class => 4,
        AttackEvaluation::class => 4,
        DoubledPawnEvaluation::class => 4,
        PassedPawnEvaluation::class => 4,
        IsolatedPawnEvaluation::class => 4,
        BackwardPawnEvaluation::class => 4,
        AbsolutePinEvaluation::class => 4,
        RelativePinEvaluation::class => 4,
        AbsoluteForkEvaluation::class => 4,
        RelativeForkEvaluation::class => 4,
        SqOutpostEvaluation::class => 4,
        KnightOutpostEvaluation::class => 4,
        BishopOutpostEvaluation::class => 4,
    ];

    /**
     * The heuristic picture of $this->board.
     *
     * @var array
     */
    protected $picture = [];

    /**
     * The balanced heuristic picture of $this->board.
     *
     * @var array
     */
    protected $balance = [];

    /**
     * Returns the weighted dimensions.
     *
     * @return array
     */
    public function getDimensions(): array
    {
        return $this->dimensions;
    }

    /**
     * Sets the dimensions.
     *
     * @param array $dimensions
     * @return \Chess\Heuristics
     */
    public function setDimensions(array $dimensions)
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * Returns the heuristic picture.
     *
     * @return array
     */
    public function getPicture(): array
    {
        return $this->picture;
    }

    /**
     * Returns the balanced heuristic picture.
     *
     * @return array
     */
    public function getBalance(): array
    {
        return $this->balance;
    }
}
