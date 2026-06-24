<?php

namespace App\Domain;

use JsonSerializable;

class Student extends Person implements JsonSerializable
{
    public function __construct(
        private string $id,
        private string $nim,
        string $nama,
        string $email,
        private string $jurusan,
        private int $angkatan,
        private float $ipk
    ) {
        parent::__construct($nama, $email);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['nim'],
            $data['nama'],
            $data['email'],
            $data['jurusan'],
            (int) $data['angkatan'],
            (float) $data['ipk']
        );
    }

    public function getRole(): string
    {
        return 'Mahasiswa';
    }

    public function getStatusAkademik(): string
    {
        return match (true) {
            $this->ipk >= 3.50 => 'Cumlaude',
            $this->ipk >= 3.00 => 'Baik',
            $this->ipk >= 2.50 => 'Cukup',
            default => 'Perlu Bimbingan',
        };
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nim' => $this->nim,
            'nama' => $this->nama,
            'email' => $this->email,
            'jurusan' => $this->jurusan,
            'angkatan' => $this->angkatan,
            'ipk' => $this->ipk,
            'role' => $this->getRole(),
            'status' => $this->getStatusAkademik(),
        ];
    }
}