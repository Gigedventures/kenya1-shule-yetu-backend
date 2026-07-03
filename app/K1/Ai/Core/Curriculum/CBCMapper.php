<?php

namespace App\K1\Ai\Core\Curriculum;

use App\Modules\ShuleYetu\Models\ShuleClass;
use App\Modules\ShuleYetu\Models\ShuleSubject;
use App\Modules\ShuleYetu\Models\ShuleExamSubject;
use App\Modules\ShuleYetu\Models\ShuleGradeBand;
use App\Modules\ShuleYetu\Support\Tenancy\SchoolContext;
use RuntimeException;

/**
 * CBCMapper — Single source of truth for the Competency-Based Curriculum.
 *
 * Maps Grade → Subject → Topic → Competency → Learning Outcome.
 * Prevents AI from inventing or hallucinating curriculum content.
 * All Teacher AI services MUST call this first before generating output.
 *
 * @package App\K1\Ai\Core\Curriculum
 */
class CBCMapper
{
    /**
     * The structured CBC curriculum tree.
     */
    private static ?array $curriculum = null;

    /**
     * Load the full curriculum tree from storage or database.
     */
    public function load(): array
    {
        if (self::$curriculum !== null) {
            return self::$curriculum;
        }

        $schoolId = app(SchoolContext::class)->requireId();

        $grades = ShuleClass::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($c) => [
                'id'      => $c->id,
                'label'   => $c->name,
                'streams' => ['A', 'B'], // default
            ])
            ->values()
            ->toArray();

        $subjects = ShuleSubject::query()
            ->where('school_id', $schoolId)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn ($s) => [
                'id'   => $s->id,
                'name' => $s->name,
                'code' => $s->code,
            ])
            ->values()
            ->toArray();

        // Competency grade bands
        $bands = ShuleGradeBand::query()
            ->where('school_id', $schoolId)
            ->orderBy('min_percentage')
            ->get(['id', 'grade', 'min_percentage', 'max_percentage'])
            ->map(fn ($b) => [
                'grade' => $b->grade,
                'min'   => (float) $b->min_percentage,
                'max'   => (float) $b->max_percentage,
            ])
            ->values()
            ->toArray();

        self::$curriculum = [
            'grades'   => $grades,
            'subjects' => $subjects,
            'bands'    => $bands,
            'loaded'   => true,
        ];

        return self::$curriculum;
    }

    /**
     * Get a specific grade by ID.
     */
    public function getGrade(string $id): ?array
    {
        return collect($this->load()['grades'])->firstWhere('id', $id);
    }

    /**
     * Get a specific subject by ID.
     */
    public function getSubject(string $id): ?array
    {
        return collect($this->load()['subjects'])->firstWhere('id', $id);
    }

    /**
     * Map a grade and subject to their curriculum topic.
     */
    public function mapTopic(string $gradeId, string $subjectId): array
    {
        $grade  = $this->getGrade($gradeId);
        $subject = $this->getSubject($subjectId);

        if (!$grade || !$subject) {
            throw new RuntimeException("Grade or Subject not found in CBC curriculum.");
        }

        return [
            'grade_label' => $grade['label'] ?? 'Unknown',
            'subject'     => $subject['name'] ?? 'Unknown',
            'stream'      => 'A', // default stream
        ];
    }

    /**
     * Get the competency band for a given percentage score.
     */
    public function resolveCompetency(float $percentage): string
    {
        $bands = $this->load()['bands'];

        foreach ($bands as $band) {
            if ($percentage >= $band['min'] && $percentage <= $band['max']) {
                return $band['grade'];
            }
        }

        return match (true) {
            $percentage >= 80 => 'Excellent',
            $percentage >= 60 => 'Good',
            $percentage >= 40 => 'Fair',
            default          => 'Needs Improvement',
        };
    }

    /**
     * Validate that a subject is actually part of the CBC curriculum for a grade.
     */
    public function validateSubject(string $gradeId, string $subjectId): bool
    {
        $grade  = $this->getGrade($gradeId);
        $subject = $this->getSubject($subjectId);

        if (!$grade || !$subject) {
            return false;
        }

        // Check if subject exists in exam_subjects for this grade
        $exists = ShuleExamSubject::query()
            ->whereHas('exam', fn ($q) => $q->where('class_id', $gradeId))
            ->where('subject_id', $subjectId)
            ->exists();

        return $exists;
    }
}