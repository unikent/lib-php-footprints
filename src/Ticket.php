<?php
/**
 * Footprints API for is-dev applications.
 * Note: This relies on the internal API maintained by Operations.
 */

namespace unikent\Footprints;

/**
 * A Footprints Ticket object.
 * 
 * @example ../examples/example-1.php Standard Ticket Example.
 * @example ../examples/example-2.php Standard Ticket Example with asynchronous creation.
 * @example ../examples/example-3.php CR Example.
 */
class Ticket
{
    /** Workspace. */
    protected $_workspace = 2;

    /** Ticket fields. */
    private $_fields;

    /** Ticket custom fields (per-entry). */
    private $_fields_entry;

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
            "PermanentCCs" => array(),
            "OneTimeCCs" => array(),
            "CI Links" => array(),
            "Ticket Links" => array()
        );
        $this->_fields_entry = array();

        // Initialize entries.
        $this->_entries = array();

        // Initialize workspace link arrays.
        $workspaces = $this->get_workspaces();
        foreach ($workspaces as $id => $name) {
            $this->_fields["Ticket Links"][$id] = array();
        }

        // Set anything we have passed through and defaults.
        $this->set_defaults();
        $this->set_title($title);
    }

    /**
     * Returns a proj fields entry.
     */
    protected function get_proj_field($name) {
        if (isset($this->_fields_entry['projfields'])) {
            $obj = $this->_fields_entry['projfields'];
            if (isset($obj->$name)) {
                return $obj->$name;
            }
        }

        return null;
    }

    /**
     * Add a proj fields entry.
     */
    protected function set_proj_field($name, $value) {
        $obj = new \stdClass();
        if (isset($this->_fields_entry['projfields'])) {
            $obj = $this->_fields_entry['projfields'];
        }

        $obj->$name = $value;

        $this->_fields_entry['projfields'] = $obj;
    }

    /**
     * Sets defaults.
     */
    protected function set_defaults() {
        $this->set_priority("Normal");
        $this->set_status("New");
        $this->set_type("Incident");
        $this->set_category("Web");
        $this->set_emails();
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
            "New",
            "Updated by Agent",
            "In Progress",
            "Waiting - Specified Time",
            "Waiting - External",
            "Waiting - User",
            "Waiting Kit Loan/Other",
            "Resolved",
            "Closed"
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
     * Add a technical note to the ticket.
     * 
     * @param string $note The note.
     */
    public function add_technical_note($note) {
        $notes = $this->get_proj_field("Technical Notes");
        if (!empty($notes)) {
            $note = "\n\n" . $note;
        } else {
            $notes = $note;
        }

        $this->set_proj_field("Technical Notes", $notes);
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
     * Add a CC to the ticket.
     * 
     * @param string $cc The username (or the email) of the recipient.
     * @param boolean $permanent Is the CC permanent or just for this update?
     */
    public function add_cc($cc, $permanent = true) {
        $field = $permanent ? 'PermanentCCs' : 'OneTimeCCs';

        if (!in_array($cc, $this->_fields[$field])) {
            $this->_fields[$field][] = $cc;
        }
    }

    /**
     * Add CCs to the ticket.
     * 
     * @param string|array $cc The username (or the email) of the recipient.
     * @param boolean $permanent Is the CC permanent or just for this update?
     */
    public function add_ccs($ccs, $permanent = true) {
        if (!is_array($ccs)) {
            $ccs = array($ccs);
        }

        foreach ($ccs as $cc) {
            $this->add_cc($cc, $permanent);
        }
    }

    /**
     * Add an entry (description) to this ticket.
     *
     * @param string $contents The contents of this entry.
     */
    public function add_entry($contents) {
        if (empty($contents)) {
            throw new \Exception("Error - contents of ticket cannot be empty!");
        }

        $this->_entries[] = $contents;
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

        $this->set_proj_field("Type of Ticket", $type);
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

        $this->set_proj_field("Category", $category);
    }

    /**
     * Who should be emailed about this ticket?
     * Note: Footprints emails assignees anyway.
     * But maybe the bug will be fixed one day!
     *
     * @param boolean $assignees Should assignees be emailed?
     * @param boolean $contact Should contacts be emailed?
     * @param boolean $cc Should cc'd addresses be emailed?
     */
    public function set_emails($assignees = true, $contact = true, $cc = true) {
        $assignees = $assignees === false ? 0 : 1;
        $contact = $contact === false ? 0 : 1;
        $cc = $cc === false ? 0 : 1;

        $this->_fields_entry["SendMail"] = array(
            "assignees" => $assignees,
            "contact" => $contact,
            "permanentCCs" => $cc
        );
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
        $obj = new \stdClass();
        $obj->Workspace = $this->_workspace;

        // First, the standard fields.
        foreach ($this->_fields as $name => $value) {
            $obj->$name = $value;
        }

        // Now the entries.
        $obj->Entries = array();
        foreach ($this->_entries as $entry) {
            $entryobj = new \stdClass();
            $entryobj->Description = $entry;

            // If this is the first one, set the custom values.
            if (empty($obj->Entries)) {
                foreach ($this->_fields_entry as $name => $value) {
                    $entryobj->$name = $value;
                }
            }

            $obj->Entries[] = $entryobj;
        }

        // We must have one entry.
        if (empty($obj->Entries)) {
            throw new \Exception("You must have at least one entry!");
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
     *
     * @return string Footprints ticket number, or an error.
     */
    public function create() {
        return API::create(array($this));
    }

    /**
     * Send ticket asynchronously.
     */
    public function create_async() {
        API::create_async(array($this));
    }
}
