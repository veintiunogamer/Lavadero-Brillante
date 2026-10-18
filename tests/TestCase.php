<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // En algunos entornos de desarrollo local el directorio storage/ no es
        // escribible (archivos generados por Docker con otro usuario), lo que
        // impide compilar las vistas Blade. En ese caso las compilamos en un
        // directorio temporal para poder ejecutar la suite igualmente.
        $compiled = config('view.compiled');

        if (is_string($compiled) && !is_writable($compiled)) {
            $temporary = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR . 'lavadero-brillante-compiled-views';

            if (!is_dir($temporary)) {
                mkdir($temporary, 0777, true);
            }

            config(['view.compiled' => $temporary]);
        }
    }
}
