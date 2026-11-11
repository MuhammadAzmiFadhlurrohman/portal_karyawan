<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Guest visiting root is redirected to the login screen, and login screen returns 200 OK.
     */
    public function test_the_application_redirects_guest_to_login_and_renders_login_page(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertStatus(200);
    }
}
