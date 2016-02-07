<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class JobTest extends TestCase
{
    public function test_init()
    {
        $job = new \TheCodeEngine\Pipeline\Job(null, 'Pipeline');

        $this->assertNotNull($job);
    }
}
