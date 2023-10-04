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
        $hasMonthShortOption = $hasOption('monthShort');
        $hasYearOption = $hasOption('year');
        $hasAutoYearOption = $hasOption('autoYear');
        $hasMonthDayOption = $hasOption('monthDay');
        $hasWeekDayOption = $hasOption('weekDay');
        $hasWeekDayShortOption = $hasOption('weekDayShort');
        $hasWeekNumber = $hasOption('weekNumber');
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

        if ($hasMonthShortOption) {
            $result['month'] = __('bearframework-localization-addon.month_' . date('n', $timestamp) . '_short');
        }

        if ($hasDateOption || $hasDateAutoYearOption || $hasYearOption || $hasAutoYearOption) {
            $year = date('Y', $timestamp);
            if (($hasDateAutoYearOption || $hasAutoYearOption) && $year === date('Y', time())) {
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

        if ($hasWeekNumber) {
            $result['weekNumber'] = date('W', $timestamp);
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


    /**
     * 
     * @param int|string $value
     * @param array $options Available values: auto, bytes, gb, mb, kb, b, autoRound, round
     * @return string
     */
    public function formatBytes($value, array $options = []): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (empty($options)) {
            $options = ['auto', 'autoRound'];
        }

        $bytes = null;
        if (is_int($value) || is_numeric($value)) {
            $bytes = (int)$value;
        } else {
            $value = str_replace(',', '.', $value);
            $suffix = strtolower(substr($value, -2));
            $number = null;
            if (in_array($suffix, ['tb', 'gb', 'mb', 'kb'])) {
                $number = (float) substr($value, 0, -2);
            } else {
                $suffix = strtolower(substr($value, -1));
                if (in_array($suffix, ['t', 'g', 'm', 'k'])) {
                    $number = (float) substr($value, 0, -1);
                }
            }
            if ($number !== null) {
                switch ($suffix) {
                    case 'tb':
                    case 't':
                        $bytes = $number * 1024 * 1024 * 1024 * 1024;
                        break;
                    case 'gb':
                    case 'g':
                        $bytes = $number * 1024 * 1024 * 1024;
                        break;
                    case 'mb':
                    case 'm':
                        $bytes = $number * 1024 * 1024;
                        break;
                    case 'kb':
                    case 'k':
                        $bytes = $number * 1024;
                        break;
                }
            }
        }

        $hasOption = function ($name) use ($options) {
            return array_search($name, $options) !== false;
        };

        if ($hasOption('bytes')) {
            return $bytes;
        }

        $hasAutoRound = $hasOption('autoRound');

        $applyAutoRound = function ($result) use ($hasAutoRound): string {
            if ($hasAutoRound) {
                return rtrim(rtrim($result, '0'), '.');
            }
            return $result;
        };

        $round = $hasOption('round');

        if (($hasOption('auto') && $bytes >= 1073741824) || $hasOption('gb')) {
            return $applyAutoRound(number_format($bytes / 1073741824, $round ? 0 : 2)) . ' GB';
        }
        if (($hasOption('auto') && $bytes >= 1048576) || $hasOption('mb')) {
            return $applyAutoRound(number_format($bytes / 1048576, $round ? 0 : 2)) . ' MB';
        }
        if (($hasOption('auto') && $bytes >= 1024) || $hasOption('kb')) {
            return $applyAutoRound(number_format($bytes / 1024, $round ? 0 : 2)) . ' KB';
        }
        if (($hasOption('auto') && $bytes > 1) || ($hasOption('b') && $bytes > 1)) {
            return $bytes . ' bytes';
        }
        if (($hasOption('auto') && $bytes === 1) || $hasOption('b')) {
            return $bytes . ' byte';
        }
        return '0 bytes';
    }

    /**
     * 
     * @return string
     */
    public function getFormatDateJsFunction(): string
    {
        $texts = [
            'bearframework-localization-addon.month_1',
            'bearframework-localization-addon.month_2',
            'bearframework-localization-addon.month_3',
            'bearframework-localization-addon.month_4',
            'bearframework-localization-addon.month_5',
            'bearframework-localization-addon.month_6',
            'bearframework-localization-addon.month_7',
            'bearframework-localization-addon.month_8',
            'bearframework-localization-addon.month_9',
            'bearframework-localization-addon.month_10',
            'bearframework-localization-addon.month_11',
            'bearframework-localization-addon.month_12',
            'bearframework-localization-addon.month_1_short',
            'bearframework-localization-addon.month_2_short',
            'bearframework-localization-addon.month_3_short',
            'bearframework-localization-addon.month_4_short',
            'bearframework-localization-addon.month_5_short',
            'bearframework-localization-addon.month_6_short',
            'bearframework-localization-addon.month_7_short',
            'bearframework-localization-addon.month_8_short',
            'bearframework-localization-addon.month_9_short',
            'bearframework-localization-addon.month_10_short',
            'bearframework-localization-addon.month_11_short',
            'bearframework-localization-addon.month_12_short',
            'bearframework-localization-addon.day_1',
            'bearframework-localization-addon.day_2',
            'bearframework-localization-addon.day_3',
            'bearframework-localization-addon.day_4',
            'bearframework-localization-addon.day_5',
            'bearframework-localization-addon.day_6',
            'bearframework-localization-addon.day_7',
            'bearframework-localization-addon.day_1_short',
            'bearframework-localization-addon.day_2_short',
            'bearframework-localization-addon.day_3_short',
            'bearframework-localization-addon.day_4_short',
            'bearframework-localization-addon.day_5_short',
            'bearframework-localization-addon.day_6_short',
            'bearframework-localization-addon.day_7_short',
            'bearframework-localization-addon.moment_ago',
            'bearframework-localization-addon.minutes_ago',
            'bearframework-localization-addon.minute_ago',
            'bearframework-localization-addon.hours_ago',
            'bearframework-localization-addon.hour_ago',
        ];
        $js = include __DIR__ . '/../assets/formatDate.min.js.php';
        //$js = file_get_contents(__DIR__ . '/../dev/formatDate.js');
        return trim(str_replace(['var f = ', 'var f=', 'GET_TEXT_FUNCTION_TO_REPLACE', 'LOCALE_TO_REPLACE'], ['', '', $this->getGetTextJsFunction($texts), json_encode($this->locale)], $js), ';');
    }

    /**
     * 
     * @param array $ids
     * @return string
     */
    public function getGetTextJsFunction(array $ids): string
    {
        $texts = [];
        foreach ($ids as $id) {
            $texts[$id] = $this->getText($id);
        }
        $js = include __DIR__ . '/../assets/getText.min.js.php';
        //$js = file_get_contents(__DIR__ . '/../dev/getText.js');
        return trim(str_replace(['var f = ', 'var f=', 'TEXTS_OBJECT_VALUE_TO_REPLACE'], ['', '', json_encode($texts)], $js), ';');
    }

    /**
     * 
     * @return string
     */
    public function getFormatBytesJsFunction(): string
    {
        $js = include __DIR__ . '/../assets/formatBytes.min.js.php';
        //$js = file_get_contents(__DIR__ . '/../dev/formatBytes.js');
        return trim(str_replace(['var f = ', 'var f='], ['', ''], $js), ';');
    }
}
