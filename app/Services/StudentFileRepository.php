<?php

namespace App\Services;

use App\Domain\HonorsStudent;
use App\Domain\Student;
use App\Exceptions\StudentException;
use Illuminate\Support\Facades\File;
use Throwable;

class StudentFileRepository
{
    private string $file;

    public function __construct()
    {
        $this->file = storage_path('app/mahasiswa.json');

        File::ensureDirectoryExists(dirname($this->file));

        if (! File::exists($this->file)) {
            File::put($this->file, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    public function all(): array
    {
        try {
            $content = File::get($this->file);
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            return is_array($data) ? $data : [];
        } catch (Throwable $e) {
            throw new StudentException('Gagal membaca file mahasiswa.');
        }
    }

    public function create(array $payload): array
    {
        $students = $this->all();

        $this->ensureUniqueNim($payload['nim']);

        $id = uniqid('mhs_', true);

        // Polimorfisme: jika IPK tinggi, objek memakai class HonorsStudent.
        $student = ((float) $payload['ipk'] >= 3.75)
            ? new HonorsStudent(
                $id,
                $payload['nim'],
                $payload['nama'],
                $payload['email'],
                $payload['jurusan'],
                (int) $payload['angkatan'],
                (float) $payload['ipk']
            )
            : new Student(
                $id,
                $payload['nim'],
                $payload['nama'],
                $payload['email'],
                $payload['jurusan'],
                (int) $payload['angkatan'],
                (float) $payload['ipk']
            );

        $students[] = $student->jsonSerialize();

        $this->saveAll($students);

        return $student->jsonSerialize();
    }

    public function update(string $id, array $payload): array
    {
        $students = $this->all();

        $this->ensureUniqueNim($payload['nim'], $id);

        $updated = null;

        // PHP tidak punya pointer seperti C/C++.
        // Operator & di bawah adalah reference, konsepnya mirip pointer.
        foreach ($students as &$student) {
            if ($student['id'] === $id) {
                $student['nim'] = $payload['nim'];
                $student['nama'] = $payload['nama'];
                $student['email'] = $payload['email'];
                $student['jurusan'] = $payload['jurusan'];
                $student['angkatan'] = (int) $payload['angkatan'];
                $student['ipk'] = (float) $payload['ipk'];

                $student['role'] = $student['ipk'] >= 3.75
                    ? 'Mahasiswa Berprestasi'
                    : 'Mahasiswa';

                $student['status'] = match (true) {
                    $student['ipk'] >= 3.50 => 'Cumlaude',
                    $student['ipk'] >= 3.00 => 'Baik',
                    $student['ipk'] >= 2.50 => 'Cukup',
                    default => 'Perlu Bimbingan',
                };

                $updated = $student;
                break;
            }
        }

        unset($student);

        if (! $updated) {
            throw new StudentException('Data mahasiswa tidak ditemukan.');
        }

        $this->saveAll($students);

        return $updated;
    }

    public function delete(string $id): void
    {
        $students = $this->all();

        $filtered = array_values(array_filter(
            $students,
            fn ($student) => $student['id'] !== $id
        ));

        if (count($filtered) === count($students)) {
            throw new StudentException('Data mahasiswa tidak ditemukan.');
        }

        $this->saveAll($filtered);
    }

    private function saveAll(array $students): void
    {
        try {
            File::put(
                $this->file,
                json_encode($students, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        } catch (Throwable $e) {
            throw new StudentException('Gagal menyimpan file mahasiswa.');
        }
    }

    private function ensureUniqueNim(string $nim, ?string $ignoreId = null): void
    {
        foreach ($this->all() as $student) {
            if ($student['nim'] === $nim && $student['id'] !== $ignoreId) {
                throw new StudentException('NIM sudah digunakan.');
            }
        }
    }
}