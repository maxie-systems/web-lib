<?php
namespace MaxieSystems;

// У конфигов есть интерфейс - это набор свойств и их типов, которые мы можем считать из конфига.
// А есть хранилище - env, ini-files, php-files, DB, secrets.
// Хранилище сильно зависит от способа деплоя (DollySites - хороший пример).
class Config implements \Iterator, \Countable
{
	final public static function Instance(string $index) : Config
	 {
		if(isset(self::$files_i[$index])) return self::$files_i[$index];
		throw new \Exception(__METHOD__ ."(): undefined index '$index'");
	 }

	final public static function InstanceExists(string $index, Config &$inst = null) : bool
	 {
		$inst = ($r = isset(self::$files_i[$index])) ? self::$files_i[$index] : null;
		return $r;
	 }

	# 1: Файл отсутствует.
	# 2: Вместо файла указана директория.
	# 3: Нет прав для чтения.
	# 4: Файл в неправильном формате (из него не возвращается массив), но он синтаксически правилен.
	# 5: Файл содержит синтаксические ошибки.
	final public static function LoadFile(string &$filename, iterable $defaults = null, \Exception &$e = null) : array
	 {
		$e = null;
		$f = realpath($filename);
		$data = [];
		if(false === $f) $e = new Exception\ConfigException("Failed opening '$filename': no such file", 1);
		else
		 {
			$filename = $f;
			if(is_dir($filename)) $e = new Exception\ConfigException("Failed opening '$filename': file required, directory given", 2);
			else
			 {
				try
				 {
					$v = (include $filename);
					if(is_array($v)) $data = $v;
					else $e = new Exception\ConfigException('', 4);
				 }
				catch(\ParseError $err)
				 {
					$e = new Exception\ConfigException('', 5, $err);
					// parser error: попытка восстановить данные.
				 }
			 }
		 }
		if(null !== $defaults) foreach($defaults as $k => $v) if(!isset($data[$k])) $data[$k] = $v;
		return $data;
	 }

	final public function __construct(string $index, string $filename, iterable $defaults = null, \Exception &$e = null)
	 {
		$this->index = $index;
		$this->filename = $filename;
		$this->data = $this->LoadFile($this->filename, $defaults, $e);
		if(isset(self::$files_i[$index])) throw new \Exception(__METHOD__ ."(): config with index '$index' exists");
		self::$files_i[$index] = $this;
		if(isset(self::$files_n[$this->filename])) throw new \Exception(__METHOD__ ."(): config with filename '$this->filename' exists");
		self::$files_n[$this->filename] = $this;
	 }

	final public function current() { return current($this->data); }

	final public function next() { next($this->data); }

	final public function key() { return key($this->data); }

	final public function valid() { return null !== key($this->data); }

	final public function rewind() { reset($this->data); }

	final public function __isset($name) { return isset($this->data[$name]); }

	final public function __get($name) { return isset($this->data[$name]) ? $this->data[$name] : null; }

	final public function __unset($name) { $this->__set($name, null); }

	final public function __set($name, $value) { throw new \Exception('Config is read-only'); }

	final public function count() { return count($this->data); }

	final public function __debugInfo() { return ['index' => $this->index, ]; }

	final public function __clone() { throw new \Exception('Can not clone instance of '.get_class($this)); }

	final public function GetIndex() : string { return $this->index; }

	final public function GetFileName() : string { return $this->filename; }

	private $index;
	private $filename;
	private $data;

	private static $files_n = [];
	private static $files_i = [];
}