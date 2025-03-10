<?php declare(strict_types=1);

namespace Frosh\Tools\Components\Health\Checker\HealthChecker;

use Doctrine\DBAL\Connection;
use Frosh\Tools\Components\Health\Checker\CheckerInterface;
use Frosh\Tools\Components\Health\HealthCollection;
use Frosh\Tools\Components\Health\SettingsResult;

class QueueChecker implements CheckerInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function collect(HealthCollection $collection): void
    {
        $result = SettingsResult::ok('queue', 'Queues working good');

        $oldestMessage = (int) $this->connection->fetchOne('SELECT IFNULL(MIN(created_at), 0) FROM messenger_messages');
        $oldestMessage /= 10000;
        $minutes = 15;

        // When the oldest message is older then $minutes minutes
        if ($oldestMessage && ($oldestMessage + ($minutes * 60)) < time()) {
            $result = SettingsResult::warning('queue', 'Open Queues older than 15 minutes found');
        }

        $result->url = 'https://developer.shopware.com/docs/guides/hosting/infrastructure/message-queue';
        $collection->add($result);
    }
}
