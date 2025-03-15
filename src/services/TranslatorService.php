<?php

namespace eronax\translate\services;

use Craft;
use craft\errors\SiteNotFoundException;
use craft\helpers\StringHelper;
use craft\models\Site;
use eronax\translate\records\TranslationRecord;

class TranslatorService
{
    private Site $site;
    private string $language = 'de';
    private array $translations = [];

    /**
     * Class constructor.
     * Initializes the current site and retrieves all translations.
     * @return void
     * @throws SiteNotFoundException
     */
    public function __construct()
    {
        // set current site
        $this->site = \Craft::$app->getSites()->getCurrentSite();

        // set language
        $this->language = $this->site?->language ?? 'de';

        // get all translations
        $this->setTranslationsFromDb();
    }

    public function translate($value, $category = 'app', $variables = [])
    {
        // is cp request?
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return $value;
        }

        // does not exist in db?
        if (!$this->exists($value, $category)) {

            // insert into db + current array
            $this->insert($value, $category);
        }

        // does exist as the current language?
        if ($this->existsAsLanguage($value, $category)) {

            $value = $this->translations[$this->buildKey($value, $category)][$this->language];

            // return with the current language
            return $this->insertVariables($value, $variables);
        }

        return $this->insertVariables($value, $variables);
    }

    private function setTranslationsFromDb(): void
    {
        // get all translations
        $translations = TranslationRecord::find()->all();

        // loop through all translations
        foreach ($translations as $translation) {

            // get unique key
            $key = $this->buildKey($translation['value'], $translation['category']);

            // add translation with unique key
            $this->translations[$key] = $translation;
        }
    }

    private function buildKey($value, $category): string
    {
        return $this->escapeString($value) . '_' . $this->escapeString($category);
    }

    private function escapeString($input): string
    {
        // covert ä, ü, ö
        $input = str_replace('Ä', 'ae', $input);
        $input = str_replace('Ü', 'ue', $input);
        $input = str_replace('Ö', 'oe', $input);
        $input = str_replace('ä', 'ae', $input);
        $input = str_replace('ü', 'ue', $input);
        $input = str_replace('ö', 'oe', $input);

        // all in lowercase
        $input = strtolower($input);

        // replace special with underscores
        $input = preg_replace('/[^a-z0-9\s]/', '_', $input);

        // replace spaces with underscores
        $input = str_replace(' ', '_', $input);

        return trim($input, '_');
    }

    private function exists($value, $category): bool
    {
        // build unique key
        $key = $this->buildKey($value, $category);

        // does key exist?
        return !empty($this->translations[$key]);
    }

    private function existsAsLanguage($value, $category): bool
    {
        // build key
        $key = $this->buildKey($value, $category);

        return !empty($this->translations[$key][$this->language]);
    }

    private function insertVariables($value, $variables)
    {
        return $value;
    }

    private function insert($value, $category): void
    {
        // build value
        $escapedValue = $this->escapeString($value);

        // get language
        $language = $this->language;

        // insert into db
        $record = new TranslationRecord();
        $record->value = $escapedValue;
        $record->category = $category;
        $record->$language = $value;

        // successfully saved?
        if ($record->save()) {

            // build key
            $key = $this->buildKey($value, $category);

            // add also to array
            $this->translations[$key] = TranslationRecord::find()->where(['value' => $escapedValue, 'category' => $category])->one();
        }
    }
}