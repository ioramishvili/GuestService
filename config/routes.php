<?php

return [
    'GET,HEAD api/guest' => 'api/guest/index',
    'GET,HEAD api/guest/<id:\d+>' => 'api/guest/view',
    'POST api/guest' => 'api/guest/create',
    'PUT,PATCH api/guest/<id:\d+>' => 'api/guest/update',
    'DELETE api/guest/<id:\d+>' => 'api/guest/delete',
];
