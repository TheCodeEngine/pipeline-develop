<?php

namespace TheCodeEngine\Pipeline;
use Exception;
use Illuminate\Support\Collection;
use DB;
use TheCodeEngine\Pipeline\Exceptions\PipelineCycleException;

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

    /**
     * @var int Count of running Commands
     */
    protected $run_loop_count = 0;

    /**
     * @var bool is Pipeline runned
     */
    public $is_runned = false;
    /**
     * @var bool is command in pipeline failed
     */
    public $is_failed = false;

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
        $this->commands->push(Command::createFromClassName($command_class, $this, $input_data));
    }

    public function run()
    {
        $this->is_runned = true;
        $this->beforeRunLoop();
        $this->runLoop();
        $this->afterRunLoop();
        return $this->commands;
    }

    protected function beforeRunLoop()
    {
        DB::beginTransaction();
    }

    protected function afterRunLoop()
    {
        if ($this->is_failed) {
            DB::rollBack();
        } else {
            DB::commit();
        }
    }

    protected function runLoop()
    {
        if ($this->run_loop_count > 200) {
            $this->failed();
            throw new PipelineCycleException('Pipeline::runLoop() Cycle ? Can not run more then 200 Commands in a Pipeline');
        }

        $this->run_loop_count += 1;

        /** @var Collection $commands */
        $commands = $this->getNotRunnedCommandCollection();

        if (count($commands) < 1) {
            return $this->run_loop_count;
        }

        foreach($commands as $command) {
            $this->runCommand($command);
        }

        if ($this->is_failed === true) {
            return $this->run_loop_count;
        }

        return $this->runLoop();
    }

    /**
     * Return a Collection of not running commands
     * @return static
     */
    protected function getNotRunnedCommandCollection()
    {
        return $this->commands->filter(function ($item){
            $value = $item->isRunned();
            if ($value === false) {
                return $item;
            }
        });
    }

    /**
     * Return a Collection of all Commands that have not running the undo action
     * @return static
     */
    protected function getNotUndoRunningCommandCollection()
    {
        return $this->commands->filter(function ($item){
            $value = $item->isUndoRun();
            if ($value === false) {
                return $item;
            }
        });
    }

    protected function failed()
    {
        $this->is_failed = true;
        // Undo all Commands in the pipeline
        /** @var Command $command */
        foreach($this->getNotUndoRunningCommandCollection() as $command) {
            $command->undo_run();
        }
    }

    protected function success()
    {

    }

    /**
     * Run the Command
     * @param Command $command
     */
    protected function runCommand($command)
    {

        list($rv, $new_class, $input_data) = $command->exec();

        $rv === true ? $this->success() : $this->failed();

        if (!is_null($new_class)) {
            $this->loadCommand($new_class, $input_data);
        }
    }
}