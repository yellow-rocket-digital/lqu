<?php

namespace WeDevs\PM_Pro\Core\Permissions;

use WeDevs\PM_Pro\Core\Permissions\Permission;
use WP_REST_Request;

abstract class Abstract_Permission implements Permission {
    /**
     * This variable holds an instance of WP_REST_Request.
     *
     * @var Object
     */
    public $request;

    /**
     * Instantiate the $request property.
     *
     * @param WP_REST_Request $request (Current request to the site as
     * WP_REST_Request)
     */
    public function __construct( WP_REST_Request $request ) {
        $this->request = $request;
    }

    /**
     * Check for a specific permission.
     *
     * @return boolean (true if operation is permitted; otherwise false).
     */
    abstract public function check();
}