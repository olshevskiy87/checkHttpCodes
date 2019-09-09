<?php

namespace App\Check;

use App\Exceptions\FileExceptions;

class File
{
    private $file = '';

    /**
     * File constructor.
     * @param $file
     * @throws FileExceptions
     */
    public function __construct(string $file)
    {
        if (!$file || !is_readable($file)) {
            throw new FileExceptions('file ' . $file . ' is not readable');
        }
        $this->file = $file;
    }

    /**
     * @return array
     * @throws FileExceptions
     */
    public function getUrls(): array
    {
        $content = file_get_contents($this->file);
        if (!$content) {
            throw new FileExceptions('file ' . $this->file . ' is empty or occurred unknown read error');
        }
        return explode(PHP_EOL, $content);
    }
}

