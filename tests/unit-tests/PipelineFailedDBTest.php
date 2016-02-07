<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Mockery\Mock;
use TheCodeEngine\Pipeline\Pipeline;

class Command1PipelineFailedDBTest extends \TheCodeEngine\Pipeline\Command
{
    public function __construct($command_pipeline)
    {
        parent::__construct($command_pipeline);
    }

    public function run()
    {
        parent::run();
        echo "\nRun command 1";
        $faker = Faker\Factory::create();
        DB::table('users')->insert(
            ['email' => $faker->email, 'name' => $faker->name]
        );
        return true;
    }

    public function nextTaskSuccess()
    {
        return [Command2PipelineFailedDBTest::class, null];
    }
}

class Command2PipelineFailedDBTest extends \TheCodeEngine\Pipeline\Command
{
    public function __construct($command_pipeline)
    {
        parent::__construct($command_pipeline);
    }

    public function run()
    {
        parent::run();
        echo "\nRun command 2";
        $faker = Faker\Factory::create();
        DB::table('users')->insert(
            ['email' => $faker->email, 'name' => $faker->name]
        );
        return false;
    }

    public function nextTaskSuccess()
    {
        return [null, null];
    }
}

class PipelineFailedDBTest extends TestCase
{
    public function test_run_pipeline_failed_first_task()
    {
        echo "\nPipelineFailedDBTest::test_run_pipeline_failed\n";
        // Mock Job
        $mock = Mockery::mock(\TheCodeEngine\Pipeline\Job::class);

        // Test
        $pipeline = new Pipeline($mock, 'Command1PipelineFailedDBTest');
        $original_count = \App\User::all()->count();
        $this->assertNotNull($pipeline);
        $this->assertFalse($pipeline->is_failed);
        $this->assertFalse($pipeline->is_runned);
        $commands = $pipeline->run();
        $this->assertTrue($pipeline->is_failed);
        $this->assertTrue($pipeline->is_runned);
        $this->assertCount(2, $commands);
        $this->assertEquals($original_count, \App\User::all()->count());
    }

}
