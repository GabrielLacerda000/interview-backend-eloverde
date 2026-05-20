<?php

namespace App\Domain\Waste\Enums;

enum Blocker: string
{
    case INVALID_STATE = 'invalid_state';
    case MISSING_REQUIRED_DOCUMENTS = 'missing_required_documents';
    case EXPIRED_REQUIRED_DOCUMENTS = 'expired_required_documents';
    case INVALID_REQUIRED_DOCUMENTS = 'invalid_required_documents';
    case DUPLICATE_COLLECT_FOR_SAME_DAY = 'duplicate_collect_for_same_day';
}
