<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('guest redirected from meeting room is redirected back to meeting after registration', function () {
    $response = $this->get('/meeting/abc-def-ghi');

    $response->assertRedirect(route('login'));
    $this->assertEquals(url('/meeting/abc-def-ghi'), session('intended_meeting_url'));

    $registerPageResponse = $this->get('/register');
    $registerPageResponse->assertStatus(200);

    $registerResponse = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test-reg@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $registerResponse->assertRedirect(url('/meeting/abc-def-ghi'));
});
