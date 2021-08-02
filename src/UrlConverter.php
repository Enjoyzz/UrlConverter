<?php

declare(strict_types=1);

namespace Enjoys;


class UrlConverter
{

    private string $path = '';

    /**
     * @var array{fragment?: string, host?: string, pass?: string, path?: string, port?: int, query?: string, scheme?: string, user?: string}
     */
    private array $baseUrlParsed = [];

    /**
     * @param string $baseUrl
     * @param string $relativeUrl
     * @return false|string
     */
    public function relativeToAbsolute(string $baseUrl, string $relativeUrl)
    {
        if (false === $path = parse_url($relativeUrl, PHP_URL_PATH)) {
            return false;
        }

        $this->path = $path;

        if(false === $this->baseUrlParsed = parse_url($baseUrl)){
            return false;
        }

        $this->baseUrlParsed['path'] = $this->normalizePath(
                pathinfo($this->baseUrlParsed['path'] ?? '', PATHINFO_DIRNAME)
            ) . '/' . $this->path;

        if (str_starts_with($this->path, '/')) {
            $this->baseUrlParsed['path'] = $this->path;
        }
        $this->baseUrlParsed['path'] = $this->url_remove_dot_segments($this->baseUrlParsed['path']);
        return $this->join_url($this->baseUrlParsed);
    }


    private function normalizePath(string $path): string
    {
        if ($path === '\\') {
            return '';
        }
        return $path;
    }

    private function url_remove_dot_segments(string $path): string
    {
        // multi-byte character explode
        $inSegs = preg_split('!/!u', $path);
        $outSegs = array();
        foreach ($inSegs as $seg) {
            if ($seg == '' || $seg == '.') {
                continue;
            }
            if ($seg == '..') {
                array_pop($outSegs);
            } else {
                array_push($outSegs, $seg);
            }
        }
        $outPath = implode('/', $outSegs);
        if ($path[0] == '/') {
            $outPath = '/' . $outPath;
        }
        // compare last multi-byte character against '/'
        if ($outPath != '/' &&
            (\mb_strlen($path) - 1) == \mb_strrpos($path, '/')) {
            $outPath .= '/';
        }
        return $outPath;
    }


    /**
     * @param array{fragment?: string, host?: string, pass?: string, path?: string, port?: int, query?: string, scheme?: string, user?: string} $parts
     * @return string
     */
    private function join_url(array $parts): string
    {
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';

        $host = $parts['host'] ?? '';
        $port = $parts['port'] ?? '';
        $user = $parts['user'] ?? '';
        $pass = $parts['pass'] ?? '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

}