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
    public function it_should_be_able_to_accept_several_command()
    {
        $this->scheduler->command('schedule:list')->hourly()->description('Show all schedule list.');
        $this->scheduler->command('schedule:fail')->monthly();

        $this->assertEquals(2, count($this->scheduler->commands()));
    }

    /** @test */
    public function commands_should_be_instance_of_Command_Class()
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
            ->date(Carbon::create(2017, 12, 12, 00, 06, 00)) // to fake current Date/Time
            ->everyFiveMinutes(); // is not ready!

        $this->scheduler->command('cp ~/project/test/ ~/tmp/')
            ->date(Carbon::create(2017, 12, 12, 00, 10, 00))
            ->everyTenMinutes()->timezone('America/Chicago'); // is ready :)

        $this->scheduler->command('rm test.php -fr')
            ->user('meysam-pc')
            ->date(Carbon::create(2017, 12, 12, 00, 00, 00)) // is ready :)
            ->hourly();

        $this->assertEquals(2, count($this->scheduler->dueCommands()));
    }

    /** @test */
    public function it_should_apply_command_prefix_to_commands()
    {
        $scheduler = new Scheduler([
            'command_prefix' => 'php your-cli',
        ]);

        $scheduler->command('test-cmd')->everyMinute();

        $this->assertEquals('php your-cli test-cmd', $scheduler->commands()[0]->command);
    }

    /** @test */
    public function by_default_commands_dose_not_use_any_prefix()
    {
        $scheduler = new Scheduler();

        $scheduler->command('php test-cmd')->everyMinute();

        $this->assertEquals('php test-cmd', $scheduler->commands()[0]->command);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->scheduler = new Scheduler();
    }
}