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
        if (false === $path = parse_url($relativeUrl)) {
            return false;
        }

        if(isset($path['scheme'])){
            return $relativeUrl;
        }

        $this->path = $path['path'] ?? '';

        if (false === $parts = parse_url($baseUrl)) {
            return false;
        }
        $this->baseUrlParts = $parts;

        $this->baseUrlParts['path'] = $this->normalizePath(
                pathinfo($this->getBaseUrlPart('path'), PATHINFO_DIRNAME)
            ) . '/' . $this->getPath();

        if (str_starts_with($this->path, '/')) {
            $this->baseUrlParts['path'] = $this->getPath();
        }
        $this->baseUrlParts['path'] = $this->urlRemoveDotSegments($this->getBaseUrlPart('path'));
        return $this->buildUrl();
    }


    private function normalizePath(string $path): string
    {
        if ($path === '\\') {
            return '';
        }
        return $path;
    }

    private function urlRemoveDotSegments(string $path): string
    {
        // multi-byte character explode
        $inSegments = preg_split('!/!u', $path);
        $outSegments = array();
        foreach ($inSegments as $segment) {
            if ($segment == '' || $segment == '.') {
                continue;
            }
            if ($segment == '..') {
                array_pop($outSegments);
            } else {
                array_push($outSegments, $segment);
            }
        }
        $outPath = implode('/', $outSegments);
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

        if ($part === 'query') {
            return '?' . ($this->baseUrlParts['query'] ?? '');
        }

        if ($part === 'pass') {
            return ':' . ($this->baseUrlParts['pass'] ?? '');
        }

        if ($part === 'port') {
            return ':' . ($this->baseUrlParts['port'] ?? '');
        }

        return (string)$this->baseUrlParts[$part];
    }


    private function getPath(): string
    {
        return $this->path;
    }

}