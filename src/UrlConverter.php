<?php


namespace Enjoys;


class UrlConverter
{

    public function relativeToAbsolute(string $baseUrl, string $relativeUrl)
    {
        // If relative URL has a scheme, clean path and return.
//        $r = $this->split_url($relativeUrl);
//        var_dump($r );
        $r = parse_url($relativeUrl);

        if ($r === false) {
            return false;
        }
        if (!empty($r['scheme'])) {
            if (!empty($r['path']) && $r['path'][0] == '/') {
                $r['path'] = $this->url_remove_dot_segments($r['path']);
            }
            return $this->join_url($r);
        }

        // Make sure the base URL is absolute.
        $b = parse_url($baseUrl);
        if ($b === false || empty($b['scheme']) || empty($b['host'])) {
            return false;
        }
        $r['scheme'] = $b['scheme'];

        // If relative URL has an authority, clean path and return.
        if (isset($r['host'])) {
            if (!empty($r['path'])) {
                $r['path'] = $this->url_remove_dot_segments($r['path']);
            }
            return $this->join_url($r);
        }
        unset($r['port']);
        unset($r['user']);
        unset($r['pass']);

        // Copy base authority.
        $r['host'] = $b['host'];
        if (isset($b['port'])) {
            $r['port'] = $b['port'];
        }
        if (isset($b['user'])) {
            $r['user'] = $b['user'];
        }
        if (isset($b['pass'])) {
            $r['pass'] = $b['pass'];
        }

        // If relative URL has no path, use base path
        if (empty($r['path'])) {
            if (!empty($b['path'])) {
                $r['path'] = $b['path'];
            }
            if (!isset($r['query']) && isset($b['query'])) {
                $r['query'] = $b['query'];
            }
            return $this->join_url($r);
        }

        // If relative URL path doesn't start with /, merge with base path
        if ($r['path'][0] != '/') {
            $base = \mb_strrchr($b['path'], '/', true, 'UTF-8');
            if ($base === false) {
                $base = '';
            }
            $r['path'] = $base . '/' . $r['path'];
        }
        $r['path'] = $this->url_remove_dot_segments($r['path']);
        return $this->join_url($r);
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


    function join_url($parts) {
        $scheme   = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host     = isset($parts['host']) ? $parts['host'] : '';
        $port     = isset($parts['port']) ? ':' . $parts['port'] : '';
        $user     = isset($parts['user']) ? $parts['user'] : '';
        $pass     = isset($parts['pass']) ? ':' . $parts['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parts['path']) ? $parts['path'] : '';
        $query    = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

}