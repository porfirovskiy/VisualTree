<?php

class Parser {
	
	private $codeFile = 'data.vt';
	private $mainDelimiter = ';';
	private $subDelimiter = '[';
	private $closeSubDelimiter = ']';
	private $childrenDelimiter = ',';
	private $struct = [];
	private $maxCount = 0;
	
	private $uniqNames = [];
	private $uniqEntities = [];
	private $arrayStructure = [];
		
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
	
	
	//new functionality!!!
	public function run2() {
		$codeString = $this->getCodeFromFile();
		$this->getUniqNames($codeString);
		$this->getUniqEntities($codeString);
		$this->makeArrayStructure($codeString);
		$this->buildTree();
	}
	
	private function getCodeFromFile() {
		$code = file_get_contents($this->codeFile);
		$code = str_replace(PHP_EOL, '', $code);
		return $code;
	}
	
	private function getUniqNames($codeString) {
		preg_match_all("/\[[a-z0-9 ,]+\]/", $codeString, $names, PREG_SET_ORDER);
		if (!empty($names)) {
			$this->fillUniqNames($names);
		}	
	}
	
	public function fillUniqNames($names) {
		foreach ($names as $name) {
			$names = preg_replace('/\[|\]/', '', $name[0]);
			$this->uniqNames = array_merge($this->uniqNames, array_map('trim', explode(',', $names)));
		}
	}
	
	public function getUniqEntities($codeString) {
		preg_match_all("/[a-z0-9 *]+\[[a-z0-9 ,]+\]/", $codeString, $entities, PREG_SET_ORDER);
		if (!empty($entities)) {
			$this->fillUniqEntities($entities);
		}	
	}
	
	public function fillUniqEntities($entities) {
		foreach ($entities as $entity) {
			$entity = $entity[0];
			$root = $this->getRoot($entity);
			$children = $this->getChildren($entity);
			$this->uniqEntities[$root] = $children;
		}
	}
	
	public function getRoot($codeString) {
		preg_match_all("/[a-z0-9 *]+\[/", $codeString, $root, PREG_SET_ORDER);
		$root = preg_replace('/\[/', '', trim($root[0][0]));
		if (!empty($root)) {
			return $root;
		} else {
			return '';
		}	
	}
	
	public function getChildren($codeString) {
		preg_match_all("/\[[a-z0-9 ,]+\]/", $codeString, $children, PREG_SET_ORDER);
		$children = preg_replace('/\[|\]/', '', $children[0][0]);
		$children = array_map('trim', explode(',', $children));
		if (!empty($children)) {
			return $children;
		} else {
			return [];
		}	
	}
	
	private function makeArrayStructure($codeString) {
		$strings = explode(';', $codeString);
		foreach ($strings as $string) {
			preg_match_all("/[a-z0-9 *]+\[/", $string, $result, PREG_SET_ORDER);
			if (!empty($result)) {
				$elements = [];
				foreach ($result as $value) {
					$elements[] = preg_replace('/\[/', '', trim($value[0]));
				}
				$this->arrayStructure[] = $elements;
			}
		}
	}
	
	public function buildTree() {
		
	}
	//echo '<pre>';var_dump($this->arrayStructure);die();
	
}

$parser = new Parser();
$parser->run2();