<?php

namespace Dillingham\Formation\Exceptions;

use Exception;

class UnregisteredFormation extends Exception
{
    protected $code = 500;

    protected $message = 'Unknown parent formation';
}
