import { Head, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function StudentsIndex(props) {
    const { props: pageProps } = usePage();
    const errors = pageProps.errors || {};

    const students = props.students || [];
    const filters = props.filters || {
        q: '',
        sort: 'nim',
        method: 'merge',
        search: 'linear',
    };

    const flash = props.flash || {};
    const operationNotice = props.operation_notice || flash.notice || null;

    useEffect(() => {
        const handlePageShow = (event) => {
            if (event.persisted) {
                router.reload({
                    replace: true,
                    preserveScroll: false,
                    preserveState: false,
                });
            }
        };

        window.addEventListener('pageshow', handlePageShow);

        return () => {
            window.removeEventListener('pageshow', handlePageShow);
        };
    }, []);

    const [editingId, setEditingId] = useState(null);

    const [form, setForm] = useState({
        nim: '',
        nama: '',
        email: '',
        jurusan: '',
        angkatan: new Date().getFullYear().toString(),
        ipk: '',
    });

    const [query, setQuery] = useState(filters.q || '');
    const [sort, setSort] = useState(filters.sort || 'nim');
    const [method, setMethod] = useState(filters.method || 'merge');
    const [search, setSearch] = useState(filters.search || 'linear');

    const resetForm = () => {
        setEditingId(null);

        setForm({
            nim: '',
            nama: '',
            email: '',
            jurusan: '',
            angkatan: new Date().getFullYear().toString(),
            ipk: '',
        });
    };

    const submit = (e) => {
        e.preventDefault();

        const payload = {
            nim: form.nim,
            nama: form.nama,
            email: form.email,
            jurusan: form.jurusan,
            angkatan: Number(form.angkatan),
            ipk: Number(form.ipk),
        };

        if (editingId) {
            router.put('/students/' + editingId, payload, {
                preserveScroll: true,
                onSuccess: resetForm,
            });
        } else {
            router.post('/students', payload, {
                preserveScroll: true,
                onSuccess: resetForm,
            });
        }
    };

    const editStudent = (student) => {
        setEditingId(student.id);

        setForm({
            nim: student.nim || '',
            nama: student.nama || '',
            email: student.email || '',
            jurusan: student.jurusan || '',
            angkatan: String(student.angkatan || ''),
            ipk: String(student.ipk || ''),
        });

        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    };

    const deleteStudent = (id) => {
        if (confirm('Yakin ingin menghapus data ini?')) {
            router.delete('/students/' + id, {
                preserveScroll: true,
            });
        }
    };

    const applyFilter = (e) => {
        e.preventDefault();

        router.get(
            '/students',
            {
                q: query,
                sort: sort,
                method: method,
                search: search,
            },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    };

    const backupData = () => {
        window.location.href = '/students/backup';
    };

    return (
        <>
            <Head title="Data Mahasiswa" />

            <div className="min-h-screen bg-pink-50 px-6 py-8">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-6 flex items-center justify-between rounded-3xl bg-white p-6 shadow">
                        <div>
                            <h1 className="text-3xl font-bold text-pink-700">
                                Data Mahasiswa
                            </h1>

                            <p className="mt-1 text-gray-500">
                                Sistem pengelolaan data akademik
                            </p>
                        </div>

                        <button
                            type="button"
                            onClick={() => router.post('/logout')}
                            className="rounded-xl bg-red-100 px-4 py-2 font-semibold text-red-700 hover:bg-red-200"
                        >
                            Logout
                        </button>
                    </div>

                    {flash.success && (
                        <div className="mb-4 rounded-xl bg-green-100 p-4 text-green-700">
                            {flash.success}
                        </div>
                    )}

                    {flash.error && (
                        <div className="mb-4 rounded-xl bg-red-100 p-4 text-red-700">
                            {flash.error}
                        </div>
                    )}

                    <div className="grid gap-6 lg:grid-cols-3">
                        <form
                            onSubmit={submit}
                            className="rounded-3xl bg-white p-6 shadow"
                        >
                            <h2 className="mb-5 text-2xl font-bold text-pink-700">
                                {editingId ? 'Edit Mahasiswa' : 'Input Mahasiswa'}
                            </h2>

                            <Input
                                label="NIM"
                                value={form.nim}
                                error={errors.nim}
                                placeholder="Contoh: 202401001"
                                onChange={(value) => setForm({ ...form, nim: value })}
                            />

                            <Input
                                label="Nama"
                                value={form.nama}
                                error={errors.nama}
                                placeholder="Contoh: Nama Mahasiswa"
                                onChange={(value) => setForm({ ...form, nama: value })}
                            />

                            <Input
                                label="Email"
                                value={form.email}
                                error={errors.email}
                                placeholder="Contoh: namamahasiswa@gmail.com"
                                onChange={(value) => setForm({ ...form, email: value })}
                            />

                            <Input
                                label="Jurusan"
                                value={form.jurusan}
                                error={errors.jurusan}
                                placeholder="Contoh: Teknik Informatika"
                                onChange={(value) => setForm({ ...form, jurusan: value })}
                            />

                            <Input
                                label="Angkatan"
                                value={form.angkatan}
                                error={errors.angkatan}
                                placeholder="Contoh: 2024"
                                onChange={(value) => setForm({ ...form, angkatan: value })}
                            />

                            <Input
                                label="IPK"
                                value={form.ipk}
                                error={errors.ipk}
                                placeholder="Contoh: 3.75"
                                onChange={(value) => setForm({ ...form, ipk: value })}
                            />

                            <div className="mt-5 flex gap-3">
                                <button
                                    type="submit"
                                    className="rounded-xl bg-pink-600 px-5 py-3 font-semibold text-white hover:bg-pink-700"
                                >
                                    {editingId ? 'Update' : 'Simpan'}
                                </button>

                                {editingId && (
                                    <button
                                        type="button"
                                        onClick={resetForm}
                                        className="rounded-xl bg-gray-200 px-5 py-3 font-semibold text-gray-700"
                                    >
                                        Batal
                                    </button>
                                )}
                            </div>
                        </form>

                        <div className="lg:col-span-2">
                            <div className="mb-6 rounded-3xl bg-white p-6 shadow">
                                <div className="mb-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <h2 className="text-2xl font-bold text-pink-700">
                                            Data Mahasiswa
                                        </h2>

                                        <p className="text-sm text-gray-500">
                                            Kelola pencarian, sorting, backup, dan data mahasiswa.
                                        </p>
                                    </div>

                                    <div className="flex gap-3">
                                        <button
                                            type="button"
                                            onClick={() =>
                                                window.scrollTo({
                                                    top: 0,
                                                    behavior: 'smooth',
                                                })
                                            }
                                            className="rounded-xl bg-pink-600 px-4 py-3 text-sm font-semibold text-white"
                                        >
                                            + Tambah Mahasiswa
                                        </button>

                                        <button
                                            type="button"
                                            onClick={backupData}
                                            className="rounded-xl bg-green-100 px-4 py-3 text-sm font-semibold text-green-700"
                                        >
                                            Backup
                                        </button>
                                    </div>
                                </div>

                                <form onSubmit={applyFilter}>
                                    <div className="grid gap-4 md:grid-cols-4">
                                        <input
                                            value={query}
                                            onChange={(e) => setQuery(e.target.value)}
                                            placeholder="Cari data..."
                                            className="rounded-xl border border-pink-200 px-4 py-3 outline-none"
                                        />

                                        <select
                                            value={search}
                                            onChange={(e) => setSearch(e.target.value)}
                                            className="rounded-xl border border-pink-200 px-4 py-3 outline-none"
                                        >
                                            <option value="linear">Linear Search</option>
                                            <option value="sequential">Sequential Search</option>
                                            <option value="binary">Binary Search</option>
                                        </select>

                                        <select
                                            value={sort}
                                            onChange={(e) => setSort(e.target.value)}
                                            className="rounded-xl border border-pink-200 px-4 py-3 outline-none"
                                        >
                                            <option value="nim">NIM</option>
                                            <option value="nama">Nama</option>
                                            <option value="jurusan">Jurusan</option>
                                            <option value="angkatan">Angkatan</option>
                                            <option value="ipk">IPK</option>
                                        </select>

                                        <select
                                            value={method}
                                            onChange={(e) => setMethod(e.target.value)}
                                            className="rounded-xl border border-pink-200 px-4 py-3 outline-none"
                                        >
                                            <option value="merge">Merge Sort</option>
                                            <option value="bubble">Bubble Sort</option>
                                            <option value="insertion">Insertion Sort</option>
                                            <option value="shell">Shell Sort</option>
                                        </select>
                                    </div>

                                    <button
                                        type="submit"
                                        className="mt-4 rounded-xl bg-pink-600 px-5 py-3 font-semibold text-white"
                                    >
                                        Terapkan
                                    </button>
                                </form>

                                {operationNotice && (
                                    <div className="mt-5 rounded-2xl border border-pink-200 bg-pink-50 p-5 text-sm text-pink-900">
                                        <h3 className="font-bold text-pink-700">
                                            ⏱️ {operationNotice.title}
                                        </h3>

                                        <p className="mt-1">
                                            {operationNotice.description}
                                        </p>

                                        <p className="mt-2 font-semibold text-pink-700">
                                            Waktu eksekusi: {operationNotice.time} detik
                                        </p>

                                        <p className="mt-1 text-xs text-pink-600">
                                            Time Complexity:{' '}
                                            <strong>{operationNotice.complexity}</strong>
                                        </p>
                                    </div>
                                )}
                            </div>

                            <div className="overflow-hidden rounded-3xl bg-white shadow">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-left text-sm">
                                        <thead className="bg-pink-100 text-pink-800">
                                            <tr>
                                                <th className="p-4">NIM</th>
                                                <th className="p-4">Nama</th>
                                                <th className="p-4">Jurusan</th>
                                                <th className="p-4">IPK</th>
                                                <th className="p-4">Aksi</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            {students.length === 0 ? (
                                                <tr>
                                                    <td
                                                        colSpan="5"
                                                        className="p-6 text-center text-gray-500"
                                                    >
                                                        Belum ada data mahasiswa.
                                                    </td>
                                                </tr>
                                            ) : (
                                                students.map((student) => (
                                                    <tr
                                                        key={student.id}
                                                        className="border-t hover:bg-pink-50"
                                                    >
                                                        <td className="p-4">{student.nim}</td>
                                                        <td className="p-4 font-semibold text-pink-800">
                                                            {student.nama}
                                                        </td>
                                                        <td className="p-4">{student.jurusan}</td>
                                                        <td className="p-4">{student.ipk}</td>
                                                        <td className="p-4">
                                                            <button
                                                                type="button"
                                                                onClick={() => editStudent(student)}
                                                                className="mr-2 rounded-lg bg-yellow-100 px-3 py-2 text-yellow-700"
                                                            >
                                                                Edit
                                                            </button>

                                                            <button
                                                                type="button"
                                                                onClick={() => deleteStudent(student.id)}
                                                                className="rounded-lg bg-red-100 px-3 py-2 text-red-700"
                                                            >
                                                                Hapus
                                                            </button>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

function Input({ label, value, error, onChange, placeholder }) {
    return (
        <div className="mb-4">
            <label className="mb-2 block font-semibold text-pink-700">
                {label}
            </label>

            <input
                value={value}
                onChange={(e) => onChange(e.target.value)}
                placeholder={placeholder}
                className="w-full rounded-xl border border-pink-200 px-4 py-3 outline-none"
            />

            {error && (
                <p className="mt-1 text-sm font-semibold text-red-500">
                    {error}
                </p>
            )}
        </div>
    );
}