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
            ]
        ];
    }

    public function getCliConfig(): array
    {
        return [
            'commands' => [
                'how-time' => Command\HowTime::class,
                'time-worker' => Worker\Time::class,
                'unisender-api-key-worker' => Worker\UniSenderApiKey::class,
                'token-worker' => Worker\Token::class,
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
