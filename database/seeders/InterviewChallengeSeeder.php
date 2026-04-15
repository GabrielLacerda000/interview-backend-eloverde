<?php

namespace Database\Seeders;

use App\Domain\Document\Models\Document;
use App\Domain\Document\Models\DocumentType;
use App\Domain\Waste\Models\CollectTask;
use App\Domain\Waste\Models\CollectTaskItem;
use App\Domain\Waste\Models\Waste;
use App\Domain\Waste\Models\WasteGenerationPoint;
use Illuminate\Database\Seeder;

class InterviewChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $environmentalLicense = DocumentType::query()->create([
            'id' => 1,
            'name' => 'Environmental License',
            'is_required' => true,
        ]);

        $operationPermit = DocumentType::query()->create([
            'id' => 2,
            'name' => 'Operation Permit',
            'is_required' => true,
        ]);

        DocumentType::query()->create([
            'id' => 3,
            'name' => 'Internal Checklist',
            'is_required' => false,
        ]);

        $plastic = Waste::query()->create([
            'id' => 1,
            'name' => 'Plastic',
            'code' => 'W-PLASTIC',
            'unit' => 'kg',
        ]);

        $paper = Waste::query()->create([
            'id' => 2,
            'name' => 'Paper',
            'code' => 'W-PAPER',
            'unit' => 'kg',
        ]);

        $glass = Waste::query()->create([
            'id' => 3,
            'name' => 'Glass',
            'code' => 'W-GLASS',
            'unit' => 'kg',
        ]);

        $eligiblePoint = $this->createWasteGenerationPoint(1, 'Eligible Point', 'WGP-001');
        $missingDocumentPoint = $this->createWasteGenerationPoint(2, 'Missing Document Point', 'WGP-002');
        $expiredDocumentPoint = $this->createWasteGenerationPoint(3, 'Expired Document Point', 'WGP-003');
        $invalidStatePoint = $this->createWasteGenerationPoint(4, 'Invalid State Point', 'WGP-004');
        $multipleBlockersPoint = $this->createWasteGenerationPoint(5, 'Multiple Blockers Point', 'WGP-005');
        $urgentEligiblePoint = $this->createWasteGenerationPoint(6, 'Urgent Eligible Point', 'WGP-006');
        $duplicatePoint = $this->createWasteGenerationPoint(7, 'Duplicate Point', 'WGP-007');
        $invalidDocumentPoint = $this->createWasteGenerationPoint(8, 'Invalid Document Point', 'WGP-008');

        $this->createValidDocumentSet($eligiblePoint->id, $environmentalLicense->id, $operationPermit->id);
        $this->createSingleRequiredDocument($missingDocumentPoint->id, $environmentalLicense->id);
        $this->createExpiredDocumentSet($expiredDocumentPoint->id, $environmentalLicense->id, $operationPermit->id);
        $this->createValidDocumentSet($invalidStatePoint->id, $environmentalLicense->id, $operationPermit->id);
        $this->createSingleRequiredDocument($multipleBlockersPoint->id, $environmentalLicense->id);
        $this->createValidDocumentSet($urgentEligiblePoint->id, $environmentalLicense->id, $operationPermit->id);
        $this->createValidDocumentSet($duplicatePoint->id, $environmentalLicense->id, $operationPermit->id);
        $this->createInvalidDocumentSet($invalidDocumentPoint->id, $environmentalLicense->id, $operationPermit->id);

        $this->createCollectTask(1, $eligiblePoint->id, '2026-04-16 08:00:00', CollectTask::STATE_PROGRAMMING, false, [
            [$plastic->id, 15],
            [$paper->id, 10],
        ]);

        $this->createCollectTask(2, $missingDocumentPoint->id, '2026-04-16 09:00:00', CollectTask::STATE_PROGRAMMING, false, [
            [$glass->id, 8],
        ]);

        $this->createCollectTask(3, $expiredDocumentPoint->id, '2026-04-16 10:00:00', CollectTask::STATE_PROGRAMMING, false, [
            [$paper->id, 12],
        ]);

        $this->createCollectTask(4, $invalidStatePoint->id, '2026-04-16 11:00:00', CollectTask::STATE_CONFIRMATION, false, [
            [$plastic->id, 7],
        ]);

        $this->createCollectTask(5, $multipleBlockersPoint->id, '2026-04-16 12:00:00', CollectTask::STATE_CONFIRMATION, false, [
            [$glass->id, 5],
        ]);

        $this->createCollectTask(6, $urgentEligiblePoint->id, '2026-04-16 07:30:00', CollectTask::STATE_PROGRAMMING, true, [
            [$plastic->id, 20],
        ]);

        $this->createCollectTask(7, $duplicatePoint->id, '2026-04-17 08:00:00', CollectTask::STATE_PROGRAMMING, false, [
            [$plastic->id, 4],
            [$paper->id, 6],
        ]);

        $this->createCollectTask(8, $duplicatePoint->id, '2026-04-17 13:00:00', CollectTask::STATE_PROGRAMMING, false, [
            [$paper->id, 6],
            [$plastic->id, 4],
        ]);

        $this->createCollectTask(9, $invalidDocumentPoint->id, '2026-04-16 07:00:00', CollectTask::STATE_PROGRAMMING, true, [
            [$glass->id, 11],
        ]);
    }

    private function createWasteGenerationPoint(int $id, string $name, string $internalCode): WasteGenerationPoint
    {
        return WasteGenerationPoint::query()->create([
            'id' => $id,
            'name' => $name,
            'internal_code' => $internalCode,
            'active' => true,
        ]);
    }

    private function createCollectTask(
        int $id,
        int $wasteGenerationPointId,
        string $scheduledTo,
        string $state,
        bool $isUrgent,
        array $items
    ): void {
        $collectTask = CollectTask::query()->create([
            'id' => $id,
            'waste_generation_point_id' => $wasteGenerationPointId,
            'scheduled_to' => $scheduledTo,
            'state' => $state,
            'is_urgent' => $isUrgent,
        ]);

        foreach ($items as [$wasteId, $expectedQuantity]) {
            CollectTaskItem::query()->create([
                'collect_task_id' => $collectTask->id,
                'waste_id' => $wasteId,
                'expected_quantity' => $expectedQuantity,
            ]);
        }
    }

    private function createValidDocumentSet(int $wasteGenerationPointId, int $firstDocumentTypeId, int $secondDocumentTypeId): void
    {
        $this->createDocument($wasteGenerationPointId, $firstDocumentTypeId, Document::STATUS_VALID, '2026-12-31');
        $this->createDocument($wasteGenerationPointId, $secondDocumentTypeId, Document::STATUS_VALID, '2026-12-31');
    }

    private function createExpiredDocumentSet(int $wasteGenerationPointId, int $firstDocumentTypeId, int $secondDocumentTypeId): void
    {
        $this->createDocument($wasteGenerationPointId, $firstDocumentTypeId, Document::STATUS_VALID, '2024-12-31');
        $this->createDocument($wasteGenerationPointId, $secondDocumentTypeId, Document::STATUS_VALID, '2026-12-31');
    }

    private function createSingleRequiredDocument(int $wasteGenerationPointId, int $documentTypeId): void
    {
        $this->createDocument($wasteGenerationPointId, $documentTypeId, Document::STATUS_VALID, '2026-12-31');
    }

    private function createInvalidDocumentSet(int $wasteGenerationPointId, int $firstDocumentTypeId, int $secondDocumentTypeId): void
    {
        $this->createDocument($wasteGenerationPointId, $firstDocumentTypeId, Document::STATUS_INVALID, '2026-12-31');
        $this->createDocument($wasteGenerationPointId, $secondDocumentTypeId, Document::STATUS_VALID, '2026-12-31');
    }

    private function createDocument(
        int $wasteGenerationPointId,
        int $documentTypeId,
        string $status,
        string $expiresAt
    ): void {
        Document::query()->create([
            'document_type_id' => $documentTypeId,
            'documentable_type' => WasteGenerationPoint::class,
            'documentable_id' => $wasteGenerationPointId,
            'status' => $status,
            'expires_at' => $expiresAt,
        ]);
    }
}
