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
			$this->setMaxCount($children);
		}
	}
	
	public function getRoot($codeString) {
		preg_match_all("/[a-z0-9 *]+\[/", $codeString, $root, PREG_SET_ORDER);
		$root = preg_replace('/\[/', '', trim($root[0][0]));
		if (!empty($root)) {
			return $root;
		}
		return '';	
	}
	
	public function getChildren($codeString) {
		preg_match_all("/\[[a-z0-9 ,]+\]/", $codeString, $children, PREG_SET_ORDER);
		$children = preg_replace('/\[|\]/', '', $children[0][0]);
		$children = array_map('trim', explode(',', $children));
		if (!empty($children)) {
			return $children;
		}
		return [];	
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
		//echo '<pre>';var_dump($this->maxCount);die();
		$spaces = $this->generateSpaces();
		foreach ($this->arrayStructure as $level) {
			if (count($level) == 1) {
				$level = $level[0];
				if (strpos($level, '*') !== false) {
					echo $spaces.$level.PHP_EOL;
				} else {
					echo $spaces.'|'.PHP_EOL.$spaces.$level.PHP_EOL;
				}
			} else {
				$spacesString = $spaces;
				foreach ($level as $key => $element) {
					if ($key == 0) {
						$spacesString .= '|';
					} else {
						$spacesString .= $this->generateSpacesByLen(strlen($element) + 1).'|';
					}
				}
				$spacesString = $spacesString.PHP_EOL.$spaces;
				echo $spacesString.implode(' ', $level).PHP_EOL;
			}
		}
	}
	
	private function setMaxCount($array) {
		$currentCount = 0;
		foreach($array as $string) {
			$currentCount += strlen($string);
		}
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
	
	private function generateSpacesByLen($count) {
		$spaces = '';
		for($i = 0;$i < $count;$i++) {
			$spaces .= ' ';
		}
		return $spaces;
	}
	
}

$parser = new Parser();
$parser->run();