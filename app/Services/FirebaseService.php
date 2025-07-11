<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;

class FirebaseService
{
    protected Database $database;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'))
            ->withDatabaseUri(config('services.firebase.database_url'));
        $this->database = $factory->createDatabase();
    }

    public function setConsolePower($consoleId, $value)
    {
        $this->database->getReference("console_status/{$consoleId}")->set($value);
    }

    public function getConsoleStatus(int $consoleId): ?int
    {
        return $this->database
            ->getReference("console_status/console_{$consoleId}")
            ->getValue();
    }
}
