<?php

class Parser
{

    const MAIN_DELIMITER = ';';
    const CODE_FILE = 'data.vt';
    const SUB_DELIMITER = ',';

    public $maxCount = 0;
    private $uniqNames = [];
    private $uniqEntities = [];
    private $arrayStructure = [];

    public function parse()
    {
        $codeString = $this->getCodeFromFile();
        $this->getUniqNames($codeString);
        $this->getUniqEntities($codeString);
        return $this->makeArrayStructure($codeString);
    }

    private function getCodeFromFile()
    {
        $code = file_get_contents(self::CODE_FILE);
        $code = str_replace(PHP_EOL, '', $code);
        return $code;
    }

    private function getUniqNames($codeString)
    {
        preg_match_all("/\[[a-z0-9 ,]+\]/", $codeString, $names, PREG_SET_ORDER);
        if (!empty($names)) {
            $this->fillUniqNames($names);
        }
    }

    public function fillUniqNames($names)
    {
        foreach ($names as $name) {
            $names = preg_replace('/\[|\]/', '', $name[0]);
            $this->uniqNames = array_merge($this->uniqNames, array_map('trim', explode(self::SUB_DELIMITER, $names)));
        }
    }

    public function getUniqEntities($codeString)
    {
        preg_match_all("/[a-z0-9 *]+\[[a-z0-9 ,]+\]/", $codeString, $entities, PREG_SET_ORDER);
        if (!empty($entities)) {
            $this->fillUniqEntities($entities);
        }
    }

    public function fillUniqEntities($entities)
    {
        foreach ($entities as $entity) {
            $entity = $entity[0];
            $root = $this->getRoot($entity);
            $children = $this->getChildren($entity);
            $this->uniqEntities[$root] = $children;
            $this->setMaxCount($children);
        }
    }

    public function getRoot($codeString)
    {
        preg_match_all("/[a-z0-9 *]+\[/", $codeString, $root, PREG_SET_ORDER);
        $root = preg_replace('/\[/', '', trim($root[0][0]));
        if (!empty($root)) {
            return $root;
        }
        return '';
    }

    public function getChildren($codeString)
    {
        preg_match_all("/\[[a-z0-9 ,]+\]/", $codeString, $children, PREG_SET_ORDER);
        $children = preg_replace('/\[|\]/', '', $children[0][0]);
        $children = array_map('trim', explode(self::SUB_DELIMITER, $children));
        if (!empty($children)) {
            return $children;
        }
        return [];
    }

    private function makeArrayStructure($codeString)
    {
        $strings = explode(self::MAIN_DELIMITER, $codeString);
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
        return $this->arrayStructure;
    }

    private function setMaxCount($array)
    {
        $currentCount = 0;
        foreach ($array as $string) {
            $currentCount += strlen($string);
        }
        if ($currentCount > $this->maxCount) {
            $this->maxCount = $currentCount;
        }
    }

}