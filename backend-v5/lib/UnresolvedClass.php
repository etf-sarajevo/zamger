<?php

// Modul: lib
// Klasa: UnresolvedClass
// Opis: referenca na klasu koja sadrži samo id objekta a može se po potrebi rezolvati 
// u puni objekat pozivom funkcije resolve()


class UnresolvedClass {
	public $className, $id;
	private $ref, $cache;
	
	public function __construct($className, $id, &$ref) {
		$this->className=$className; $this->id=$id; $this->ref = &$ref;
		$this->ref = $this;
		$this->cache = array();
	}
	
	// Shortcut constructor
	public static function makeForParent($parent, $className) {
		$parent->$className = new UnresolvedClass($className, $parent->$className, $parent->$className);
	}
	
	public function resolve() {
		if ($this->id === 0 || $this->id === null) return; // Don't resolve if id is null since this is a placeholder
		if (array_key_exists($this->className, $this->cache) && array_key_exists($this->id, $this->cache[$this->className]))
			$this->ref = $this->cache[$this->className][$this->id];
		if (is_numeric($this->id)) {
			$this->ref = eval("return " . $this->className . "::fromId(" . $this->id . ");");
			$this->cache[$this->className][$this->id] = $this->ref;
		}
	}
	
	// Recursively resolve all unresolved classes in $object of type $className
	// (if $className is empty, then resolve all classes)
	public static function resolveAll($object, $className = "") {
		foreach($object as $key=>&$value) {
			if (is_object($value) && get_class($value) == "UnresolvedClass") {
				if ($className == "" || $value->className == $className)
					$value->resolve();
			}
			else if (is_object($value))
				UnresolvedClass::resolveAll($value, $className);
			else if (is_array($value))
				foreach($value as &$member)
					if (is_object($member))
						UnresolvedClass::resolveAll($member, $className);
		}
	}
}

?>
