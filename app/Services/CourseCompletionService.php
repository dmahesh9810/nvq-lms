<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;

/**
 * CourseCompletionService
 * 
 * NOTE: This service is now a wrapper around CertificateService to maintain
 * backward compatibility while ensuring a single source of truth for competency.
 */
class CourseCompletionService
{
    /**
     * Proxies to CertificateService to check eligibility.
     */
    public function isCompetent(User $user, Course $course): bool
    {
        $status = app(CertificateService::class)->getEligibilityStatus($user, $course);
        return $status['is_eligible'];
    }

    /**
     * Proxies to CertificateService to award certificate.
     */
    public function awardCertificateIfEligible(User $user, Course $course): ?Certificate
    {
        return app(CertificateService::class)->checkAndIssueCertificate($user, $course);
    }
}
