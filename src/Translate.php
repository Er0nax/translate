<?php

namespace eronax\translate;

use Craft;
use craft\base\Plugin;
use eronax\translate\services\TranslatorService;
use Twig\TwigFilter;

/**
 * Translate plugin
 *
 * @method static Translate getInstance()
 * @author Eronax <zapfetim@gmail.com>
 * @copyright Eronax
 * @license MIT
 */
class Translate extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                'translator' => TranslatorService::class,
            ],
        ];

    }

    public function init(): void
    {
        Craft::$app->view->registerTwigExtension(new class extends \Twig\Extension\AbstractExtension {
            public function getFilters(): array
            {
                return [
                    new TwigFilter('t', function ($value, $category = 'app', $variables = []) {
                        return Translate::getInstance()->translator->translate($value, $category, $variables);
                    }),
                ];
            }
        });
    }
}
