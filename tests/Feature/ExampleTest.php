<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Oops - gavelisten.app er lige gået i stykker.');
});
