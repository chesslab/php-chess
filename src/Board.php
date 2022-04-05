<?php

namespace Chess;

use Chess\Castling;
use Chess\Exception\BoardException;
use Chess\Evaluation\DefenseEvaluation;
use Chess\Evaluation\PressureEvaluation;
use Chess\Evaluation\SpaceEvaluation;
use Chess\Evaluation\SqEvaluation;
use Chess\PGN\Convert;
use Chess\PGN\Move;
use Chess\PGN\Symbol;
use Chess\PGN\Validate;
use Chess\Piece\AbstractPiece;
use Chess\Piece\Bishop;
use Chess\Piece\King;
use Chess\Piece\Knight;
use Chess\Piece\Pawn;
use Chess\Piece\Piece;
use Chess\Piece\Queen;
use Chess\Piece\Rook;
use Chess\Piece\Type\RookType;

/**
 * Chess board.
 *
 * @author Jordi Bassagañas
 * @license GPL
 */
final class Board extends \SplObjectStorage
{
    use BoardObserverPieceTrait;

    /**
     * Current player's turn.
     *
     * @var string
     */
    private $turn;

    /**
     * Captured pieces.
     *
     * @var array
     */
    private $captures = [
        Symbol::WHITE => [],
        Symbol::BLACK => [],
    ];

    /**
     * History.
     *
     * @var array
     */
    private $history = [];

    /**
     * Castling status.
     *
     * @var array
     */
    private $castling = [];

    /**
     * Observers.
     *
     * @var array
     */
    private $observers = [];

    /**
     * Defense evaluation.
     *
     * @var \stdClass
     */
    private $defenseEval;

    /**
     * Pressure evaluation.
     *
     * @var \stdClass
     */
    private $pressureEval;

    /**
     * Space evaluation.
     *
     * @var \stdClass
     */
    private $spaceEval;

    /**
     * Square evaluation.
     *
     * @var \stdClass
     */
    private $sqEval;

    /**
     * Constructor.
     *
     * @param array $pieces
     * @param array $castling
     */
    public function __construct(array $pieces = null, array $castling = null)
    {
        if (empty($pieces)) {
            $this->attach(new Rook(Symbol::WHITE, 'a1', RookType::CASTLE_LONG));
            $this->attach(new Knight(Symbol::WHITE, 'b1'));
            $this->attach(new Bishop(Symbol::WHITE, 'c1'));
            $this->attach(new Queen(Symbol::WHITE, 'd1'));
            $this->attach(new King(Symbol::WHITE, 'e1'));
            $this->attach(new Bishop(Symbol::WHITE, 'f1'));
            $this->attach(new Knight(Symbol::WHITE, 'g1'));
            $this->attach(new Rook(Symbol::WHITE, 'h1', RookType::CASTLE_SHORT));
            $this->attach(new Pawn(Symbol::WHITE, 'a2'));
            $this->attach(new Pawn(Symbol::WHITE, 'b2'));
            $this->attach(new Pawn(Symbol::WHITE, 'c2'));
            $this->attach(new Pawn(Symbol::WHITE, 'd2'));
            $this->attach(new Pawn(Symbol::WHITE, 'e2'));
            $this->attach(new Pawn(Symbol::WHITE, 'f2'));
            $this->attach(new Pawn(Symbol::WHITE, 'g2'));
            $this->attach(new Pawn(Symbol::WHITE, 'h2'));
            $this->attach(new Rook(Symbol::BLACK, 'a8', RookType::CASTLE_LONG));
            $this->attach(new Knight(Symbol::BLACK, 'b8'));
            $this->attach(new Bishop(Symbol::BLACK, 'c8'));
            $this->attach(new Queen(Symbol::BLACK, 'd8'));
            $this->attach(new King(Symbol::BLACK, 'e8'));
            $this->attach(new Bishop(Symbol::BLACK, 'f8'));
            $this->attach(new Knight(Symbol::BLACK, 'g8'));
            $this->attach(new Rook(Symbol::BLACK, 'h8', RookType::CASTLE_SHORT));
            $this->attach(new Pawn(Symbol::BLACK, 'a7'));
            $this->attach(new Pawn(Symbol::BLACK, 'b7'));
            $this->attach(new Pawn(Symbol::BLACK, 'c7'));
            $this->attach(new Pawn(Symbol::BLACK, 'd7'));
            $this->attach(new Pawn(Symbol::BLACK, 'e7'));
            $this->attach(new Pawn(Symbol::BLACK, 'f7'));
            $this->attach(new Pawn(Symbol::BLACK, 'g7'));
            $this->attach(new Pawn(Symbol::BLACK, 'h7'));
            $this->castling = Castling::$initialState;
        } else {
            foreach ($pieces as $piece) {
                $this->attach($piece);
            }
            $this->castling = $castling;
        }

        $this->refresh();
    }

    /**
     * Gets the current turn.
     *
     * @return string
     */
    public function getTurn(): string
    {
        return $this->turn;
    }

    /**
     * Sets the current turn.
     *
     * @param string $color
     * @return \Chess\Board
     */
    public function setTurn(string $color): Board
    {
        $this->turn = Validate::color($color);

        return $this;
    }

    /**
     * Gets the square evaluation.
     *
     * @return \stdClass
     */
    public function getSqEval(): \stdClass
    {
        return $this->sqEval;
    }

    /**
     * Gets the space evaluation.
     *
     * @return \stdClass
     */
    public function getSpaceEval(): \stdClass
    {
        return $this->spaceEval;
    }

    /**
     * Gets the defense evaluation.
     *
     * @return \stdClass
     */
    public function getDefenseEval(): \stdClass
    {
        return $this->defenseEval;
    }

    /**
     * Gets the castling status.
     *
     * @return array
     */
    public function getCastling(): ?array
    {
        return $this->castling;
    }

    /**
     * Gets the captured pieces.
     *
     * @return \stdClass
     */
    public function getCaptures(): ?array
    {
        return $this->captures;
    }

    /**
     * Adds a new element to the captured pieces.
     *
     * @param string $color
     * @param \stdClass $capture
     * @return \Chess\Board
     */
    private function pushCapture(string $color, \stdClass $capture): Board
    {
        $this->captures[$color][] = $capture;

        return $this;
    }

    /**
     * Removes an element from the captured pieces.
     *
     * @param string $color
     * @return \Chess\Board
     */
    private function popCapture(string $color): Board
    {
        array_pop($this->captures[$color]);

        return $this;
    }

    /**
     * Gets the history.
     *
     * @return array
     */
    public function getHistory(): ?array
    {
        return $this->history;
    }

    /**
     * Gets the last history entry.
     *
     * @return mixed object|null
     */
    public function getLastHistory(): ?\stdClass
    {
        if (!empty($this->history)) {
            return end($this->history);
        }

        return null;
    }

    /**
     * Gets the movetext.
     *
     * @return string
     */
    public function getMovetext(): string
    {
        $i = 1;
        $movetext = '';
        foreach ($this->history as $key => $val) {
            if ($key % 2 === 0) {
                $movetext .= $i . ".{$val->move->pgn}";
                $i++;
            } else {
                $movetext .= " {$val->move->pgn} ";
            }
        }

        return trim($movetext);
    }

    /**
     * Adds a new element to the history.
     *
     * @param \stdClass $piece The piece's previous position along with a move object
     * @return \Chess\Board
     */
    private function pushHistory(Piece $piece): Board
    {
        $this->history[] = (object) [
            'sq' => $piece->getSquare(),
            'move' => $piece->getMove(),
        ];

        return $this;
    }

    /**
     * Removes an element from the history.
     *
     * @param string $color
     */
    private function popHistory(): Board
    {
        array_pop($this->history);

        return $this;
    }

    /**
     * Gets the first piece on the board matching the search criteria.
     *
     * @param string $color
     * @param string $id
     * @return mixed \Chess\Piece\Piece|null
     */
    public function getPiece(string $color, string $id): ?Piece
    {
        $this->rewind();
        while ($this->valid()) {
            $piece = $this->current();
            if ($piece->getColor() === $color && $piece->getId() === $id) {
                return $piece;
            }
            $this->next();
        }

        return null;
    }

    /**
     * Gets all pieces by color.
     *
     * @param string $color
     * @return array
     */
    public function getPiecesByColor(string $color): array
    {
        $pieces = [];
        $this->rewind();
        while ($this->valid()) {
            $piece = $this->current();
            $piece->getColor() !== $color ?: $pieces[] = $piece;
            $this->next();
        }

        return $pieces;
    }

    /**
     * Gets all pieces.
     *
     * @return array
     */
    public function getPieces(): array
    {
        $pieces = [];
        $this->rewind();
        while ($this->valid()) {
            $pieces[] = $this->current();
            $this->next();
        }

        return $pieces;
    }

    /**
     * Gets a piece by its position on the board.
     *
     * @param string $sq
     * @return mixed \Chess\Piece\Piece|null
     */
    public function getPieceBySq(string $sq): ?Piece
    {
        $this->rewind();
        while ($this->valid()) {
            $piece = $this->current();
            if ($piece->getSquare() === $sq) {
                return $piece;
            }
            $this->next();
        }

        return null;
    }

    /**
     * Picks a piece to be moved.
     *
     * @param \stdClass $move
     * @return array The piece(s) matching the PGN move; otherwise null
     * @throws \Chess\Exception\BoardException
     */
    private function pickPiece(\stdClass $move): array
    {
        $found = [];
        foreach ($this->getPiecesByColor($move->color) as $piece) {
            if ($piece->getId() === $move->id) {
                if ($piece->getId() === Symbol::K) {
                    return [$piece->setMove($move)];
                } elseif (preg_match("/{$move->sq->current}/", $piece->getSquare())) {
                    $found[] = $piece->setMove($move);
                }
            }
        }
        if (empty($found)) {
            throw new BoardException("{$move->color} {$move->id} on {$move->sq->current}.");
        }

        return $found;
    }

    /**
     * Captures a piece.
     *
     * @param \Chess\Piece\Piece $piece
     * @return \Chess\Board
     */
    private function capture(Piece $piece): Board
    {
        $piece->getSqs(); // creates the enPassantSquare property if the piece is a pawn
        if ($piece->getId() === Symbol::P && !empty($piece->getEnPassantSq()) &&
            empty($this->getPieceBySq($piece->getMove()->sq->next))
           ) {
            if ($captured = $this->getPieceBySq($piece->getEnPassantSq())) {
                $capturedData = (object) [
                    'id' => $captured->getId(),
                    'sq' => $piece->getEnPassantSq(),
                ];
            }
        } else {
            if ($captured = $this->getPieceBySq($piece->getMove()->sq->next)) {
                $capturedData = (object) [
                    'id' => $captured->getId(),
                    'sq' => $captured->getSquare(),
                ];
            }
        }
        if ($captured) {
            $capturingData = (object) [
                'id' => $piece->getId(),
                'sq' => $piece->getSquare(),
            ];
            $piece->getId() !== Symbol::R ?: $capturingData->type = $piece->getType();
            $captured->getId() !== Symbol::R ?: $capturedData->type = $captured->getType();
            $capture = (object) [
                'capturing' => $capturingData,
                'captured' => $capturedData,
            ];
            $this->pushCapture($piece->getColor(), $capture);
            $this->detach($captured);
        }

        return $this;
    }

    /**
     * Promotes a pawn.
     *
     * @param \Chess\Piece\Pawn $pawn
     * @return \Chess\Board
     */
    private function promote(Pawn $pawn): Board
    {
        $this->detach($this->getPieceBySq($pawn->getMove()->sq->next));
        switch ($pawn->getMove()->newIdentity) {
            case Symbol::N:
                $this->attach(new Knight($pawn->getColor(), $pawn->getMove()->sq->next));
                break;
            case Symbol::B:
                $this->attach(new Bishop($pawn->getColor(), $pawn->getMove()->sq->next));
                break;
            case Symbol::R:
                $this->attach(new Rook($pawn->getColor(), $pawn->getMove()->sq->next, RookType::PROMOTED));
                break;
            default:
                $this->attach(new Queen($pawn->getColor(), $pawn->getMove()->sq->next));
                break;
        }

        return $this;
    }

    /**
     * Checks out if a chess move is valid.
     *
     * @param \stdClass $move
     * @return bool true if the move is valid; otherwise false
     */
    private function isValidMove(\stdClass $move): bool
    {
        if ($move->color !== $this->turn) {
            return false;
        } elseif (
            $move->isCapture &&
            empty($this->getPieceBySq($move->sq->next)) &&
            $move->id !== Symbol::P
        ) {
            return false;
        } elseif (!$move->isCapture && !empty($this->getPieceBySq($move->sq->next))) {
            return false;
        }

        return true;
    }

    /**
     * Checks out if a chess move is legal.
     *
     * @param \stdClass $move
     * @return bool true if the move is legal; otherwise false
     */
    private function isLegalMove(\stdClass $move): bool
    {
        $isLegalMove = false;
        $pieces = $this->pickPiece($move);
        if (count($pieces) > 1) {
            foreach ($pieces as $piece) {
                if ($piece->isMovable() && !$this->leavesInCheck($piece)) {
                    return $this->move($piece);
                }
            }
        } elseif (current($pieces)->isMovable() && !$this->leavesInCheck(current($pieces))) {
            $piece = current($pieces);
            switch ($piece->getMove()->type) {
                case Move::CASTLE_SHORT:
                    Castling::short($this->turn, $this->castling, $this->spaceEval)
                        ? $isLegalMove = $this->castle($piece)
                        : $isLegalMove = false;
                    break;
                case Move::CASTLE_LONG:
                    Castling::long($this->turn, $this->castling, $this->spaceEval)
                        ? $isLegalMove = $this->castle($piece)
                        : $isLegalMove = false;
                    break;
                default:
                    $isLegalMove = $this->move($piece);
                    break;
            }
        }

        return $isLegalMove;
    }

    /**
     * Makes a move.
     *
     * @param string $color
     * @param string $pgn
     * @return bool true if the move can be made; otherwise false
     */
    public function play(string $color, string $pgn): bool
    {
        $stdObj = Convert::toStdClass($color, $pgn);

        return $this->isValidMove($stdObj) && $this->isLegalMove($stdObj);
    }

    /**
     * Castles the king.
     *
     * @param \Chess\Piece\King $king
     * @return bool true if the castling move can be made; otherwise false
     */
    private function castle(King $king): bool
    {
        $rook = $king->getCastlingRook(iterator_to_array($this, false));
        if (!empty($rook)) {
            $this->detach($this->getPieceBySq($king->getSquare()));
            $this->attach(
                new King(
                    $king->getColor(),
                    Castling::color($king->getColor())[Symbol::K][rtrim($king->getMove()->pgn, '+')]['sq']['next']
                )
             );
            $this->detach($rook);
            $this->attach(
                new Rook(
                    $rook->getColor(),
                    Castling::color($king->getColor())[Symbol::R][rtrim($king->getMove()->pgn, '+')]['sq']['next'],
                    $rook->getId() === Symbol::R
                )
            );
            $this->castling[$this->turn] = [
                Castling::IS_CASTLED => true,
                Symbol::CASTLE_SHORT => false,
                Symbol::CASTLE_LONG => false,
            ];
            $this->pushHistory($king)->refresh();
            return true;
        }

        return false;
    }

    /**
     * Undoes a castle move.
     *
     * @param array $prevCastling
     * @return \Chess\Board
     */
    private function undoCastle(array $prevCastling): Board
    {
        $prev = end($this->history);
        $king = $this->getPieceBySq($prev->move->sq->next);
        $kingUndone = new King($prev->move->color, $prev->sq);
        $this->detach($king);
        $this->attach($kingUndone);
        if (Move::CASTLE_SHORT === $prev->move->type) {
            $rook = $this->getPieceBySq(
                Castling::color($prev->move->color)[Symbol::R][Symbol::CASTLE_SHORT]['sq']['next']
            );
            $rookUndone = new Rook(
                $prev->move->color,
                Castling::color($prev->move->color)[Symbol::R][Symbol::CASTLE_SHORT]['sq']['current'],
                $rook->getType()
            );
            $this->detach($rook);
            $this->attach($rookUndone);
        } elseif (Move::CASTLE_LONG === $prev->move->type) {
            $rook = $this->getPieceBySq(
                Castling::color($prev->move->color)[Symbol::R][Symbol::CASTLE_LONG]['sq']['next']
            );
            $rookUndone = new Rook(
                $prev->move->color,
                Castling::color($prev->move->color)[Symbol::R][Symbol::CASTLE_LONG]['sq']['current'],
                $rook->getType()
            );
            $this->detach($rook);
            $this->attach($rookUndone);
        }
        $this->castling = $prevCastling;
        $this->popHistory()->refresh();

        return $this;
    }

    /**
     * Updates the castling property.
     *
     * @param \Chess\Piece\Piece $pieceMoved
     * @return \Chess\Board
     */
    private function updateCastling(Piece $pieceMoved): Board
    {
        if (!$this->castling[$this->turn][Castling::IS_CASTLED]) {
            if ($pieceMoved->getId() === Symbol::K) {
                $this->castling[$this->turn] = [
                    Castling::IS_CASTLED => false,
                    Symbol::CASTLE_SHORT => false,
                    Symbol::CASTLE_LONG => false,
                ];
            } elseif ($pieceMoved->getId() === Symbol::R) {
                if ($pieceMoved->getType() === RookType::CASTLE_SHORT) {
                    $this->castling[$this->turn][Symbol::CASTLE_SHORT] = false;
                } elseif ($pieceMoved->getType() === RookType::CASTLE_LONG) {
                    $this->castling[$this->turn][Symbol::CASTLE_LONG] = false;
                }
            }
        }
        $oppColor = Convert::toOpposite($this->turn);
        if (!$this->castling[$oppColor][Castling::IS_CASTLED]) {
            if ($pieceMoved->getMove()->isCapture) {
                if ($pieceMoved->getMove()->sq->next ===
                    Castling::color($oppColor)[Symbol::R][Symbol::CASTLE_SHORT]['sq']['current']
                ) {
                    $this->castling[$oppColor][Symbol::CASTLE_SHORT] = false;
                } elseif (
                    $pieceMoved->getMove()->sq->next ===
                    Castling::color($oppColor)[Symbol::R][Symbol::CASTLE_LONG]['sq']['current']
                ) {
                    $this->castling[$oppColor][Symbol::CASTLE_LONG] = false;
                }
            }
        }

        return $this;
    }

    /**
     * Moves a piece.
     *
     * @param \Chess\Piece\Piece $piece
     * @return bool true if the move can be made; otherwise false
     */
    private function move(Piece $piece): bool
    {
        if ($piece->getMove()->isCapture) {
            $this->capture($piece);
        }
        $this->detach($this->getPieceBySq($piece->getSquare()));
        $pieceClass = new \ReflectionClass(get_class($piece));
        $this->attach(
            $pieceClass->newInstanceArgs([
                $piece->getColor(),
                $piece->getMove()->sq->next,
                $piece->getId() !== Symbol::R ?: $piece->getType(),
            ])
        );
        if ($piece->getId() === Symbol::P) {
            if ($piece->isPromoted()) {
                $this->promote($piece);
            }
        }
        $this->updateCastling($piece)->pushHistory($piece)->refresh();

        return true;
    }

    /**
     * Undoes the last move.
     *
     * @param array $prevCastling
     * @return \Chess\Board
     */
    public function undoMove(array $prevCastling): Board
    {
        $prev = end($this->history);
        if ($prev) {
            $piece = $this->getPieceBySq($prev->move->sq->next);
            $this->detach($piece);
            if ($prev->move->type === Move::PAWN_PROMOTES ||
                $prev->move->type === Move::PAWN_CAPTURES_AND_PROMOTES) {
                $pieceUndone = new Pawn($prev->move->color, $prev->sq);
            } else {
                $pieceUndoneClass = new \ReflectionClass(get_class($piece));
                $pieceUndone = $pieceUndoneClass->newInstanceArgs([
                    $prev->move->color,
                    $prev->sq,
                    $piece->getId() !== Symbol::R ?: $piece->getType(),
                ]);
            }
            $this->attach($pieceUndone);
            if ($prev->move->isCapture && $capture = end($this->captures[$prev->move->color])) {
                $capturedClass = new \ReflectionClass(Convert::toClassName($capture->captured->id));
                $this->attach($capturedClass->newInstanceArgs([
                    $prev->move->color === Symbol::WHITE ? Symbol::BLACK : Symbol::WHITE,
                    $capture->captured->sq,
                    $capture->captured->id !== Symbol::R ?: $capture->captured->type,
                  ])
                );
                $this->popCapture($prev->move->color);
            }
            !isset($prevCastling) ?: $this->castling = $prevCastling;
            $this->popHistory()->refresh();
        }

        return $this;
    }

    /**
     * Refreshes the board's status.
     */
    public function refresh(): void
    {
        $this->turn = Convert::toOpposite($this->turn);

        $this->sqEval = (object) [
            SqEvaluation::TYPE_FREE => (new SqEvaluation($this))->eval(SqEvaluation::TYPE_FREE),
            SqEvaluation::TYPE_USED => (object) (new SqEvaluation($this))->eval(SqEvaluation::TYPE_USED),
        ];

        $this->detachPieces()
            ->attachPieces()
            ->notifyPieces();

        $this->spaceEval = (object) (new SpaceEvaluation($this))->eval();
        $this->pressureEval = (object) (new PressureEvaluation($this))->eval();
        $this->defenseEval = (object) (new DefenseEvaluation($this))->eval();

        $this->notifyPieces();
    }

    /**
     * Checks out if the board is in check when a piece is moved.
     *
     * @param \Chess\Piece\Piece $piece
     * @return bool
     */
    private function leavesInCheck(Piece $piece): bool
    {
        $prevCastling = $this->castling;
        if ($piece->getMove()->type === Move::CASTLE_SHORT ||
            $piece->getMove()->type === Move::CASTLE_LONG) {
            $this->castle($piece);
            $king = $this->getPiece($piece->getColor(), Symbol::K);
            $leavesInCheck = in_array($king->getSquare(), $this->pressureEval->{$king->getOppColor()});
            $this->undoCastle($prevCastling);
        } else {
            $this->move($piece);
            $king = $this->getPiece($piece->getColor(), Symbol::K);
            $leavesInCheck = in_array($king->getSquare(), $this->pressureEval->{$king->getOppColor()});
            $this->undoMove($prevCastling);
        }

        return $leavesInCheck;
    }

    /**
     * Checks out whether a player is trapped.
     *
     * @return bool
     */
    private function isTrapped(): bool
    {
        $escape = 0;
        foreach ($this->getPiecesByColor($this->turn) as $piece) {
            foreach ($piece->getSqs() as $sq) {
                switch ($piece->getId()) {
                    case Symbol::K:
                        if (in_array($sq, $this->sqEval->used->{$piece->getOppColor()})) {
                            $escape += (int) !$this->leavesInCheck(
                                $piece->setMove(Convert::toStdClass($this->turn, Symbol::K."x$sq"))
                            );
                        } elseif (!in_array($sq, $this->spaceEval->{$piece->getOppColor()})) {
                            $escape += (int) !$this->leavesInCheck(
                                $piece->setMove(Convert::toStdClass($this->turn, Symbol::K.$sq))
                            );
                        }
                        break;
                    case Symbol::P:
                        if (in_array($sq, $this->sqEval->used->{$piece->getOppColor()})) {
                            $escape += (int) !$this->leavesInCheck(
                                $piece->setMove(Convert::toStdClass($this->turn, $piece->getFile()."x$sq"))
                            );
                        } else {
                            $escape += (int) !$this->leavesInCheck(
                                $piece->setMove(Convert::toStdClass($this->turn, $sq))
                            );
                        }
                        break;
                    default:
                        if (in_array($sq, $this->sqEval->used->{$piece->getOppColor()})) {
                            $escape += (int) !$this->leavesInCheck(
                                $piece->setMove(Convert::toStdClass($this->turn, $piece->getId()."x$sq"))
                            );
                        } else {
                            $escape += (int) !$this->leavesInCheck(
                                $piece->setMove(Convert::toStdClass($this->turn, $piece->getId().$sq))
                            );
                        }
                        break;
                }
            }
        }

        return $escape === 0;
    }

    /**
     * Checks out whether a player is in check.
     *
     * @return bool
     */
    public function isCheck(): bool
    {
        $king = $this->getPiece($this->turn, Symbol::K);

        return in_array(
            $king->getSquare(),
            $this->pressureEval->{$king->getOppColor()}
        );
    }

    /**
     * Checks out whether a player is checkmated.
     *
     * @return bool
     */
    public function isMate(): bool
    {
        return $this->isTrapped() && $this->isCheck();
    }

    /**
     * Checks out whether a player is stalemated.
     *
     * @return bool
     */
    public function isStalemate(): bool
    {
        return $this->isTrapped() && !$this->isCheck();
    }

    /**
     * Returns all possible moves.
     *
     * @return array
     */
    public function getMoves(): array
    {
        $moves = [];
        $color = $this->getTurn();
        foreach ($this->getPiecesByColor($color) as $piece) {
            foreach ($piece->getSqs() as $sq) {
                $clone = unserialize(serialize($this));
                switch ($piece->getId()) {
                    case Symbol::K:
                        if (
                            Castling::color($color)[Symbol::K][Symbol::CASTLE_SHORT]['sq']['next'] === $sq &&
                            $clone->play($color, Symbol::CASTLE_SHORT)
                        ) {
                            $moves[] = Symbol::CASTLE_SHORT;
                        } elseif (
                            Castling::color($color)[Symbol::K][Symbol::CASTLE_LONG]['sq']['next'] === $sq &&
                            $clone->play($color, Symbol::CASTLE_LONG)
                        ) {
                            $moves[] = Symbol::CASTLE_LONG;
                        } elseif ($clone->play($color, Symbol::K.$sq)) {
                            $moves[] = Symbol::K.$sq;
                        } elseif ($clone->play($color, Symbol::K.'x'.$sq)) {
                            $moves[] = Symbol::K.'x'.$sq;
                        }
                        break;
                    case Symbol::P:
                        if ($clone->play($color, $sq)) {
                            $moves[] = $sq;
                        } elseif ($clone->play($color, $piece->getFile()."x$sq")) {
                            $moves[] = $piece->getFile()."x$sq";
                        }
                        break;
                    default:
                        if ($clone->play($color, $piece->getId().$sq)) {
                            $moves[] = $piece->getId().$sq;
                        } elseif ($clone->play($color, "{$piece->getId()}x$sq")) {
                            $moves[] = "{$piece->getId()}x$sq";
                        }
                        break;
                }
            }
        }

        return $moves;
    }
}
