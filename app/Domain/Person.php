<?php

namespace App\Domain;

abstract class Person implements Displayable
{
    public function __construct(
        protected string $nama,
        protected string $email
    ) {}

    public function getNama(): string
    {
        return $this->nama;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getDisplayName(): string
    {
        return "{$this->nama} <{$this->email}>";
    }

    abstract public function getRole(): string;
}