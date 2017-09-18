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

$app->get('/timeout', function () {
    sleep(2);
});

$app->get('/basic-auth', function () {
    $headers = [
        (bool) preg_match('/Basic\s[a-zA-Z0-9]+/', app('request')->header('Authorization')),
        app('request')->header('php-auth-user') === 'zttp',
        app('request')->header('php-auth-pw') === 'secret'
    ];

    return (count(array_unique($headers)) === 1) ? response(null, 200) : response(null, 401);
});

$app->post('/multi-part', function () {
    return response()->json([
        'body_content' => app('request')->only(['foo', 'baz']),
        'has_file' => app('request')->hasFile('test-file'),
        'file_content' => file_get_contents($_FILES['test-file']['tmp_name']),
        'headers' => app('request')->header(),
    ], 200);
});

$app->run();
