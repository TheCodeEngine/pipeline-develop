<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use TheCodeEngine\Pipeline\Command;

class MyCommandTestCommand extends Command{}

class CommandTest extends TestCase
{
    public function test_init()
    {
        $mock = Mockery::mock(\TheCodeEngine\Pipeline\Job::class);

        $command = new MyCommandTestCommand($mock);
    }
}
