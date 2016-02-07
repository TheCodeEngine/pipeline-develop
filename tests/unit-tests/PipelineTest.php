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

    public function test_run_two_commands_and_success()
    {
        // Mock Job
        $job = Mockery::mock(\TheCodeEngine\Pipeline\Job::class);
        // Mock Command2
        $command2 = Mockery::mock(\TheCodeEngine\Pipeline\Command::class);
        $command2->shouldReceive('exec')->once()->andReturn([true, null, null]);
        $command2->shouldReceive('isRunned')->twice()->andReturnValues([false, true]);
        // Mock Command1
        $command1 = Mockery::mock(\TheCodeEngine\Pipeline\Command::class);
        $command1->shouldReceive('exec')->once()->andReturn([true, $command2, null]);
        $command1->shouldReceive('isRunned')->between(1, 3)->andReturnValues([false, true]);


        $pipeline = new Pipeline($job, $command1);
        $this->assertEquals(false, $pipeline->is_runned);
        $this->assertEquals(false, $pipeline->is_failed);
        $pipeline->run();
        $this->assertEquals(2, count($pipeline->commands));
        $this->assertEquals(true, $pipeline->is_runned);
        $this->assertEquals(false, $pipeline->is_failed);
    }

    public function test_run_one_command_failover()
    {
        // Mock Job
        $job = Mockery::mock(\TheCodeEngine\Pipeline\Job::class);
        // Mock Command
        $command1 = Mockery::mock(\TheCodeEngine\Pipeline\Command::class);
        $command1->shouldReceive('exec')->once()->andReturn([false, null, null]);
        $command1->shouldReceive('undo_run')->once()->andReturn(true);
        $command1->shouldReceive('isRunned')->once()->andReturn(false);


        $pipeline = new Pipeline($job, $command1);
        $this->assertEquals(false, $pipeline->is_runned);
        $this->assertEquals(false, $pipeline->is_failed);
        $pipeline->run();
        $this->assertEquals(1, count($pipeline->commands));
        $this->assertEquals(true, $pipeline->is_runned);
        $this->assertEquals(true, $pipeline->is_failed);
    }
}
