<?php

return [
  'gcm' => [
      'priority' => 'normal',
      'dry_run' => false,
      'apiKey' => 'My_ApiKey',
  ],
  'fcm' => [
        'priority' => 'high',
        'dry_run' => false,
        'apiKey' => 'My_ApiKey',
  ],
  'apn' => [
      'certificate' => __DIR__ . '/path/cert.pem',
      'passPhrase' => '', //Optional
//      'passFile' => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
      'dry_run' => false,
  ]
];
