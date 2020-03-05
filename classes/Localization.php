<?php

/*
 * Localization addon for Bear Framework
 * https://github.com/bearframework/localization-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace BearFramework;

use BearFramework\App;

/**
 * 
 */
class Localization
{

    /**
     *
     * @var string 
     */
    private $locale = 'en';

    /**
     *
     * @var string 
     */
    private $backupLocale = 'en';

    /**
     *
     * @var array 
     */
    private $dictionaries = [];

    /**
     *
     * @var array 
     */
    private $defaultLocales = ['bg' => 0, 'en' => 0, 'ru' => 0];

    /**
     * Sets a new locale code.
     * @param string $locale The new locale code.
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Returns the current locale code.
     * @return string The current locale code.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Sets a new backup locale code.
     * @param string $locale The new backup locale code.
     */
    public function setBackupLocale(string $locale): void
    {
        $this->backupLocale = $locale;
    }

    /**
     * Returns the current backup locale code.
     * @return string The current backup locale code.
     */
    public function getBackupLocale(): string
    {
        return $this->backupLocale;
    }

    /**
     * Adds a new dictionary.
     * @param string $locale A locale code.
     * @param type $callbackOrArray An array containing dictionary data (in key=>value format) or a callback that returns such array.
     * @return \BearFramework\Localization Returns a instance to itself.
     */
    public function addDictionary(string $locale, $callbackOrArray): \BearFramework\Localization
    {
        if (!isset($this->dictionaries[$locale])) {
            $this->dictionaries[$locale] = [];
        }
        $this->dictionaries[$locale][] = $callbackOrArray;
        return $this;
    }

    /**
     * Returns a text from the dictionary for the current locale.
     * @param string $id The ID of the text.
     * @return string A text from the dictionary for the current locale. Returns null if no text is found.
     */
    public function getText(string $id): ?string
    {
        $getText = function(string $id, string $locale) {
            if (isset($this->defaultLocales[$locale]) && $this->defaultLocales[$locale] === 0) {
                $app = App::get();
                $context = $app->contexts->get(__DIR__);
                $this->defaultLocales[$locale] = 1;
                $filename = $context->dir . '/locales/' . $locale . '.php';
                if (is_file($filename)) {
                    $data = include $filename;
                    if (is_array($data)) {
                        $this->addDictionary($locale, $data);
                    }
                }
            }
            if (isset($this->dictionaries[$locale])) {
                foreach ($this->dictionaries[$locale] as $i => $dictionary) {
                    if (is_callable($dictionary)) {
                        $dictionary = call_user_func($dictionary);
                        $this->dictionaries[$locale][$i] = $dictionary;
                    }
                    if (is_array($dictionary)) {
                        foreach ($dictionary as $_id => $text) {
                            if ($id === $_id) {
                                return (string) $text;
                            }
                        }
                    }
                }
            }
            return null;
        };
        $text = $getText($id, $this->locale);
        if ($text === null || !isset($text[0])) {
            $text = $getText($id, $this->backupLocale);
        }
        return $text;
    }

    /**
     * Returns a text representation of the date provided that contains the elements listed.
     * @param int|DateTime $date The date to format.
     * @param array $options The elements that the text representation of the date must contain. Available values: date, dateAutoYear, time, timeAgo.
     * @todo Additional options: timeAutoDate, day, month, year, autoYear, hours, minutes, seconds.
     */
    public function formatDate($date, array $options = []): string
    {
        if (is_int($date) || is_numeric($date)) {
            $timestamp = (int) $date;
        } elseif ($date instanceof \DateTime) {
            $timestamp = $date->getTimestamp();
        } else {
            $timestamp = (new \DateTime($date))->getTimestamp();
        }

        if (empty($options)) {
            $options = ['dateAutoYear'];
        }

        $result = [];

        $hasDateOption = array_search('date', $options) !== false;
        $hasDateAutoYearOption = array_search('dateAutoYear', $options) !== false;
        $hasTimeOption = array_search('time', $options) !== false;
        $hasTimeAgoOption = array_search('timeAgo', $options) !== false;

        if ($hasTimeAgoOption) {
            $secondsAgo = time() - $timestamp;
            if ($secondsAgo < 60) {
                $result[] = __('bearframework-localization-addon.moment_ago');
            } elseif ($secondsAgo < 60 * 60) {
                $minutes = floor($secondsAgo / 60);
                $result[] = $minutes > 1 ? sprintf(__('bearframework-localization-addon.minutes_ago'), $minutes) : __('bearframework-localization-addon.minute_ago');
            } elseif ($secondsAgo < 60 * 60 * 24) {
                $hours = floor($secondsAgo / (60 * 60));
                $result[] = $hours > 1 ? sprintf(__('bearframework-localization-addon.hours_ago'), $hours) : __('bearframework-localization-addon.hour_ago');
            } else {
                $hasDateAutoYearOption = true;
            }
        }

        if ($hasDateOption || $hasDateAutoYearOption) {
            $day = date('j', $timestamp);
            $month = __('bearframework-localization-addon.month_' . date('n', $timestamp));
            $year = date('Y', $timestamp);
            $showYear = $hasDateOption || ($hasDateAutoYearOption && $year !== date('Y', time()));
            if ($this->locale === 'bg') {
                $result[] = $day . ' ' . $month . ($showYear ? ' ' . $year . 'г.' : '');
            } elseif ($this->locale === 'ru') {
                $result[] = $day . ' ' . $month . ($showYear ? ' ' . $year : '');
            } else {
                $result[] = $month . ' ' . $day . ($showYear ? ', ' . $year : '');
            }
        }

        if ($hasTimeOption) {
            $result[] = date('G:i', $timestamp);
        }

        return implode(' ', $result);
    }

}
