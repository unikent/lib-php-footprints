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
 * );
 *
 * $ticket->add_entry("We did this cool thing the other day!");
 * $ticket->add_entry("Oh, it isnt working.");
 * $ticket->add_entry("Fixed!");
 * 
 * $ticket->set_status("Closed");
 * 
 * $ticket->create();
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

    /** CMDB links. */
    private $_links_ci;

    /** Ticket links. */
    private $_links_tickets;

    /**
     * Create a new ticket instance.
     *
     * @param string $title The title of the ticket.
     */
    public function __construct($title = "") {
        // Initialize fields.
        $this->_fields = array(
            "Assignees" => array()
        );
        $this->_fields_custom = array();

        // Initialize entries.
        $this->_entries = array();

        // Initialize links.
        $this->_links_ci = array();
        $this->_links_tickets = array();

        // Initialize workspace link arrays.
        $workspaces = $this->get_workspaces();
        foreach ($workspaces as $id => $name) {
            $this->_links_tickets[$id] = array();
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

        $this->_links_ci[] = array(
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
        if (!isset($this->_links_tickets[$workspace])) {
            throw new \Exception("Invalid workspace '{$workspace}'!");
        }

        if (!in_array($number, $this->_links_tickets[$workspace])) {
            $this->_links_tickets[$workspace][] = $number;
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
}
