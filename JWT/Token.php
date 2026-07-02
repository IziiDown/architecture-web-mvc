<?php

class Token {
    private string $tokenString;

    public function __construct(string $tokenString) {
        $this->tokenString = $tokenString;
    }

    public function toString(): string {
        return $this->tokenString;
    }
}
