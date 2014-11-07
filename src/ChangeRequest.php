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
}