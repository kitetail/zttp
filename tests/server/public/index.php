<?php

require_once __DIR__.'/../../../vendor/autoload.php';

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

function build_response($request)
{
    return response()->json([
        'headers' => $request->header(),
        'query' => $request->query(),
        'json' => $request->json()->all(),
        'form_params' => $request->request->all(),
    ], $request->header('Z-Status', 200));
}

$app->get('/get', function () {
    return build_response(app('request'));
});

$app->post('/post', function () {
    return build_response(app('request'));
});

$app->put('/put', function () {
    return build_response(app('request'));
});

$app->patch('/patch', function () {
    return build_response(app('request'));
});

$app->delete('/delete', function () {
    return build_response(app('request'));
});

$app->get('/redirect', function () {
    return redirect('redirected');
});

$app->get('/redirected', function () {
    return "Redirected!";
});

$app->get('/simple-response', function () {
    return "A simple string response";
});

$app->get('/xml-response', function () {
    return "<?xml version='1.0' encoding='UTF-8'?><zttp><author>Adam Wathan</author></zttp>";
});

$app->run();
