<?php
/**
 * Created by PhpStorm.
 * User: m.valiolahi
 * Date: 12/10/2017
 * Time: 10:48 AM
 */

namespace Mvaliolahi\Scheduler;


/**
 * Class CommandBuilder
 * @package Mvaliolahi\Scheduler
 */
class CommandBuilder
{
    /**
     * @var Command
     */
    protected $command;

    /**
     * CommandBuilder constructor.
     * @param Command $command
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * @return mixed
     */
    public function build()
    {
        $command = "{$this->command->command} {$this->command->output}";

        return $this->command->user ? "sudo -u {$this->command->user} {$command}" : trim($command);
    }
}