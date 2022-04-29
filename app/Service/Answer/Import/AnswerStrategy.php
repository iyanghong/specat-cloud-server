<?php

namespace App\Service\Answer\Import;

interface AnswerStrategy
{
    public function resolve(string $content);
}
