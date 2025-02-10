<?php

namespace wideweb\deployment\controllers;

use Craft;
use craft\helpers\App;
use craft\web\Controller;
use yii\web\Response;
use wideweb\deployment\Deployment;

class DeploymentController extends Controller
{
    protected int|bool|array $allowAnonymous = false;

    private function sendCurlRequest(string $url, array $options = []): array
    {
        $plugin = Deployment::getInstance();

        $settings = $plugin->getSettings();

        $apiUser = App::parseEnv($settings['user']) ??  $settings['user'];
        $apiKey = App::parseEnv($settings['apiKey']) ??  $settings['apiKey'];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$apiUser}:{$apiKey}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        // Additional settings for cURL
        foreach ($options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['response' => $response, 'httpCode' => $httpCode];
    }

    public function actionRun(): Response
    {
        $plugin = Deployment::getInstance();

        $settings = $plugin->getSettings();

        $apiBaseUrl = App::parseEnv($settings['baseUrl']) ??  $settings['baseUrl'];
        $projectPermalink = App::parseEnv($settings['projectPermalink']) ??  $settings['projectPermalink'];

        $apiUrl = "{$apiBaseUrl}/projects/{$projectPermalink}/deployments";

        $identifier = null;
        $lastRevision = null;
        $error = null;
        $result = null;

        try {
            // Get data about the last deployment
            $responseData = $this->sendCurlRequest($apiUrl);

            if ($responseData['httpCode'] !== 200) {
                $error = 'Error retrieving deployment data: ' . $responseData['response'];
                $this->logError($error);
                return $this->asJson(['success' => false, 'error' => $error]);
            }

            $deployments = json_decode($responseData['response'], true);

            if (isset($deployments['records']) && is_array($deployments['records'])) {
                $firstRecord = $deployments['records'][0] ?? null;

                if ($firstRecord && isset($firstRecord['servers'][0]['last_revision'])) {
                    $lastRevision = $firstRecord['servers'][0]['last_revision'];
                    $identifier = $firstRecord['servers'][0]['identifier'];
                } else {
                    $error = 'Could not find last_revision in data.';
                    $this->logError($error);
                }
            } else {
                $error = 'Could not find records.';
                $this->logError($error);
            }

            if ($error === null && $lastRevision) {
                // Deployment parameters
                $deploymentData = [
                    'deployment' => [
                        'parent_identifier' => $identifier,
                        'start_revision' => '',
                        'end_revision' => $lastRevision,
                        'branch' => 'master',
                        'mode' => 'queue',
                        'copy_config_files' => true,
                        'run_build_commands' => true,
                        'use_build_cache' => true,
                    ],
                ];

                // Start deployment
                $deployResponseData = $this->sendCurlRequest("{$apiBaseUrl}/projects/{$projectPermalink}/deployments", [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($deploymentData),
                ]);

                if ($deployResponseData['httpCode'] !== 201) {
                    $error = 'Error starting deployment: ' . $deployResponseData['response'];
                    $this->logError($error);
                } else {
                    $result = json_decode($deployResponseData['response'], true);
                }
            }

        } catch (\Exception $e) {
            $error = 'Exception: ' . $e->getMessage();
            $this->logError($error);
        }

        return $this->asJson([
            'last_revision' => $lastRevision,
            'deployment_result' => $result,
            'success' => isset($result),
            'error' => $error ?? null,
        ]);
    }

    public function actionGetLastDeployment(): Response
    {

        $plugin = Deployment::getInstance();

        $settings = $plugin->getSettings();

        $apiBaseUrl = App::parseEnv($settings['baseUrl']) ??  $settings['baseUrl'];
        $projectPermalink = App::parseEnv($settings['projectPermalink']) ??  $settings['projectPermalink'];

        $apiUrl = "{$apiBaseUrl}/projects/{$projectPermalink}/deployments";

        try {
            $responseData = $this->sendCurlRequest($apiUrl);

            if ($responseData['httpCode'] !== 200) {
                $error = 'Error retrieving deployment data: ' . $responseData['response'];
                $this->logError($error);
                return $this->asJson(['success' => false, 'error' => $error]);
            }

            $deployments = json_decode($responseData['response'], true);

            if (isset($deployments['records']) && is_array($deployments['records'])) {
                return $this->asJson([
                    'success' => true,
                    'lastDeployment' => $deployments['records'][0],
                ]);
            }

            $error = 'No data on recent deployments.';
            $this->logError($error);
            return $this->asJson(['success' => false, 'error' => $error]);

        } catch (\Exception $e) {
            $error = 'Exception: ' . $e->getMessage();
            $this->logError($error);
            return $this->asJson(['success' => false, 'error' => $error]);
        }
    }

    public function actionStatus(): Response
    {

        $plugin = Deployment::getInstance();

        $settings = $plugin->getSettings();

        $apiBaseUrl = App::parseEnv($settings['baseUrl']) ??  $settings['baseUrl'];
        $projectPermalink = App::parseEnv($settings['projectPermalink']) ??  $settings['projectPermalink'];

        $deploymentId = Craft::$app->getRequest()->getQueryParam('deploymentId');

        if (!$deploymentId) {
            $error = 'Deployment ID is required.';
            $this->logError($error);
            return $this->asJson(['success' => false, 'error' => $error]);
        }

        $apiUrl = "{$apiBaseUrl}/projects/{$projectPermalink}/deployments/{$deploymentId}";

        try {
            $responseData = $this->sendCurlRequest($apiUrl);

            if ($responseData['httpCode'] !== 200) {
                $error = "Error getting deployment status: " . $responseData['response'];
                $this->logError($error);
                return $this->asJson(['success' => false, 'error' => $error]);
            }

            $deploymentStatus = json_decode($responseData['response'], true);

            if ($deploymentStatus === null) {
                $error = 'Invalid JSON response from API.';
                $this->logError($error);
                return $this->asJson(['success' => false, 'error' => $error]);
            }

            $status = $deploymentStatus['status'] ?? 'unknown';
            $steps = $deploymentStatus['steps'] ?? [];

            $stages = [];
            foreach ($steps as $step) {
                $stages[] = [
                    'step' => $step['step'] ?? 'unknown',
                    'stage' => $step['stage'] ?? 'unknown',
                    'description' => $step['description'] ?? 'No description available',
                    'status' => $step['status'] ?? 'unknown',
                ];
            }

            return $this->asJson([
                'success' => true,
                'status' => $status,
                'stages' => $stages,
                'data' => $deploymentStatus,
            ]);

        } catch (\Exception $e) {
            $error = 'Exception: ' . $e->getMessage();
            $this->logError($error);
            return $this->asJson(['success' => false, 'error' => $error]);
        }
    }

    public function actionRunViaHook(): Response
    {
        $plugin = Deployment::getInstance();

        $settings = $plugin->getSettings();

        $deployUrl = App::parseEnv($settings['webhookUrl']) ??  $settings['webhookUrl'];
        $userEmail = App::parseEnv($settings['user']) ??  $settings['user'];
        $cloneUrl = App::parseEnv($settings['cloneUrl']) ??  $settings['cloneUrl'];

        $payload = [
            'payload' => [
                'new_ref' => 'latest',
                'branch' => 'master',
                'email' => $userEmail,
                'clone_url' => $cloneUrl
            ]
        ];

        $jsonData = json_encode($payload);
        $statusSuccess = false;

        try {
            $ch = curl_init($deployUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (!curl_errno($ch) && $httpCode === 200) {
                $statusSuccess = true;
            }

            curl_close($ch);
        } catch (\Exception $e) {
            Craft::error('Deployment error: ' . $e->getMessage(), __METHOD__);
        }

        return $this->redirect(Craft::$app->getRequest()->getReferrer() .
            "?success=" . ($statusSuccess ? '1' : '0'));
    }
}
