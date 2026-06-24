<?php

namespace App\Domain;

interface Displayable
{
    public function getDisplayName(): string;
}