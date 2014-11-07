<?php
/**
 * Footprints API for is-dev applications.
 * Note: This relies on the internal API maintained by Operations.
 */

namespace Footprints;

class ChangeRequest extends Ticket
{
    /** Workspace. */
    protected $_workspace = 4;

    /**
     * Sets defaults.
     */
    protected function set_defaults() {
        $this->set_priority("Normal");
        $this->set_status("Closed");
        $this->set_type("Standard Change - Change Request");
        $this->set_emails();
    }

    /**
     * Returns a list of valid statuses.
     */
    public function get_statuses() {
        return array(
            "Open",
            "Allocated",
            "In Progress",
            "Holding",
            "Paused - Waiting further info",
            "Paused - Waiting on another task",
            "Paused - Waiting external",
            "Paused - Waiting scheduled time",
            "Paused - Other work taking priority",
            "Agreed not to do",
            "In Alpha test",
            "Beta test",
            "Handed over",
            "Closed"
        );
    }

    /**
     * Returns a list of valid ticket types.
     */
    public function get_types() {
        return array(
            "Service Change Request",
            "Standard Change - Change Request",
            "Standard Change - System Booking",
            "System Booking",
            "Timesheet"
        );
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

        $this->_fields_custom["Change Type"] = $type;
    }

    /**
     * Set ticket category.
     * 
     * @param string $category The category of the ticket.
     */
    public function set_category($category) {
        throw new \Exception("You cannot set the category for a Change Request ticket!");
    }
}
