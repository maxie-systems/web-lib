<?php
namespace MaxieSystems;

interface IEStream
{
	public function InsertException(\Throwable $e, string $handler);
	public function InsertError(array $error, ?array $backtrace, string $handler);
	public function __debugInfo();
}

trait TEStream
{
	final public function InsertException(\Throwable $e, string $handler)
	 {
		$this->InsertE('exception', Config::Exception2Array($e, $handler));
	 }

	final public function InsertError(array $error, ?array $backtrace, string $handler)
	 {
		$this->InsertE('error', Config::Error2Array($error, $backtrace, $handler));
	 }

	final public function InsertData(string $type, array $data)
	 {
		static $t = ['error' => true, 'exception' => true];
		if(empty($t[$type])) throw new \Exception('Invalid type: '.$type);
		unset($data['id']);
		return $this->InsertE($type, $data);
	 }

	public function __debugInfo() { return []; }
}

class EStream implements IEStream
{
	use TEStream;

	final public function __construct(string $domain, callable $on_insert, bool $debug = false)
	 {
		$this->domain = $domain;
		$this->on_insert = $on_insert;
		$this->debug = $debug;
	 }

	final protected function InsertE(string $path, array $data)
	 {
		$hdrs = [];
		$f = $this->on_insert;
		$f($data, $hdrs);
		$r = HTTP::POST($this->MkURL($path), $data, ['headers' => $hdrs]);
		if($this->debug && Config::DisplayErrors())
		 {
			static $skip = ['headers_source' => 1, ];
			$s = '';
			foreach($r as $k => $v)
			 {
				if(isset($skip[$k])) continue;
				$s .= $k.str_repeat(' ', 15 - strlen($k)).': ';
				if('url' === $k || is_scalar($v)) $s .= $v;
				else $s .= GetVarDump($v);
				$s .= PHP_EOL;
			 }
			echo '<pre>', __METHOD__, substr($path, 1), PHP_EOL, GetVarDump($data), '</pre>', '<pre>', $s, PHP_EOL, PHP_EOL, $r, '</pre>';
		 }
	 }

	final protected function MkURL(string $path) : string { return "https://$this->domain/$path"; }

	private $domain;
	private $on_insert;
	private $debug;
}

class EStreamDB implements IEStream
{
	use TEStream;

	final public function __construct($dbi = 0, string $prefix = null, string $postfix = null, array $o = null)
	 {
		$this->dbi = $dbi;
		$this->prefix = (null === $prefix) ? 'sys_' : $prefix;
		$this->postfix = (null === $postfix) ? '' : $postfix;
		$this->o = $o;
	 }

	protected function InsertE(string $type, array $data)
	 {
		return $this->GetDB()->Insert($this->TName($type), $data);
	 }

	final protected function TName(string $type) : string
	 {
		return $this->prefix.$type.$this->postfix;
	 }

	final protected function GetDB() : SQLDB
	 {
		if(null === $this->db)
		 {
			$this->db = SQLDB::Instantiate($this->dbi, $type);
			if('index' === $type) $this->db = clone $this->db;
		 }
		return $this->db;
	 }

	final protected function GetOption(string $name)
	 {
		return isset($this->o[$name]) ? $this->o[$name] : null;
	 }

	private $dbi;
	private $db = null;
	private $prefix;
	private $postfix;
	private $o;
}

trait TEStreamCompact
{
	final public static function GetHash(array $data) : string
	 {
		foreach(['id', 'hash', 'count', 'referer', 'date_time', 'backtrace', 'no_backtrace_message', 'handler'] as $k) unset($data[$k]);
		ksort($data);
		return hash('sha256', json_encode($data));
	 }
}

class EStreamDBCompact extends EStreamDB
{
	use TEStreamCompact;

	final protected function InsertE(string $type, array $data)
	 {
		$data['hash'] = $hash = $this->GetHash($data);
		$db = $this->GetDB();
		$t_name = $this->TName($type);
		$res = $db->InsertUpdate($t_name, $data, ['=count' => '`count` + 1', 'referer' => true, 'backtrace' => true, 'no_backtrace_message' => true, 'handler' => true]);
		if(!$this->GetOption('entry_disabled'))
		 {
			$time = time();
			$t = $this->GetOption('entry_period');
			if(is_int($t) && $t > 1)
			 {
				$r = $time % $t;
				if($r > 0) $time -= $r;
			 }
			$db->InsertUpdate($t_name.'_entry', ['hash' => $hash, 'date_time' => date('Y-m-d H:i:s', $time)], ['=count' => '`count` + 1']);
		 }
		unset($data['hash']);
		if(0 === $res);# запись существовала, но не была обновлена
		elseif(1 === $res)# запись была добавлена
		 {
			if($f = $this->GetOption('after_insert')) $f($type, $data, $hash);
		 }
		elseif(2 === $res)# запись была обновлена
		 {
			if($f = $this->GetOption('after_update')) $f($type, $data, $hash);
		 }
		return $hash;
	 }
}

class DebugEStream implements IEStream
{
	final public function __construct()
	 {
		$this->formats['type'] = function($v){ return Config::ErrorConstToString($v)." [$v]"; };
		$this->formats['backtrace'] = function($v){
			$len = strlen($v);
			return 'string ('.$len.' byte'.($len > 1 ? 's' : '').')';
		};
	 }

	final public function __destruct()
	 {
		$has_e = false;
		foreach(['errors', 'exceptions'] as $k)
		 {
			foreach($this->$k as $e)
			 {
				echo $this->FormatData($e['m'], $e['a']), $this->FormatTrace(isset($e['a']['backtrace']) ? $e['a']['backtrace'] : '');
				$has_e = true;
			 }
		 }
		if($has_e) echo $this->style;
	 }

	final public function InsertException(\Throwable $e, string $handler)
	 {
		$this->exceptions[] = ['m' => __METHOD__, 'a' => Config::Exception2Array($e, $handler)];
	 }

	final public function InsertError(array $error, ?array $backtrace, string $handler)
	 {
		$this->errors[] = ['m' => __METHOD__, 'a' => Config::Error2Array($error, $backtrace, $handler)];
		if('shutdown' === $handler) $this->__destruct();
	 }

	final protected function FormatTrace(string $enc_trace) : string
	 {
		return $enc_trace ? Config::FormatTrace(Config::UnserializeTrace($enc_trace), 'debugestream_trace') : '<pre>Backtrace is empty</pre>';
	 }

	final protected function FormatData(string $caption, array $data) : string
	 {
		$s = '';
		foreach($data as $k => $v)
		 {
			$strv = isset($this->formats[$k]) ? $this->formats[$k]($v) : $v;
			$s .= "<tr><th>$k</th><td>$strv</td></tr>";
		 }
		return "<table class='debugestream_data'><caption>$caption</caption><tbody>$s</tbody></table>";
	 }

	public function __debugInfo() { return []; }

	private $errors = [];
	private $exceptions = [];
	private $formats = [];
	private $style = <<<EOT
<style type="text/css">
.debugestream_data, .debugestream_trace{font-family:monospace;background-color:#cfcff7;border-collapse:separate;border-spacing:0 1px;border:solid #cfcff7;border-width:0 1px;line-height:125%;}
.debugestream_data{margin-bottom:1.25em;}
.debugestream_trace{margin-bottom:1.75em;}
.debugestream_data caption{text-align:left;padding:5px 2px;}
.debugestream_data tbody th, .debugestream_data tbody td, .debugestream_trace tbody th, .debugestream_trace tbody td{background-color:white;padding-top:4px;padding-bottom:4px;}
.debugestream_data tbody tr:nth-child(2n) > th, .debugestream_data tbody tr:nth-child(2n) > td{background-color:#f8f8ff;}
.debugestream_data tbody th{text-align:left;padding-left:9px;padding-right:4px;color:#444;}
.debugestream_data tbody td{padding-left:4px;padding-right:9px;white-space:pre-wrap;}
.debugestream_trace tbody th{padding-left:9px;padding-right:9px;text-align:left;}
.debugestream_trace tbody td{padding-right:9px;}
.debugestream_trace tbody th._num{background-color:#e8e8ef;}
.debugestream_trace tbody th._n_arg{padding-left:19px;}
.debugestream_trace tbody td._type{font-style:italic;}
</style>
EOT;
}

class EStreamEmail implements IEStream
{
	final public function __construct(string $email_to, array $conf, string $info_url)
	 {
		$this->email_to = $email_to;
		$this->conf = $conf;
		$this->fields['type']['f'] = function($v) : string { return Config::ErrorConstToString($v)." [$v]"; };
		$this->info_url = $info_url;
	 }

	public function __debugInfo() { return []; }

	final public function InsertError(array $error, ?array $backtrace, string $handler)
	 {
		$this->SendMail('Error', Config::Error2Array($error, $backtrace, $handler));
	 }

	final public function InsertException(\Throwable $e, string $handler)
	 {
		$this->SendMail('Exception', Config::Exception2Array($e, $handler));
	 }

	final protected function DecodeHost(array &$e) : string
	 {
		if(empty($e['host'])) return 'undefined host';
		if(null === $this->idna) $this->idna = new \idna_convert();
		$e['host'] = $this->idna->decode($e['host']);
		return $e['host'];
	 }

	final protected function GetMail() : Mail
	 {
		if(null === $this->mail)
		 {
			Config::RequireFile('mail', 'idna_convert');
			$args = [];
			if(!empty($this->conf['smtp'])) $args[] = new Mail\SMTP(...$this->conf['smtp']);
			$this->mail = new Mail(...$args);
			$this->mail->SetTo($this->email_to);
			$this->mail->SetFrom(...$this->conf['from']);
		 }
		return $this->mail;
	 }

	final protected function SendMail(string $type, array $e)
	 {
		$mail = $this->GetMail();
		$host = $this->DecodeHost($e);
		$mail->SetSubject("MSSE $type — $host");
		$text = $type.PHP_EOL;
		foreach($e as $k => $v)
		 {
			if(isset($this->fields[$k]))
			 {
				$text .= PHP_EOL.$this->fields[$k]['title'].': '.(isset($this->fields[$k]['f']) ? $this->fields[$k]['f']($v) : $v);
			 }
		 }
		if($this->info_url)
		 {
			;//$text .= PHP_EOL.'You can view detailed information here: '.$host.$this->url;// Абсолютный???
		 }
		$mail->SetText($text)->Send();
	 }

	private $fields = [
		'type' => ['title' => 'Type'],
		'class' => ['title' => 'Class'],
		'code' => ['title' => 'Code'],
		'message' => ['title' => 'Message'],
		'file' => ['title' => 'File'],
		'line' => ['title' => 'Line'],
		'protocol' => ['title' => 'Protocol'],
		'host' => ['title' => 'Host'],
		'uri' => ['title' => 'Request URI'],
		'method' => ['title' => 'HTTP method'],
		'date_time' => ['title' => 'Timestamp'],
		'handler' => ['title' => 'Handler'],
		'referer' => ['title' => 'Referer'],
	];
	private $mail = null;
	private $email_to;
	private $conf;
	private $info_url;
	private $idna = null;
}