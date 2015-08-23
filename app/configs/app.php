<?php

$app['i18n']['timezone'] = 'Europe/Moscow';
$app['i18n']['timeformat'] = 'r';

$app['baseURI'] = '';
$app['theme'] = 'paramatma';
$app['theme_suffix'] = 'phtml';

$app['database']['db']['adapter'] = 'mysql';
$app['database']['db']['host'] = '127.0.0.1';
$app['database']['db']['port'] = 3306;
$app['database']['db']['username'] = 'root';
$app['database']['db']['password'] = '';
$app['database']['db']['dbname'] = 'paramatma.dev';
$app['database']['db']['tablePrefix'] = 'pa_';

$app['session']['adapter'] = 'mysql';
$app['session']['dbId'] = 'db'; // Database ID
$app['session']['table'] = 'session';

return $app;