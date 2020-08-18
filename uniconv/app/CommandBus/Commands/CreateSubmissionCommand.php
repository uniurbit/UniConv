<?php

namespace App\CommandBus\Commands;

/**
 * Class CreateSubmissionCommand
 * @package App\CommandBus\Commands
 */
class CreateSubmissionCommand
{
    public $data;
    /**
     * CreateSubmissionCommand constructor.
     */
    public function __construct($data)
    {
        $this->$data = $data;
    }
}
