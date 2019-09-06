<?php

return [
    'broker_version' => env("KAFKA_BROKER_VERSION", "0.10.0.0"),

    'client_id' => 'walter-bridge',

    'brokers' => [
        "pkc-e0x5p.us-east1.gcp.confluent.cloud:9092"
    ],

    'sasl' => [
        'username' => env('KAFKA_USERNAME', ''),
        'password' => env('KAFKA_PASSWORD', ''),
    ],

    'ca_file' => env('SSL_CA_FILE', '/etc/ssl/cert.pem'),

    'topics' => ['user']
];
