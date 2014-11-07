<?php
/**
 * Footprints API for is-dev applications.
 * Note: This relies on the internal API maintained by Operations.
 */

require_once(dirname(__FILE__) . "/../src/Ticket.php");
require_once(dirname(__FILE__) . "/../src/API.php");

$ticket = new \Footprints\Ticket("My Example Ticket");
$ticket->set_priority("Normal");
$ticket->set_user("sk");
$ticket->set_type("Incident");
$ticket->set_category("Web");
$ticket->add_assignees(array(
    "Learning and Research Systems",
    "sk"
));

$ticket->add_entry("We did this cool thing the other day!");
$ticket->add_entry("Oh, it isnt working.");
$ticket->add_entry("Fixed!");

$ticket->set_status("Resolved");

echo $ticket->create_async();