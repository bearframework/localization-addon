<?php

/*
 * Localization addon for Bear Framework
 * https://github.com/bearframework/localization-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

use BearFramework\App;

$app = App::get();
$context = $app->contexts->get(__DIR__);

$context->classes
        ->add('BearFramework\Localization', 'classes/Localization.php');

$app->shortcuts
        ->add('localization', function() {
            return new \BearFramework\Localization();
        });

function __(string $id): ?string
{
    $app = App::get();
    return $app->localization->getText($id);
}
