<?php

namespace Dillingham\Formation\Exceptions;

use Exception;

class ReservedException extends Exception
{
    /**
     * The http status code.
     * @var int
     */
    protected $code = 500;
}
