<?php

namespace Chess\UciEngine;

use Chess\Board;

/**
 * Stockfish.
 *
 * PHP wrapper for the Stockfish chess engine.
 *
 * @author Jordi Bassagañas
 * @license GPL
 */
class Stockfish
{
    const NAME = '/usr/games/stockfish';

    /**
     * PHP Chess board.
     *
     * @var \Chess\Board
     */
    private Board $board;

    /**
     * Process descriptor.
     *
     * @var array
     */
    private array $descr = [
        ['pipe', 'r'],
        ['pipe', 'w'],
    ];

    /**
     * Process pipes.
     *
     * @var array
     */
    private array $pipes = [];

    /**
     * Constructor.
     *
     * @param \Chess\Board $board
     */
    public function __construct(Board $board)
    {
        $this->board = $board;
    }

    /**
     * Returns the PHP Chess board.
     *
     * @return \Chess\Board
     */
    public function getBoard(): Board
    {
        return $this->board;
    }

    /**
     * Calculates the best move.
     *
     * @param string $fen
     * @param int $msec
     * @return string
     */
    public function bestMove(string $fen, int $msec): string
    {
        $bestMove = '(none)';
        $process = proc_open(self::NAME, $this->descr, $this->pipes);
        if (is_resource($process)) {
            fwrite($this->pipes[0], "uci\n");
            fwrite($this->pipes[0], "ucinewgame\n");
            fwrite($this->pipes[0], "position fen $fen\n");
            fwrite($this->pipes[0], "go movetime $msec\n");
            while (!feof($this->pipes[1])) {
                $line = fgets($this->pipes[1]);
                if (str_starts_with($line, 'bestmove')) {
                    $exploded = explode(' ', $line);
                    $bestMove = $exploded[1];
                    fclose($this->pipes[0]);
                }
            }
            fclose($this->pipes[1]);
            proc_close($process);
        }

        return $bestMove;
    }

    /**
     * Makes the best move returning a short FEN string.
     *
     * @param string $fen
     * @param int $msec
     * @return string
     */
    public function shortFen(string $fen, int $msec): string
    {
        $bestMove = $this->bestMove($fen, $msec);
        if ($bestMove !== '(none)') {
            $process = proc_open(self::NAME, $this->descr, $this->pipes);
            if (is_resource($process)) {
                fwrite($this->pipes[0], "uci\n");
                fwrite($this->pipes[0], "position fen $fen moves $bestMove\n");
                fwrite($this->pipes[0], "d\n");
                fclose($this->pipes[0]);
                while (!feof($this->pipes[1])) {
                    $line = fgets($this->pipes[1]);
                    if (str_starts_with($line, 'Fen: ')) {
                        $fen = substr($line, 5);
                        $exploded = explode(' ', $fen);
                        $fen = "{$exploded[0]} {$exploded[1]}";
                    }
                }
                fclose($this->pipes[1]);
                proc_close($process);
            }
        }

        return $fen;
    }
}
