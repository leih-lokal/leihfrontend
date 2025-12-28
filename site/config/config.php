<?php

return [
    'debug' => true,
    'panel' => [
        'install' => true
    ],
    'api'=> [
        'slug' => 'rest'
    ],

    // Janitor job for emergency closing
    'callemergency' => function ($model, $data = null) {
        $baseUrl = 'https://buergerstiftung-karlsruhe.de';
        $email = 'kirby@llka.link';
        $password = 'meldetsichan';

        // Step 1: Authenticate with PocketBase superuser
        $authUrl = $baseUrl . '/api/collections/_superusers/auth-with-password';
        $authData = json_encode([
            'identity' => $email,
            'password' => $password
        ]);

        $ch = curl_init($authUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $authData);

        $authResponse = curl_exec($ch);
        $authHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $authError = curl_error($ch);
        curl_close($ch);

        if ($authError) {
            return [
                'status' => 500,
                'message' => 'Authentifizierungsfehler: ' . $authError
            ];
        }

        if ($authHttpCode !== 200) {
            return [
                'status' => $authHttpCode,
                'message' => 'Authentifizierung fehlgeschlagen (HTTP ' . $authHttpCode . ')'
            ];
        }

        $authResult = json_decode($authResponse, true);
        if (!isset($authResult['token'])) {
            return [
                'status' => 500,
                'message' => 'Kein Token in der Authentifizierungsantwort'
            ];
        }

        $token = $authResult['token'];

        // Step 2: Call emergency closing endpoint
        $emergencyUrl = $baseUrl . '/api/misc/emergency_closing';

        $ch = curl_init($emergencyUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'status' => 500,
                'message' => 'Fehler: ' . $error
            ];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'status' => 200,
                'message' => 'Notfallschließung wurde erfolgreich eingeleitet!'
            ];
        }

        return [
            'status' => $httpCode,
            'message' => 'Fehler beim Einleiten der Notfallschließung (HTTP ' . $httpCode . $error . ')'
        ];
    },
];