<?php

namespace TheCodeEngine\Pipeline;

/**
 * Command from the Command Pipeline
 *
 * Running a logical command and get the $data from the task
 *
 * @package TheCodeEngine\Pipeline
 */
use Exception;

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

    /**
     * @var bool is command running
     */
    public $is_runned = false;
    /**
     * @var bool is command successful running
     */
    public $is_success = false;
    /**
     * @var bool is command running with error
     */
    public $is_failed = false;
    /**
     * @var bool is command has exec the undo action
     */
    public $is_undo_run = false;

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
        $this->is_undo_run = true;
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

    /**
     * Exec Command
     * @return array [returnvalue, $new_class, $input_data]
     */
    public function exec()
    {
        $new_class = null;
        $input_data = [];
        $rv = false;
        try {
            $rv = $this->run();
            if ($rv === false) {
                $this->failed();
                $this->undo_run();
                list($new_class, $input_data) = $this->nextTaskfailed();
            } else {
                $this->success();
                list($new_class, $input_data) = $this->nextTaskSuccess();
            }
        } catch (Exception $e) {
            $this->failed();
            $this->undo_run();
            list($new_class, $input_data) = $this->nextTaskfailed();
        }

        return [$rv, $new_class, $input_data];
    }
}