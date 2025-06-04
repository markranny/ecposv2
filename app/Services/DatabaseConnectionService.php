<?php 

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\ConnectionInterface;
use App\Models\StoreConnection;
use Exception;

class DatabaseConnectionService
{
    /**
     * Default connection configuration
     */
    private array $defaultConnection = [
        'driver' => 'mysql',
        'host' => null,
        'database' => null,
        'username' => null,
        'password' => null,
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ];

    /**
     * Switch database connection
     */
    public function switchDatabase(string $storeId): ConnectionInterface
    {
        try {
            $connection = $this->getConnectionConfig($storeId);
            
            config(['database.connections.dynamic' => $connection]);
            DB::purge('dynamic');
            DB::reconnect('dynamic');
            
            return DB::connection('dynamic');
        } catch (Exception $e) {
            throw new Exception("Failed to switch database for store {$storeId}: " . $e->getMessage());
        }
    }

    /**
     * Get connection configuration
     */
    public function getConnectionConfig(string $storeId): array
    {
        // Check if it's HQ2 (main database)
        if ($storeId === 'HQ2') {
            return $this->getMainConnection();
        }

        try {
            // For all other connections, try to get from store_connections table
            $storeConnection = StoreConnection::where('store_id', $storeId)
                ->where('is_active', true)
                ->first();

            if (!$storeConnection) {
                throw new Exception("No connection configuration found for store ID: {$storeId}");
            }

            return $this->buildConnectionFromModel($storeConnection);
        } catch (Exception $e) {
            throw new Exception("Failed to get connection config for {$storeId}: " . $e->getMessage());
        }
    }

    /**
     * Get main (HQ2) connection
     */
    protected function getMainConnection(): array
    {
        return array_merge($this->defaultConnection, [
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE', 'ecpostarlac'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]);
    }

    /**
     * Build connection config from StoreConnection model
     */
    protected function buildConnectionFromModel(StoreConnection $connection): array
    {
        return array_merge($this->defaultConnection, [
            'host' => $connection->host ?: env('DB_HOST'),
            'database' => $connection->database_name,
            'username' => $connection->username ?: env('DB_USERNAME'),
            'password' => $connection->password ?: env('DB_PASSWORD'),
        ]);
    }

    /**
     * Add or update a custom database connection
     */
    public function setCustomConnection(array $connectionDetails): void
    {
        if ($connectionDetails['store_id'] === 'HQ2') {
            throw new Exception("Cannot modify HQ2 connection. It is managed through environment variables.");
        }

        StoreConnection::updateOrCreate(
            ['store_id' => $connectionDetails['store_id']],
            [
                'database_name' => $connectionDetails['database_name'],
                'host' => $connectionDetails['host'] ?? env('DB_HOST'),
                'username' => $connectionDetails['username'] ?? env('DB_USERNAME'),
                'password' => $connectionDetails['password'] ?? env('DB_PASSWORD'),
                'is_active' => $connectionDetails['is_active'] ?? true,
            ]
        );
    }

    /**
     * Remove a custom database connection
     */
    public function removeCustomConnection(string $storeId): void
    {
        if ($storeId === 'HQ2') {
            throw new Exception("Cannot remove HQ2 connection. It is the main database.");
        }

        StoreConnection::where('store_id', $storeId)->delete();
    }

    /**
     * Get all custom database connections
     */
    public function getCustomConnections(): array
    {
        return StoreConnection::where('is_active', true)->get()->toArray();
    }
}