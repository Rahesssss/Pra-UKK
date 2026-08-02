<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staf; // Sesuaikan dengan nama Model login kamu (misal: Staf atau User)
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        // 1. Validasi Input Form
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username tidak boleh kosong!',
            'password.required' => 'Password tidak boleh kosong!',
        ]);

        // 2. Cari data akun berdasarkan username
        $staf = Staf::where('username', $request->username)->first();

        // 3. Pengecekan Aman: Jika akun TIDAK ADA atau PASSWORD SALAH
        // if (!$staf || !Hash::check($request->password, $staf->password)) {
        // Jika memakai password tanpa hash (plain text), gunakan kondisi ini:
        if (!$staf || $request->password !== $staf->password) {

            return redirect()->back()
                ->withInput($request->only('username')) // Agar username yang sudah diketik tidak hilang
                ->withErrors(['login_error' => 'Username atau password yang Anda masukkan salah!']);
        }

        // 4. Jika Username & Password Benar, Buat Session Login
        session([
            'staf_id' => $staf->id_staf ?? $staf->id,
            'nama_staf' => $staf->nama_staf ?? $staf->nama,
            'username' => $staf->username,
        ]);

        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        session()->forget(['staf_id', 'nama_staf', 'username']);
        session()->flush();

        return redirect('/admin/login')->with('success', 'Anda telah berhasil logout.');
    }
}
