<?php

namespace TheCodeEngine\Pipeline;

use Illuminate\Support\Facades\Request;

/**
 * Class Job
 * @package TheCodeEngine\Pipeline
 */
class Job
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var string
     */
    public $pipeline_class;

    public static function create($request, $pipeline_class)
    {
        return new Job($request, $pipeline_class);
    }

    /**
     * Job constructor.
     * @param Request $request
     * @param string $pipeline_class
     */
    public function __construct($request, $pipeline_class)
    {
        $this->request = $request;
        $this->pipeline_class = $pipeline_class;
    }

    public function run()
    {

    }

    public function fail()
    {

    }

    public function success()
    {

    }
}