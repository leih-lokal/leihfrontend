<?php

use Kirby\Http\Remote;
use Kirby\Http\Response;

return function ($kirby) {
    // Get the action from the URL
    $action = get('action'); 
    
    // Your permanent API key
    $apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb2xsZWN0aW9uSWQiOiJwYmNfMzE0MjYzNTgyMyIsImV4cCI6MTkwMjMzMTg0NCwiaWQiOiJnZThoMjI1NXM3N21sODkiLCJyZWZyZXNoYWJsZSI6ZmFsc2UsInR5cGUiOiJhdXRoIn0.CEpQoWLWSBfZ5ySn76VQ19F51B8N1lltB4LfdjKm0p8';
    
    // Handle different actions
    switch ($action) {
        case 'list-items':
            // Get query params
            $page = get('page', 1);
            $perPage = get('perPage', 20);
            $filter = get('filter', '');
            $sort = get('sort', 'iid');
            
            // Build query string
            $queryParams = [
                'page' => $page,
                'perPage' => $perPage,
                'filter' => $filter,
                'sort' => $sort
            ];
            
            if (get('fields')) {
                $queryParams['fields'] = get('fields');
            }
            
            $queryString = '?' . http_build_query($queryParams);
            
            // Make request to PocketBase
            $response = Remote::get('https://stage.leihlokal-ka.de/api/collections/item/records' . $queryString, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey
                ]
            ]);
            
            // Return the data as JSON
            return Response::json($response->content(), $response->code());
            
        case 'get-item':
            $id = get('id');
            if (!$id) {
                return Response::json(['error' => 'Missing item ID'], 400);
            }
            
            $response = Remote::get('https://stage.leihlokal-ka.de/api/collections/item/records/' . $id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey
                ]
            ]);
            
            return Response::json($response->content(), $response->code());
            
        case 'create-reservation':
            // Get JSON body
            $body = file_get_contents('php://input');
            if (!$body) {
                return Response::json(['error' => 'Missing request body'], 400);
            }
            
            $response = Remote::post('https://stage.leihlokal-ka.de/api/collections/reservation/records', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json'
                ],
                'body' => $body
            ]);
            
            return Response::json($response->content(), $response->code());
            
        default:
            return Response::json(['error' => 'Invalid API action'], 400);
    }
};