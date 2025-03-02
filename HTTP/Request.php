<?php

namespace MaxieSystems\HTTP {
    use MaxieSystems as MS;
    use MaxieSystems\{Containers,URL};

    abstract class Exception extends \Exception
    {
    }
    class ECURL extends Exception
    {
    }
    class ETooManyRedirects extends Exception
    {
    }
    class EInvalidResponse extends Exception
    {
    }
    class EProxyListEmpty extends Exception
    {
    }

    interface IHeader
    {
        public function __get($name);
        public function __toString();
        public function __clone();
        public function __debugInfo();
    }

    class MultipleHeader implements IHeader, \Iterator, \Countable, \ArrayAccess
    {
        final public static function IsMultiple(string $lc_name): bool
        {
            static $names = [
                'set-cookie' => 1,
                'Set-Cookie' => 1,
            ];
            return isset($names[$lc_name]);
        }
    
    
        final public function __construct(Header $header, Header ...$headers)
        {
            $this->headers[] = $header;
            foreach ($headers as $header) {
                $this->AddHeader($header);
            }
        }

        final public function __get($name)
        {
            if ('value' === $name) {
                return '' === $this->headers[0]->name ? (string)end($this->headers) : $this->ToArray();
            }
            static $names = ['name' => 1, '_name' => 1, 'lc_name' => 1, ];
            if (isset($names[$name])) {
                return $this->headers[0]->$name;
            }
            throw new \Error('Undefined property: ' . __CLASS__ . "::$$name");
        }

        final public function __toString()
        {
            if ('' === $this->headers[0]->name) {
                return (string)end($this->headers);
            } else {
                throw new \Exception('Not implemented yet...');
            }
        }

        final public function ToArray(): array
        {
            $a = [];
            foreach ($this as $v) {
                $a[] = "$v";
            }
            return $a;
        }

        final public function offsetSet($k, $value)
        {
            if (null === $k) {
                $this->AddHeader($value);
            } else {
                throw new \Exception('Not implemented yet...');
            }
        }

        final public function offsetExists($k)
        {
            return isset($this->headers[$k]);
        }
        final public function offsetUnset($k)
        {
            throw new \Exception('Not implemented yet...');
        }// удалить один заголовок из списка. Переиндексация: например, был удален первый заголовок, после чего индексы должны снова начинаться с нуля и идти по порядку.
        final public function offsetGet($k)
        {
            return $this->headers[$k];
        }
        final public function count()
        {
            return count($this->headers);
        }
        final public function rewind()
        {
            reset($this->headers);
        }
        final public function current()
        {
            if (false !== ($v = current($this->headers))) {
                return $v;
            }
        }
        final public function key()
        {
            return key($this->headers);
        }
        final public function next()
        {
            next($this->headers);
        }
        final public function valid()
        {
            return null !== key($this->headers);
        }
        final public function __debugInfo()
        {
            return $this->ToArray();
        }

        final public function __clone()
        {
            foreach ($this->headers as $k => $v) {
                $this->headers[$k] = clone $v;
            }
        }

        private function AddHeader(Header $h)
        {
            if ($this->headers[0]->lc_name !== $h->lc_name) {
                throw new \Exception('Unable to add header');
            }
            $this->headers[] = $h;
        }

        private $headers;
    }


    class Request
    {
        final public function __construct(array $o = null)
        {
            $this->o = new CURL_Config_Request($o);
        }

        final public function GET(string $url, iterable $data = [], array $o = null): Response
        {
            $data = $this->MergeData($data);
            if ($data) {
                $url = $this->o->ModifyQueryString($url, $data);
            }
            return $this->o->__invoke($url, __FUNCTION__, null, null, $o);
        }

        final public function POST(string $url, iterable $data = [], array $o = null): Response
        {
            return $this->o->__invoke($url, __FUNCTION__, [$this->o, 'Config_POST'], $this->MergeData($data), $o);
        }

        final public function DELETE(string $url, $data = '', array $o = null): Response
        {
            return $this->o->__invoke($url, __FUNCTION__, [$this->o, 'Config_DELETE'], null === $data ? ($this->MergeData() ?: null) : $data, $o);
        }

        final public function jPOST(string $url, $data, array $o = null): Response
        {
            return $this->o->__invoke($url, __FUNCTION__, [$this->o, 'Config_jPOST'], null === $data ? ($this->MergeData() ?: null) : $data, $o);
        }

        final public function xPOST(string $url, $data, array $o = null): Response
        {
            throw new \Exception('not implemented yet...');//return $this->o->__invoke($url, __FUNCTION__, [$this->o, 'Config_xPOST'], null === $data ? ($this->MergeData() ?: null) : $data, $o);
        }

        final protected function MergeData(...$args): array
        {
            $data = [];
            foreach ($this->o->data as $k => $v) {
                $data[$k] = $v;
            }
            foreach ($args as $d) {
                foreach ($d as $k => $v) {
                    $data[$k] = $v;
                }
            }
            return $data;
        }

        final public function __debugInfo(): array
        {
            return [];
        }

        private $o = null;
    }

    class CURL_Config
    {
        public function __construct(array $o = null)
        {
            $meta = [
            'ssl_verifypeer' => ['type' => 'bool', 'value' => true],# https://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYPEER.html
            'ssl_verifyhost' => ['type' => 'bool', 'value' => true],# https://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
            'user_agent' => ['type' => 'string,true', 'value' => ''],# если равен true, то подставляется значение, переданное текущим браузером (из $_SERVER['HTTP_USER_AGENT'])
            'referer' => ['type' => 'string,callable', 'value' => ''],
            'accept_encoding' => ['type' => 'string,bool', 'value' => false],
            'follow_location' => ['type' => 'int,gte0', 'value' => 0],# обрабатывать ли самостоятельно редиректы, переходя по URL в Location. Указывается максимальное количество переходов.
            'before_redirect' => ['type' => 'callable,null'],# значение передаваемое ->GET \ ->POST \ ->DELETE, перекрывает эту настройку, а если ему передать false, то этот метод деактивируется.
            'connect_timeout' => ['type' => 'int,gte0', 'value' => 5],
            'proxy' => ['type' => 'object,null'],
            'headers' => ['type' => 'iterator,array', 'value' => [], 'proxy' => ['OptionConverter', 'HTTP\\Headers']],
            'cookies' => ['type' => 'iterator,array', 'value' => []],// это ещё не реализовано!!!
            // 'basic' => [],// Как эта хрень вообще работает???
            ];# решено НЕ ДЕЛАТЬ здесь опций, специфичных для запроса. Поэтому ::DELETE($url, $data, ['json' => true]) - не может быть, а ->DELETE($url, $data, ['json' => true]) - может.
            $this->OnCreate($meta);
            $this->o = new Containers\Options($o, $meta);
        }

        final public static function ModifyQueryString(string $url, array $data): string
        {
            if (false === strpos($url, '?')) {
                $url .= '?' . http_build_query($data);
            } else {
                $url = URL::Parse($url);
                parse_str($url->query, $q);
                $url->query = http_build_query(array_merge($q, $data));
                $url = URL::Build($url);
            }
            return $url;
        }

        final public function __get($name)
        {
            return $this->o->$name;
        }

        final public function __invoke(string $url, string $method, callable $before_exec = null, $data = null, ...$args): Response
        {
            return $this->Run($url, $method, $this->o, $before_exec, $data, ...$args);
        }

        // if($o = $this->GetOption('basic'))// Как эта хрень вообще работает???
         // {
            // curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            // curl_setopt($ch, CURLOPT_USERPWD, $o);
         // }
        protected function Run(string $url, string $method, Containers\IContainer $o, callable $before_exec = null, $data = null, ...$args): Response
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (!$o->ssl_verifypeer) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }
            if (!$o->ssl_verifyhost) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $o->connect_timeout);
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header_line) {
                if (0 === strncasecmp($header_line, 'set-cookie:', 11)) {
                    $this->response_cookies[] = new Cookie(substr($header_line, 11));
                }
                if ($s = trim($header_line)) {
                    $this->response_headers[] = $s;
                }
                return strlen($header_line);
            });
            $headers = $o->headers;
            $cookies = $o->cookies;
            if (null !== $before_exec) {
                $before_exec($ch, $headers, $cookies, $data, ...$args);
            }
            $h = $headers->ToArray(function (namespace\Header $h) use ($ch): bool {
                if ('User-Agent' === $h->name) {
                    curl_setopt($ch, CURLOPT_USERAGENT, $h->value);
                    return false;
                } elseif ('Accept-Encoding' === $h->name) {
                    $v = $o->accept_encoding;
                    if (false !== $v) {
                        curl_setopt($ch, CURLOPT_ENCODING, '' === $v || true === $v ? $h->value : $v);
                    }
                    return false;
                } elseif ('Referer' === $h->name) {
                    curl_setopt($ch, CURLOPT_REFERER, $h->value);
                    return false;
                }
                return true;
            });
            if ($h) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $h);# Опции user_agent, referer и accept_encoding должны использоваться после задания заголовков.
            }
            if ($o->user_agent) {
                curl_setopt($ch, CURLOPT_USERAGENT, true === $o->user_agent ? (empty($_SERVER['HTTP_USER_AGENT']) ? self::DEFAULT_UA : $_SERVER['HTTP_USER_AGENT']) : $o->user_agent);
            }
            if ($o->referer) {
                $v = is_callable($o->referer) ? $o->referer() : $o->referer;
                // curl_setopt($ch, CURLOPT_REFERER, $v);# The contents of the "Referer: " header to be used in a HTTP request.
                // curl_setopt($ch, CURLOPT_AUTOREFERER, $v);//TRUE to automatically set the Referer: field in requests where it follows a Location: redirect.
            }
            if ($v = $o->accept_encoding) {
                if (true === $v) {
                    $v = empty($_SERVER['HTTP_ACCEPT_ENCODING']) ? false : $_SERVER['HTTP_ACCEPT_ENCODING'];
                }
                if ($v) {
                    curl_setopt($ch, CURLOPT_ENCODING, $v);
                }
            }
            // true === $opts->cookies ? $_COOKIE : $opts->cookies];
            // if(($o = $this->GetOption('cookies')) && 0 < count($o)) foreach($o as $k => $v) $cookies[$k] = "$v";// почему дефолтные куки добавляются последними???
            // if($cookies) curl_setopt($ch, CURLOPT_COOKIE, HTTP\Cookie::ArrayToHeader($cookies));
            $get_result = null;
            if ($p = $o->proxy) {
                $config_proxy = function (ProxyConfig $p) use ($ch) {
                    return $p($ch);
                };
                if ($p instanceof namespace\IProxies) {
                    if (!count($p) && $p->Required()) {
                        throw new EProxyListEmpty();
                    }
                    $handle = function (CURL_Exec $run) use ($p, $config_proxy) {
                        foreach ($p as $proxy) {
                            $config_proxy($proxy);
                            if ($run()) {
                                return;# Всё в порядке, прерываем цикл и уходим к объекту ответа на запрос: return new Response($result, ...);
                            }
                            $e_type = $p->GetEType($run->e_msg, $run->e_num, $p);# Выясняем причину ошибки, чтобы выбрать действие: переход к следующему прокси, или завершение цикла.
                            if (1 === $e_type) {
                                $proxy->error = [$run->e_msg, $run->e_num];
                            } elseif (-1 === $e_type) {
                                return;# Есть случаи, когда бесполезно делать дополнительные попытки.
                            }
                            // echo $p->host, ' : ';var_dump($run->e_num, $run->e_msg, $e_type);echo '<br />';
                        }
                        throw new EProxyListEmpty('No proxies left', 1);
                        # Выход за пределы цикла означает, что не нашлось рабочих прокси: при наличии рабочего прокси происходит выход из этой функции.
                        # Это исключени можно не выбрасывать: тогда управление перейдёт на проверку результата запроса, и будет выброшено HTTP\ECURL;
                        # то есть, будет выброшено исключение, соответствующее ошибке последнего прокси-сервера.
                        # Но EProxyListEmpty явно показывает, что в списке нет рабочих прокси.
                    };
                    $get_result = new CURL_Get_Result($ch, $handle, function () {
                        $this->response_cookies = $this->response_headers = [];
                    });
                } else {
                    $config_proxy($p);
                }
            }
            $r = $this->CURL_Exec($ch, $get_result);
            if ($o->follow_location > 0) {
                static $redirects = [301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 307 => 'Temporary Redirect', 308 => 'Permanent Redirect'];# 303 ВСЕГДА меняет метод на GET. 307 НИКОГДА не меняет метод.
                $n = 0;
                $max = $o->follow_location;
                do {
                    if (isset($redirects[$r->http_code])) {
                        ++$n;
                        if ($n > $max) {
                            throw new ETooManyRedirects("Maximum ($max) redirects followed.");
                        }
                        if (!$r->headers->Location) {
                            throw new EInvalidResponse('Location header is undefined.');
                        }
                        $next_u = new URL($r->headers->Location);
                        $t = $next_u->type;
                        if ('absolute' === $t) {
                        } else {
                            throw new \Exception('Not implemented yet: ' . $next_u);
                        }
                        if (303 === $r->http_code) {
                            $method = 'GET';
                        }
                        if (null !== $o->before_redirect) {
                            if (false === $o->before_redirect($r, $next_u, $n, $o)) {
                                break;
                            }
                        }
                        curl_setopt($ch, CURLOPT_URL, "$next_u");
                        if ('GET' === $method) {
                            curl_setopt($ch, CURLOPT_HTTPGET, true);
                        } elseif ('POST' === $method) {
                            curl_setopt($ch, CURLOPT_POST, true);
                        } else {
                            throw new \Exception('Not implemented yet: ' . $method);
                        }
                        $r = $this->CURL_Exec($ch, $get_result);
                    } else {
                        break;
                    }
                } while ($n <= $max);
            }
            curl_close($ch);
            return $r;
        }

        protected function OnCreate(array &$meta)
        {
        }

        protected function Config_POST($ch, Headers $headers, $cookies, array $data)
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data ? http_build_query($data) : '');
        }

        protected function Config_DELETE($ch, Headers $headers, $cookies, $data)
        {
            if ('' === $data || null === $data) {
            } elseif (!$headers->content_type || 'application/json' === $headers->content_type) {
                if (!$headers->content_type) {
                    $headers->content_type = 'application/json';
                }
                $data = $this->DATA2JSON($data);
                $headers->content_length = strlen($data);
            } else {
                if (is_array($data)) {
                    $data = http_build_query($data);
                }
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        protected function Config_jPOST($ch, Headers $headers, $cookies, $data)
        {
            $this->Config__POST($ch, $headers, $cookies, $this->DATA2JSON($data), 'application/json');
        }

        protected function Config_xPOST($ch, Headers $headers, $cookies, string $data)
        {
            $this->Config__POST($ch, $headers, $cookies, $data, 'application/xml');
        }

        protected function DATA2JSON($data): string
        {
            if (is_string($data)) {
                return $data;# считаем, что если строка, то это уже закодированная в формате JSON строка.
            } else {
                return json_encode($data);
            }
        }

        protected function CURL_Exec($ch, callable $get_result = null): Response
        {
            $this->response_cookies = $this->response_headers = [];
            $result = (null === $get_result) ? curl_exec($ch) : $get_result();
            if (false === $result) {
                $e_msg = curl_error($ch);
                $e_num = curl_errno($ch);
                curl_close($ch);
                throw new ECURL($e_msg, $e_num);
            }
            return new Response($result, curl_getinfo($ch), $this->response_headers, $this->response_cookies);
        }

        private function Config__POST($ch, Headers $headers, $cookies, string $data, string $content_type)
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $headers->content_type = $content_type;
            $headers->content_length = strlen($data);
        }

        private $o;
        private $response_cookies = [];
        private $response_headers = [];

        const DEFAULT_UA = 'Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0';
    }

    class CURL_Get_Result
    {
        final public function __construct($curlh, callable $handle, callable $before)
        {
            $this->c = ['handle' => $handle];
            $this->run = new CURL_Exec($curlh, $before, function ($r) {
                $this->result = $r;
            });
        }

        final public function __set($name, $value)
        {
        }

        final public function __get($name)
        {
        }

        final public function __invoke()# : CURL result
        {
            $this->result = null;
            $this->c['handle']($this->run);
            if ($this->run->idle) {
                $this->run->__invoke();
            }
            return $this->result;
        }

        final public function __debugInfo()
        {
            return [];
        }

        private $c;
        private $run;
        private $result = null;
    }

    class CURL_Exec
    {
        final public function __construct($curlh, callable $before, callable $after)
        {
            $this->curlh = $curlh;
            $this->c = ['before' => $before, 'after' => $after];
        }

        final public function __set($name, $value)
        {
        }

        final public function __get($name)
        {
            if ('idle' === $name) {
                return null === $this->success;
            }
            if ('success' === $name) {
                return $this->success;
            }
            static $func = ['e_num' => 'curl_errno', 'e_msg' => 'curl_error'];
            if (isset($func[$name])) {
                return (false === $this->success) ? $func[$name]($this->curlh) : null;
            }
            throw new \Error('Undefined property: ' . get_class($this) . "::$$name");
        }

        final public function __debugInfo()
        {
            return ['success' => $this->success, 'e_num' => $this->__get('e_num'), 'e_msg' => $this->__get('e_msg')];
        }

        final public function __invoke(): bool
        {
            $this->c['before']();
            $this->success = ($result = curl_exec($this->curlh)) === false ? false : true;
            $this->c['after']($result);
            return $this->success;
        }

        private $curlh;
        private $c;
        private $success = null;
    }

    class CURL_Config_Request extends CURL_Config
    {
        protected function Run(string $url, string $method, Containers\IContainer $o, callable $before_exec = null, $data = null, ...$args): Response
        {
            $opts = $this->InitOpts($method, array_shift($args));
            $o = new Containers\Wrapper($o, [
            'headers' => function ($h, string $name) use ($opts) {
                return empty($opts->$name) ? clone $h : $h->Merge($opts->$name);
            },
            'before_redirect' => function (callable $f0 = null, string $name) use ($opts): ?callable {
                $f = $opts->$name;
                return false === $f ? null : (null === $f ? $f0 : $f);
            },
            ]);
            return parent::Run($url, $method, $o, $before_exec, $data, $opts, ...$args);
        }

        protected function OnCreate(array &$meta)
        {
            $meta['data'] = ['type' => 'iterator,array', 'value' => []];# данные по умолчанию во всех запросах, сделанных с помощью этого объекта: GET - в параметрах, POST - в теле запроса; перекрываются данными ->GET\POST.
        }

        final protected function InitOpts(string $method, array $o = null): Containers\Options
        {
            return new Containers\Options($o, self::$meta['request_options']);
        }

        private static $meta = [
        'request_options' => ['headers' => ['type' => 'array,iterator,null'], 'cookies' => ['type' => 'array,iterator,null'], 'before_redirect' => ['type' => 'callable,null,false']],
        ];
    }

    abstract class ProxyConfig implements \JsonSerializable
    {
        final public static function GetProperties(): array
        {
            return self::$props;
        }
        final public static function GetTypes(): array
        {
            return self::$types;
        }

        # Ошибки делятся на 3 категории:
        # 1. Точно проблема в прокси - переходим к следующему, помечаем текущий как проблемный. Обозначаем 1.
        # 2. Точно проблема НЕ в прокси - прерываем обход списка прокси и, вероятно, делаем некое уведомление админу о возникшей проблеме. Обозначаем -1.
        # 3. Не удаётся автоматически определить источник проблемы. Обозначаем 0.
        final public static function GetEType(string $e_msg, int $e_num, ProxyConfig $proxy = null): int
        {
            if (CURLE_COULDNT_RESOLVE_PROXY === $e_num) {
                return 1; # (5) Couldn't resolve proxy. The given proxy host could not be resolved.
            } elseif (CURLE_COULDNT_CONNECT === $e_num) { # (7) Failed to connect() to host or proxy.
                if (null === $proxy) {
                } elseif (false !== stripos($e_msg, "Failed to connect to $proxy->host port $proxy->port: Connection refused")) {
                    return 1;
                }
            } elseif (CURLE_OPERATION_TIMEDOUT === $e_num) { # (28) Operation timeout. The specified time-out period was reached according to the conditions.
                if ('SOCKS5 read timeout' === $e_msg) {
                    return 1;
                }
                // Connection timed out after 5000 milliseconds
                // Operation timed out after 5000 milliseconds with 0 out of 0 bytes received
            } elseif (CURLE_RECV_ERROR === $e_num) { # (56) Failure with receiving network data.
                if (preg_match('/^Received HTTP code ([0-9]{3}) from proxy after CONNECT$/i', $e_msg, $m)) {
                    return 1;
                }
            }
            return 0;
        }

        final public function __invoke($ch)
        {
            curl_setopt($ch, CURLOPT_PROXY, $this->host);
            if ($this->port) {
                curl_setopt($ch, CURLOPT_PROXYPORT, $this->port);
            }
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->user ? "$this->user:$this->password" : '');
            curl_setopt($ch, CURLOPT_PROXYTYPE, $this->type);
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $this->tunnel);
        }

        final public function __get($name)
        {
            if (isset(self::$props[$name])) {
                return $this->p ? $this->p->$name : null;
            }
            throw new \Error('Undefined property: ' . get_class($this) . "::$$name");
        }

        final public function __isset($name)
        {
            return isset(self::$props[$name]);
        }

        final public function __set($name, $value)
        {
            if ('error' === $name) {
                $this->SetError(...$value);
            } else {
                throw new \Error('Undefined property: ' . get_class($this) . "::$$name");
            }
        }

        public function __debugInfo()
        {
            return [];
        }

        final protected function Init(object $p): ProxyConfig
        {
            if (empty(self::$types[$p->type])) {
                throw new \UnexpectedValueException('Invalid proxy type: ' . $p->type);
            }
            $this->p = $p;
            return $this;
        }

        final protected static function CreateEntity(string $host, int $port = null, string $user, string $password, int $type, bool $tunnel = false): \stdClass
        {
            $p = new \stdClass();
            $p->host = $host;
            $p->port = $port;
            $p->user = $user;
            $p->password = $password;
            $p->type = $type;
            $p->tunnel = $tunnel;
            return $p;
        }

        private function SetError(string $e_msg, int $e_num, ...$args)
        {
            static $n = 'Error';
            if (method_exists($this->p, $n)) {
                $this->p->$n($e_msg, $e_num, ...$args);
            }
        }

        private $p;

        private static $props = ['host' => 'host', 'port' => 'port', 'user' => 'user', 'password' => 'password', 'type' => 'type', 'tunnel' => 'tunnel', ];
        private static $types = [CURLPROXY_HTTP => 'HTTP', CURLPROXY_SOCKS4 => 'SOCKS4', CURLPROXY_SOCKS5 => 'SOCKS5', CURLPROXY_SOCKS4A => 'SOCKS4A', CURLPROXY_SOCKS5_HOSTNAME => 'SOCKS5 HOSTNAME'];
    }

    class Proxy extends ProxyConfig
    {
        final public function __construct(string $host, int $port = null, string $user, string $password, int $type, bool $tunnel = false)
        {
            $this->Init($this->CreateEntity($host, $port, $user, $password, $type, $tunnel));
        }

        final public function jsonSerialize()
        {
            $a = [];
            foreach (self::GetProperties() as $k) {
                $a[$k] = $this->__get($k);
            }
            return $a;
        }
    }

    interface IProxies extends \Iterator, \Countable
    {
    }

    class Proxies extends ProxyConfig implements IProxies
    {
        final public static function FromArray(array $data, bool $no_proxy = false): Proxies
        {
            foreach ($data as $i => $args) {
                $data[$i] = self::CreateEntity(...$args);
            }
            return new self($data, $no_proxy);
        }

        final public function __construct(iterable $storage, bool $no_proxy = false)
        {
            foreach ($storage as $i => $proxy) {
                $this->AddEntity($i, $proxy);
            }
            $this->no_proxy = $no_proxy;
        }

        final public function SetProxy(string $host, int $port = null, string $user, string $password, int $type, bool $tunnel = false)
        {
            $this->Init($this->CreateEntity($host, $port, $user, $password, $type, $tunnel));
            return $this;
        }

        final public function jsonSerialize()
        {
            return $this->data;
        }

        final public function rewind()
        {
            $this->add_key = true;
            if ($this->data) {
                $this->idx = array_keys($this->data);
                shuffle($this->idx);// должна быть возможность отключить рандомизацию
                $this->key = reset($this->idx);
                if ($p = $this->data[$this->key]) {
                    $this->Init($p);
                }
            } elseif ($this->no_proxy) {
                $this->SetEmpty();
            }
        }

        final public function next()
        {
            $this->key = next($this->idx);
            if (false === $this->key) {
                if ($this->no_proxy) {
                    $this->SetEmpty();
                }
            } elseif ($p = $this->data[$this->key]) {
                $this->Init($p);
            }
        }

        final public function key()
        {
            return $this->key;
        }
        final public function valid()
        {
            return false !== $this->key;
        }
        final public function current()
        {
            return $this;
        }
        final public function count()
        {
            return count($this->data);
        }
        final public function Required(): bool
        {
            return !$this->no_proxy;
        }

        final protected function AddEntity(int $i, object $proxy)
        {
            $this->data[$i] = $proxy;
            return $this;
        }

        private function SetEmpty()
        {
            if ($this->add_key) {
                $this->SetProxy('', null, '', '', CURLPROXY_HTTP);
                $this->key = -1;
                $this->add_key = false;
            }
        }

        private $data = [];
        private $idx = [];
        private $key = false;
        private $add_key = true;
        private $no_proxy;
    }

}

namespace MaxieSystems\HTTP\Proxies {
    use MaxieSystems as MS;

    class FileStorage
    {
        public function __construct(string $filename)
        {
            $this->filename = $filename;
        }

        public function Load(string $c = 'stdClass'): array
        {
            $e = null;
            $this->data = MS\SimpleConfig::LoadFile($this->filename, $e);
            // if($e);
            $data = [];
            foreach ($this->data as $i => $row) {
                if (!empty($row['disabled'])) {
                    continue;
                }
                $p = new $c($this);
                foreach (MS\HTTP\Proxy::GetProperties() as $k) {
                    $p->$k = $row[$k];
                }
                $data[$i] = $p;
            }
            return $data;
        }

        private $filename;
        private $data;
    }

}


namespace MaxieSystems\Containers\ProxyElements {
    use MaxieSystems\{Containers,HTTP};

    class OptionConverter extends Containers\ProxyElement
    {
        public function __construct(Containers\Element $element, string $v_class)
        {
            parent::__construct($element);
            $this->v_class = $v_class;
        }

        public function Set($value)
        {
            $this->modified = true;
        }

        public function Get(iterable $value): iterable
        {
            if ($this->modified) {
                $c = 'MaxieSystems\\' . $this->v_class;
                if (is_array($value)) {
                    $this->value = new $c($value);
                } elseif (is_a($value, $c)) {
                    $this->value = $value;
                } else {
                    throw new \Exception('not implemented yet...');
                }
                $this->modified = false;
            }
            return $this->value;
        }

        private $v_class;
        private $value;
        private $modified = false;
    }

}
