import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect } from 'react';

function Logo() {
    return (
        <div className="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-3xl bg-white/20 shadow-xl backdrop-blur">
            <svg
                viewBox="0 0 64 64"
                className="h-12 w-12 text-white"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M32 10L8 22L32 34L56 22L32 10Z"
                    fill="currentColor"
                />
                <path
                    d="M18 30V42C18 48 24 52 32 52C40 52 46 48 46 42V30L32 37L18 30Z"
                    fill="currentColor"
                    opacity="0.85"
                />
                <path
                    d="M54 24V38"
                    stroke="currentColor"
                    strokeWidth="4"
                    strokeLinecap="round"
                />
            </svg>
        </div>
    );
}

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    useEffect(() => {
        window.history.pushState(null, '', window.location.href);

        const handleBack = () => {
            window.history.pushState(null, '', window.location.href);
        };

        window.addEventListener('popstate', handleBack);

        return () => {
            window.removeEventListener('popstate', handleBack);
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();

        post('/register', {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <>
            <Head title="Register" />

            <div
                className="relative flex min-h-screen items-center justify-center bg-cover bg-center px-6 py-10"
                style={{
                    backgroundImage: "url('/images/sakura.jpg')",
                }}
            >
                <div className="absolute inset-0 bg-slate-950/50"></div>

                <div className="relative z-10 w-full max-w-md">
                    <div className="mb-6 text-center">
                        <Logo />

                        <h1 className="text-6xl font-bold tracking-tight text-white drop-shadow-lg">
                            SIMAK
                        </h1>

                        <p className="mt-3 text-pink-100 drop-shadow">
                            Sistem Informasi Mahasiswa Akademik
                        </p>
                    </div>

                    <div className="rounded-3xl border border-white/60 bg-white/90 p-8 shadow-2xl backdrop-blur-md">
                        <div className="mb-8 text-center">
                            <h2 className="text-3xl font-bold text-slate-800">
                                Register
                            </h2>

                            <p className="mt-2 text-gray-600">
                                Buat akun untuk mulai mengelola data mahasiswa
                            </p>
                        </div>

                        <form onSubmit={submit}>
                            <Input
                                label="Nama"
                                type="text"
                                value={data.name}
                                error={errors.name}
                                placeholder="Contoh: Andi Pratama"
                                onChange={(value) => setData('name', value)}
                            />

                            <Input
                                label="Email"
                                type="email"
                                value={data.email}
                                error={errors.email}
                                placeholder="Contoh: andi@email.com"
                                onChange={(value) => setData('email', value)}
                            />

                            <Input
                                label="Password"
                                type="password"
                                value={data.password}
                                error={errors.password}
                                placeholder="Masukkan password"
                                onChange={(value) => setData('password', value)}
                            />

                            <Input
                                label="Konfirmasi Password"
                                type="password"
                                value={data.password_confirmation}
                                error={errors.password_confirmation}
                                placeholder="Ulangi password"
                                onChange={(value) =>
                                    setData('password_confirmation', value)
                                }
                            />

                            <button
                                type="submit"
                                disabled={processing}
                                className="mt-6 w-full rounded-xl bg-pink-600 py-3 font-semibold text-white transition hover:bg-pink-700 disabled:opacity-60"
                            >
                                {processing ? 'Memproses...' : 'Register'}
                            </button>

                            <div className="mt-5 text-center">
                                <Link
                                    href="/login"
                                    className="text-sm font-semibold text-pink-600 hover:text-pink-800"
                                >
                                    Sudah punya akun? Login
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}

function Input({ label, type, value, error, onChange, placeholder }) {
    return (
        <div className="mb-5">
            <label className="mb-2 block font-semibold text-slate-700">
                {label}
            </label>

            <input
                type={type}
                value={value}
                placeholder={placeholder}
                onChange={(e) => onChange(e.target.value)}
                className="block w-full rounded-xl border border-pink-200 bg-white px-4 py-3 outline-none focus:border-pink-400 focus:ring-2 focus:ring-pink-300"
            />

            {error && (
                <p className="mt-2 text-sm font-semibold text-red-500">
                    {error}
                </p>
            )}
        </div>
    );
}