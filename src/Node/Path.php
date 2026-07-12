<?php

$normalize = function($p) {
    $p = \str_replace('\\', '/', $p);
    $parts = \explode('/', $p);
    $out = [];
    foreach ($parts as $part) {
        if ($part === '.' || $part === '') continue;
        if ($part === '..') {
            if (!empty($out) && \end($out) !== '..') {
                \array_pop($out);
            } else {
                $out[] = $part;
            }
        } else {
            $out[] = $part;
        }
    }
    $prefix = (\str_starts_with($p, '/') ? '/' : '');
    return $prefix . \implode('/', $out);
};

$exports['normalize'] = $normalize;

$exports['concat'] = function($segments) use ($normalize) {
    return $normalize(\implode('/', $segments));
};

$exports['resolve'] = function($from) use ($normalize) {
    return function($to) use ($from, $normalize) {
        return function() use ($from, $to, $normalize) {
            $paths = \array_merge([\getcwd()], $from, [$to]);
            return $normalize(\implode('/', $paths));
        };
    };
};

$exports['relative'] = function($from) {
    return function($to) use ($from) {
        // Very naive stub
        return $to;
    };
};

$exports['dirname'] = function($p) {
    return \dirname($p);
};

$exports['basename'] = function($p) {
    return \basename($p);
};

$exports['basenameWithoutExt'] = function($p) {
    return function($ext) use ($p) {
        return \basename($p, $ext);
    };
};

$exports['extname'] = function($p) {
    $ext = \pathinfo($p, PATHINFO_EXTENSION);
    return $ext === '' ? '' : '.' . $ext;
};

$exports['sep'] = DIRECTORY_SEPARATOR;
$exports['delimiter'] = PATH_SEPARATOR;

$exports['parse'] = function($p) {
    $info = \pathinfo($p);
    return (object)[
        'root' => '',
        'dir' => $info['dirname'] ?? '',
        'base' => $info['basename'] ?? '',
        'ext' => isset($info['extension']) ? '.' . $info['extension'] : '',
        'name' => $info['filename'] ?? ''
    ];
};

$exports['isAbsolute'] = function($p) {
    return \str_starts_with($p, '/') || \preg_match('/^[a-zA-Z]:\\\\/', $p);
};

return $exports;
