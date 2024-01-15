<?php
namespace MaxieSystems;

trait InstancesTrait
{
	final public static function Instance(string $index = '0') : object
	 {
		if(empty(self::$instances[$index]))
		 {
			if($inst = static::OnUndefinedInstance($index))
			 {
				if(empty(self::$instances[$index])) self::SetInstance($index, $inst);
				elseif(self::$instances[$index] !== $inst) throw new \Exception(get_called_class().": object with index '$index' already exists (".get_class(self::$instances[$index]).").");
			 }
			elseif(empty(self::$instances[$index])) throw new \Exception(get_called_class().": instance with index '$index' is undefined.");
		 }
		return self::$instances[$index];
	 }

	final public static function InstanceExists(string $index = null, object &$inst = null) : bool
	 {
		$inst = null;
		if(null === $index)
		 {
			$count = count(self::$instances);
			if(self::$on_undefined_instance) foreach(self::$on_undefined_instance as $k => $v) if(!isset(self::$instances[$k])) ++$count;
			return $count > 0;
		 }
		elseif(isset(self::$instances[$index]))
		 {
			$inst = self::$instances[$index];
			return true;
		 }
		elseif(isset(self::$on_undefined_instance[$index]))
		 {
			$c = self::$on_undefined_instance[$index];
			if($inst = $c($index))
			 {
				$inst = self::SetInstance($index, $inst);
				return true;
			 }
			elseif(isset(self::$instances[$index]))
			 {
				$inst = self::$instances[$index];
				return true;
			 }
		 }
		return false;
	 }

	final public static function GetInstancesIDs() : array
	 {
		$r = [];
		foreach(self::$instances as $k => $v) $r[$k] = $k;
		return $r;
	 }

	final public static function NewIndex(string $i) : string
	 {
		static $s = '-';
		$index = (int)$i;
		if("$index" === $i)
		 {
			while(self::InstanceExists(++$index));
		 }
		else
		 {
			$j = 1;
			if(false !== ($pos = strrpos($i, $s)))
			 {
				$a = substr($i, $pos + strlen($s));
				$b = (int)$a;
				if("$b" === $a)
				 {
					$j = $b;
					$i = substr($i, 0, $pos);
				 }
			 }
			while(self::InstanceExists($index = $i.$s.$j++));
		 }
		return "$index";
	 }

	final public static function AddOnUndefinedInstance(string $index, callable $callback)
	 {
		if(isset(self::$on_undefined_instance[$index])) throw new \Exception("Duplicate index: $index");
		self::$on_undefined_instance[$index] = $callback;
	 }

	protected static function OnUndefinedInstance(string $index)
	 {
		if(isset(self::$on_undefined_instance[$index]))
		 {
			$c = self::$on_undefined_instance[$index];
			return $c($index);
		 }
	 }

	final protected static function SetInstance(string $index, object $obj) : object
	 {
		if(isset(self::$instances[$index])) throw new \Exception(get_called_class().": object with index '$index' already exists (".get_class(self::$instances[$index]).").");
		if(!is_a($obj, __CLASS__)) throw new \Exception('Object must be an instance of '. __CLASS__ .'; '.\MaxieSystems\GetVarType($obj).' given.');
		return (self::$instances[$index] = $obj);
	 }

	private static $instances = [];
	private static $on_undefined_instance = [];
}
