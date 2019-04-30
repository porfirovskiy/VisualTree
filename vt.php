<?php

include_once('Parser.php');
include_once('Visualizator.php');

$parser = new Parser();
$visualizator = new Visualizator();
$structure = $parser->parse();
$visualizator->buildTree($structure, $parser->maxCount);
//echo '<pre>';var_dump($this->maxCount);die();