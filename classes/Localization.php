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
        $getText = function (string $id, string $locale) {
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
     * @param int|DateTime|string $date The date to format.
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
            $timestamp = (new \DateTime((string)$date))->getTimestamp();
        }

        if (empty($options)) {
            $options = ['dateAutoYear'];
        }

        $result = [];

        $hasOption = function ($name) use ($options) {
            return array_search($name, $options) !== false;
        };

        $hasDateOption = $hasOption('date');
        $hasDateAutoYearOption = $hasOption('dateAutoYear');
        $hasMonthOption = $hasOption('month');
        $hasYearOption = $hasOption('year');
        $hasMonthDayOption = $hasOption('monthDay');
        $hasWeekDayOption = $hasOption('weekDay');
        $hasWeekDayShortOption = $hasOption('weekDayShort');
        $hasTimeOption = $hasOption('time');
        $hasTimeAgoOption = $hasOption('timeAgo');

        if ($hasTimeAgoOption) {
            $secondsAgo = time() - $timestamp;
            if ($secondsAgo < 60) {
                $result['timeAgo'] = __('bearframework-localization-addon.moment_ago');
            } elseif ($secondsAgo < 60 * 60) {
                $minutesAgo = floor($secondsAgo / 60);
                $result['timeAgo'] = $minutesAgo > 1 ? sprintf(__('bearframework-localization-addon.minutes_ago'), $minutesAgo) : __('bearframework-localization-addon.minute_ago');
            } elseif ($secondsAgo < 60 * 60 * 24) {
                $hoursAgo = floor($secondsAgo / (60 * 60));
                $result['timeAgo'] = $hoursAgo > 1 ? sprintf(__('bearframework-localization-addon.hours_ago'), $hoursAgo) : __('bearframework-localization-addon.hour_ago');
            } else {
                $hasDateAutoYearOption = true;
            }
        }

        if ($hasDateOption || $hasDateAutoYearOption || $hasMonthDayOption) {
            $result['monthDay'] = date('j', $timestamp);
        }

        if ($hasDateOption || $hasDateAutoYearOption || $hasMonthOption) {
            $result['month'] = __('bearframework-localization-addon.month_' . date('n', $timestamp));
        }

        if ($hasDateOption || $hasDateAutoYearOption || $hasYearOption) {
            $year = date('Y', $timestamp);
            if ($hasDateAutoYearOption && $year === date('Y', time())) {
                // skip
            } else {
                if ($this->locale === 'bg') {
                    $year .= 'г.';
                }
                $result['year'] = $year;
            }
        }

        if ($hasWeekDayOption) {
            $result['weekDay'] = __('bearframework-localization-addon.day_' . date('N', $timestamp));
        }

        if ($hasWeekDayShortOption) {
            $result['weekDay'] = __('bearframework-localization-addon.day_' . date('N', $timestamp) . '_short');
        }

        if ($hasTimeOption) {
            if ($this->locale === 'bg') {
                $result['time'] = date('G:i', $timestamp) . 'ч.';
            } else {
                $result['time'] = date('G:i', $timestamp);
            }
        }

        $templates = [];
        if (array_search($this->locale, ['bg', 'ru']) !== false) {
            $templates[] = '{weekDay}, {monthDay} {month} {year}';
            $templates[] = '{weekDay}, {monthDay} {month}';
            $templates[] = '{monthDay} {month} {year}';
            $templates[] = '{monthDay} {month}';
        } else {
            $templates[] = '{weekDay}, {month} {monthDay}, {year}';
            $templates[] = '{weekDay}, {month} {monthDay}';
            $templates[] = '{month} {monthDay}, {year}';
            $templates[] = '{month} {monthDay}';
        }

        $replacedTemplate = '';
        $resultKeys = array_keys($result);
        foreach ($templates as $template) {
            $matches = null;
            preg_match_all('/\{(.*?)\}/', $template, $matches);
            if (is_array($matches) && isset($matches[1])) {
                $keys = $matches[1];
                if (sizeof(array_intersect($resultKeys, $keys)) === sizeof($keys)) {
                    $replacedTemplate = $template;
                    foreach ($keys as $key) {
                        $replacedTemplate = str_replace('{' . $key . '}', $result[$key], $replacedTemplate);
                        unset($result[$key]);
                    }
                    break;
                }
            }
        }

        if (!empty($result)) {
            if ($replacedTemplate !== '') {
                $replacedTemplate .= ',';
            }
            $replacedTemplate .= ' ' . implode(', ', $result);
        }

        return $replacedTemplate;
    }
}
