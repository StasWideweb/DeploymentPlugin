<?php

namespace wideweb\deployment\services;

use Craft;
use craft\helpers\App;
use yii\base\Component;
use GuzzleHttp\Client;

class DeployService extends Component
{
    private $apiBaseUrl = 'https://api.deployhq.com';
    private $apiKey;

    public function init()
    {
        parent::init();
        $this->apiKey = App::env('DEPLOY_HQ_API_KEY');
    }

    public function getDeploymentStatus($deploymentId)
    {
        $client = new Client();
        try {
            $response = $client->request('GET', "{$this->apiBaseUrl}/deployments/{$deploymentId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ],
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Craft::error('Error fetching deployment status: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    public function getCurrentStep($deploymentId)
    {
        $client = new Client();
        try {
            $response = $client->request('GET', "{$this->apiBaseUrl}/deployments/{$deploymentId}/steps", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ],
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Craft::error('Error fetching deployment step: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }
}
