<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CRM\CompanyController;
use App\Http\Controllers\CRM\CustomerController;
use App\Http\Controllers\CRM\DealController;
use App\Http\Controllers\CRM\LeadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Files\DocumentController;
use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Work\CalendarEventController;
use App\Http\Controllers\Work\ProjectController;
use App\Http\Controllers\Work\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Every route here is prefixed with /api automatically (see bootstrap/app.php).
| Public auth routes are unguarded; everything else requires a valid
| Sanctum token (auth:sanctum). This file grows as each module's
| controller is built — see the CONTINUE FROM HERE log for what's next.
*/

// ---------- Public auth routes ----------
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ---------- Authenticated routes ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // Leads
    Route::get('/leads', [LeadController::class, 'index']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::get('/leads/{lead}', [LeadController::class, 'show']);
    Route::put('/leads/{lead}', [LeadController::class, 'update']);
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy']);
    Route::post('/leads/bulk-assign', [LeadController::class, 'bulkAssign']);
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert']);

    // Customers
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);
    Route::put('/customers/{customer}', [CustomerController::class, 'update']);
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);

    // Companies
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::post('/companies', [CompanyController::class, 'store']);
    Route::get('/companies/{company}', [CompanyController::class, 'show']);
    Route::put('/companies/{company}', [CompanyController::class, 'update']);
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy']);

    // Deals / Pipeline
    Route::get('/deals', [DealController::class, 'index']);
    Route::post('/deals', [DealController::class, 'store']);
    Route::get('/deals/{deal}', [DealController::class, 'show']);
    Route::put('/deals/{deal}', [DealController::class, 'update']);
    Route::patch('/deals/{deal}/stage', [DealController::class, 'updateStage']);
    Route::delete('/deals/{deal}', [DealController::class, 'destroy']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    // Calendar
    Route::get('/calendar', [CalendarEventController::class, 'index']);
    Route::post('/calendar', [CalendarEventController::class, 'store']);
    Route::get('/calendar/{calendarEvent}', [CalendarEventController::class, 'show']);
    Route::put('/calendar/{calendarEvent}', [CalendarEventController::class, 'update']);
    Route::delete('/calendar/{calendarEvent}', [CalendarEventController::class, 'destroy']);

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf']);

    // Documents
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::patch('/documents/{document}', [DocumentController::class, 'update']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);

    // Reports
    Route::get('/reports', [ReportController::class, 'catalog']);
    Route::get('/reports/saved', [ReportController::class, 'saved']);
    Route::post('/reports', [ReportController::class, 'save']);
    Route::get('/reports/{type}/export', [ReportController::class, 'exportExcel']);

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Profile (self-service, distinct from Admin's user management)
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);

    // Administration (user & role management)
    Route::get('/users', [AdminController::class, 'index']);
    Route::post('/users/invite', [AdminController::class, 'invite']);
    Route::patch('/users/{user}/role', [AdminController::class, 'updateRole']);
    Route::patch('/users/{user}/status', [AdminController::class, 'updateStatus']);
    Route::get('/roles', [AdminController::class, 'roles']);
});
