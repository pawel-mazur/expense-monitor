<?php

$finder = PhpCsFixer\Finder::create()
    ->in('src')
;

return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRules(array(
        '@Symfony' => true,
    ))
    ->setFinder($finder)
;
