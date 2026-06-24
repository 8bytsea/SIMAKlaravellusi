<?php

namespace App\Domain;

class HonorsStudent extends Student
{
    public function getRole(): string
    {
        return 'Mahasiswa Berprestasi';
    }
}