<?php

namespace Module;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'laminas-cli' => $this->getCliConfig(),
            'templates' => $this->getTemplates(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Command\HowTime::class => Command\HowTime::class,
            ],
            'factories' => [
                Worker\Time::class => Worker\Factory\TimeFactory::class,
                Worker\UniSenderApiKey::class => Worker\Factory\UniSenderApiKeyFactory::class,
                Worker\Token::class => Worker\Factory\TokenFactory::class,
                Worker\Enums::class => Worker\Factory\EnumsFactory::class,
                Worker\Webhooks::class => Worker\Factory\WebhooksFactory::class,
                Worker\ContactsSync::class => Worker\Factory\ContactsSyncFactory::class,
            ]
        ];
    }

    public function getCliConfig(): array
    {
        return [
            'commands' => [
                'how-time' => Command\HowTime::class,
                'worker:time' => Worker\Time::class,

                'worker:unisender-api-key' => Worker\UniSenderApiKey::class,
                'worker:token' => Worker\Token::class,
                'worker:enums' => Worker\Enums::class,
                'worker:webhooks' => Worker\Webhooks::class,
                'worker:contacts-sync' => Worker\ContactsSync::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app' => ['templates/app'],
                'error' => ['templates/error'],
                'layout' => ['templates/layout'],
            ],
        ];
    }
}
