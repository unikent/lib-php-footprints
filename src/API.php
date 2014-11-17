<?php
/**
 * Footprints API for is-dev applications.
 * Note: This relies on the internal API maintained by Operations.
 */

namespace unikent\Footprints;

/**
 * Sends tickets to FP.
 */
class API
{
    const api_url = 'https://orange.kent.ac.uk/api/logTicket/index.php';

    /**
     * Returns the POST data for a request.
     * 
     * @param mixed[] $tickets The ticket object(s).
     */
    private static function get_post_data($tickets) {
        if (!is_array($tickets)) {
            $tickets = array($tickets);
        }

        // Grab the raw objects.
        $raw = array();
        foreach ($tickets as $ticket) {
            $raw[] = $ticket->get_footprints_entry();
        }

        return json_encode($raw);
    }

    /**
     * Send a ticket (or tickets) to FP.
     * 
     * @param mixed[] $tickets The ticket object(s).
     */
    public static function create($tickets) {
        $json = static::get_post_data($tickets);
        return static::create_raw($json);
    }

    /**
     * Send a ticket (or tickets) to FP.
     * 
     * @param string $json The raw JSON to send.
     */
    public static function create_raw($json) {
        // Send it to orange!
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            self::api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST,           1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain'));

        $result = curl_exec($ch);

        // Parse the result.
        $parts = explode(' ', $result);
        if (count($parts) !== 3) {
            throw new \Exception("Unknown result from Footprints API: '{$result}'.");
        }

        if ($parts[0] !== "OK") {
            throw new \Exception("Footprints API did not return OK: '{$result}'.");
        }

        // Return the ticket number.
        $ticketnumber = explode(':', $parts[2]);
        if (isset($ticketnumber[1])) {
            return $ticketnumber[1];
        }

        throw new \Exception("Unknown result from Footprints API: '{$result}'.");
    }

    /**
     * Send a ticket (or tickets) to FP asynchronously.
     * 
     * @param mixed[] $tickets The ticket object(s).
     */
    public static function create_async($tickets) {
        $json = static::get_post_data($tickets);
        return static::create_async_raw($json);
    }

    /**
     * Send a ticket (or tickets) to FP asynchronously.
     * 
     * @param string $json The raw JSON to send.
     */
    public static function create_async_raw($json) {
        $parts = parse_url(self::api_url);
        $fp = fsockopen($parts['host'], 443, $errno, $errstr, 30);

        // Create output.
        $output  = "POST " . $parts['path'] . " HTTP/1.1\r\n";
        $output .= "Host: " . $parts['host'] . "\r\n";
        $output .= "Content-Type: text/plain\r\n";
        $output .= "Content-Length: " . strlen($json) . "\r\n";
        $output .= "Connection: Close\r\n\r\n";
        $output .= $json;

        fwrite($fp, $output);
        fclose($fp);
    }
}
