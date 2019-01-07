<?php

namespace SUP;

interface roleActions {
    function student(\Slim\Http\Request $request, \Slim\Http\Response &$response, $args);

    function teacher(\Slim\Http\Request $request, \Slim\Http\Response &$response, $args);

    function admin(\Slim\Http\Request $request, \Slim\Http\Response &$response, $args);
}
