<?php
/**
 * @file
 * --------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * --------------------------------------------------------------------------------------
 */

namespace bigfathom;

/**
 * A custom Bigfathom Core Exception
 *
 * @author Frank
 */
class BFCException extends \Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($raw_message=NULL, $code = 0, \Exception $previous = NULL) 
    {
        $message = !empty($raw_message) ? $raw_message : 'detected trouble';
    
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction() 
    {
        echo "A custom function for this type of exception\n";
    }
}

