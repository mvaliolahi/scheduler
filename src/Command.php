<?php
/**
 * Created by PhpStorm.
 * User: m.valiolahi
 * Date: 12/5/2017
 * Time: 6:50 PM
 */

namespace Mvaliolahi\Scheduler;


/**
 * Class Command
 * @package Mvaliolahi\Scheduler
 */
use Carbon\Carbon;
use Closure;
use Cron\CronExpression;
use Mvaliolahi\Scheduler\Contracts\OverlappingCache;
use Mvaliolahi\Scheduler\Traits\ManagesFrequencies;
use Symfony\Component\Process\Process;


/**
 * Class Command
 * @package Mvaliolahi\Scheduler
 */
class Command
{
    use ManagesFrequencies;

    /**
     * The command name.
     *
     * @var
     */
    public $command;

    /**
     * The specified user for execute command.
     *
     * @var
     */
    public $user;

    /**
     * The command description.
     *
     * @var
     */
    public $description;

    /**
     * Cron expression for event frequency.
     *
     * @var string
     */
    public $expression = '* * * * *';

    /**
     * The timezone to evaluate date.
     *
     * @var
     */
    public $timezone;

    /**
     * Default timezone for all commands.
     *
     * @var
     */
    public $globalTimezone;

    /**
     * The location to send output.
     *
     * @var string
     */
    public $output = '';

    /**
     * The current date to check due commands in test-cases.
     *
     * @var Carbon
     */
    public $date;

    /**
     * Prevent overlapping.
     *
     * @var bool
     */
    public $withoutOverlapping = false;

    /**
     * @var OverlappingCache
     */
    protected $cache;

    /**
     * The array of callbacks to be run before the event is started.
     *
     * @var array
     */
    protected $beforeCallbacks = [];

    /**
     * The array of callbacks to be run after the event is finished.
     *
     * @var array
     */
    protected $afterCallbacks = [];

    /**
     * The array of filter callbacks.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The array of reject callbacks.
     *
     * @var array
     */
    protected $rejects = [];

    /**
     * @var
     */
    protected $currentWorkDirectory;

    /**
     * Command constructor.
     *
     * @param $cache
     * @param $command
     * @param $currentWorkDirectory
     * @param $timezone
     */
    public function __construct($cache, $command, $currentWorkDirectory, $timezone)
    {
        $this->cache = $cache;
        $this->command = $command;
        $this->currentWorkDirectory = $currentWorkDirectory ?? null;
        $this->globalTimezone = $timezone;
    }

    /**
     * @return mixed
     */
    public function command()
    {
        return $this->command;
    }

    /**
     * @param mixed $date
     * @return $this
     */
    public function date($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @param string $output
     * @return $this
     */
    public function output(string $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param mixed $description
     * @return $this
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDue()
    {
        $date = $this->date ?? Carbon::now();

        if ($this->timezone) {
            $date->setTimezone($this->timezone);
        } else if ($this->globalTimezone) {
            $date->setTimezone($this->globalTimezone);
        }

        return CronExpression::factory($this->expression)->isDue($date->toDateTimeString());
    }

    /**
     * @return mixed
     */
    public function run()
    {
        if ($this->withoutOverlapping) {
            if ($this->cache->has($this->mutexName())) {
                return false;
            }

            $this->cache->put($this->mutexName(), true, 1440);
        }

        $this->runBeforeCallbacks();

        $process = new Process($this->buildCommand(), $this->currentWorkDirectory);
        $process->run();

        if ($process->isSuccessful()) {
            $this->runAfterCallbacks();
            return $process->getOutput();
        }

        return false;
    }

    /**
     * @return string
     */
    public function mutexName()
    {
        return 'schedule-' . sha1($this->expression . $this->command);
    }

    /**
     * Run all predefined callback before execute command.
     */
    private function runBeforeCallbacks()
    {
        foreach ($this->beforeCallbacks as $callback) {
            $callback();
        }
    }

    /**
     * Generate final form of given command to execute using symphony process class.
     *
     * @return mixed
     */
    public function buildCommand()
    {
        return (new CommandBuilder($this))->build();
    }

    /**
     * Run all predefined callback after execute command.
     */
    private function runAfterCallbacks()
    {
        foreach ($this->afterCallbacks as $callback) {
            $callback();
        }
    }

    /**
     * @param Closure $callback
     * @return $this
     */
    public function when(Closure $callback)
    {
        $this->filters[] = $callback;

        return $this;
    }

    /**
     * @param $user
     * @return $this
     */
    public function user($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Add new closure to execute right before commands fired.
     *
     * @param Closure $closure
     * @return $this
     */
    public function before(Closure $closure)
    {
        $this->beforeCallbacks[] = $closure;

        return $this;
    }

    /**
     * Using this method we decide to run command or not.
     *
     * @return bool
     */
    public function filtersPass()
    {
        foreach ($this->filters as $callback) {
            if (!$callback()) {
                return false;
            }
        }

        foreach ($this->rejects as $callback) {
            if ($callback()) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     */
    public function withoutOverlapping()
    {
        $this->withoutOverlapping = true;

        return $this->after(function () {
            $this->cache->forget($this->mutexName());
        });
    }

    /**
     * @param Closure $callback
     * @return $this
     */
    public function skip(Closure $callback)
    {
        $this->rejects[] = $callback;

        return $this;
    }

    /**
     * Add new closure to execute right after commands fired.
     *
     * @param Closure $closure
     * @return $this
     */
    public function after(Closure $closure)
    {
        $this->afterCallbacks[] = $closure;

        return $this;
    }
}