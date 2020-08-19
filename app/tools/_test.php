<?php
//TODO make your test here
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
debug::drawdebug($request->query->all(), "params from request parser symfony");
debug::drawdebug("Hello Fucking world!");


