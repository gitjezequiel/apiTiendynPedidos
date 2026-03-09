<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;

class FirestoreService
{
    private string $projectId;
    private string $credentialsPath;

    public function __construct()
    {
        $this->projectId = config('firebase.project_id', env('FIREBASE_PROJECT_ID'));
        $this->credentialsPath = base_path(env('FIREBASE_CREDENTIALS'));
    }

    public function addDocument(string $collection, array $data): void
    {
        $token = $this->getAccessToken();

        $fields = $this->toFirestoreFields($data);

        $client = new Client();
        $url = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents/{$collection}";

        $client->post($url, [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type'  => 'application/json',
            ],
            'json' => ['fields' => $fields],
        ]);
    }

    private function getAccessToken(): string
    {
        $scopes = ['https://www.googleapis.com/auth/datastore'];
        $credentials = new ServiceAccountCredentials($scopes, $this->credentialsPath);
        $token = $credentials->fetchAuthToken();
        return $token['access_token'];
    }

    private function toFirestoreFields(array $data): array
    {
        $fields = [];
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $fields[$key] = ['booleanValue' => $value];
            } elseif (is_int($value) || is_float($value)) {
                $fields[$key] = ['integerValue' => $value];
            } elseif (is_array($value)) {
                $fields[$key] = ['mapValue' => ['fields' => $this->toFirestoreFields($value)]];
            } else {
                $fields[$key] = ['stringValue' => (string) $value];
            }
        }
        return $fields;
    }
}
