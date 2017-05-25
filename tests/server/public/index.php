<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

function request($key = null, $default = null)
{
    if (is_null($key)) {
        return app('request');
    }

    if (is_array($key)) {
        return app('request')->only($key);
    }

    return data_get(app('request')->all(), $key, $default);
}

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
    return build_response(request());
});

$app->post('/post', function () {
    return build_response(request());
});

$app->put('/put', function () {
    return build_response(request());
});

$app->patch('/patch', function () {
    return build_response(request());
});

$app->delete('/delete', function () {
    return build_response(request());
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

$app->run();
