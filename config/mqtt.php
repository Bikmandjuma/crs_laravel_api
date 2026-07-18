<?php

return [

    'host' => env('MQTT_HOST', '192.168.1.222'),

    'port' => env('MQTT_PORT', 1884),

    'username' => env('MQTT_USERNAME', null),

    'password' => env('MQTT_PASSWORD', null),

    'topic' => 'agriculture/devices/+/readings',

];