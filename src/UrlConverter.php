<?php

declare(strict_types=1);

namespace Enjoys;


class UrlConverter
{

    private string $path = '';

    /**
     * @var array{fragment?: string, host?: string, pass?: string, path?: string, port?: int, query?: string, scheme?: string, user?: string}
     */
    private array $baseUrlParts = [];

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

        if (false === $this->baseUrlParts = parse_url($baseUrl)) {
            return false;
        }

        $this->baseUrlParts['path'] = $this->normalizePath(
                pathinfo($this->getBaseUrlPart('path'), PATHINFO_DIRNAME)
            ) . '/' . $this->getPath();

        if (str_starts_with($this->path, '/')) {
            $this->baseUrlParts['path'] = $this->getPath();
        }
        $this->baseUrlParts['path'] = $this->url_remove_dot_segments($this->getBaseUrlPart('path'));
        return $this->buildUrl();
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
     * @return string
     */
    private function buildUrl(): string
    {
        return $this->getBaseUrlPart('scheme')
            . $this->getBaseUrlPartUserPass()
            . $this->getBaseUrlPart('host')
            . $this->getBaseUrlPart('port')
            . $this->getBaseUrlPart('path')
            . $this->getBaseUrlPart('query')
            . $this->getBaseUrlPart('fragment');
    }

    private function getBaseUrlPartUserPass(): string
    {
        $user = $this->getBaseUrlPart('user');
        $pass = $this->getBaseUrlPart('pass');
        $pass = ($user || $pass) ? "$pass@" : '';
        return $user . $pass;
    }


    private function getBaseUrlPart(string $part): string
    {
        if (!isset($this->baseUrlParts[$part])) {
            return '';
        }

        if ($part === 'scheme') {
            return ($this->baseUrlParts['scheme'] ?? 'http') . '://';
        }

        if ($part === 'fragment') {
            return '#' . ($this->baseUrlParts['fragment'] ?? '');
        }

        return (string)$this->baseUrlParts[$part];
    }


    private function getPath(): string
    {
        return $this->path;
    }

}