## Command Scheduler

framework agnostic command scheduler that can be easily integrated with any project. 

#### Install

    `composer require mvaliolahi/scheduler`
    
##### Usage

    1. create an instance of Scheduler then add some command and schedule them.
    
        $scheduler = new Scheduler([
            'cwd' => 'project path | where commands can be run',
            'command_prefix' => 'php specific-cli'
        ]);
        
        $scheduler->command('rm test.php -fr')
        ->hourly()
        >when(function()
            {
                return true; // in this situation.
            }
        );
        
        
        $scheduler->start();
        
    2. use a cron job to run the $scheduler->start() in a specific period of time.: `* * * * * php /project/schedule:run >> /dev/null 2>&1`
    
Tips: Scheduler can be configured using another params named `timezone`, this parameter apply an specific timezone to add commands but you can overwrite them using `->timezone()` method.   

###### Frequencies: 
           ->cron('* * * * *');	Run the command on a custom Cron schedule
           ->everyMinute();	Run the command every minute
           ->everyFiveMinutes();	Run the command every five minutes
           ->everyTenMinutes();	Run the command every ten minutes
           ->everyFifteenMinutes();	Run the command every fifteen minutes
           ->everyThirtyMinutes();	Run the command every thirty minutes
           ->hourly();	Run the command every hour
           ->hourlyAt(17);	Run the command every hour at 17 mins past the hour
           ->daily();	Run the command every day at midnight
           ->dailyAt('13:00');	Run the command every day at 13:00
           ->twiceDaily(1, 13);	Run the command daily at 1:00 & 13:00
           ->weekly();	Run the command every week
           ->monthly();	Run the command every month
           ->monthlyOn(4, '15:00');	Run the command every month on the 4th at 15:00
           ->quarterly();	Run the command every quarter
           ->yearly();	Run the command every year
           ->timezone('America/New_York');	Set the timezone
           ->weekdays();	Limit the command to weekdays
           ->sundays();	Limit the command to Sunday
           ->mondays();	Limit the command to Monday
           ->tuesdays();	Limit the command to Tuesday
           ->wednesdays();	Limit the command to Wednesday
           ->thursdays();	Limit the command to Thursday
           ->fridays();	Limit the command to Friday
           ->saturdays();	Limit the command to Saturday
           ->between($start, $end);	Limit the command to run between start and end times
           ->when(Closure);	Limit the command based on a truth test
                   
###### Todo
           
           - handle overlapping.
           - add specific class with specific method for trigger instead usual command to scheduler.