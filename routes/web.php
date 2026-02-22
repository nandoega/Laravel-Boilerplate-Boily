<?php

use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/', function () { return view('auth.login'); });
Route::get('/login', function () { return view('auth.login'); })->name('login');

// Protected-like Routes (Protection is actually handled via JS Token on client-side)
Route::get('/dashboard', function () { return view('dashboard'); });
Route::get('/clients', function () { return view('clients.index'); });
Route::get('/projects', function () { return view('projects.index'); });
Route::get('/tasks', function () { return view('tasks.index'); });
Route::get('/teams', function () { return view('teams.index'); });
Route::get('/invoices', function () { return view('invoices.index'); });
Route::get('/payments', function () { return view('payments.index'); });
Route::get('/time-entries', function () { return view('time_entries.index'); });
