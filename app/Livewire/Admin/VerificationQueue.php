<?php

namespace App\Livewire\Admin;

use App\Domain\Providers\Actions\ApproveProviderVerification;
use App\Domain\Providers\Actions\RejectProviderVerification;
use App\Domain\Providers\Models\ProviderVerification;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Verification queue')]
class VerificationQueue extends Component
{
    public function pending(): Collection
    {
        return ProviderVerification::with('provider')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();
    }

    public function approve(int $id, ApproveProviderVerification $approve): void
    {
        $approve(ProviderVerification::findOrFail($id), auth()->user());
    }

    public function reject(int $id, RejectProviderVerification $reject): void
    {
        $reject(ProviderVerification::findOrFail($id), auth()->user());
    }

    public function render()
    {
        return view('livewire.admin.verification-queue', [
            'pending' => $this->pending(),
        ]);
    }
}
