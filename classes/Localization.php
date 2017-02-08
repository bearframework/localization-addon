<?php

/*
 * Localization addon for Bear Framework
 * https://github.com/bearframework/localization-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

namespace BearFramework;

class Localization
{

    private $locale = 'en';
    private $dictionaries = [];

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function addDictionary(string $locale, $callbackOrArray): void
    {
        if (!isset($this->dictionaries[$locale])) {
            $this->dictionaries[$locale] = [];
        }
        $this->dictionaries[$locale][] = $callbackOrArray;
    }

    public function getText($id): string
    {
        if (isset($this->dictionaries[$this->locale])) {
            foreach ($this->dictionaries[$this->locale] as $i => $dictionary) {
                if (is_callable($dictionary)) {
                    $dictionary = call_user_func($dictionary);
                    $this->dictionaries[$this->locale][$i] = $dictionary;
                }
                foreach ($dictionary as $_id => $text) {
                    if ($id === $_id) {
                        return $text;
                    }
                }
            }
        }
        return '';
    }

}
