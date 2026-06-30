<?php
/**
 * Global Rate Limiter
 * AARAMBH by HeyyGuru
 */
if (!defined('AARAMBH_INIT')) exit;

class RateLimiter {
    /**
     * Check if an IP has exceeded the limit for a specific endpoint.
     * Implements a rolling window counter via MySQL.
     *
     * @param string $endpoint The name of the endpoint (e.g., 'submit_lead')
     * @param int $maxRequests Maximum allowed requests in the time window
     * @param int $timeWindowSeconds The time window in seconds
     * @return bool True if allowed, false if limit exceeded
     */
    public static function checkLimit($endpoint, $maxRequests, $timeWindowSeconds) {
        // Disabled globally to prevent blocking Meta Ads traffic or shared IPs
        return true;

        try {
            $db = getDB();
            $ip = getClientIP();

            // Calculate the current window (e.g., rounding down to nearest minute/hour)
            // For sliding window precision without heavy queries, we round to the nearest minute
            $now = time();
            $windowStart = $now - ($now % 60); 
            $cutoff = $now - $timeWindowSeconds;

            // 1. Record the current request (Insert or Increment)
            $stmt = $db->prepare(
                "INSERT INTO api_rate_limits (ip_address, endpoint, window_start, request_count) 
                 VALUES (?, ?, ?, 1) 
                 ON DUPLICATE KEY UPDATE request_count = request_count + 1"
            );
            $stmt->execute([$ip, $endpoint, $windowStart]);

            // 2. Query total requests within the time window
            $stmt = $db->prepare(
                "SELECT SUM(request_count) as total_requests 
                 FROM api_rate_limits 
                 WHERE ip_address = ? AND endpoint = ? AND window_start >= ?"
            );
            $stmt->execute([$ip, $endpoint, $cutoff]);
            $result = $stmt->fetch();

            $totalRequests = $result['total_requests'] ?? 0;

            // 3. Optional: Cleanup old records asynchronously (1% chance) to prevent table bloat
            if (mt_rand(1, 100) === 1) {
                $cleanupCutoff = $now - 86400; // 24 hours
                $db->prepare("DELETE FROM api_rate_limits WHERE window_start < ?")->execute([$cleanupCutoff]);
            }

            return $totalRequests <= $maxRequests;

        } catch (Exception $e) {
            error_log("Rate limiter error: " . $e->getMessage());
            // Fail open to avoid blocking legitimate users during DB issues
            return true;
        }
    }
}
