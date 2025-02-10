<?php

namespace wideweb\deployment\models;

use craft\base\Model;

class Settings extends Model
{
    public string $baseUrl = '';
    public string $projectPermalink = '';
    public string $user = '';
    public string $apiKey = '';
    public string $branch = '';
    public string $email = '';
    public string $cloneUrl = '';
    public string $webhookUrl = '';
    public string $integrationType = '';

    public function rules(): array
    {
        return [
            [['baseUrl'], 'string'],
            [['projectPermalink'], 'string'],
            [['user'], 'string'],
            [['apiKey'], 'string'],
            [['email'], 'string'],
            [['cloneUrl'], 'string'],
            [['webhookUrl'], 'string'],
            [['integrationType'], 'in', 'range' => ['api', 'webhook']],
        ];
    }
}
