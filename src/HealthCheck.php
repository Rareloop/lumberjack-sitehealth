<?php

namespace Rareloop\Lumberjack\SiteHealth;

use Stringy\Stringy;

abstract class HealthCheck
{
    const DIRECT = 'direct';
    const ASYNC = 'async';

    /**
     * A unique identifier for this check
     *
     * @return string
     */
    abstract public function identifier(): string;

    /**
     * The label that defines the checks name during registration
     *
     * @return string
     */
    abstract public function label(): string;

    /**
     * Perform the test
     *
     * Should return data in the format defined here:
     * https://make.wordpress.org/core/2019/04/25/site-health-check-in-5-2/
     *
     * @return array
     */
    abstract public function execute(): array;

    /**
     * Determines if this check is loaded async or direct
     *
     * @return void
     */
    public function type()
    {
        return static::ASYNC;
    }

    /**
     * Registers this check with the Site Health system
     *
     * @return void
     */
    public function register()
    {
        if ($this->type() === static::ASYNC) {
            add_filter('site_status_tests', function ($tests) {
                $tests[static::ASYNC][$this->identifier()] = array(
                    'label' => $this->label(),
                    'test' => $this->testId(),
                    'has_rest' => true,
                    'async_direct_test' => [$this, 'execute'],
                );

                return $tests;
            });

            add_action('rest_api_init', function () {
                register_rest_route(
                    $this->restNameSpace(),
                    $this->restRoute(),
                    [
                        [
                            'methods'             => 'GET',
                            'callback'            => [$this, 'execute'],
                            'permission_callback' => function () {
                                // Perform any capability checks or similar here.
                                return current_user_can('view_site_health_checks');
                            },
                        ],
                    ]
                );
            });
        }

        if ($this->type() === static::DIRECT) {
            add_filter('site_status_tests', function ($tests) {
                $tests[static::DIRECT][$this->identifier()] = array(
                    'label' => $this->label(),
                    'test' => [$this, 'execute'],
                );

                return $tests;
            });
        }
    }

    protected function testId()
    {
        return rest_url($this->restNameSpace() . '/' . $this->restRoute());
    }

    protected function restNameSpace()
    {
        return 'rareloop/lumberjack/v1';
    }

    protected function restRoute()
    {
        return 'site-health/' . Stringy::create($this->identifier())->slugify();
    }
}
