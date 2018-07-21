<?php
/**
 * Created by PhpStorm.
 * User: m.valiolahi
 * Date: 12/10/2017
 * Time: 11:16 AM
 */

namespace Tests\Feature;


use Carbon\Carbon;
use Mvaliolahi\Scheduler\Scheduler;
use Tests\TestCase;

/**
 * Class SchedulerTest
 * @package Tests\Feature
 */
class SchedulerTest extends TestCase
{
    /**
     * @var Scheduler
     */
    protected $scheduler;

    /** @test */
    public function it_should_call_before_callbacks_right_before_command_execute()
    {
        $before = null;
        $after = null;

        $result = $this->scheduler->command('cd')->everyMinute()->output('')
            ->before(function () use (&$before) {
                $before = 'done!';
            })->after(function () use (&$after) {
                $after = 'done!';
            })
            ->run();

        // $this->assertEquals(__DIR__, $this->getProcessResult($result));
        $this->assertEquals('done!', $before);
    }

    /** @test */
    public function it_should_prevent_to_run_command_if_there_are_any_filter()
    {
        $date = Carbon::create(2017, 12, 12, 23, 00, 00);

        $isDue = $this->scheduler->command('cp ~/project/test/ ~/tmp/')
            ->date($date)
            ->everyMinute()->when(function () {
                return false;
            })
            ->filtersPass();

        $this->assertFalse($isDue);
    }

    /** @test */
    public function it_should_return_result_of_commands()
    {
        $this->scheduler->exec('cd')
            ->output('')
            ->everyMinute()->when(function () {
                return false;
            });

        $command = 'echo this is a test!';
        $this->scheduler->exec($command)
            ->everyMinute();

        $this->scheduler->start();

        $this->assertEquals(
            'this is a test',
            $this->getProcessResult($this->scheduler->result()[$command])
        );
    }

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->scheduler = new Scheduler([
            'cwd' => __DIR__
        ]);
    }
}