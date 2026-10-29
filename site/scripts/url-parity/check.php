#!/usr/bin/env php
<?php

/**
 * URL parity check: every URL in the live site's sitemaps, requested on another site
 * (local, staging…), so we can see what matches, redirects, or is missing before launch.
 *
 *   php scripts/url-parity/check.php                       # live sitemap vs https://betterbybike.test
 *   php scripts/url-parity/check.php --base=https://staging.example.com
 *   php scripts/url-parity/check.php --source=https://betterbybike.info/sitemap_index.xml --only=post,category
 *
 * Read-only: only GET requests, redirects are reported not followed. Exits 1 if any URL is
 * missing (4xx/5xx), so it can gate a deploy. Plain PHP (ext-curl, ext-simplexml), no WordPress.
 */
$options = getopt('', ['base::', 'source::', 'only::', 'concurrency::', 'help']);

if (isset($options['help'])) {
    echo "Usage: php scripts/url-parity/check.php [--base=URL] [--source=SITEMAP_INDEX_URL] [--only=post,page] [--concurrency=6]\n";
    exit(0);
}

$base = rtrim($options['base'] ?? 'https://betterbybike.test', '/');
$source = $options['source'] ?? 'https://betterbybike.info/sitemap_index.xml';
$only = isset($options['only']) ? array_filter(explode(',', $options['only'])) : [];
$concurrency = max(1, (int) ($options['concurrency'] ?? 6));

/** @return string[] the page/sitemap <loc> values (not <image:loc>) in a sitemap or sitemap index */
function locs(string $url): array
{
    $xml = @file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 30, 'user_agent' => 'url-parity-check']]));
    $doc = $xml ? @simplexml_load_string($xml) : false;

    if (! $doc) {
        fwrite(STDERR, "Could not read {$url}\n");
        exit(2);
    }

    return array_map('strval', iterator_to_array($doc->xpath('//*[local-name()="url" or local-name()="sitemap"]/*[local-name()="loc"]') ?: [], false));
}

// Sitemap name ("post", "page", "case_study"…) => paths on the source site.
$groups = [];

foreach (locs($source) as $sitemap) {
    if (! str_ends_with($sitemap, '.xml')) {
        continue;
    }

    $name = preg_replace('/-sitemap\d*\.xml$/', '', basename($sitemap));

    if ($only && ! in_array($name, $only, true)) {
        continue;
    }

    foreach (locs($sitemap) as $loc) {
        if (! str_ends_with($loc, '.xml')) {
            $groups[$name][] = parse_url($loc, PHP_URL_PATH) . (($q = parse_url($loc, PHP_URL_QUERY)) ? "?{$q}" : '');
        }
    }
}

$results = [];
$queue = [];

foreach ($groups as $name => $paths) {
    foreach ($paths as $path) {
        $queue[] = [$name, $path];
    }
}

$multi = curl_multi_init();
$active = [];
$next = 0;

$start = function () use (&$next, &$active, $queue, $base, $multi) {
    [$name, $path] = $queue[$next++];
    $handle = curl_init($base . $path);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => 'url-parity-check',
    ]);
    curl_multi_add_handle($multi, $handle);
    $active[(int) $handle] = [$handle, $name, $path];
};

while ($next < count($queue) && count($active) < $concurrency) {
    $start();
}

while ($active) {
    curl_multi_exec($multi, $running);
    curl_multi_select($multi, 1);

    while ($done = curl_multi_info_read($multi)) {
        [$handle, $name, $path] = $active[(int) $done['handle']];
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $location = (string) curl_getinfo($handle, CURLINFO_REDIRECT_URL);
        $results[$name][] = [$path, $status, $location];

        curl_multi_remove_handle($multi, $handle);
        unset($active[(int) $handle]);

        if ($next < count($queue)) {
            $start();
        }
    }
}

$missing = 0;
printf("Source: %s\nTarget: %s\n\n", $source, $base);
printf("%-24s %6s %9s %8s\n", 'sitemap', 'ok', 'redirect', 'missing');

foreach ($results as $name => $rows) {
    $ok = count(array_filter($rows, fn($r) => $r[1] >= 200 && $r[1] < 300));
    $redirect = count(array_filter($rows, fn($r) => $r[1] >= 300 && $r[1] < 400));
    $bad = count($rows) - $ok - $redirect;
    $missing += $bad;
    printf("%-24s %6d %9d %8d\n", $name, $ok, $redirect, $bad);
}

foreach ($results as $name => $rows) {
    foreach ($rows as [$path, $status, $location]) {
        if ($status >= 200 && $status < 300) {
            continue;
        }

        printf("\n%-8s %-22s %s%s", $status >= 300 && $status < 400 ? 'REDIRECT' : 'MISSING', "{$name} ({$status})", $path, $location ? "  ->  {$location}" : '');
    }
}

echo "\n\n" . ($missing ? "{$missing} URL(s) missing.\n" : "All URLs resolve.\n");
exit($missing ? 1 : 0);
