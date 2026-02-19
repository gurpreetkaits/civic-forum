<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class BotDetection
{
    /**
     * Bot indicators to check
     */
    private array $botUserAgents = [
        'bot', 'crawler', 'spider', 'curl', 'wget', 'python', 'requests',
        'httpclient', 'java/', 'ruby', 'go-http', 'node-fetch', 'axios',
        'facebookexternalhit', 'twitterbot', 'linkedinbot', 'slackbot',
        'discordbot', 'telegrambot', 'whatsapp', '信号', 'scrapy',
    ];

    /**
     * Suspicious patterns in user agent
     */
    private array $suspiciousPatterns = [
        '/^Mozilla\/5\.0 \(compatible; (Googlebot|Bingbot|Yandex|Slurp)/i',
        '/^Mozilla\/5\.0 \(Windows NT 10\.0; Win64; x64\) AppleWebKit\/537.36 \(KHTML, like Gecko\) Chrome\/\d+\.0\.0\.0 Safari\/537.36$/',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip bot detection for common web crawlers
        if ($this->isAllowedBot($request)) {
            return $next($request);
        }

        // Check for obvious bots
        if ($this->isObviousBot($request)) {
            return $this->respondBlocked($request, 'Automated requests not allowed');
        }

        // Check IP reputation
        if ($this->isSuspiciousIP($request)) {
            return $this->respondBlocked($request, 'Suspicious activity detected');
        }

        // Check for request anomalies
        if ($this->hasRequestAnomalies($request)) {
            return $this->respondBlocked($request, 'Request pattern blocked');
        }

        // Track request for behavioral analysis
        $this->trackRequest($request);

        // Add request fingerprint header
        $response = $next($request);

        // Add verification token for suspicious requests
        if ($this->isSuspiciousRequest($request)) {
            $this->addVerificationChallenge($response);
        }

        return $response;
    }

    /**
     * Check if the request is from an allowed bot (search engines, etc.)
     */
    private function isAllowedBot(Request $request): bool
    {
        $userAgent = $request->userAgent() ?? '';

        // Allow search engine crawlers
        $allowedBots = [
            'Googlebot',
            'Googlebot-Image',
            'Googlebot-News',
            'Googlebot-Video',
            'Bingbot',
            'Bingpreview',
            'Msnbot',
            'Slurp',
            'Yandex',
            'DuckDuckBot',
            'Baiduspider',
            'Sogou web spider',
            'Exabot',
            'facebookexternalhit',
            'Twitterbot',
            'LinkedInBot',
        ];

        foreach ($allowedBots as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if request is from an obvious bot
     */
    private function isObviousBot(Request $request): bool
    {
        $userAgent = $request->userAgent() ?? '';

        // Check for common bot indicators in user agent
        foreach ($this->botUserAgents as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                // Verify it's not a legitimate browser
                if (!preg_match('/Mozilla\/5\.0/', $userAgent)) {
                    return true;
                }
            }
        }

        // Check for missing essential headers (common in bots)
        $essentialHeaders = [
            'Accept',
            'Accept-Language',
        ];

        foreach ($essentialHeaders as $header) {
            if (!$request->hasHeader($header)) {
                return true;
            }
        }

        // Check for suspicious patterns
        foreach ($this->suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                // Additional verification needed
                $cacheKey = 'bot_verify:' . $request->ip();
                if (!Cache::has($cacheKey)) {
                    Cache::put($cacheKey, true, now()->addMinutes(5));
                    return false; // First time, let through but challenge later
                }
            }
        }

        return false;
    }

    /**
     * Check if IP is suspicious
     */
    private function isSuspiciousIP(Request $request): bool
    {
        $ip = $request->ip();

        // Check for known malicious IPs (you can extend this with a database)
        $suspiciousIPs = [
            // Add known malicious IP ranges or individual IPs
        ];

        if (in_array($ip, $suspiciousIPs)) {
            return true;
        }

        // Check for data center IPs (common for bots)
        $dataCenterPrefixes = [
            '104.244',   // Cloudflare
            '172.64',    // Cloudflare
            '172.65',    // Cloudflare
            '172.66',    // Cloudflare
            '172.67',    // Cloudflare
            '172.68',    // Cloudflare
            '172.69',    // Cloudflare
            '172.70',    // Cloudflare
            '108.162',   // Cloudflare
            '141.101',   // Cloudflare
            '198.41',    // Cloudflare
        ];

        foreach ($dataCenterPrefixes as $prefix) {
            if (str_starts_with($ip, $prefix)) {
                // Not necessarily bad, but require additional verification
                return false;
            }
        }

        return false;
    }

    /**
     * Check for request anomalies
     */
    private function hasRequestAnomalies(Request $request): bool
    {
        // Check for missing referer on POST requests (common bot behavior)
        if ($request->isMethod('POST') && !$request->hasHeader('Referer')) {
            return true;
        }

        // Check for unusual request timing
        $sessionId = $request->session()->getId();
        if ($sessionId) {
            $lastRequestTime = Cache::get("request_time:{$sessionId}");
            $currentTime = microtime(true);

            if ($lastRequestTime !== null) {
                $timeDiff = $currentTime - $lastRequestTime;

                // If requests are too fast (< 0.5 seconds consistently), likely a bot
                if ($timeDiff < 0.5 && $timeDiff > 0) {
                    $fastRequestCount = Cache::increment("fast_requests:{$sessionId}");

                    if ($fastRequestCount > 5) {
                        return true;
                    }
                }
            }

            Cache::put("request_time:{$sessionId}", $currentTime, now()->addMinutes(10));
        }

        return false;
    }

    /**
     * Track request for behavioral analysis
     */
    private function trackRequest(Request $request): void
    {
        $sessionId = $request->session()->getId() ?? $request->ip();
        $key = "request_count:{$sessionId}";

        // Increment request count
        $count = Cache::increment($key);

        // Reset after 1 minute of inactivity
        Cache::put($key, $count, now()->addMinutes(1));

        // Track different action types
        $action = $request->route()?->getName() ?? 'unknown';
        Cache::put("last_action:{$sessionId}", $action, now()->addMinutes(10));
    }

    /**
     * Check if request is suspicious enough to require verification
     */
    private function isSuspiciousRequest(Request $request): bool
    {
        $sessionId = $request->session()->getId() ?? $request->ip();

        // Check for high request frequency
        $count = Cache::get("request_count:{$sessionId}", 0);
        if ($count > 50) {
            return true;
        }

        return false;
    }

    /**
     * Add verification challenge to response
     */
    private function addVerificationChallenge(Response $response): void
    {
        // Add a header to trigger JavaScript verification on client side
        $response->headers->set('X-Verification-Required', '1');

        // Set a cookie that will be checked on next request
        $verificationToken = bin2hex(random_bytes(16));
        $response->headers->setCookie(
            \Illuminate\Cookie\Cookie::make(
                'verify',
                $verificationToken,
                now()->addMinutes(5)->timestamp,
                '/',
                null,
                true,
                true,
                'Lax'
            )
        );
    }

    /**
     * Respond with blocked message
     */
    private function respondBlocked(Request $request, string $message): Response
    {
        // If AJAX/JSON request, return JSON error
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'access_denied',
                'message' => $message,
                'code' => 'BOT_DETECTED',
            ], 403);
        }

        // For regular requests, show a friendly error page
        return response()->view('errors.bot-detected', [
            'message' => $message,
        ], 403);
    }
}
