@extends('layouts.app')

@section('title', 'Profil Saya')

@php
    $user = auth()->user();
    $transactionCount = $user->transactions()->count();
    $savingGoalsCount = $user->savingGoals()->count();
    
    // Use calculated balance (single source of truth)
    $totalIncome = $user->transactions()->where('type', 'income')->sum('amount');
    $totalExpense = $user->transactions()->where('type', 'expense')->sum('amount');
    $balance = $totalIncome - $totalExpense;
@endphp

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="w-7 h-7 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Profil Saya
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola informasi akun dan keamananmu</p>
        </div>
    </div>

    {{-- Profile Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Update Profile Information --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                @include('profile.partials.update-profile-information-form')
            </div>

            {{-- Update Password --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                @include('profile.partials.update-password-form')
            </div>

            {{-- Delete Account --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                @include('profile.partials.delete-user-form')
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Profile Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 text-center">
                <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <span class="text-4xl text-white font-bold">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Member sejak</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('d M Y') }}</p>
                </div>
            </div>

            {{-- Financial Summary --}}
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl p-6 text-white shadow-lg">
                <h4 class="text-sm font-semibold mb-4 flex items-center">
                    <span class="text-lg mr-2">💰</span>
                    Ringkasan Keuangan
                </h4>
                <div class="text-center mb-4">
                    <p class="text-emerald-100 text-xs uppercase tracking-wider mb-1">Saldo Saat Ini</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($balance, 0, ',', '.') }}</p>
                </div>
                <a href="{{ route('dashboard') }}" 
                   class="block w-full text-center py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                    Lihat Dashboard →
                </a>
            </div>

            {{-- Quick Stats --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Statistik Akun</h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">💳</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Total Transaksi</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                            {{ $transactionCount }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🎯</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Target Tabungan</span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                            {{ $savingGoalsCount }}
                        </span>
                    </div>
                </div>
                
                @if($transactionCount == 0 && $savingGoalsCount == 0)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Belum ada aktivitas</p>
                        <a href="{{ route('transactions.create-income') }}" 
                           class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline font-medium">
                            + Catat transaksi pertamamu
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
