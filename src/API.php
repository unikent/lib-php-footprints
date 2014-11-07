<?php
/**
 * Footprints API for is-dev applications.
 * Note: This relies on the internal API maintained by Operations.
 */

namespace Footprints;

/**
 * Sends tickets to FP.
 */
class API
{
    const api_url = 'https://orange.kent.ac.uk/api/logTicket/index.php';
    /**
     * Send a ticket (or tickets) to FP.
     * 
     * @param Ticket|array $tickets The ticket object(s).
     */
    public static function create($tickets) {
        if (!is_array($tickets)) {
            $tickets = array($tickets);
        }

        // Grab the raw objects.
        $raw = array();
        foreach ($tickets as $ticket) {
            $raw[] = $ticket->get_footprints_entry();
        }

        $json = json_encode($raw);

        // Send it to orange!
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            self::api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST,           1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $json); 
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain')); 

        $result = curl_exec($ch);
        echo $result;
    }
}
