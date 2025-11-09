<?php
namespace App\Support;

class Env
{
    private array $data = [];

    public function __construct(string $path)
    {
        if (is_file($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#')) continue;
                if (!str_contains($line, '=')) continue;
                [$k,$v] = array_map('trim', explode('=', $line, 2));
                $v = trim($v, '\"\'');
                $this->data[$k] = $v;
            }
        }
    }

    public function get(string $key, $default=null) {
        return $this->data[$key] ?? $default;
    }
}
