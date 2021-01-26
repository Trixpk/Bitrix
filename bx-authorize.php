<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

// Настройки клиента - можно вынести в какой-нибудь отдельный файл
$config = [
  'base_uri' => 'https://xn--80aaxglxcn5f.xn--p1ai/',
  'verify' => false,
  'cookies' => true,
  'headers' => [
    'User-Agent' => 'Mozilla/5.0 (Linux 3.4; rv:64.0) Gecko/20100101 Firefox/15.0',
    'Accept-Language' => 'ru,en-US' // Если не задать - будут выдаватся страницы на английском языке
  ]
];

// Создаём экземпляр клиента - можно вынести в IoC-контейнер
$client = new Client($config);

// И.. Авторизуемся
$login = $client->post('bitrix/admin/?login=yes', [
    'form_params' => [
      'AUTH_FORM' => 'Y',
      'TYPE' => 'AUTH',
      'backurl' => '/',
      'USER_LOGIN' => 'admin',
      'USER_PASSWORD' => '100100',
      'USER_REMEMBER' => 'Y'
    ]
  ]
);

$cookie = $login->getHeaderLine('Set-Cookie');

$result = $client->request('GET', 'bitrix/admin/update_system.php?lang=ru', [
  'headers' => [
    'Cookie' => $cookie
  ],
  //'debug' => true
]);

$html = $result->getBody()->getContents();

echo $html;
