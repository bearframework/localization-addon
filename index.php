<?php

/*
 * Localization addon for Bear Framework
 * https://github.com/bearframework/localization-addon
 * Copyright (c) 2017 Ivo Petkov
 * Free to use under the MIT license.
 */

use BearFramework\App;

$app = App::get();
$context = $app->context->get(__FILE__);

$context->classes->add('BearFramework\Localization', 'classes/Localization.php');

$app->shortcuts->add('localization', function() {
    return new \BearFramework\Localization();
});

function __($id)
{
    $app = App::get();
    return $app->localization->getText($id);
}