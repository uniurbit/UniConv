<?php

namespace App\CommandBus\Commands;

/**
 * Class UpdateSubmissionCommand
 * @package App\CommandBus\Commands
 */
class UpdateSubmissionCommand
{
    public $data;
    /**
     * UpdateSubmissionCommand constructor.
     */
    public function __construct($data)
    {
        $this->$data = $data;
    }
}
