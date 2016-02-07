<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use TheCodeEngine\Pipeline\Command;

class MyCommandTestCommandSuccess extends Command{}
class MyCommandTestCommandFailedReturnFalse extends Command
{
    public function run()
    {
        parent::run();

        return false;
    }

    public function nextTaskfailed()
    {
        parent::nextTaskfailed();
        return ['Test', ['data' => "Test"]];
    }
}
class MyCommandTestCommandFailedThrowException extends Command
{
    public function run()
    {
        parent::run();

        throw new Exception('My Exception');
    }
}

class CommandTest extends TestCase
{
    public function test_init()
    {
        $mock = Mockery::mock(\TheCodeEngine\Pipeline\Job::class);
        $command = new MyCommandTestCommandSuccess($mock);
    }

    public function test_exec_success()
    {
        // Mock
        $mock = Mockery::mock(\TheCodeEngine\Pipeline\Job::class);

        // Test
        $command = new MyCommandTestCommandSuccess($mock);
        list($rv, $new_class, $input_data) = $command->exec();
        $this->assertTrue($rv);
        $this->assertNull($new_class);
        $this->assertEquals([], $input_data);
        // Check Flags
        $this->assertEquals(true, $command->isRunned());
        $this->assertEquals(true, $command->is_success);
        $this->assertEquals(false, $command->is_failed);
        $this->assertEquals(false, $command->is_undo_run);
        $this->assertNull($command->getExecException());
    }

    public function test_exec_fail_run_return_false()
    {
        // Mock
        $mock = Mockery::mock(\TheCodeEngine\Pipeline\Job::class);

        // Test
        $command = new MyCommandTestCommandFailedReturnFalse($mock);
        list($rv, $new_class, $input_data) = $command->exec();
        $this->assertFalse($rv);
        $this->assertEquals('Test', $new_class);
        $this->assertEquals(['data' => "Test"], $input_data);
        // Check Flags
        $this->assertEquals(true, $command->isRunned());
        $this->assertEquals(false, $command->is_success);
        $this->assertEquals(true, $command->is_failed);
        $this->assertEquals(true, $command->is_undo_run);
        $this->assertNull($command->getExecException());
    }

    public function test_exec_fail_by_exception()
    {
        // Mock
        $mock = Mockery::mock(\TheCodeEngine\Pipeline\Job::class);

        // Test
        $command = new MyCommandTestCommandFailedThrowException($mock);
        list($rv, $new_class, $input_data) = $command->exec();
        $this->assertFalse($rv);
        $this->assertNull($new_class);
        $this->assertEquals([], $input_data);
        // Check Flags
        $this->assertEquals(true, $command->isRunned());
        $this->assertEquals(false, $command->is_success);
        $this->assertEquals(true, $command->is_failed);
        $this->assertEquals(true, $command->isUndoRun());
        $this->assertTrue($command->getExecException() instanceof Exception);
    }
}
