<?php

class Parser {
	
	private $codeFile = 'data.vt';
	private $mainDelimiter = ';';
	private $subDelimiter = '[';
	private $closeSubDelimiter = ']';
	private $childrenDelimiter = ',';
	private $struct = [];
	private $maxCount = 0;
		
	public function run() {
		$struct = $this->getTreeStructure();
		$textTree = [];
		$counter = 0;
		foreach ($struct as $nodes) {
			$childrenString = '';
			$childrenStringUnder = '';
			if ($counter == 0) {
				reset($this->struct);
				$parent = key($this->struct);
				$spaces = $this->generateSpaces();
				$textTree[] = "\n".$spaces.$parent."\n";
				$spaces = $this->generateCountedSpaces(intval(strlen($parent)/2) - 1);
				$textTree[] = $spaces.$this->generateVerticalLines(count($this->struct[$parent]))."\n";
			}
			foreach ($nodes as $node) {
				if (isset($this->struct[$node])) {
					$childrenString .= ' '.$node;
					$childrenStringUnder .= $this->generateVerticalLines(count($this->struct[$node]));
				} else {
					$childrenString .= ' '.$node;
				}
			}
			$spaces = $this->generateCountedSpaces(intval(strlen($childrenString)/2) - 1);
			$spaces2 = $this->generateCountedSpaces(intval(strlen($childrenString)/2) - 1);
			$textTree[] = $spaces.$childrenString."\n";
			$textTree[] = $spaces2.$childrenStringUnder."\n";
			$counter++;
		}
		$fullTree = implode('', $textTree);
		echo $fullTree;
		//echo '<pre>';var_dump($fullTree);die();
	}
	
	private function getTreeStructure() {
		$data = file($this->codeFile);
		$modifiedStruct = [];
		foreach ($data as $line) {
			$parentParts = explode($this->mainDelimiter, trim($line));
			$joinedChildren = []; 
			foreach ($parentParts as $part) {
				$splitedData = explode($this->subDelimiter, $part);
				$children = trim($splitedData[1], $this->closeSubDelimiter);
				$children = explode($this->childrenDelimiter, $children);
				$children = array_map('trim', $children);
				$joinedChildren = array_merge($joinedChildren, $children);
				$this->struct[trim($splitedData[0])] = $children;	
			}
			$this->setMaxCount($joinedChildren);
			$modifiedStruct[] = $joinedChildren;
		}
		return $modifiedStruct;
	}
	
	private function generateVerticalLines($count) {
		$lines = '';
		for($i = 0;$i < $count;$i++) {
			$lines .= ' | ';
		}
		return $lines;
	}
	
	private function setMaxCount($array) {
		$currentCount = 0;
		foreach($array as $string) {
			$currentCount += strlen($string);
		}
		$currentCount += 3 * count($array);
		if ($currentCount > $this->maxCount) {
			$this->maxCount = $currentCount;
		}
	}
	
	private function generateSpaces() {
		$spaces = '';
		for($i = 0;$i < $this->maxCount;$i++) {
			$spaces .= ' ';
		}
		return $spaces;
	}
	
		private function generateCountedSpaces($count) {
		$spaces = '';
		for($i = 0;$i < ($this->maxCount - $count);$i++) {
			$spaces .= ' ';
		}
		return $spaces;
	}
	
}

$parser = new Parser();
$parser->run();