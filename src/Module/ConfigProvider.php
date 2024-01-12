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
                Worker\Time::class => Worker\TimeFactory::class,
            ]
        ];
    }

    public function getCliConfig(): array
    {
        return [
            'commands' => [
                'how-time' => Command\HowTime::class,
                'time-worker' => Worker\Time::class,
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
