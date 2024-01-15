<?php

namespace MaxieSystems;

class ErrorHandler
{
   # https://www.php.net/manual/en/errorfunc.configuration.php
    /*public function __construct()
    {
        $prev_e_h = set_error_handler($this, int $error_levels = E_ALL);
    }*/
    public function __invoke(int $type, string $message, ?string $file, ?int $line): bool
    {
        //игнор ошибок по некоторым условиям
        //if transform to E//throw new \ErrorException($message, 0, $type, $file, $line);
        //вывод хоть какой-то осмысленной страницы в случае фатальных ошибок
        return false;
    }

    public function __debugInfo()
    {
        return [];
    }
}
/*$dir = __DIR__ . '/../src/OpenAPI/';
$path = realpath($dir);
if (!$path) {
    throw new Error('No such directory');
}
set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline) use ($path) {
    if (0 === strncmp($path, $errfile, strlen($path))) {
        return true;
    } else {
        return false;
    }
}, E_DEPRECATED);
         final public static function HandleError(int $type, string $message, $file, $line) : ?bool
          {
             $c = 'ErrorException';
             if(isset(self::$e_types[$type]))
              {
                 $c = self::$e_types[$type]['name'];
                 if(self::$e_types[$type]['critical'] < 1)
                  {
                     if(!(error_reporting() & $type)) return null;
                  }
              }
             $c = __NAMESPACE__ ."\\$c";
             $e = new $c($message, 0, $type, $file, $line);
             $trace = $e->getTrace();
             self::ShiftTrace($trace, __FUNCTION__);
             if($trace)
              {
                 $cl = empty($trace[0]['class']) ? '' : strtolower($trace[0]['class']);
                 $fn = $trace[0]['function'] ? strtolower($trace[0]['function']) : '';
                 if(isset(self::$error_handler_ignore[$cl]) && isset(self::$error_handler_ignore[$cl][$fn])) return false;
              }
             if(self::GetOption('debug')) throw $e;
             $error = self::ErrorArgs2Array($type, $message, $file, $line);
             if(null !== self::$error2exception && self::$error2exception['call']($error, $trace, ...self::$error2exception['args'])) throw $e;// if ErrorThrowable
             return self::StreamError($error, 'global', $trace);
          }

         final public static function ErrorArgs2Array(int $type, string $message, ?string $file, ?int $line) : array
          {
             return ['type' => $type, 'message' => $message, 'file' => $file, 'line' => $line];
          }

         final public static function ErrorConstToString(int $val) : string
          {
             if(isset(self::$e_types[$val])) return self::$e_types[$val]['name'];
             elseif(E_ALL === $val) return 'E_ALL';
             else return '';
          }

         final public static function StreamError(array $error, string $handler, array $trace = null) : ?bool
          {
             if(self::$e_streams)
              {
                 set_time_limit(8);
                 foreach(self::$e_streams as $stream)
                  {
                     try
                      {
                         $stream->InsertError($error, $trace, $handler);
                      }
                     catch(\Throwable $e2)
                      {
                         if(self::DisplayErrors()) echo $e2->getMessage(), PHP_EOL, $e2->getCode(), PHP_EOL, self::TransformFileName($e2->getFile()), PHP_EOL, $e2->getLine();
                      }
                  }
                 return null;
              }
             else return false;
          }

         final public static function ThrowErrors(?callable $filter, ...$args) : array
          {
             $ret_val = [];
             if(null !== self::$error2exception) array_push($ret_val, self::$error2exception['call'], ...self::$error2exception['args']);
             self::$error2exception = null === $filter ? null : ['call' => $filter, 'args' => $args];
             return $ret_val;
          }
     
         final public static function SetErrorStreams(IEStream ...$streams)#void
          {
             if(self::$e_streams) throw new \Exception('Error streams are already set!');
             self::$e_streams = $streams;
          }
         final public function __construct(bool $handle_e = true, int $error_levels = E_ALL)
          {
             register_shutdown_function(function(){
                 if($error = error_get_last())
                  {
                     $t = isset(self::$e_types[$error['type']]) ? self::$e_types[$error['type']] : false;
                     if(($t && $t['fatal']) || (!$error['line'] && 'Unknown' === $error['file']))
                      {
                         if($t && $t['critical'] < 0)
                          {
                             if(!(error_reporting() & $error['type'])) return;
                          }
                         if(false === self::StreamError($error, 'shutdown')) error_log(implode(PHP_EOL, $error));
                      }
                  }
             });
          }

         # https://www.php.net/manual/en/function.set-error-handler.php
         # The following error types cannot be handled with a user defined function: E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, and most of E_STRICT raised in the file where set_error_handler() is called.
         private static $e_types = [
             E_ERROR				=> ['name' => 'E_ERROR',				'fatal' => true,	'catchable' => false,	'critical' => 1],
             E_PARSE				=> ['name' => 'E_PARSE',				'fatal' => true,	'catchable' => false,	'critical' => 1],
             E_CORE_ERROR		=> ['name' => 'E_CORE_ERROR',			'fatal' => true,	'catchable' => false,	'critical' => 1],
             E_CORE_WARNING		=> ['name' => 'E_CORE_WARNING',			'fatal' => false,	'catchable' => false,	'critical' => 0],// fatal, on shutdown // ???
             E_COMPILE_ERROR		=> ['name' => 'E_COMPILE_ERROR',		'fatal' => true,	'catchable' => false,	'critical' => 1],
             E_COMPILE_WARNING	=> ['name' => 'E_COMPILE_WARNING',		'fatal' => false,	'catchable' => false,	'critical' => 0],
             E_RECOVERABLE_ERROR	=> ['name' => 'E_RECOVERABLE_ERROR',	'fatal' => true,	'catchable' => true,	'critical' => 1],# Catchable fatal error
             E_WARNING			=> ['name' => 'E_WARNING',				'fatal' => false,	'catchable' => true,	'critical' => 1],
             E_NOTICE			=> ['name' => 'E_NOTICE',				'fatal' => false,	'catchable' => true,	'critical' => 0],
             E_USER_ERROR		=> ['name' => 'E_USER_ERROR',			'fatal' => false,	'catchable' => true,	'critical' => 1],
             E_USER_WARNING		=> ['name' => 'E_USER_WARNING',			'fatal' => false,	'catchable' => true,	'critical' => 1],
             E_USER_NOTICE		=> ['name' => 'E_USER_NOTICE',			'fatal' => false,	'catchable' => true,	'critical' => 0],
             E_STRICT			=> ['name' => 'E_STRICT',				'fatal' => false,	'catchable' => true,	'critical' => -1],
             E_DEPRECATED		=> ['name' => 'E_DEPRECATED',			'fatal' => false,	'catchable' => true,	'critical' => -1],
             E_USER_DEPRECATED	=> ['name' => 'E_USER_DEPRECATED',		'fatal' => false,	'catchable' => true,	'critical' => -1],
         ];
         private static $error2exception = null;
         private static $e_streams = [];

*/