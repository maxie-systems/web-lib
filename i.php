<?php
//php ./vendor/bin/phpcs --standard=MyStandard MyStandard/Tests/
//Сделать отдельный скрипт для поиска репозиториев. findComposerRepositories(string $dir)
//Найти репозитории
//Найти все PHP файлы в папке
//Найти все PHP файлы в автозагрузке composer.json (psr-4)
//require __DIR__ . '/vendor/autoload.php';

//class PHPCodeNamespace
//https://www.php.net/manual/en/language.namespaces.basics.php
// Unqualified name | Qualified name | Fully qualified name
//https://www.php.net/manual/en/language.namespaces.rules.php
//only the following types of code are affected by namespaces:
// classes (including abstracts and traits), interfaces, functions and constants.
//  А это значит, что FQCN - не очень подходящее имя, поскольку не только классов это касается.
class PHPCodeFQCN
{

}

/**
 * @property-read array $platform
 * @property-read array $require
 * @property-read array $require-dev
 */
class ComposerJson
{
    final public function __construct(string $dir)
    {
        $path = realpath($dir);
        $base = DIRECTORY_SEPARATOR . 'composer.';
        if ($path) {
            $file = $path . $base;
            $this->jsonFile = $file . 'json';
            $this->jsonExists = is_file($this->jsonFile);
            $this->lockFile = $file . 'lock';
            $this->lockExists = is_file($this->lockFile);
        } else {
            $this->jsonExists = $this->lockExists = false;
            $file = rtrim($dir, '/\\') . $base;
            $this->jsonFile = $file . 'json';
            $this->lockFile = $file . 'lock';
        }
    }

    final public function fileExists(): bool
    {
        return $this->jsonExists;
    }

    final public function getFileName(): string
    {
        return $this->jsonFile;
    }

    final public function lockExists(): bool
    {
        return $this->lockExists;
    }

    final public function getLockName(): string
    {
        return $this->lockFile;
    }

    final public function __get(string $key): mixed
    {
        if (!isset(self::$properties[$key])) {
            throw new \UnexpectedValueException('Undefined property "' . $key . '"');
        }
        if (!isset($this->data)) {
            $this->data = $this->loadData();
        }
        return $this->data[$key] ?? self::$properties[$key]['default'];
    }

    final protected function loadData(): array
    {
        if ($this->fileExists()) {
            $content = file_get_contents($this->jsonFile);
            $json = json_decode($content, true);
            if ($json && is_array($json)) {
                return $json;
            }
        }
        return [];
    }

    private readonly string $jsonFile;
    private readonly string $lockFile;
    private readonly bool $jsonExists;
    private readonly bool $lockExists;
    private readonly array $data;
    private static $properties = [
        'platform' => ['default' => []],
        'require' => ['default' => []],
        'require-dev' => ['default' => []],
    ];
}

$project_root = realpath('../agrotech-web/vendor/');//dirname(__DIR__) . '/src';
require $project_root . '/autoload.php';
var_dump($project_root);
$directory = new \RecursiveDirectoryIterator(
	$project_root,
	\FilesystemIterator::KEY_AS_FILENAME | \FilesystemIterator::CURRENT_AS_SELF
	| \FilesystemIterator::UNIX_PATHS | \FilesystemIterator::SKIP_DOTS
);
$value = 'xxx';
$filter = new \RecursiveCallbackFilterIterator(
	$directory,
	function (\RecursiveDirectoryIterator $current, string $filename) use ($value): bool {
		if ($filename[0] === '.') {
			return false;
		}
		/*if ($current->isDot()) {
			return false;
		}*/
		if ($current->isDir()) {
			if ('tests' === $filename || 'composer' === $filename) {
				return false;
			}
			if ('symfony' === $current->getSubPathname() ||
			 	(0 === strpos($current->getSubPathname(), 'symfony/') &&
				 false !== strpos($current->getSubPathname(), 'twig-bundle'))
			  ) {
				if (0 === strpos($current->getSubPathname(), 'symfony/doctrine-bridge/')) {
					return false;
				}
			//	return true;
			} else {
				return false;
			}
			#echo 'Sub: ';
			#var_dump($current->getSubPathname());
			return true;
		}
		$ext = $current->getExtension();
		if ('json' === $ext) {
			if ('composer.json' === $current->getBasename()) {
				#var_dump($current->getSubPath());
				echo PHP_EOL;
				var_dump($current->getPath());
				$composer_json = json_decode(file_get_contents($current->getPathname()));
				var_dump($composer_json->name);
				#var_dump($composer_json->type);
				var_dump($composer_json->autoload);
				// Нужно посмотреть секции autoload: бывают репозитории с кодом прямо в корне репозитория (без папки src/)
			} else {
				var_dump($current->getPathname());
			}
			return false;
		} elseif ('php' === $ext) {
			$php_code = file_get_contents($current->getPathname());
			$name = $current->getBasename('.php');
			if (preg_match('/namespace\s+(?P<ns>.+);/', $php_code, $m)
				&& (false !== strpos($php_code, "class $name")
				|| false !== strpos($php_code, "interface $name")
				|| false !== strpos($php_code, "trait $name"))) {
				var_dump($current->getSubPathname());
				var_dump($m['ns'] . '\\' . $name);
//				return true;
				// Можно показать только исключения. Например: DateMalformedStringException
				// а ещё бывают Dto, Bundle, Trait
				#https://github.com/symfony/twig-bundle
				try {
					require_once($current->getPathname());
					return true;
				} catch (\Error $e) {
					var_dump($e->getMessage());
				} catch (\Exception $e) {
					var_dump($e->getMessage());
				}
			}
	} elseif ('' === $ext) {
			//var_dump($filename);
		}
		//var_dump($ext);
		return false;//$this->segmentStartsWith($current->getSubPathname(), $value);
	}
);
$iterator = new \RecursiveIteratorIterator($filter);
$values = [];
foreach ($iterator as $current) {
	/** @var \RecursiveDirectoryIterator $current */
	$values[] = $current->getSubPathname();
}
var_dump(count($values));
var_dump($values);

$iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
$values = [];
foreach ($iterator as $current) {
	/** @var \RecursiveDirectoryIterator $current */
	$values[] = $current->getSubPathname();
}
var_dump(count($values));
var_dump($values);

$iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::CHILD_FIRST);
$values = [];
foreach ($iterator as $current) {
	/** @var \RecursiveDirectoryIterator $current */
	$values[] = $current->getSubPathname();
}
var_dump(count($values));
var_dump($values);





/*namespace MaxieSystems;
require __DIR__ . '/vendor/autoload.php';
$log = new Log\AppTrace();
var_dump($log->id);
var_dump($log);*/


die;
$base = [
	//'/test/xxx/',
    '',
	'/',
    '/test',
	'test2',
	'/f1/',
	'/f1/f2',
	'f1/',
	'f1/f2',
	'/f1/f2/f3/',
/*	'/f1/f2/f3',
	'/f1/f2//f3/',
	'/f1/f2//f3//',
	'/f1/f2//f3///',
	'/f1/f2///f3/',
	'/f1/f2/f3/f4/f5/',*/
];
$obj = new URL\DomainName\Labels('www.example.com');
$arr = $obj->toArray();
var_dump($arr);
die;
foreach([
	// '//example.com',
	// 'https://pushkinskiy-tc.ru/x.php',
	// '/about-pushkinskiy-tc/test/my-folder/234/index.php',
	// '././././css/../main.css',
	// '../css/main.css',
	// '../../css/main.css',
	// '../../css/../main.css',
	// '/css/../main.css',
	// 'mailto:info@example.com',
	// ---
	's56/my-page.php',
    '../..',
	'../../css/../test55xx.css',
] as $u)
 {break;
	echo $u, PHP_EOL;
	//$url = new URL($u);
	//var_dump($url->absolute, $url->type);
	echo 'URL::pathToAbsolute:', PHP_EOL;
	foreach($base as $b)
	{
		echo var_export($b, true), '		', var_export(URL::pathToAbsolute($b, $u), true), PHP_EOL;
	}
	echo PHP_EOL;
	// echo $url, PHP_EOL;
 }
//echo PHP_EOL;

die;
$url_str = 'https://example.com:8080/pictures/search.php?size=actual&nocompress#main-nav';
//var_dump(parse_url($url_str));
class URLTest extends URL
{
    protected function filterComponent(string $name, mixed $value): mixed
    {
        if ('query' === $name) {
            return new URL\Query($value);
        }
        return $value;
    }
}
class URLRedirect extends URLReadOnly
{
    protected function filterComponent(string $name, mixed $value, array|\ArrayAccess $src_url): mixed
    {
        if ('query' === $name) {
            return new URL\Query($value);
        }
        return $value;
    }
    protected function onCreate(URL $url): void
    {
        //echo 'onCreate';
    }
}
$url_str = '';
$url = new URLRedirect($url_str, function(string $name, $value, array|\ArrayAccess $src_url) {
    if ('path' === $name) {
        return new URL\Path\Segments($value, [URL\Path\Segments::class, 'filterSegmentRaw']);
    }
    return $value;
});
//$url = new URL('max.v.antipin@gmail.com');
var_dump($url->path, $url->query, $url->getType());
/*try
{
    $x = new URL\Path\Segments('/test//page');
    $x[1] = 'x';
    var_dump($x);
} catch (\Throwable $e) {
    echo get_class($e), PHP_EOL;
    echo $e->getMessage(), PHP_EOL;
}
$x = new URL\Path\Segments(['test', 'page']);
var_dump($x);*/
