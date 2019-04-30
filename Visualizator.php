<?php

class Visualizator
{
    private $maxCount = 0;

    public function buildTree($structure, $maxCount)
    {
        $this->maxCount = $maxCount;
        $spaces = $this->generateSpaces();
        foreach ($structure as $level) {
            if (count($level) == 1) {
                $level = $level[0];
                if (strpos($level, '*') !== false) {
                    echo $spaces . $level . PHP_EOL;
                } else {
                    echo $spaces . '|' . PHP_EOL . $spaces . $level . PHP_EOL;
                }
            } else {
                $spacesString = $spaces;
                foreach ($level as $key => $element) {
                    if ($key == 0) {
                        $spacesString .= '|';
                    } else {
                        $spacesString .= $this->generateSpacesByLen(strlen($element) + 1) . '|';
                    }
                }
                $spacesString = $spacesString . PHP_EOL . $spaces;
                echo $spacesString . implode(' ', $level) . PHP_EOL;
            }
        }
    }

    private function generateSpaces()
    {
        $spaces = '';
        for ($i = 0; $i < $this->maxCount; $i++) {
            $spaces .= ' ';
        }
        return $spaces;
    }

    private function generateSpacesByLen($count)
    {
        $spaces = '';
        for ($i = 0; $i < $count; $i++) {
            $spaces .= ' ';
        }
        return $spaces;
    }
}
