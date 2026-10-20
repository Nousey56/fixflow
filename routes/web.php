<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaintenanceCalendarController;
use App\Http\Controllers\MaintenanceAnalyticsController;
use App\Http\Controllers\TechnicianWorkloadController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\WorkOrderScheduleController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\WorkOrderAssignmentController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TechnicianDashboardController;
use App\Http\Controllers\JobWorkflowController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest')
    ->name('login.submit');

// Public, rate-limited recruiter demo login.
// The controller verifies an active demo_viewer membership.
Route::post('/demo-login', [LoginController::class, 'demoLogin'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('demo.login');

Route::middleware(['auth', \App\Http\Middleware\EnsureDemoViewerReadOnly::class])->group(function () {

    // Maintenance Operations Calendar

    Route::get('/calendar', [MaintenanceCalendarController::class, 'index'])
        ->name('calendar.index');

    // Technician Workload Management

    Route::get('/workload', [TechnicianWorkloadController::class, 'index'])
        ->name('workload.index');

    // Maintenance Analytics & Reporting

    Route::get('/analytics', [MaintenanceAnalyticsController::class, 'index'])
        ->name('analytics.index');

    // Authentication

    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    // Maintenance Requests

    Route::get('/maintenance', [MaintenanceRequestController::class, 'index'])
        ->name('maintenance.index');

    Route::get('/maintenance/create', [MaintenanceRequestController::class, 'create'])
        ->name('maintenance.create');

    Route::post('/maintenance', [MaintenanceRequestController::class, 'store'])
        ->name('maintenance.store');

    Route::get('/maintenance/{maintenanceRequest}', [MaintenanceRequestController::class, 'show'])
        ->name('maintenance.show');

    Route::patch('/maintenance/{maintenanceRequest}', [MaintenanceRequestController::class, 'update'])
        ->name('maintenance.update');

    // Work Order Scheduling

    Route::patch(
        '/maintenance/{maintenanceRequest}/schedule',
        [WorkOrderScheduleController::class, 'update']
    )->name('maintenance.schedule.update');

    // Technician Dispatch Board

    Route::get('/dispatch', [DispatchController::class, 'index'])
        ->name('dispatch.index');

    // Direct Technician Assignment

    Route::patch(
        '/maintenance/{maintenanceRequest}/assignment',
        [WorkOrderAssignmentController::class, 'update']
    )->name('maintenance.assignment.update');

    // Team Management

    Route::get('/team', [TeamController::class, 'index'])
        ->name('team.index');

    Route::post('/team', [TeamController::class, 'store'])
        ->name('team.store');

    Route::patch('/team/{membership}', [TeamController::class, 'update'])
        ->name('team.update');

    Route::delete('/team/{membership}', [TeamController::class, 'destroy'])
        ->name('team.destroy');

    // Technician Dashboard

    Route::get('/technician', [TechnicianDashboardController::class, 'index'])
        ->name('technician.index');

    Route::post(
        '/technician/jobs/{maintenanceRequest}/start',
        [JobWorkflowController::class, 'start']
    )->name('technician.jobs.start');

    Route::post(
        '/technician/jobs/{maintenanceRequest}/complete',
        [JobWorkflowController::class, 'complete']
    )->name('technician.jobs.complete');

    // Property Management

    Route::get('/properties', [PropertyController::class, 'index'])
        ->name('properties.index');

    Route::get('/properties/create', [PropertyController::class, 'create'])
        ->name('properties.create');

    Route::post('/properties', [PropertyController::class, 'store'])
        ->name('properties.store');

    Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])
        ->name('properties.edit');

    Route::patch('/properties/{property}', [PropertyController::class, 'update'])
        ->name('properties.update');

    // Property Archiving and Restoration

    Route::patch(
        '/properties/{property}/archive',
        [PropertyController::class, 'archive']
    )->name('properties.archive');

    Route::patch(
        '/properties/{property}/restore',
        [PropertyController::class, 'restore']
    )->name('properties.restore');

    // Unit Management

    Route::get(
        '/properties/{property}/units/create',
        [UnitController::class, 'create']
    )->name('units.create');

    Route::post(
        '/properties/{property}/units',
        [UnitController::class, 'store']
    )->name('units.store');

    Route::get(
        '/properties/{property}/units/{unit}/edit',
        [UnitController::class, 'edit']
    )->name('units.edit');

    Route::patch(
        '/properties/{property}/units/{unit}',
        [UnitController::class, 'update']
    )->name('units.update');

    // Property Details

    Route::get('/properties/{property}', [PropertyController::class, 'show'])
        ->name('properties.show');
});
