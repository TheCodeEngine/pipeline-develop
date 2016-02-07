<?php

namespace TheCodeEngine\Pipeline;

/**
 * Command from the Command Pipeline
 *
 * Running a logical command and get the $data from the task
 *
 * @package TheCodeEngine\Pipeline
 */
/**
 * Class Command
 * @package TheCodeEngine\Pipeline
 */
abstract class Command
{
    /**
     * @var Pipeline
     */
    protected $command_pipeline;

    /**
     * Data thats returned from the pipeline
     * @var mixed
     */
    protected $data;

    public $is_runned = false;
    public $is_success = false;
    public $is_failed = false;

    /**
     * Create A Command from Class string
     * @param string $class_name
     * @param Pipeline $pipeline
     * @param $input_data
     * @return
     */
    public static function createFromClassName($class_name, $pipeline, $input_data)
    {
        return new $class_name($pipeline, $input_data);
    }

    /**
     * Command constructor.
     * @param $command_pipeline
     */
    public function __construct($command_pipeline, $input_data=[])
    {
        $this->command_pipeline = $command_pipeline;
    }

    /**
     * validate if the Command can run with the given Data
     * @return array [Boolean can_run, array of errormessages]
     */
    public function validate()
    {
        return [true, []];
    }

    /**
     * Run the Commands
     * @return bool
     */
    public function run()
    {
        $this->is_runned = true;
        return true;
    }

    /**
     * Run when failed
     */
    public function failed()
    {
        $this->is_failed = true;
    }

    /**
     * Run when success
     */
    public function success()
    {
        $this->is_success = true;
    }

    /**
     * Return the next Command class with and his $input_data
     * @return array
     */
    public function nextTaskSuccess()
    {
        return [null, []];
    }

    /**
     * * Return the next Command class with and his $input_data
     * @return array
     */
    public function nextTaskfailed()
    {
        return [null, []];
    }
}