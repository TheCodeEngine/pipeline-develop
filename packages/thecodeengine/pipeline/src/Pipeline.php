<?php

namespace TheCodeEngine\Pipeline;
use Exception;
use Illuminate\Support\Collection;

/**
 * Class CommandPipeline
 * @package TheCodeEngine\Pipeline
 */
class Pipeline
{
    /**
     * @var Job
     */
    public $job;

    protected $run_loop_count = 0;

    /**
     * @var Collection
     */
    public $commands;

    /**
     * CommandPipeline constructor.
     * @param Job $job
     * @param string $command_class init Command Class
     * @param array $options
     */
    public function __construct($job, $command_class, $options=[])
    {
        $this->job = $job;
        $this->commands = new Collection();
        $this->loadCommand($command_class);
    }

    protected function loadCommand($command_class, $input_data=[])
    {
        var_dump(count($this->commands));
        $this->commands->push(Command::createFromClassName($command_class, $this, $input_data));
        var_dump(count($this->commands));
    }

    public function run()
    {
        $this->beforeRunLoop();
        $this->runLoop();
        $this->afterRunLoop();
        return $this->commands;
    }

    protected function beforeRunLoop()
    {

    }

    protected function afterRunLoop()
    {

    }

    protected function runLoop()
    {
        if ($this->run_loop_count > 1000) {
            return $this->run_loop_count;
        }

        /** @var Collection $commands */
        $commands = $this->getNotRunnedCommandCollection();

        if (count($commands) < 1) {
            return $this->run_loop_count;
        }

        foreach($this->getNotRunnedCommandCollection() as $command) {
                $this->runCommand($command);
        }

        $this->run_loop_count += 1;

        return $this->runLoop();
    }

    /**
     * Return a Collection of not running commands
     * @return static
     */
    protected function getNotRunnedCommandCollection()
    {
        return $this->commands->filter(function ($item){
            if ($item->is_runned === false) {
                return $item;
            }
        });
    }

    /**
     * Run the Command
     * @param Command $command
     */
    protected function runCommand($command)
    {
        $new_class = null;
        $input_data = [];
        try {
            $rv = $command->run();
            if ($rv===false) {
                $command->failed();
                list($new_class, $input_data) = $command->nextTaskfailed();
            } else {
                $command->success();
                list($new_class, $input_data) = $command->nextTaskSuccess();
            }
        } catch (Exception $e) {
            $command->failed();
            list($new_class, $input_data) = $command->nextTaskfailed();
        }

        if (is_string($new_class)) {
            $this->loadCommand($new_class, $input_data);
        }
    }
}