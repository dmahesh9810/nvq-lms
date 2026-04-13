<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\KnowledgeTrackerController;

// Public Routes (No authentication required)
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Require Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/profile', [AuthController::class, 'profile']);
    
    // Learning Materials
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::get('/lessons/{id}/materials', [CourseController::class, 'lessonMaterials']);
    
    // Assessments: Quizzes
    Route::get('/quizzes/{id}', [QuizController::class, 'show']);
    Route::post('/quizzes/{id}/submit', [QuizController::class, 'submit']);
    
    // Assessments: Assignments
    Route::get('/assignments/{id}', [AssignmentController::class, 'show']);
    Route::post('/assignments/{id}/upload', [AssignmentController::class, 'submit']);
    Route::post('/assignments/submissions/{id}/mark', [AssignmentController::class, 'markCriteria']);
    
    // Knowledge Tracking System
    Route::get('/student/knowledge-radar', [KnowledgeTrackerController::class, 'radarChart']);
    Route::get('/student/weaknesses', [KnowledgeTrackerController::class, 'weaknesses']);
    Route::get('/student/recommendations', [KnowledgeTrackerController::class, 'recommendations']);

});
