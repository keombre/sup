<?php

namespace modules\lang;

class routes {
    function __construct($app) {
        $app->get('/{lang}', \controller\lang::class);
    }
}
