<?php

class Payload {
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function toArray(): array {
        return $this->data;
    }

    public function get(string $key) {
        return $this->data[$key] ?? null;
    }
}
