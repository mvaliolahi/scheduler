<?php
/**
 * Created by PhpStorm.
 * User: m.valiolahi
 * Date: 12/9/2017
 * Time: 10:27 AM
 */

namespace Tests\Unit;


use Carbon\Carbon;
use Mvaliolahi\Scheduler\Command;
use Mvaliolahi\Scheduler\Scheduler;
use Tests\TestCase;

/**
 * Class SchedulerTest
 * @package Tests\Unit
 */
class SchedulerTest extends TestCase
{
    /**
     * @var Scheduler
     */
    protected $scheduler;

    /** @test */
    public function it_should_have_an_array_of_commands()
    {
        $this->scheduler->command('schedule:list')->hourly()->description('Show all schedule list.');
        $this->scheduler->command('schedule:fail')->monthly();

        $this->assertEquals(2, count($this->scheduler->commands()));
    }

    /** @test */
    public function it_should_return_an_instance_of_command_class()
    {
        $this->assertInstanceOf(
            Command::class,
            $this->scheduler->command('test command')->cron('* * * * *')
        );
    }

    /** @test */
    public function it_should_return_all_due_commands()
    {
        $this->scheduler->command('cp ~/project/test/ ~/tmp/')
            ->date(Carbon::create(2017, 12, 12, 00, 06, 00))
            ->everyFiveMinutes(); // is not ready!

        $this->scheduler->command('cp ~/project/test/ ~/tmp/')
            ->date(Carbon::create(2017, 12, 12, 00, 10, 00))
            ->everyTenMinutes()->timezone('America/Chicago'); // is ready :)

        $this->scheduler->command('rm test.php -fr')
            ->user('meysam-pc')
            ->date(Carbon::create(2017, 12, 12, 00, 00, 00))
            ->hourly();

        $this->assertEquals(2, count($this->scheduler->dueCommands()));
    }


    protected function setUp()
    {
        parent::setUp();

        $this->scheduler = new Scheduler();
    }
}