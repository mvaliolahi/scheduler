<?php
/**
 * Created by PhpStorm.
 * User: m.valiolahi
 * Date: 12/5/2017
 * Time: 6:50 PM
 */

namespace Mvaliolahi\Scheduler;


/**
 * Class Scheduler
 * @package Mvaliolahi\Scheduler
 */
/**
 * Class Scheduler
 * @package Mvaliolahi\Scheduler
 */
class Scheduler
{
    /**
     * @var array
     */
    protected $commands = [];

    /**
     * @var mixed|null
     */
    protected $currentWorkDirectory;

    /**
     * @var
     */
    protected $commandPrefix;

    /**
     * @var
     */
    protected $runOutput;

    /**
     * Scheduler constructor.
     * @param $params
     */
    public function __construct($params = [])
    {
        $this->currentWorkDirectory = $params['cwd'] ?? null;
        $this->commandPrefix = $params['command_prefix'] ?? '';
    }

    /**
     * @param $command
     * @return Command
     */
    public function command($command)
    {
        return $this->exec(trim("{$this->commandPrefix} {$command}"));
    }

    public function exec($command)
    {
        $this->commands[] = $command = new Command($command, $this->currentWorkDirectory);

        return $command;
    }

    /**
     * Run scheduler to execute all due commands.
     *
     * @return mixed
     */
    public function start()
    {
        foreach ($this->dueCommands() as $command) {

            if (!$command->filtersPass()) {
                continue;
            }

            $this->runOutput[$command->command()] = $command->run();
        }

        return 'There is not command to execute.';
    }

    /**
     * All commands ready to launch.
     *
     * @return array
     */
    public function dueCommands()
    {
        return array_filter($this->commands, function ($command) {
            return $command->isDue();
        });
    }

    /**
     * @return array
     */
    public function commands()
    {
        return $this->commands;
    }

    /**
     * Result of execute all due commands.
     *
     * @return mixed
     */
    public function result()
    {
        return $this->runOutput;
    }
}