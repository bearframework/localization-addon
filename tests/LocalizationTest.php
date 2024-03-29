<?php

/*
 * Localization addon for Bear Framework
 * https://github.com/bearframework/localization-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

/**
 * @runTestsInSeparateProcesses
 */
class LocalizationTest extends BearFramework\AddonTests\PHPUnitTestCase
{

    /**
     * 
     */
    public function testDefaultDictionary()
    {
        $app = $this->getApp();
        $app->localization->setLocale('en');
        $this->assertEquals($app->localization->getText('bearframework-localization-addon.month_1'), 'January');
        $app->localization->setLocale('bg');
        $this->assertEquals($app->localization->getText('bearframework-localization-addon.month_1'), 'Януари');
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
        $this->assertEquals($formatedDate, 'April 5, ' . date('Y'));
        $formatedDate = $app->localization->formatDate($date, ['dateAutoYear']);
        $this->assertEquals($formatedDate, 'April 5');
        $formatedDate = $app->localization->formatDate($date);
        $this->assertEquals($formatedDate, 'April 5');
        $formatedDate = $app->localization->formatDate($date, ['time']);
        $this->assertEquals($formatedDate, '1:02');

        $date = time() - 4;
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertEquals($formatedDate, 'a moment ago');

        $date = time() - (60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertEquals($formatedDate, 'a minute ago');

        $date = time() - (5 * 60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertEquals($formatedDate, '5 minutes ago');

        $date = time() - (60 * 60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertEquals($formatedDate, 'an hour ago');

        $date = time() - (5 * 60 * 60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertEquals($formatedDate, '5 hours ago');

        $date = time() - (24 * 60 * 60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertEquals($formatedDate, $app->localization->formatDate($date, ['dateAutoYear']));

        $date = time() - (400 * 24 * 60 * 60 + 4);
        $formatedDate = $app->localization->formatDate($date, ['timeAgo']);
        $this->assertEquals($formatedDate, $app->localization->formatDate($date, ['dateAutoYear']));
    }

    /**
     * 
     */
    public function testBackupLocale()
    {
        $app = $this->getApp();
        $app->localization->setLocale('en');
        $this->assertEquals($app->localization->getText('bearframework-localization-addon.month_1'), 'January');
        $this->assertEquals($app->localization->getBackupLocale(), 'en');
        $this->assertEquals($app->localization->getLocale(), 'en');
        $app->localization->setLocale('xx');
        $this->assertEquals($app->localization->getLocale(), 'xx');
        $this->assertEquals($app->localization->getText('bearframework-localization-addon.month_1'), 'January');
        $app->localization->setBackupLocale('bg');
        $this->assertEquals($app->localization->getBackupLocale(), 'bg');
        $this->assertEquals($app->localization->getText('bearframework-localization-addon.month_1'), 'Януари');
    }
}
