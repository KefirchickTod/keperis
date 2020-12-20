<?php


namespace src\Http;


use src\Interfaces\UriInterface;
use Error;

class Uri implements UriInterface
{

    public $basePath;
    private $path;
    private $host;
    private $scheme;
    private $port;
    private $query;
    private $fragment;
    private $method;
    private $user;
    private $password;

    public function __construct(
        $scheme,
        $host,
        $port = null,
        $path = '/',
        $query = '',
        $fragment = '',
        $method = 'get',
        $user = '',
        $password = ''
    ) {
        $this->scheme = $this->filterScheme($scheme);
        $this->host = $host;
        $this->port = $port;
        $this->path = empty($path) ? '/' : $this->filterPath($path);
        $this->query = $this->filterQuery($query);
        $this->fragment = $this->filterQuery($fragment);
        $this->method = $method;

        $this->user = $user;
        $this->password = $password;


    }

    protected function filterScheme($scheme)
    {
        $scheme = str_replace('://', '', strtolower((string)$scheme));
        return $scheme;
    }

    protected function filterPath($path)
    {

        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $path
        );
    }

    protected function filterQuery($query)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function ($match) {
                return rawurlencode($match[0]);
            },
            $query
        );
    }

    public static function creat(ServerData $serverData)
    {
        $isSecure = $serverData->get('HTTPS');
        $scheme = (empty($isSecure) || $isSecure === 'off') ? 'http' : 'https';

        $user = $serverData->get('PHP_AUTH_USER', 'root@root.com');
        $password = $serverData->get('PHP_AUTH_PW', 'asign_admin');

        if ($serverData->has('HTTP_HOST')) {
            $host = $serverData->get('HTTP_HOST');
        } else {
            $host = $serverData->get('SERVER_NAME');
        }
        $port = (int)$serverData->get('SERVER_PORT', 80);
        if (preg_match('/^(\[[a-fA-F0-9:.]+\])(:\d+)?\z/', $host, $matches)) {
            $host = $matches[1];

            if ($matches[2]) {
                $port = (int)substr($matches[2], 1);
            }
        } else {
            $pos = strpos($host, ':');
            if ($pos !== false) {
                $port = (int)substr($host, $pos + 1);
                $host = strstr($host, ':', true);
            }
        }

        $requestScriptName = parse_url($serverData->get('SCRIPT_NAME'), PHP_URL_PATH);
        $requestScriptDir = dirname($requestScriptName);
        $requestUri = parse_url($serverData->get('REQUEST_URI'), PHP_URL_PATH);
        $basePath = '';
        $virtualPath = $requestUri;

        if (stripos($requestUri, $requestScriptName) === 0) {

          //  var_dump($requestScriptName);
            $basePath = $requestScriptName;
        } elseif ($requestScriptDir !== '/' && stripos($requestUri, $requestScriptDir) === 0) {
          //  var_dump($requestScriptDir, 'test');
            $basePath = $requestScriptDir;
        }
        if(APP_URL === 'http://dev.bc-club.org.ua/'){
            $basePath = "";//todo
        }

        if ($basePath) {
            $virtualPath = ltrim(substr($requestUri, strlen($basePath)), '/');
        }
     //   debug($virtualPath, $requestUri, $basePath);

        $queryString = $serverData->get('QUERY_STRING', '');

        $fragment = '';
        $method = $serverData->get('REQUEST_METHOD');
        $uri = new static($scheme, $host, $port, $virtualPath, $queryString, $fragment, $method);
        if ($basePath) {
            $uri = $uri->withBasePath($basePath);
        }

        return $uri;
    }

    /**
     * @param $basePath
     * @return Uri
     * @throws Error
     */
    public function withBasePath($basePath)
    {
        if (!is_string($basePath)) {
            throw new Error('Uri path must be a string');
        }
        if (!empty($basePath)) {
            $basePath = '/' . trim($basePath, '/');
        }
        $clone = clone $this;

        if ($basePath !== '/') {
            $clone->basePath = $this->filterPath($basePath);
        }

        return $clone;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getHost()
    {
        return $this->host;
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme)
    {
        $clone = clone $this;
        $scheme = $this->filterScheme($scheme);
        $clone->scheme = $scheme;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->user = $user;
        $clone->password = $password;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = $host;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port)
    {
        $clone = clone $this;
        $clone->port = $port;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path)
    {
        $clone = clone $this;
        $path = $this->filterPath($path);
        $clone->path = $path;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query)
    {
        $clone = clone $this;
        $query = ltrim((string)$query, '?');
        $clone->query = $this->filterQuery($query);
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withFragment($fragment)
    {
        $clone = clone $this;
        $clone->fragment = $fragment;
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $basePath = $this->getBasePath();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        $path = $basePath . '/' . ltrim($path, '/');

        return ($scheme ? $scheme . ':' : '')
            . ($authority ? '//' . $authority : '')
            . $path
            . ($query ? '?' . $query : '')
            . ($fragment ? '#' . $fragment : '');
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @inheritDoc
     */
    public function getAuthority()
    {
        return "[{$this->getUserInfo()}@]host[:{$this->getPort()}]";
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo()
    {
        return $this->user . ($this->password ? ':' . $this->password : '');
    }

    /**
     * @inheritDoc
     */
    public function getPort()
    {
        return $this->port;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getPath()
    {

        return $this->path;
    }

    public function getParseQuery() : array {
        $result = [];

        if($this->query){
            parse_str($this->query, $result);
        }

        return  $result;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

}