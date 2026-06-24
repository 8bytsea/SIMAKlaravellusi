<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class StudentController extends Controller
{
    private string $filePath;

    public function __construct()
    {
        $this->filePath = storage_path('app/mahasiswa.json');

        File::ensureDirectoryExists(dirname($this->filePath));

        if (! File::exists($this->filePath)) {
            File::put($this->filePath, json_encode([], JSON_PRETTY_PRINT));
        }
    }

    public function backup()
    {
        if (! File::exists($this->filePath)) {
            File::put($this->filePath, json_encode([], JSON_PRETTY_PRINT));
        }

        return response()->download($this->filePath, 'backup-mahasiswa.json');
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $startTime = microtime(true);

        try {
            $students = $this->readStudents();

            $q = trim((string) $request->query('q', ''));
            $sort = (string) $request->query('sort', 'nim');
            $method = (string) $request->query('method', 'merge');
            $search = (string) $request->query('search', 'linear');

            if ($q !== '') {
                $students = match ($search) {
                    'binary' => $this->binarySearchByNim($students, $q),
                    'sequential' => $this->sequentialSearch($students, $q),
                    default => $this->linearSearch($students, $q),
                };
            }

            $students = match ($method) {
                'bubble' => $this->bubbleSort($students, $sort),
                'insertion' => $this->insertionSort($students, $sort),
                'shell' => $this->shellSort($students, $sort),
                default => $this->mergeSort($students, $sort),
            };

            $executionTime = round(microtime(true) - $startTime, 6);

            $operationNotice = null;

            if ($q !== '') {
                $operationNotice = [
                    'title' => 'Pencarian Data Selesai',
                    'description' => 'Data dicari menggunakan '.$this->searchLabel($search).' dan diurutkan menggunakan '.$this->sortLabel($method).'.',
                    'time' => $executionTime,
                    'complexity' => $this->searchComplexity($search).' + '.$this->sortComplexity($method),
                ];
            }

            return Inertia::render('students/index', [
                'students' => array_values($students),
                'filters' => [
                    'q' => $q,
                    'sort' => $sort,
                    'method' => $method,
                    'search' => $search,
                ],
                'operation_notice' => $operationNotice,
                'flash' => [
                    'success' => session('success'),
                    'error' => session('error'),
                    'notice' => session('notice'),
                ],
            ]);
        } catch (Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat memuat data mahasiswa.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $this->validated($request);

            $students = $this->readStudents();

            foreach ($students as $student) {
                if ($student['nim'] === $validated['nim']) {
                    throw new Exception('NIM sudah digunakan.');
                }
            }

            $ipk = (float) $validated['ipk'];

            $students[] = [
                'id' => uniqid('mhs_', true),
                'nim' => $validated['nim'],
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'jurusan' => $validated['jurusan'],
                'angkatan' => (int) $validated['angkatan'],
                'ipk' => $ipk,
                'role' => $ipk >= 3.75 ? 'Mahasiswa Berprestasi' : 'Mahasiswa',
                'status' => $this->getStatusAkademik($ipk),
            ];

            $this->writeStudents($students);

            $executionTime = round(microtime(true) - $startTime, 6);

            return back()
                ->with('success', 'Data mahasiswa berhasil ditambahkan.')
                ->with('notice', [
                    'title' => 'Tambah Data Selesai',
                    'description' => 'Data baru berhasil disimpan ke file JSON.',
                    'time' => $executionTime,
                    'complexity' => 'O(n)',
                ]);
        } catch (Exception $e) {
            return back()->withErrors([
                'nim' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat menambahkan data.');
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $startTime = microtime(true);

        try {
            $validated = $this->validated($request);

            $students = $this->readStudents();

            foreach ($students as $student) {
                if ($student['nim'] === $validated['nim'] && $student['id'] !== $id) {
                    throw new Exception('NIM sudah digunakan.');
                }
            }

            $found = false;

            foreach ($students as &$student) {
                if ($student['id'] === $id) {
                    $ipk = (float) $validated['ipk'];

                    $student['nim'] = $validated['nim'];
                    $student['nama'] = $validated['nama'];
                    $student['email'] = $validated['email'];
                    $student['jurusan'] = $validated['jurusan'];
                    $student['angkatan'] = (int) $validated['angkatan'];
                    $student['ipk'] = $ipk;
                    $student['role'] = $ipk >= 3.75 ? 'Mahasiswa Berprestasi' : 'Mahasiswa';
                    $student['status'] = $this->getStatusAkademik($ipk);

                    $found = true;
                    break;
                }
            }

            unset($student);

            if (! $found) {
                throw new Exception('Data mahasiswa tidak ditemukan.');
            }

            $this->writeStudents($students);

            $executionTime = round(microtime(true) - $startTime, 6);

            return back()
                ->with('success', 'Data mahasiswa berhasil diperbarui.')
                ->with('notice', [
                    'title' => 'Update Data Selesai',
                    'description' => 'Data mahasiswa berhasil diperbarui berdasarkan ID.',
                    'time' => $executionTime,
                    'complexity' => 'O(n)',
                ]);
        } catch (Exception $e) {
            return back()->withErrors([
                'nim' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat memperbarui data.');
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        $startTime = microtime(true);

        try {
            $students = $this->readStudents();

            $filtered = array_values(array_filter($students, function ($student) use ($id) {
                return $student['id'] !== $id;
            }));

            if (count($filtered) === count($students)) {
                throw new Exception('Data mahasiswa tidak ditemukan.');
            }

            $this->writeStudents($filtered);

            $executionTime = round(microtime(true) - $startTime, 6);

            return back()
                ->with('success', 'Data mahasiswa berhasil dihapus.')
                ->with('notice', [
                    'title' => 'Hapus Data Selesai',
                    'description' => 'Data mahasiswa berhasil dihapus dari file JSON.',
                    'time' => $executionTime,
                    'complexity' => 'O(n)',
                ]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate(
            [
                'nim' => ['required', 'regex:/^[0-9]{8,12}$/'],
                'nama' => ['required', 'regex:/^[A-Za-z\s.\'-]{3,100}$/'],
                'email' => ['required', 'email'],
                'jurusan' => ['required', 'regex:/^[A-Za-z\s]{3,100}$/'],
                'angkatan' => ['required', 'integer', 'min:2000', 'max:2100'],
                'ipk' => ['required', 'numeric', 'min:0', 'max:4'],
            ],
            [
                'nim.required' => 'NIM wajib diisi.',
                'nim.regex' => 'NIM harus berupa angka 8 sampai 12 digit.',
                'nama.required' => 'Nama wajib diisi.',
                'nama.regex' => 'Nama minimal 3 karakter dan hanya boleh huruf, spasi, titik, petik, atau strip.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'jurusan.required' => 'Jurusan wajib diisi.',
                'jurusan.regex' => 'Jurusan hanya boleh berisi huruf dan spasi.',
                'angkatan.required' => 'Angkatan wajib diisi.',
                'angkatan.integer' => 'Angkatan harus berupa angka.',
                'angkatan.min' => 'Angkatan minimal tahun 2000.',
                'angkatan.max' => 'Angkatan maksimal tahun 2100.',
                'ipk.required' => 'IPK wajib diisi.',
                'ipk.numeric' => 'IPK harus berupa angka.',
                'ipk.min' => 'IPK minimal 0.',
                'ipk.max' => 'IPK maksimal 4.00.',
            ]
        );
    }

    private function readStudents(): array
    {
        $content = File::get($this->filePath);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    private function writeStudents(array $students): void
    {
        File::put(
            $this->filePath,
            json_encode($students, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function getStatusAkademik(float $ipk): string
    {
        return match (true) {
            $ipk >= 3.50 => 'Cumlaude',
            $ipk >= 3.00 => 'Baik',
            $ipk >= 2.50 => 'Cukup',
            default => 'Perlu Bimbingan',
        };
    }

    private function linearSearch(array $data, string $keyword): array
    {
        $keyword = mb_strtolower($keyword);

        return array_values(array_filter($data, function ($student) use ($keyword) {
            return str_contains(mb_strtolower($student['nim']), $keyword)
                || str_contains(mb_strtolower($student['nama']), $keyword)
                || str_contains(mb_strtolower($student['jurusan']), $keyword);
        }));
    }

    private function sequentialSearch(array $data, string $keyword): array
    {
        $result = [];
        $keyword = mb_strtolower($keyword);

        foreach ($data as $student) {
            if (
                mb_strtolower($student['nim']) === $keyword ||
                mb_strtolower($student['nama']) === $keyword
            ) {
                $result[] = $student;
            }
        }

        return $result;
    }

    private function binarySearchByNim(array $data, string $nim): array
    {
        $data = $this->mergeSort($data, 'nim');

        $low = 0;
        $high = count($data) - 1;

        while ($low <= $high) {
            $mid = intdiv($low + $high, 2);

            if ($data[$mid]['nim'] === $nim) {
                return [$data[$mid]];
            }

            if ($data[$mid]['nim'] < $nim) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }

        return [];
    }

    private function bubbleSort(array $data, string $key): array
    {
        $n = count($data);

        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = 0; $j < $n - $i - 1; $j++) {
                if ($this->compare($data[$j][$key], $data[$j + 1][$key]) > 0) {
                    [$data[$j], $data[$j + 1]] = [$data[$j + 1], $data[$j]];
                }
            }
        }

        return $data;
    }

    private function insertionSort(array $data, string $key): array
    {
        for ($i = 1; $i < count($data); $i++) {
            $current = $data[$i];
            $j = $i - 1;

            while ($j >= 0 && $this->compare($data[$j][$key], $current[$key]) > 0) {
                $data[$j + 1] = $data[$j];
                $j--;
            }

            $data[$j + 1] = $current;
        }

        return $data;
    }

    private function shellSort(array $data, string $key): array
    {
        $n = count($data);
        $gap = intdiv($n, 2);

        while ($gap > 0) {
            for ($i = $gap; $i < $n; $i++) {
                $temp = $data[$i];
                $j = $i;

                while ($j >= $gap && $this->compare($data[$j - $gap][$key], $temp[$key]) > 0) {
                    $data[$j] = $data[$j - $gap];
                    $j -= $gap;
                }

                $data[$j] = $temp;
            }

            $gap = intdiv($gap, 2);
        }

        return $data;
    }

    private function mergeSort(array $data, string $key): array
    {
        if (count($data) <= 1) {
            return $data;
        }

        $middle = intdiv(count($data), 2);
        $left = array_slice($data, 0, $middle);
        $right = array_slice($data, $middle);

        return $this->merge(
            $this->mergeSort($left, $key),
            $this->mergeSort($right, $key),
            $key
        );
    }

    private function merge(array $left, array $right, string $key): array
    {
        $result = [];

        while (count($left) > 0 && count($right) > 0) {
            if ($this->compare($left[0][$key], $right[0][$key]) <= 0) {
                $result[] = array_shift($left);
            } else {
                $result[] = array_shift($right);
            }
        }

        return array_merge($result, $left, $right);
    }

    private function compare(mixed $a, mixed $b): int
    {
        if (is_numeric($a) && is_numeric($b)) {
            return $a <=> $b;
        }

        return strcasecmp((string) $a, (string) $b);
    }

    private function searchLabel(string $search): string
    {
        return match ($search) {
            'binary' => 'Binary Search',
            'sequential' => 'Sequential Search',
            default => 'Linear Search',
        };
    }

    private function sortLabel(string $method): string
    {
        return match ($method) {
            'bubble' => 'Bubble Sort',
            'insertion' => 'Insertion Sort',
            'shell' => 'Shell Sort',
            default => 'Merge Sort',
        };
    }

    private function searchComplexity(string $search): string
    {
        return match ($search) {
            'binary' => 'O(log n)',
            'sequential' => 'O(n)',
            default => 'O(n)',
        };
    }

    private function sortComplexity(string $method): string
    {
        return match ($method) {
            'bubble' => 'O(n²)',
            'insertion' => 'O(n²)',
            'shell' => 'O(n log n) sampai O(n²)',
            default => 'O(n log n)',
        };
    }
}