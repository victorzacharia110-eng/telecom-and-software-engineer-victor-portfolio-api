<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── CSRF Cookie Route ────────────────────────────────────────────────────
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

// ── Public ────────────────────────────────────────────────────────────────
Route::get('/certificates', [CertificateController::class, 'index']);
Route::middleware(['throttle:60,1'])->group(function () {

    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::get('/featured', [ProjectController::class, 'featured']);
        Route::get('/categories', [ProjectController::class, 'categories']);
        Route::get('/{project}', [ProjectController::class, 'show']);
    });

    Route::middleware('throttle:5,1')
         ->post('/contact', [ContactController::class, 'store']);

    Route::get('/testimonials/featured', [TestimonialController::class, 'featured']);
    Route::get('/projects/{project}/testimonials', [TestimonialController::class, 'forProject']);
});

// ── AUTH ROUTES - Under /auth prefix ────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});

// ── Client Routes (Sanctum protected) ────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('client')->group(function () {
    Route::get('/dashboard/stats', [ClientController::class, 'stats']);
    Route::get('/projects', [ClientController::class, 'projects']);
    Route::get('/projects/{project}', [ClientController::class, 'showProject']);
    Route::get('/messages', [ClientController::class, 'messages']);
    Route::get('/messages/{message}', [ClientController::class, 'showMessage']);
    Route::post('/messages', [ClientController::class, 'sendMessage']);
    Route::get('/profile', [ClientController::class, 'profile']);
    Route::patch('/profile', [ClientController::class, 'updateProfile']);
});

// ── Admin (Sanctum protected) ─────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:120,1'])->prefix('admin')->group(function () {
    
    // ── Profile ──────────────────────────────────────────────────────────
    Route::get('/profile', [AdminController::class, 'profile']);
    Route::patch('/profile', [AdminController::class, 'updateProfile']);
    
    // ── Dashboard ──────────────────────────────────────────────────────────
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/enquiries', [AdminController::class, 'enquiries']);
        Route::get('/categories', [AdminController::class, 'categories']);
        Route::get('/messages', [AdminController::class, 'messages']);
        Route::get('/system', [AdminController::class, 'system']);
    });

    // ── Contacts ──────────────────────────────────────────────────────────
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::get('/contacts/{contact}', [ContactController::class, 'show']);
    Route::patch('/contacts/{contact}/read', [ContactController::class, 'markRead']);
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy']);

    // ── Projects ──────────────────────────────────────────────────────────
    Route::get('/projects', [ProjectController::class, 'adminIndex']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::patch('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    // ── Testimonials ──────────────────────────────────────────────────────
    Route::get('/testimonials', [TestimonialController::class, 'index']);
    Route::post('/testimonials', [TestimonialController::class, 'store']);
    Route::get('/testimonials/{testimonial}', [TestimonialController::class, 'show']);
    Route::patch('/testimonials/{testimonial}', [TestimonialController::class, 'update']);
    Route::patch('/testimonials/{testimonial}/featured', [TestimonialController::class, 'toggleFeatured']);
    Route::delete('/testimonials/{testimonial}', [TestimonialController::class, 'destroy']);

    // ── Certificates ──────────────────────────────────────────────────────
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::post('/certificates', [CertificateController::class, 'store']);
    Route::get('/certificates/{certificate}', [CertificateController::class, 'show']);
    Route::patch('/certificates/{certificate}', [CertificateController::class, 'update']);
    Route::delete('/certificates/{certificate}', [CertificateController::class, 'destroy']);

    // ── Users ─────────────────────────────────────────────────────────────
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::patch('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});