<?php

/**
 * Testet das die Commands nacheinander ausgeführt werden
 */

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Mockery\Mock;
use TheCodeEngine\Pipeline\Pipeline;

class PipelineFailedTest extends TestCase
{
    public function test_run_pipeline_failed_first_task()
    {
        echo "\nPipelineFailedTest::test_run_pipeline_failed\n";
        // Mock Job
        $mock = Mockery::mock(\TheCodeEngine\Pipeline\Job::class);
        // Mock Commands
        $command1 = Mockery::mock(\TheCodeEngine\Pipeline\Command::class);
        $command1->shouldReceive('run')->once()->andReturn(false);
        $command1->shouldNotReceive('success');
        $command1->shouldReceive('failed')->once();
        $command1->shouldReceive('undo_run')->once();
        $command1->shouldReceive('nextTaskfailed')->once()->andReturn([null, []]);

        // Test
        $pipeline = new Pipeline($mock, $command1);
        $this->assertNotNull($pipeline);
        $this->assertFalse($pipeline->is_failed);
        $this->assertFalse($pipeline->is_runned);
        $commands = $pipeline->run();
        $this->assertTrue($pipeline->is_failed);
        $this->assertTrue($pipeline->is_runned);
        $this->assertCount(1, $commands);
    }

}
