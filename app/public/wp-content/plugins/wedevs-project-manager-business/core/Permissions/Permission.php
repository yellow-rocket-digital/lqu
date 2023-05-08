<?php

namespace WeDevs\PM_Pro\Core\Permissions;

interface Permission {
    /**
     * Check for a specific permission.
     *
     * @return boolean (true if operation is permitted; otherwise false).
     */
    public function check();
}
