<?php

namespace App\Modules\ShuleYetu\Support\Tenancy;

use App\Modules\ShuleYetu\Models\ShuleSchool;
use RuntimeException;

class SchoolContext
{
    private ?string $id = null;

    private ?ShuleSchool $school = null;

    public function setId(string $id): void
    {
        $this->id = $id;
        $this->school = null;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function requireId(): string
    {
        if (empty($this->id)) {
            throw new RuntimeException('No active school context.');
        }

        return $this->id;
    }

    public function setSchool(ShuleSchool $school): void
    {
        $this->school = $school;
        $this->id = (string) $school->getKey();
    }

    public function school(): ?ShuleSchool
    {
        return $this->school;
    }

    public function clear(): void
    {
        $this->id = null;
        $this->school = null;
    }
}
