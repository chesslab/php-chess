<?php

namespace PGNChess\PGN\File;

use PGNChess\PGN\Validate;

/**
 * Syntax class.
 *
 * @author Jordi Bassagañas <info@programarivm.com>
 * @link https://programarivm.com
 * @license GPL
 */
class Syntax extends AbstractFile
{
    private $invalid = [];

    public function __construct($filepath)
    {
        parent::__construct($filepath);
    }

    /**
     * Checks if the syntax of a PGN file is valid.
     *
     * @return mixed array|bool
     */
    public function check()
    {
        $tags = $this->resetTags();
        $movetext = '';
        if ($file = fopen($this->filepath, 'r')) {
            while (!feof($file)) {
                $line = preg_replace('~[[:cntrl:]]~', '', fgets($file));
                try {
                    $tag = Validate::tag($line);
                    $tags[$tag->name] = $tag->value;
                } catch (\Exception $e) {
                    switch (true) {
                        case $this->startsMovetext($line) && !$this->hasStrTags($tags):
                            $this->invalid[] = $tags;
                            $tags = $this->resetTags();
                            $movetext = '';
                            break;
                        case $this->startsMovetext($line) && $this->hasStrTags($tags):
                            $movetext .= $line;
                            break;
                        case $this->endsMovetext($line) && $this->hasStrTags($tags):
                            $movetext .= $line;
                            Validate::movetext($movetext) ? true : $this->invalid[] = $tags;
                            $tags = $this->resetTags();
                            $movetext = '';
                            break;
                        case $this->hasStrTags($tags):
                            $movetext .= $line;
                            break;
                    }
                }
            }
            fclose($file);
        }

        return $this->invalid;
    }
}
