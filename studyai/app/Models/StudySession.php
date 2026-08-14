<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudySession extends Model
{
    protected $attributes = [
        'completed_stages' => '[]',
    ];

    protected $fillable = [
        'subject',
        'content',
        'available_time',
        'study_plan',
        'completed_stages',
    ];

    protected function casts(): array
    {
        return [
            'completed_stages' => 'array',
        ];
    }

    public function studyPlanStages(): array
    {
        $structuredPlan = json_decode((string) $this->study_plan, true);

        if (is_array($structuredPlan) && isset($structuredPlan['stages'])) {
            return array_values(array_filter(
                $structuredPlan['stages'],
                fn (mixed $stage): bool => is_array($stage) && ! empty($stage['title'])
            ));
        }

        return $this->parseLegacyStudyPlan();
    }

    public function completedStageIndexes(): array
    {
        $stageCount = count($this->studyPlanStages());

        return array_values(array_unique(array_filter(
            array_map('intval', $this->completed_stages ?? []),
            fn (int $stage): bool => $stage >= 0 && $stage < $stageCount
        )));
    }

    public function nextStageIndex(): ?int
    {
        $completedStages = $this->completedStageIndexes();

        foreach (array_keys($this->studyPlanStages()) as $stageIndex) {
            if (! in_array($stageIndex, $completedStages, true)) {
                return $stageIndex;
            }
        }

        return null;
    }

    public function progressPercentage(): int
    {
        $stageCount = count($this->studyPlanStages());

        if ($stageCount === 0) {
            return 0;
        }

        return (int) round(count($this->completedStageIndexes()) / $stageCount * 100);
    }

    public function stageStatus(int $stageIndex): string
    {
        if (in_array($stageIndex, $this->completedStageIndexes(), true)) {
            return 'completed';
        }

        return $stageIndex === $this->nextStageIndex() ? 'in_progress' : 'pending';
    }

    public function completeStage(int $stageIndex): bool
    {
        $completedStages = $this->completedStageIndexes();

        if (in_array($stageIndex, $completedStages, true)) {
            return true;
        }

        if ($stageIndex !== $this->nextStageIndex()) {
            return false;
        }

        $completedStages[] = $stageIndex;
        sort($completedStages);

        $this->completed_stages = $completedStages;

        return $this->save();
    }

    private function parseLegacyStudyPlan(): array
    {
        $plan = trim((string) $this->study_plan);

        if ($plan === '') {
            return [];
        }

        preg_match_all(
            '/^[\t ]*\d+\.[\t ]+.*?(?=^[\t ]*\d+\.[\t ]+|\z)/msu',
            $plan,
            $matches
        );

        $blocks = $matches[0] ?? [];

        if (! is_array($blocks) || $blocks === []) {
            $blocks = [$plan];
        }

        return array_map(function (string $block): array {
            $lines = preg_split('/\R/u', trim($block)) ?: [];
            $heading = preg_replace('/^[\t ]*\d+\.[\t ]*/u', '', array_shift($lines) ?? '') ?? '';
            $headingParts = preg_split('/\s+[—–-]\s+/u', $heading, 2) ?: [];

            return [
                'title' => trim($headingParts[0] ?? $heading),
                'duration' => trim($headingParts[1] ?? ''),
                'content' => trim(implode("\n", $lines)),
                'explanation' => '',
            ];
        }, $blocks);
    }
}
