<?php

namespace App\CommandBus\Handlers;

use App\CommandBus\Commands\CreateSubmissionCommand;
use App\Repositories\SubmissionRepository;


/**
 * Class CreateSubmissionHandler
 * @package App\CommandBus\Handlers
 */
class CreateSubmissionHandler
{
    /**
     * CreateSubmissionHandler constructor.
     */
    public function __construct(SubmissionRepository $submissionRepository)
    {
        $this->submissionRepository = $submissionRepository;        
    }

    /**
     * @param CreateSubmissionCommand $command
     */
    public function handle(CreateSubmissionCommand $command)
    {
        $this->submissionRepository->store($command->data);
    }
}
