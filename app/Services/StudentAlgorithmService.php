<?php

namespace App\Services;

class StudentAlgorithmService
{
    public function linearSearch(array $data, string $keyword): array
    {
        $keyword = mb_strtolower(trim($keyword));

        return array_values(array_filter($data, function ($student) use ($keyword) {
            return str_contains(mb_strtolower($student['nim']), $keyword)
                || str_contains(mb_strtolower($student['nama']), $keyword)
                || str_contains(mb_strtolower($student['jurusan']), $keyword);
        }));
    }

    public function sequentialSearch(array $data, string $keyword): array
    {
        $result = [];
        $keyword = mb_strtolower(trim($keyword));

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

    public function binarySearchByNim(array $data, string $nim): array
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

    public function bubbleSort(array $data, string $key): array
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

    public function insertionSort(array $data, string $key): array
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

    public function shellSort(array $data, string $key): array
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

    public function mergeSort(array $data, string $key): array
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
}