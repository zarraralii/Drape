<?php

if (! function_exists('asset_ver')) {
    /**
     * asset() with a ?v=<content hash> query string, so browsers/CDNs fetch
     * a new copy the moment the file's contents change instead of serving a
     * stale cached one for the file's Cache-Control lifetime.
     *
     * Hashes the contents rather than using filemtime(): uploading via a
     * zip/FTP often preserves the original (older) timestamps, which left
     * ?v= unchanged on the live server and browsers kept the old CSS/JS.
     */
    function asset_ver(string $path): string
    {
        static $versions = [];

        if (!isset($versions[$path])) {
            $fullPath = public_path($path);
            $versions[$path] = is_file($fullPath)
                ? substr(md5_file($fullPath), 0, 10)
                : (string) time();
        }

        return asset($path) . '?v=' . $versions[$path];
    }
}

if (! function_exists('money')) {
    /** "PKR 2,450" — in the currency set in Site Info. */
    function money(float|int|string|null $amount): string
    {
        return \App\Support\Store::money($amount);
    }
}

if (! function_exists('safe_redirect_url')) {
    /**
     * $url if it points at this site, otherwise $fallback — so a URL
     * taken from user input can't bounce a visitor to another domain.
     */
    function safe_redirect_url(?string $url, string $fallback): string
    {
        if (! $url || str_starts_with($url, '//') || str_contains($url, '\\')) {
            return $fallback;
        }

        if (str_starts_with($url, '/')) {
            return $url;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return $host && strcasecmp($host, request()->getHost()) === 0 ? $url : $fallback;
    }
}

if (! function_exists('safe_link')) {
    /**
     * For admin-entered links (banners, gallery CTAs): any http(s) or
     * site-relative URL, but never javascript:/data: — those would run
     * script in a visitor's browser.
     */
    function safe_link(?string $url, string $fallback): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return $fallback;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        return preg_match('#^https?://#i', $url) ? $url : $fallback;
    }
}
