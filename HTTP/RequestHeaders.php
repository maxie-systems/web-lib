<?php

namespace MaxieSystems\HTTP;

class RequestHeaders extends Headers
{
    # PHP объединяет в массиве $_SERVER множественные HTTP-заголовки в один элемент массива. Например:
    # Два отдельных заголовка 'Accept: application/json' и 'Accept: application/xml'
    # превратятся в элемент $_SERVER['HTTP_ACCEPT'] со значением 'application/json, application/xml'
    # Три отдельных заголовка 'DAV: http://subversion.tigris.org/xmlns/dav/svn/depth', 'DAV: http://subversion.tigris.org/xmlns/dav/svn/mergeinfo' и 'DAV: http://subversion.tigris.org/xmlns/dav/svn/log-revprops'
    # превратятся в элемент $_SERVER['HTTP_DAV'] со значением 'http://subversion.tigris.org/xmlns/dav/svn/depth, http://subversion.tigris.org/xmlns/dav/svn/mergeinfo, http://subversion.tigris.org/xmlns/dav/svn/log-revprops'
    # Поэтому можно использовать $this->SetHeader().
    final public function __construct(iterable $headers = null)
    {
        if ($headers) {
            parent::__construct($headers);
        }
        foreach ($_SERVER as $k => $v) {
            if ($k = $this->GetHeaderName($k)) {
                $this->SetHeader(new Header($k, $v));
            }
        }
    }

    private function GetHeaderName(string $k): string
    {
        static $hdrs = ['CONTENT_TYPE' => 'Content-Type', 'CONTENT_LENGTH' => 'Content-Length'];
        if (isset($hdrs[$k])) {
            return $hdrs[$k];
        } elseif ('HTTP_' === substr($k, 0, 5)) {
            return str_replace('_', '-', strtolower(substr($k, 5)));
        }
        return '';
    }
}
