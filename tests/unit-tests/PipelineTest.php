<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TheCodeEngine\Pipeline\Pipeline;

class PipelineTest extends TestCase
{
    public function test_run_one_command_success()
    {
        // Mock Job
        $job = Mockery::mock(\TheCodeEngine\Pipeline\Job::class);
        // Mock Command
        $command1 = Mockery::mock(\TheCodeEngine\Pipeline\Command::class);
        $command1->shouldReceive('exec')->once()->andReturn([true, null, null]);
        $command1->shouldReceive('isRunned')->twice()->andReturnValues([false, true]);


        $pipeline = new Pipeline($job, $command1);
        $this->assertEquals(false, $pipeline->is_runned);
        $this->assertEquals(false, $pipeline->is_failed);
        $pipeline->run();
        $this->assertEquals(1, count($pipeline->commands));
        $this->assertEquals(true, $pipeline->is_runned);
        $this->assertEquals(false, $pipeline->is_failed);
    }
}
