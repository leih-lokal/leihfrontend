<?php

use Kirby\Http\Remote;

return [
    // Proxy for item collection requests
    [
        'pattern' => 'items/list',  // Simpler URL
        'method' => 'GET',
        'action' => function() {
            // Your permanent API key - kept secure on the server
            $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb2xsZWN0aW9uSWQiOiJwYmNfMzE0MjYzNTgyMyIsImV4cCI6MTkwMjMzMTg0NCwiaWQiOiJnZThoMjI1NXM3N21sODkiLCJyZWZyZXNoYWJsZSI6ZmFsc2UsInR5cGUiOiJhdXRoIn0.CEpQoWLWSBfZ5ySn76VQ19F51B8N1lltB4LfdjKm0p8';
            
            // Get any query parameters from the request
            $queryParams = kirby()->request()->query()->toArray();
            $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
            
            // Forward the request to PocketBase with your API key
            $response = Remote::get('https://stage.leihlokal-ka.de/api/collections/item/records' . $queryString, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey
                ]
            ]);
            
            // Return the response with appropriate status code
            return response::json(
                json_decode($response->content()),
                $response->code()
            );
        }
    ],
    
    // Proxy for single item requests
    [
        'pattern' => 'items/single/(:any)',  // Simpler URL
        'method' => 'GET',
        'action' => function($id) {
            $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb2xsZWN0aW9uSWQiOiJwYmNfMzE0MjYzNTgyMyIsImV4cCI6MTkwMjMzMTg0NCwiaWQiOiJnZThoMjI1NXM3N21sODkiLCJyZWZyZXNoYWJsZSI6ZmFsc2UsInR5cGUiOiJhdXRoIn0.CEpQoWLWSBfZ5ySn76VQ19F51B8N1lltB4LfdjKm0p8';
            
            $response = Remote::get('https://stage.leihlokal-ka.de/api/collections/item/records/' . $id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey
                ]
            ]);
            
            return response::json(
                json_decode($response->content()),
                $response->code()
            );
        }
    ],
    
    // Proxy for reservations
    [
        'pattern' => 'reservations/create',  // Simpler URL
        'method' => 'POST',
        'action' => function() {
            $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb2xsZWN0aW9uSWQiOiJwYmNfMzE0MjYzNTgyMyIsImV4cCI6MTkwMjMzMTg0NCwiaWQiOiJnZThoMjI1NXM3N21sODkiLCJyZWZyZXNoYWJsZSI6ZmFsc2UsInR5cGUiOiJhdXRoIn0.CEpQoWLWSBfZ5ySn76VQ19F51B8N1lltB4LfdjKm0p8';
            
            // Get JSON data from request body
            $data = json_decode(kirby()->request()->body(), true);
            
            $response = Remote::post('https://stage.leihlokal-ka.de/api/collections/reservation/records', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json'
                ],
                'data' => $data
            ]);
            
            return response::json(
                json_decode($response->content()),
                $response->code()
            );
        }
    ]
];