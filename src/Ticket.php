<?php
/**
 * Footprints API for is-dev applications.
 * Note: This relies on the internal API maintained by Operations.
 */

namespace Footprints;

/**
 * Usage example:
 * <code>
 * $ticket = new \Footprints\Ticket("My Example Ticket");
 * $ticket->set_priority("Normal");
 * $ticket->set_user("sk");
 * $ticket->add_assignees(array(
 *     "Operations",
 *     "aejm"
 * ));
 *
 * $ticket->add_entry("We did this cool thing the other day!");
 * $ticket->add_entry("Oh, it isnt working.");
 * $ticket->add_entry("Fixed!");
 * 
 * $ticket->set_status("Resolved");
 * 
 * $ticket->create();
 *
 * // Or (Bulk).
 * \Footprints\API::create(array($ticket, $ticket2));
 * </code>
 */
class Ticket
{
    /** Workspace. */
    protected $_workspace = 2;

    /** Ticket fields. */
    private $_fields;

    /** Ticket custom fields. */
    private $_fields_custom;

    /** Ticket entries. */
    private $_entries;

    /**
     * Create a new ticket instance.
     *
     * @param string $title The title of the ticket.
     */
    public function __construct($title = "") {
        // Initialize fields.
        $this->_fields = array(
            "Assignees" => array(),
            "CI Links" => array(),
            "Ticket Links" => array()
        );
        $this->_fields_custom = array();

        // Initialize entries.
        $this->_entries = array();

        // Initialize workspace link arrays.
        $workspaces = $this->get_workspaces();
        foreach ($workspaces as $id => $name) {
            $this->_fields["Ticket Links"][$id] = array();
        }

        // Set anything we have passed through and defaults.
        $this->set_title($title);
        $this->set_priority("Normal");
        $this->set_status("Open");
    }

    /**
     * Returns a list of valid workspaces.
     */
    public function get_workspaces() {
        return array(
            2 => "Support",
            4 => "Change Request"
        );
    }

    /**
     * Returns a list of valid priorities.
     */
    public function get_priorities() {
        return array(
            "Background",
            "Normal",
            "High",
            "Critical"
        );
    }

    /**
     * Returns a list of valid statuses.
     */
    public function get_statuses() {
        return array(
            "Open",
            "Updated by Agent",
            "In Progress",
            "Waiting - Specified Time",
            "Waiting - External",
            "Waiting - User",
            "Waiting Kit Loan/Other",
            "Resolved"
        );
    }

    /**
     * Returns a list of valid ticket types.
     */
    public function get_types() {
        return array(
            "Incident",
            "Service Request - Service",
            "Service Request - Question",
            "Problem",
            "Quality and Standards"
        );
    }

    /**
     * Returns a list of valid ticket categories.
     */
    public function get_categories() {
        return array(
            "AV & Media",
            "Email and Calendaring",
            "File store",
            "Hardware",
            "IT Account",
            "Library",
            "Network",
            "Other",
            "Software",
            "Web"
        );
    }

    /**
     * Set the ticket title.
     * 
     * @param string $title The title of the ticket.
     */
    public function set_title($title) {
        $this->_fields["Title"] = $title;
    }

    /**
     * Set the ticket's user.
     * 
     * @param string $username The username of the user.
     */
    public function set_user($username) {
        $this->_fields["Username"] = $username;
    }

    /**
     * Add an assignee to the ticket.
     * 
     * @param array $assignee The username of the user or the name of the team.
     */
    public function add_assignee($assignee) {
        if (!in_array($assignee, $this->_fields["Assignees"])) {
            $this->_fields["Assignees"][] = $assignee;
        }
    }

    /**
     * Add assignees to the ticket.
     * 
     * @param string|array $assignees The username(s) of the user or the name(s) of the team(s).
     */
    public function add_assignees($assignees) {
        if (!is_array($assignees)) {
            $assignees = array($assignees);
        }

        foreach ($assignees as $assignee) {
            $this->add_assignee($assignee);
        }
    }

    /**
     * Add an entry (description) to this ticket.
     *
     * @param  string $contents The contents of this entry.
     */
    public function add_entry($contents) {
        if (empty($contents)) {
            throw new \Exception("Error - contents of ticket cannot be empty!");
        }

        $entry = array(
            "Description" => $contents
        );

        // Custom fields need to go in the entry (Footprints reasons).
        foreach ($this->_fields_custom as $name => $val) {
            $entry[$name] = $val;
        }

        $this->_entries[] = $entry;
    }

    /**
     * Set ticket priority.
     * 
     * @param string $priority The priority of the ticket.
     */
    public function set_priority($priority) {
        if (!in_array($priority, $this->get_priorities())) {
            throw new \Exception("Invalid priority '{$priority}'!");
        }

        $this->_fields["Priority"] = $priority;
    }

    /**
     * Set ticket status.
     * 
     * @param string $status The status of the ticket.
     */
    public function set_status($status) {
        if (!in_array($status, $this->get_statuses())) {
            throw new \Exception("Invalid status '{$status}'!");
        }

        $this->_fields["Status"] = $status;
    }

    /**
     * Set ticket type.
     * 
     * @param string $type The type of the ticket.
     */
    public function set_type($type) {
        if (!in_array($type, $this->get_types())) {
            throw new \Exception("Invalid type '{$type}'!");
        }

        $this->_fields_custom["Type of Ticket"] = $type;
    }

    /**
     * Set ticket category.
     * 
     * @param string $category The category of the ticket.
     */
    public function set_category($category) {
        if (!in_array($category, $this->get_categories())) {
            throw new \Exception("Invalid category '{$category}'!");
        }

        $this->_fields_custom["Category"] = $category;
    }

    /**
     * Link to an object in the CMDB.
     * 
     * @param string|array $type The type(s) of the link.
     * @param string|array $name The name(s) of the link.
     */
    public function add_ci_link($type, $name) {
        // Footprints API expects arrays.
        $type = is_array($type) ? $type : array($type);
        $name = is_array($name) ? $name : array($name);

        $this->_fields["CI Links"][] = array(
            "Type" => $type,
            "Name" => $name
        );
    }

    /**
     * Short-hand to link the ticket to a server.
     * 
     * @param string|array $hostname The hostname of the server, or an array of hostnames.
     */
    public function add_server_link($hostname) {
        $this->add_ci_link(array(
            "Server", "Virtual Server"
        ), $hostname);
    }

    /**
     * Link the ticket to another ticket.
     * 
     * @param string $number The number of the ticket.
     * @param string $workspace The workspace of the ticket.
     */
    public function add_ticket_link($number, $workspace = 2) {
        // Validate the workspace.
        if (!isset($this->_fields["Ticket Links"][$workspace])) {
            throw new \Exception("Invalid workspace '{$workspace}'!");
        }

        if (!in_array($number, $this->_fields["Ticket Links"][$workspace])) {
            $this->_fields["Ticket Links"][$workspace][] = $number;
        }
    }

    /**
     * Link the ticket to a CR.
     * 
     * @param string $number The number of the ticket.
     */
    public function add_change_request_link($number) {
        $this->add_ticket_link($number, 4);
    }

    /**
     * Coalesces everything into a Footprints object.
     */
    public function get_footprints_entry() {
        $customvals = array();
        $obj = new \stdClass();

        // First, the standard fields.
        foreach ($this->_fields as $name => $value) {
            $obj->$name = $value;
        }

        // Now the entries.
        $obj->Entries = array();
        foreach ($this->_entries as $entry) {
            $entryobj = new \stdClass();
            foreach ($entry as $name => $value) {
                // Only set something that has changed.
                if (!isset($customvals[$name]) || $customvals[$name] != $value) {
                    $entryobj->$name = $value;
                    $customvals[$name] = $value;
                }
            }
            $obj->Entries[] = $entryobj;
        }

        // We must have one entry.
        if (empty($obj->Entries)) {
            throw new \Exception("You must have at least one entry!");
        }

        // Are there any custom values that were not set?
        foreach ($this->_fields_custom as $name => $value) {
            if (!isset($customvals[$name]) || $customvals[$name] != $value) {
                // Update the last entry.
                $index = count($obj->Entries) - 1;
                $lastentry = $obj->Entries[$index];
                $lastentry->$name = $value;

                $customvals[$name] = $value;
            }
        }

        // Now cleanup, only send what we need to send.
        if (empty($this->_fields["CI Links"])) {
            $k = "CI Links";
            unset($obj->$k);
        }

        // Cleanup ticket links - workspaces.
        $workspaces = $this->get_workspaces();
        foreach ($workspaces as $id => $name) {
            if (empty($this->_fields["Ticket Links"][$id])) {
                $k = "Ticket Links";
                $arr = $obj->$k;
                unset($arr[$id]);
                $obj->$k = $arr;
            }
        }

        // Cleanup ticket links.
        $k = "Ticket Links";
        if (empty($obj->$k)) {
            unset($obj->$k);
        }

        return $obj;
    }

    /**
     * Send the ticket to FP!
     */
    public function create() {
        \Footprints\API::create(array($this));
    }

    /**
     * Send ticket asynchronously.
     */
    public function create_async() {
        \Footprints\API::create_async(array($this));
    }
}
