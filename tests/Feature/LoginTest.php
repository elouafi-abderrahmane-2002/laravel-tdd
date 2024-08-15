<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LoginTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
    //Session::start();
		$response = $this->call('POST', '/login', [
			'email' => 'tantanium@live.co.uk',
			'password' => 'niaga123',
			'_token' => csrf_token()
		]);
		$response->assertRedirect('/home');	
    }    
}

