<?php namespace App\Models;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class UccLanguageModel {
    protected Client $client;
    protected $environment;
    protected $logs_path;

    public function __construct()
    {
        $this->environment = new Environment();
        $apiKey = $this->environment->get('AI_LLM_API_KEY');
        $this->client = new Client([
            'base_uri' => $this->environment->get('AI_LLM_BASEURL'),
            'headers' => [
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ]
        ]);
        $this->logs_path = "{$_SERVER['DOCUMENT_ROOT']}/storage/logs";
    }

    private function logger($data)
    {
        file_put_contents(
            "$this->logs_path/LanguageModel.txt",
            $data,
            FILE_APPEND
        );
    }

    public function completions(array $messages)
    {
        $payload = [
            'model'       => $this->environment->get('AI_LLM_MODEL'),
            'messages'    => $messages,
            'temperature' => 0.1,
            'top_p'       => 1,
            'stream'      => false,
            'max_tokens'  => 1024,
        ];

        try {
            $response = $this->client->post($this->environment->get('AI_LLM_ENDPOINT'),['json' => $payload]);
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['choices'][0]['message']['content'] ?? null;
        }
        catch (RequestException $e) {
            json([
                'result' => false,
                'message' => $e->getMessage(),
            ]);
            exit();
        }
    }
}
