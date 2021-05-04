<?php

namespace Nordsec\StatusChecker\Services;

use Exception;

class DatabaseChecker implements StatusCheckerInterface
{
    protected const DEFAULT_DRIVER = 'mysql';
    protected const DEFAULT_HOST = 'localhost';
    protected const DEFAULT_PORT = 3306;
    protected const DEFAULT_USER = 'root';
    protected const DEFAULT_PASSWORD = '';

    private $name;
    private $configuration;
    private $critical = true;

    public function __construct($name, $configuration)
    {
        $this->name = $name;
        $this->configuration = $configuration;
    }

    public function setCritical(bool $critical): StatusCheckerInterface
    {
        $this->critical = $critical;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isCritical(): bool
    {
        return $this->critical;
    }

    public function needsOutput(): bool
    {
        return true;
    }

    public function checkStatus(): string
    {
        try {
            $this->executeSelect('select 1');
        } catch (Exception $exception) {
            return StatusCheckerInterface::STATUS_FAIL;
        }

        return StatusCheckerInterface::STATUS_OK;
    }

    protected function createConnection(string $dsn, $user = null, $password = null, array $options = []): \PDO
    {
        return new \PDO($dsn, $user, $password, $options);
    }

    private function executeSelect(string $query): void
    {
        $config = $this->resolveConfig();

        $dsn = $this->resolveDsn($config);
        $user = $this->resolveUser($config);
        $pass = $this->resolvePassword($config);

        $pdo = $this->createConnection($dsn, $user, $pass, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

        $pdo->exec($query);
    }

    private function resolveDsn(array $config): string
    {
        if (
            is_string($this->configuration) &&
            strpos($this->configuration, ':') !== false &&
            strpos($this->configuration, ':') !== strpos($this->configuration, '://')
        ) {
            return $this->configuration;
        }

        return $config['dsn'] ?? $this->buildDsn($config);
    }

    private function resolveConfig(): array
    {
        if (
            is_string($this->configuration) &&
            strpos($this->configuration, '://') !== false &&
            strpos($this->configuration, '://') === strpos($this->configuration, ':')
        ) {
            return $this->resolveConfigFromUrl($this->configuration);
        }

        if (is_array($this->configuration)) {
            if (isset($this->configuration['url'])) {
                return $this->resolveConfigFromUrl($this->configuration['url']);
            }
            if (isset($this->configuration['driver']) || isset($this->configuration['adapter'])) {
                return $this->configuration;
            }

            $firstElement = reset($this->configuration);
            if (isset($firstElement['url'])) {
                return $this->resolveConfigFromUrl($firstElement['url']);
            }
            if (isset($firstElement['driver']) || isset($firstElement['adapter'])) {
                return $firstElement;
            }
        }

        $urlFromEnv = getenv('DATABASE_URL');
        if (!empty($urlFromEnv)) {
            return $this->resolveConfigFromUrl($urlFromEnv);
        }

        return [];
    }

    private function buildDsn(array $config): string
    {
        if (($config['driver'] ?? '') === 'sqlite') {
            return sprintf('%s:%s', $config['driver'], $config['database'] ?? $config['path']);
        }

        return sprintf(
            '%s:host=%s;port=%d;dbname=%s',
            $this->resolveDriver($config),
            $config['host'] ?? $config['read']['host'] ?? $config['write']['host'] ?? static::DEFAULT_HOST,
            $config['port'] ?? static::DEFAULT_PORT,
            $config['dbname'] ?? $config['database'] ?? null,
        );
    }

    private function resolveConfigFromUrl(string $url): array
    {
        $details = parse_url($url);
        $additionalPathChars = 1;

        if ($details === false) {
            // $url is in format sqlite:///path/to/file
            preg_match('|^(?<scheme>\w+)://(?<path>.*)$|', $url, $details);
            $additionalPathChars = 0;
        }

        $result = [
            'driver' => $details['scheme'],
            'user' => $details['user'] ?? '',
            'password' => $details['pass'] ?? '',
            'database' => substr($details['path'] ?? null, $additionalPathChars),
            'host' => $details['host'] ?? '',
        ];

        if (empty($result['database']) && !empty($result['host']) && $result['driver'] === 'sqlite') {
            // $url is in format sqlite://file_in_current_dir
            $result['database'] = $result['host'];
            $result['host'] = '';
        }

        return $result;
    }

    private function resolveUser(array $config): string
    {
        return $config['user'] ?? $config['username'] ?? $config['dbuser'] ?? static::DEFAULT_USER;
    }

    private function resolvePassword(array $config): string
    {
        return $config['password'] ?? $config['pass'] ?? $config['dbpass'] ?? static::DEFAULT_PASSWORD;
    }

    private function resolveDriver(array $config): string
    {
        $driver = $config['driver'] ?? $config['adapter'] ?? static::DEFAULT_DRIVER;

        if ($driver === 'mysqli') {
            $driver = 'mysql';
        }
        if (strpos($driver, 'pdo_') !== false) {
            $driver = str_replace('pdo_', '', $driver);
        }

        return $driver;
    }
}
