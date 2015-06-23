<?php

namespace App\Console\Commands\Parsers;

interface IParser
{
    /**
     * Parse source for data.
     * @return void
     */
    public function parse();
}