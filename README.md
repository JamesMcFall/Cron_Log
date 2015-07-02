## Cron_Log - High level task logging for CodeIgniter
A lot of my projects interact heavily with API's. This means there are lots of cron jobs running, sending data, pulling down data etc. 

APIs can be flakey sometimes, especially the smaller ones. They throw strange and unexpected errors and they can have bad up-time. This introduces a lot of potential for issues. The last thing you want is to have a client calling saying "My pricing changes from yesterday haven't come through to your application! Did the script not run?" and not being able to answer quickly.

This class allows you to easily see: 

* When a cron job last ran.
* How long it took to run.
* Whether it finished or not.
* How far through the task it got. How many rows were processed.

* * *

### Basic Usage
There are three main methods. To start logging, create an instance of the object and start logging for this script.

#### Starting Logging
```php   
$cronLog = new Cron_Log();
$cronLog->startCronLog("/crons/superAwesomeCron");
```

#### Updating The Log
As you process each task, update the log like so:

```php   
$cronLog->updateCronLog("Processed " . $processedCount . "/" . count($thingsToDo) . 
    " rows. (succ:" . $successCount . "|fail:" . $failedCount . ")");
```

#### Marking The Task As Completed

```php
$cronLog->finishCronLog();
```

#### Example
Here's a pseudocode example inside a controller and action in CodeIgniter.

```php   
class Crons extends CI_Controller {
    public function superAwesomeCron() {
    
        # Mark the cron task as started.
        $cronLog = new Cron_Log();
        $cronLog->startCronLog("/crons/superAwesomeCron");
        
        $processedCount = $successCount = $failedCount = 0;
        
        # Do all the tasks
        foreach ($thingsToDo as $thingToDo) {
            
            $isThingDone = $this->things_model->doTheThing($thingToDo);
            
            $processedCount++; 
            
            if ($isThingDone) {
                $successCount++;
            } else {
                $failedCount++;
            }
            
            # Update the cron log
            $cronLog->updateCronLog("Processed " . $processedCount . "/" . count($thingsToDo) . 
                " rows. (succ:" . $successCount . "|fail:" . $failedCount . ")");
        }
        
        # All done - mark as completed
        $cronLog->finishCronLog();
    }

}
```

* * *

### Detecting When A Cron Was Last Run
Sometimes you need to know when a cron was last run, so I've added a static method to the class to be able to do this easily. Note the "script" column in the database is how a task is identified.

```php
# This returns a DateTime object, or false if the script has never been ran (according to this log).
$lastRun = Cron_Log::getLastRan("/upload-the-things")
```

The behaviour here could change depending on your needs. In the repo here it's set to return the start time. I typically use exit time so I know specific date ranges I need to process. It's easy to change if you need it.

