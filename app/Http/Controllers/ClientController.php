<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Menampilkan daftar semua klien
     */
    public function index()
    {
        // Menampilkan data klien diurutkan dari yang terbaru
        $clients = Client::orderBy('created_at', 'desc')->get();
        
        // Sesuaikan dengan nama view kamu, misalnya 'admin.clients.index'
        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Menyimpan data klien baru ke database
     */
    public function store(Request $request)
    {
        // Proteksi: Hanya manajemen yang bisa menambah data klien secara manual
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Anda tidak memiliki otoritas untuk menambah data klien.');
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $client = Client::create($validated);

        // JEJAK DIGITAL: Catat aktivitas penambahan klien
        ActivityLog::record(
            'Tambah Klien', 
            "Menambahkan entitas klien baru ke direktori: {$client->name}"
        );

        return back()->with('success', 'Data klien berhasil ditambahkan ke direktori.');
    }

    /**
     * Menampilkan detail spesifik satu klien
     */
    public function show(Client $client)
    {
        return view('admin.clients.show', compact('client'));
    }

    /**
     * Memperbarui data klien yang sudah ada
     */
    public function update(Request $request, Client $client)
    {
        // Proteksi: Hanya manajemen yang bisa mengubah data klien
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Anda tidak memiliki otoritas untuk mengedit data klien.');
        }

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $oldName = $client->name; // Simpan nama lama untuk keperluan log
        $client->update($validated);

        // JEJAK DIGITAL: Catat aktivitas pembaruan
        ActivityLog::record(
            'Edit Klien', 
            "Memperbarui informasi kontak/identitas untuk klien: {$oldName}"
        );

        return back()->with('success', 'Informasi klien berhasil diperbarui.');
    }

    /**
     * Menghapus data klien dari sistem
     */
    public function destroy(Client $client)
    {
        // Proteksi: Hanya manajemen yang bisa menghapus data klien
        if (!auth()->user()->hasRole(['Founder', 'Co-Founder', 'HR'])) {
            abort(403, 'Anda tidak memiliki otoritas untuk menghapus data klien.');
        }

        $clientName = $client->name; // Simpan nama sebelum dihapus untuk log
        
        $client->delete();

        // JEJAK DIGITAL: Catat aktivitas penghapusan
        ActivityLog::record(
            'Hapus Klien', 
            "Menghapus data klien '{$clientName}' dari sistem secara permanen."
        );

        return back()->with('success', 'Data klien berhasil dihapus dari sistem.');
    }
}