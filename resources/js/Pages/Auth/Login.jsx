import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import { FaUserGraduate } from 'react-icons/fa';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
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

        post('/login', {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Login" />

            <div
                className="relative flex min-h-screen items-center justify-center bg-cover bg-center px-6 py-10"
                style={{
                    backgroundImage: "url('/images/sakura.jpg')",
                }}
            >
                <div className="absolute inset-0 bg-slate-950/50"></div>

                <div className="relative z-10 w-full max-w-md">
                    <div className="mb-6 text-center">
                        <div className="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-3xl bg-white/20 shadow-xl backdrop-blur">
                            <FaUserGraduate className="text-5xl text-white" />
                        </div>

                        <h1 className="text-6xl font-bold tracking-tight text-white drop-shadow-lg">
                            SIMAK
                        </h1>

                        <p className="mt-3 text-pink-100 drop-shadow">
                            Sistem Informasi Mahasiswa Akademik
                        </p>
                    </div>

                    {status && (
                        <div className="mb-4 rounded-xl bg-green-100 p-3 text-green-700 shadow">
                            {status}
                        </div>
                    )}

                    <div className="rounded-3xl border border-white/60 bg-white/90 p-8 shadow-2xl backdrop-blur-md">
                        <div className="mb-8 text-center">
                            <h2 className="text-3xl font-bold text-slate-800">
                                Login
                            </h2>

                            <p className="mt-2 text-gray-600">
                                Masuk untuk mengelola data mahasiswa
                            </p>
                        </div>

                        <form onSubmit={submit}>
                            <div>
                                <label
                                    htmlFor="email"
                                    className="mb-2 block font-semibold text-slate-700"
                                >
                                    Email
                                </label>

                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={data.email}
                                    autoComplete="username"
                                    autoFocus
                                    onChange={(e) =>
                                        setData('email', e.target.value)
                                    }
                                    className="block w-full rounded-xl border border-pink-200 bg-white px-4 py-3 outline-none focus:border-pink-400 focus:ring-2 focus:ring-pink-300"
                                />

                                {errors.email && (
                                    <p className="mt-2 text-sm font-semibold text-red-500">
                                        {errors.email}
                                    </p>
                                )}
                            </div>

                            <div className="mt-5">
                                <label
                                    htmlFor="password"
                                    className="mb-2 block font-semibold text-slate-700"
                                >
                                    Password
                                </label>

                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    value={data.password}
                                    autoComplete="current-password"
                                    onChange={(e) =>
                                        setData('password', e.target.value)
                                    }
                                    className="block w-full rounded-xl border border-pink-200 bg-white px-4 py-3 outline-none focus:border-pink-400 focus:ring-2 focus:ring-pink-300"
                                />

                                {errors.password && (
                                    <p className="mt-2 text-sm font-semibold text-red-500">
                                        {errors.password}
                                    </p>
                                )}
                            </div>

                            <div className="mt-5 flex items-center">
                                <input
                                    id="remember"
                                    type="checkbox"
                                    name="remember"
                                    checked={data.remember}
                                    onChange={(e) =>
                                        setData('remember', e.target.checked)
                                    }
                                    className="rounded border-pink-300 text-pink-600 focus:ring-pink-500"
                                />

                                <label
                                    htmlFor="remember"
                                    className="ms-2 text-sm text-gray-600"
                                >
                                    Ingat saya
                                </label>
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="mt-6 w-full rounded-xl bg-pink-600 py-3 font-semibold text-white transition hover:bg-pink-700 disabled:opacity-60"
                            >
                                {processing ? 'Memproses...' : 'Login'}
                            </button>

                            <div className="mt-5 flex items-center justify-between">
                                {canResetPassword && (
                                    <Link
                                        href="/forgot-password"
                                        className="text-sm text-pink-600 hover:text-pink-800"
                                    >
                                        Lupa password?
                                    </Link>
                                )}

                                <Link
                                    href="/register"
                                    className="text-sm font-semibold text-pink-600 hover:text-pink-800"
                                >
                                    Buat akun
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </>
    );
}