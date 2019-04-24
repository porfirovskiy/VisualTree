<?php

class Parser {
	
	private $codeFile = 'data.vt';
	private $mainDelimiter = ';';
	private $subDelimiter = '[';
	private $closeSubDelimiter = ']';
	private $childrenDelimiter = ',';
	private $struct = [];
		
	public function run() {
		$struct = $this->getTreeStructure();
		$textTree = [];
		$counter = 0;
		foreach ($struct as $parent => $nodes) {
			$childrenString = '';
			$childrenStringUnder = '';
			if ($counter == 0) {
				$textTree[] = "\n".$parent."\n";
				$textTree[] = $this->generateVerticalLines(count($struct[$parent]))."\n";
			}
			//echo '<pre>';var_dump($nodes);
			foreach ($nodes as $node) {
				if (isset($struct[$node])) {
					$childrenString .= ' '.$node;
					$childrenStringUnder .= $this->generateVerticalLines(count($struct[$node]));
				} else {
					$childrenString .= ' '.$node;
				}
			}
			$textTree[] = $childrenString."\n";
			$textTree[] = $childrenStringUnder."\n";
			$counter++;
		}
		//die();
		echo '<pre>';var_dump($struct);die();
		/*foreach ($data as $code) {
			$parentParts = explode($this->mainDelimiter, trim(trim($code), $this->mainDelimiter));
			$childrenString = '';
			$childrenStringUnder = '';
			foreach ($parentParts as $part) {
				$splitedData = explode($this->subDelimiter, $part);
				echo '<pre>';var_dump($splitedData);die();
				$parent = $splitedData[0];
				$children = trim($splitedData[1], $this->closeSubDelimiter);
				$children = explode($this->childrenDelimiter, $children);
				if ($counter === 0) {
					$textTree[] = "\n".$parent."\n";
					$textTree[] = $this->generateVerticalLines(count($children))."\n";
				}
				foreach ($children as $child) {
					$childrenString .= ' '.$child;
					$childrenStringUnder .= ' | ';
				}
			}
			$textTree[] = $childrenString."\n";
			$textTree[] = $childrenStringUnder."\n";
		}*/
		$fullTree = implode('', $textTree);
		echo '<pre>';var_dump($textTree, $fullTree);die();
		
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
	
}

$parser = new Parser();
$parser->run();