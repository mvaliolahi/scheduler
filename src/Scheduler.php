<?php
/**
 * Created by PhpStorm.
 * User: m.valiolahi
 * Date: 12/5/2017
 * Time: 6:50 PM
 */

namespace Mvaliolahi\Scheduler;


use Mvaliolahi\Scheduler\Contracts\OverlappingCache;


/**
 * Class Scheduler
 * @package Mvaliolahi\Scheduler
 */
class Scheduler
{
    /**
     * @var OverlappingCache
     */
    protected $cache;
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
     * @var
     */
    protected $timezone;

    /**
     * Scheduler constructor.
     * @param $params
     */
    public function __construct($params = [])
    {
        $this->currentWorkDirectory = $params['cwd'] ?? null;
        $this->commandPrefix = $params['command_prefix'] ?? '';
        $this->timezone = $params['timezone'] ?? null;
        $this->cache = $params['cache'] ?? null;
    }

    /**
     * @param $command
     * @return Command
     */
    public function command($command)
    {
        return $this->exec(trim("{$this->commandPrefix} {$command}"));
    }

    /**
     * @param $command
     * @return Command
     */
    public function exec($command)
    {
        $this->commands[] = $command = new Command(
            $this->cache,
            $command,
            $this->currentWorkDirectory,
            $this->timezone
        );

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

        return 'There is no command to execute.';
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
     * Execute results of all the due commands.
     *
     * @return mixed
     */
    public function result()
    {
        return $this->runOutput;
    }

    /**
     * @return mixed|null
     */
    public function timezone()
    {
        return $this->timezone;
    }
}