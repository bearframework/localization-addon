<?php

/*
 * Localization addon for Bear Framework
 * https://github.com/bearframework/localization-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

/**
 * @runTestsInSeparateProcesses
 */
class localizationTest extends BearFrameworkAddonTestCase
{

    /**
     * 
     */
    public function testDefaultDictionary()
    {
        $app = $this->getApp();
        $app->localization->setLocale('en');
        $this->assertTrue($app->localization->getText('bearframework-localization-addon.month_1') === 'January');
        $app->localization->setLocale('bg');
        $this->assertTrue($app->localization->getText('bearframework-localization-addon.month_1') === 'Януари');
    }

    /**
     * 
     */
    public function testFormatDate()
    {
        $app = $this->getApp();
        $date = mktime(1, 2, 3, 4, 5, date('Y'));
        $app->localization->setLocale('en');
        $formatedDate = $app->localization->formatDate($date, ['date']);
        $this->assertTrue($formatedDate === 'April 5, ' . date('Y'));
        $formatedDate = $app->localization->formatDate($date, ['dateAutoYear']);
        $this->assertTrue($formatedDate === 'April 5');
        $formatedDate = $app->localization->formatDate($date);
        $this->assertTrue($formatedDate === 'April 5');
        $formatedDate = $app->localization->formatDate($date, ['time']);
        $this->assertTrue($formatedDate === '1:02');

        $date = time() - 4;
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertTrue($formatedDate === 'a moment ago');

        $date = time() - (60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertTrue($formatedDate === 'a minute ago');

        $date = time() - (5 * 60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertTrue($formatedDate === '5 minutes ago');

        $date = time() - (60 * 60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertTrue($formatedDate === 'an hour ago');

        $date = time() - (5 * 60 * 60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertTrue($formatedDate === '5 hours ago');

        $date = time() - (24 * 60 * 60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertTrue($formatedDate === $app->localization->formatDate($date, ['dateAutoYear']));

        $date = time() - (400 * 24 * 60 * 60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertTrue($formatedDate === $app->localization->formatDate($date, ['dateAutoYear']));
    }

    /**
     * 
     */
    public function testBackupLocale()
    {
        $app = $this->getApp();
        $app->localization->setLocale('en');
        $this->assertTrue($app->localization->getText('bearframework-localization-addon.month_1') === 'January');
        $this->assertTrue($app->localization->getBackupLocale() === 'en');
        $this->assertTrue($app->localization->getLocale() === 'en');
        $app->localization->setLocale('xx');
        $this->assertTrue($app->localization->getLocale() === 'xx');
        $this->assertTrue($app->localization->getText('bearframework-localization-addon.month_1') === 'January');
        $app->localization->setBackupLocale('bg');
        $this->assertTrue($app->localization->getBackupLocale() === 'bg');
        $this->assertTrue($app->localization->getText('bearframework-localization-addon.month_1') === 'Януари');
    }

}
