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
 *
 * validate() --> run()
 *
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
     * @param string|object $class_name Object only for test purpose
     * @param Pipeline $pipeline
     * @param $input_data
     * @return mixed
     */
    public static function createFromClassName($class_name, $pipeline, $input_data)
    {
        if (is_object($class_name)) {
            return $class_name;
        }
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
    public function is_valid()
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
        $this->is_valid();
        return true;
    }

    /**
     * Run when the Command is failing
     *
     * Undo only things thats not done in the database the Pipline has make a transaction !
     *
     * @return bool
     */
    public function undo_run()
    {
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