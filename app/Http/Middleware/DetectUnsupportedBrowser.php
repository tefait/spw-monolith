<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DetectUnsupportedBrowser
{
    protected $unsupportedPattern = '/MiuiBrowser|SamsungBrowser|VivoBrowser|OppoBrowser|HeyTapBrowser|UCWEB|UCBrowser|QQBrowser/i';

    public function handle(Request $request, Closure $next)
    {
        $userAgent = $request->header('User-Agent');
        $isUnsupported = preg_match($this->unsupportedPattern, $userAgent);

        if ($request->is('unsupported-browser')) {
            if (! $isUnsupported) {
                return redirect('/');
            }

            return $next($request);
        }
        if ($isUnsupported) {
            return redirect('/unsupported-browser');
        }

        return $next($request);
    }
}
