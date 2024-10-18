<?php

    $finder = (new PhpCsFixer\Finder())
        ->in(__DIR__ . "/examples")
        ->in(__DIR__ . "/recipe");

    return (new PhpCsFixer\Config())
        ->setRules([ "@PER-CS" => true ])
        ->setFinder($finder);
