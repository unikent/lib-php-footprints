Footprints PHP API
==============

[![Latest Stable Version](https://poser.pugx.org/unikent/lib-php-footprints/v/stable.png)](https://packagist.org/packages/unikent/lib-php-footprints)

Full API docs available here: http://unikent.github.io/lib-php-footprints/

Add this to your composer require:
 * "unikent/lib-php-footprints": "dev-master"

Then create tickets like so:
```
$ticket = new \unikent\Footprints\Ticket("My Example Ticket");
$ticket->set_emails(false, false, false);
$ticket->set_priority("Normal");
$ticket->set_user("sk");
$ticket->set_type("Incident");
$ticket->set_category("Web");
$ticket->add_assignees(array(
    "Learning and Research Development",
    "sk"
));
$ticket->add_entry("We did this cool thing the other day!");
$ticket->add_technical_note("Something is broken.");
$ticket->add_entry("Oh, it isnt working.");
$ticket->add_technical_note("Hah it was that function I wrote a long time ago.");
$ticket->add_entry("Fixed!");
$ticket->set_status("Resolved");

$ticketnumber = $ticket->create();
```
