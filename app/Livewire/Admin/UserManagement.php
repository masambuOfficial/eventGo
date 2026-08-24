<?php

namespace App\Livewire\Admin;

use App\Domain\Identity\Actions\SetUserStatus;
use App\Domain\Identity\Actions\ToggleAdminRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Unlike VerificationQueue/BillingActivation, this list has no natural
 * ceiling — it grows with every registration, so it's paginated (unlike
 * those two, which only ever show a handful of rows by construction).
 */
#[Title('Users')]
class UserManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $actionError = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search !== '', function ($query) {
                $query->where(fn ($q) => $q
                    ->where('full_name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"));
            })
            ->orderByDesc('created_at')
            ->paginate(25);
    }

    public function toggleAdmin(int $id, ToggleAdminRole $toggleAdminRole): void
    {
        $this->actionError = null;

        try {
            $toggleAdminRole(User::findOrFail($id), auth()->user());
        } catch (\RuntimeException $e) {
            $this->actionError = $e->getMessage();
        }
    }

    public function suspend(int $id, SetUserStatus $setUserStatus): void
    {
        $this->actionError = null;

        try {
            $setUserStatus(User::findOrFail($id), auth()->user(), 'suspended');
        } catch (\RuntimeException $e) {
            $this->actionError = $e->getMessage();
        }
    }

    public function reactivate(int $id, SetUserStatus $setUserStatus): void
    {
        $this->actionError = null;

        try {
            $setUserStatus(User::findOrFail($id), auth()->user(), 'active');
        } catch (\RuntimeException $e) {
            $this->actionError = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.admin.user-management', [
            'users' => $this->users(),
        ]);
    }
}
