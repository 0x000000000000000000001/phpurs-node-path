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

$exports['resolve'] = function($from, $to) use ($normalize) {
    return function() use ($from, $to, $normalize) {
        $paths = \array_merge([\getcwd()], $from, [$to]);
        return $normalize(\implode('/', $paths));
    };
};

$exports['relative'] = function($from, $to) use ($normalize) {
    // Node-like POSIX relative path: resolve both sides, drop the common
    // prefix, then climb out of the remaining from-segments.
    $fromAbs = $normalize(\str_starts_with($from, '/') ? $from : \getcwd() . '/' . $from);
    $toAbs = $normalize(\str_starts_with($to, '/') ? $to : \getcwd() . '/' . $to);
    if ($fromAbs === $toAbs) return '';
    $fromParts = \array_values(\array_filter(\explode('/', $fromAbs), function($p) { return $p !== ''; }));
    $toParts = \array_values(\array_filter(\explode('/', $toAbs), function($p) { return $p !== ''; }));
    $i = 0;
    $min = \min(\count($fromParts), \count($toParts));
    while ($i < $min && $fromParts[$i] === $toParts[$i]) { $i++; }
    $segments = \array_merge(\array_fill(0, \count($fromParts) - $i, '..'), \array_slice($toParts, $i));
    return \implode('/', $segments);
};

$exports['dirname'] = function($p) {
    return \dirname($p);
};

$exports['basename'] = function($p) {
    return \basename($p);
};

$exports['basenameWithoutExt'] = function($p, $ext) {
    return \basename($p, $ext);
};

$exports['extname'] = function($p) {
    $base = \basename($p);
    $dot = \strrpos($base, '.');
    if ($dot === false || $dot === 0) return '';
    return \substr($base, $dot);
};

$exports['sep'] = DIRECTORY_SEPARATOR;
$exports['delimiter'] = PATH_SEPARATOR;

$exports['parse'] = function($p) {
    $info = \pathinfo($p);
    return (object)[
        'root' => \str_starts_with($p, '/') ? '/' : '',
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
