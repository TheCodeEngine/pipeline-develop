<?php

namespace TheCodeEngine\Pipeline;
use Exception;
use Illuminate\Support\Collection;
use DB;

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
        if ($this->run_loop_count > 1000) {
            return $this->run_loop_count;
        }

        $this->run_loop_count += 1;

        /** @var Collection $commands */
        $commands = $this->getNotRunnedCommandCollection();

        if (count($commands) < 1) {
            return $this->run_loop_count;
        }

        foreach($this->getNotRunnedCommandCollection() as $command) {
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
            if ($item->is_runned === false) {
                return $item;
            }
        });
    }

    protected function failed()
    {
        $this->is_failed = true;
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
                $this->failed();
                $command->failed();
                $command->undo_run();
                list($new_class, $input_data) = $command->nextTaskfailed();
            } else {
                $command->success();
                list($new_class, $input_data) = $command->nextTaskSuccess();
            }
        } catch (Exception $e) {
            $this->failed();
            $command->failed();
            $command->undo_run();
            list($new_class, $input_data) = $command->nextTaskfailed();
        }

        if (is_string($new_class)) {
            $this->loadCommand($new_class, $input_data);
        }
    }
}