<?php

/**
 * Testet das die Commands nacheinander ausgeführt werden
 */

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Mockery\Mock;
use TheCodeEngine\Pipeline\Pipeline;

class MyExamplePipelineTestCommand1 extends \TheCodeEngine\Pipeline\Command
{
    public function __construct($command_pipeline)
    {
        parent::__construct($command_pipeline);
    }

    public function run()
    {
        parent::run();
        echo "\nRun command 1";
        return true;
    }

    public function nextTaskSuccess()
    {
        return [MyExamplePipelineTestCommand2::class, null];
    }
}

class MyExamplePipelineTestCommand2 extends \TheCodeEngine\Pipeline\Command
{
    public function __construct($command_pipeline)
    {
        parent::__construct($command_pipeline);
    }

    public function run()
    {
        parent::run();
        echo "\nRun command 2";
        return true;
    }

    public function nextTaskSuccess()
    {
        return [MyExamplePipelineTestCommand3::class, null];
    }
}

class MyExamplePipelineTestCommand3 extends \TheCodeEngine\Pipeline\Command
{
    public function __construct($command_pipeline)
    {
        parent::__construct($command_pipeline);
    }

    public function run()
    {
        parent::run();
        echo "\nRun command 3";
        return true;
    }
}

class PipelineSuccessTest extends TestCase
{
    public function test_run_pipeline_success()
    {
        echo "\nPipelineSuccessTest::test_run_pipeline_success\n";
        $mock = Mockery::mock(\TheCodeEngine\Pipeline\Job::class);

        $pipeline = new Pipeline($mock, 'MyExamplePipelineTestCommand1');
        $this->assertNotNull($pipeline);
        $this->assertFalse($pipeline->is_failed);
        $this->assertFalse($pipeline->is_runned);
        $commands = $pipeline->run();
        $this->assertFalse($pipeline->is_failed);
        $this->assertTrue($pipeline->is_runned);
        $this->assertCount(3, $commands);
    }
}
