<?php


namespace Enjoys;


class UrlConverter
{

    public function relativeToAbsolute(string $baseUrl, string $relativeUrl)
    {
        $path = parse_url($relativeUrl, PHP_URL_PATH);
        if ($path === false) {
            return $relativeUrl;
        }
        $base = parse_url($baseUrl);
        $base['path'] = $this->normalizePath(pathinfo($base['path'], PATHINFO_DIRNAME)) . '/' . $path;

        if (str_starts_with($path, '/')) {
            $base['path'] = $path;
        }
        $base['path'] = $this->url_remove_dot_segments($base['path']);
        return $this->join_url($base);
    }


    private function normalizePath($path)
    {
        if ($path === '\\') {
            return null;
        }
        return $path;
    }

    private function url_remove_dot_segments($path)
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


    function join_url($parts)
    {
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : null;

        $host = $parts['host'] ?? '';
        $port = $parts['port'] ?? '';
        $user = $parts['user'] ?? '';
        $pass = $parts['pass'] ?? '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : null;
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

}