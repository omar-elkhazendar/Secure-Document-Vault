<?php

class IpHelper {
    /**
     * Get the client's real IP address
     * @return string
     */
    public static function getClientIp() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }

    /**
     * Format IP address for display
     * @param string $ip
     * @return string
     */
    public static function formatIpForDisplay($ip) {
        // If IP is IPv4, format it
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ip;
        }
        
        // If IP is IPv6, format it
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $ip;
        }
        
        // If IP is invalid, return a placeholder
        return '0.0.0.0';
    }

    /**
     * Check if an IP address is private/local
     * @param string $ip
     * @return bool
     */
    public static function isPrivateIp($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
} 