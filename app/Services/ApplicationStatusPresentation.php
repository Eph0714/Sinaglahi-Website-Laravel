<?php

namespace App\Services;

use App\Models\MembershipApplication;

/**
 * Applicant-facing label/description/badge-class for each application
 * status, and the visual timeline steps - mirrors
 * Sinaglahi.Web.Services.ApplicationStatusPresentation.
 */
class ApplicationStatusPresentation
{
    public static function label(int $status): string
    {
        return match ($status) {
            MembershipApplication::STATUS_SUBMITTED, MembershipApplication::STATUS_PENDING_REVIEW => 'Submitted',
            MembershipApplication::STATUS_UNDER_REVIEW => 'Under Review',
            MembershipApplication::STATUS_ADDITIONAL_INFO_REQUIRED => 'For Revision',
            MembershipApplication::STATUS_APPROVED => 'Approved',
            MembershipApplication::STATUS_REJECTED => 'Rejected',
            MembershipApplication::STATUS_WITHDRAWN => 'Withdrawn',
            default => 'Unknown',
        };
    }

    public static function description(int $status): string
    {
        return match ($status) {
            MembershipApplication::STATUS_SUBMITTED, MembershipApplication::STATUS_PENDING_REVIEW => 'Your application has been successfully received by Sinaglahi Artists and is waiting for review.',
            MembershipApplication::STATUS_UNDER_REVIEW => 'Your application is currently being reviewed by the Sinaglahi Artists administration.',
            MembershipApplication::STATUS_ADDITIONAL_INFO_REQUIRED => "Additional information or corrections are required. Please review the administrator's remarks and follow the instructions provided.",
            MembershipApplication::STATUS_APPROVED => 'Congratulations! Your membership application has been approved. Welcome to Sinaglahi Artists.',
            MembershipApplication::STATUS_REJECTED => "Your membership application was not approved. Please review the administrator's remarks for additional information.",
            MembershipApplication::STATUS_WITHDRAWN => 'This application has been withdrawn and is no longer being reviewed.',
            default => '',
        };
    }

    public static function badgeClass(int $status): string
    {
        return match ($status) {
            MembershipApplication::STATUS_SUBMITTED, MembershipApplication::STATUS_PENDING_REVIEW => 'status-badge-submitted',
            MembershipApplication::STATUS_UNDER_REVIEW => 'status-badge-review',
            MembershipApplication::STATUS_ADDITIONAL_INFO_REQUIRED => 'status-badge-revision',
            MembershipApplication::STATUS_APPROVED => 'status-badge-approved',
            MembershipApplication::STATUS_REJECTED => 'status-badge-rejected',
            default => 'status-badge-withdrawn',
        };
    }

    /**
     * The 3-4 node visual progress tracker for a given status.
     *
     * @return list<array{label: string, state: string}>
     */
    public static function buildTimeline(int $status): array
    {
        return match ($status) {
            MembershipApplication::STATUS_SUBMITTED, MembershipApplication::STATUS_PENDING_REVIEW => [
                ['label' => 'Submitted', 'state' => 'current'],
                ['label' => 'Under Review', 'state' => 'upcoming'],
                ['label' => 'Approved', 'state' => 'upcoming'],
            ],
            MembershipApplication::STATUS_UNDER_REVIEW => [
                ['label' => 'Submitted', 'state' => 'done'],
                ['label' => 'Under Review', 'state' => 'current'],
                ['label' => 'Approved', 'state' => 'upcoming'],
            ],
            MembershipApplication::STATUS_ADDITIONAL_INFO_REQUIRED => [
                ['label' => 'Submitted', 'state' => 'done'],
                ['label' => 'Under Review', 'state' => 'done'],
                ['label' => 'For Revision', 'state' => 'current'],
                ['label' => 'Approved', 'state' => 'upcoming'],
            ],
            MembershipApplication::STATUS_APPROVED => [
                ['label' => 'Submitted', 'state' => 'done'],
                ['label' => 'Under Review', 'state' => 'done'],
                ['label' => 'Approved', 'state' => 'done'],
            ],
            MembershipApplication::STATUS_REJECTED => [
                ['label' => 'Submitted', 'state' => 'done'],
                ['label' => 'Under Review', 'state' => 'done'],
                ['label' => 'Rejected', 'state' => 'rejected'],
            ],
            default => [],
        };
    }
}
