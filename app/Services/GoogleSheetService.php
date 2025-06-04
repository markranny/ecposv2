<?php
namespace App\Services;
use Google\Client;
use Google\Service\Sheets;

class GoogleSheetService
{
    protected $client;
    protected $sheetsService;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path('app/public/API/finished-goods-9b2565bb6e35.json')); // Adjust path as needed
        $this->client->addScope(Sheets::SPREADSHEETS_READONLY);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        $this->sheetsService = new Sheets($this->client);
    }

    public function getSelectedData($spreadsheetId, $range)
{
    try {
        $this->refreshTokenIfNeeded();
        $response = $this->sheetsService->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();
        \Log::info('Successfully fetched data from Google Sheets', ['rowCount' => count($values)]);
        return $values;
    } catch (\Exception $e) {
        \Log::error('Google Sheets API error: ' . $e->getMessage(), [
            'spreadsheetId' => $spreadsheetId,
            'range' => $range,
            'trace' => $e->getTraceAsString()
        ]);
        return [];
    }
}

    private function refreshTokenIfNeeded()
    {
        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithAssertion();
        }
    }

    
}