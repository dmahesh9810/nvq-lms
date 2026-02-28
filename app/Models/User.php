<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Role constants for consistent usage across the app
    const ROLE_ADMIN = 'admin';
    const ROLE_INSTRUCTOR = 'instructor';
    const ROLE_ASSESSOR = 'assessor';
    const ROLE_STUDENT = 'student';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // -------------------------------------------------------------------------
    // Role Helper Methods
    // -------------------------------------------------------------------------

    /** Check if the user has a specific role */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isInstructor(): bool
    {
        return $this->role === self::ROLE_INSTRUCTOR;
    }

    public function isAssessor(): bool
    {
        return $this->role === self::ROLE_ASSESSOR;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    /** Get the redirect route name for this user's role */
    public function dashboardRoute(): string
    {
        return match ($this->role) {
                self::ROLE_ADMIN => 'admin.dashboard',
                self::ROLE_INSTRUCTOR => 'instructor.dashboard',
                self::ROLE_ASSESSOR => 'assessor.dashboard',
                default => 'student.dashboard',
            };
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /** Courses this instructor has created */
    public function instructedCourses()
    {
        return $this->hasMany(Course::class , 'instructor_id');
    }

    /** Courses this student is enrolled in (via pivot table) */
    public function enrolledCourses()
    {
        return $this->belongsToMany(
            Course::class ,
            'student_enrollments',
            'user_id',
            'course_id'
        )->withPivot('status', 'enrolled_at')->withTimestamps();
    }

    /** Lesson progress records for this student */
    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    /** Enrollment records */
    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }
}
