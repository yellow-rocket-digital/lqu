<?php

namespace WeDevs\PM_Pro\Core\Exceptions;

use Exception;

class Invalid_Route_Handler extends Exception
{
	public function __construct( $message )
    {
        $message = $message . ' is not a valid route handler';

        parent::__construct( $message );
    }
}