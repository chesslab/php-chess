<?php

namespace ChessData\Cli;

require_once __DIR__ . '/../vendor/autoload.php';

use Chess\Ascii;
use Chess\FEN\StringToBoard;
use Chess\Game;
use Chess\PGN\Convert;
use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;

class ModelPlayCli extends CLI
{
    const PROMPT = 'chess > ';

    protected function setup(Options $options)
    {
        $options->setHelp('Play with an AI.');
        $options->registerArgument('model', 'AI model name. The AIs are stored in the model folder.', true);
    }

    protected function main(Options $options)
    {
        $board = (new StringToBoard('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1'))
            ->create();

        $board->play(Convert::toStdObj('b', 'e5'));

        $ascii = (new Ascii())->print($board);

        print_r($ascii);

        exit;


        $game = new Game(Game::MODE_AI, $options->getArgs()[0]);

        do {
            $move = readline(self::PROMPT);
            if ($move === 'fen') {
                echo $game->fen() . PHP_EOL;
            } elseif ($move !== 'quit') {
                $game->play('w', $move);
                $response = $game->response();
                $game->play('b', $response);
                echo self::PROMPT . $game->movetext() . PHP_EOL;
                echo $game->ascii();
            } else {
                break;
            }
        } while (!$game->isMate());
    }
}

$cli = new ModelPlayCli();
$cli->run();
