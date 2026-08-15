<?php

namespace App\Livewire\Sourcing;

use App\Domain\Bookings\Actions\AcceptOffer;
use App\Domain\Events\Models\Requirement;
use App\Domain\Sourcing\Actions\AnswerClarification;
use App\Domain\Sourcing\Actions\RejectOffer;
use App\Domain\Sourcing\Actions\ShortlistOffer;
use App\Domain\Sourcing\Models\Clarification;
use App\Domain\Sourcing\Models\Offer;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Compare offers')]
class OfferComparison extends Component
{
    public int $requirementId;

    /** @var array<int, string> */
    public array $answers = [];

    public function mount(Requirement $requirement): void
    {
        abort_unless($requirement->event->owner_user_id === auth()->id(), 403);

        $this->requirementId = $requirement->id;
    }

    protected function requirement(): Requirement
    {
        return Requirement::with('event')->findOrFail($this->requirementId);
    }

    public function offers(): Collection
    {
        return Offer::where('requirement_id', $this->requirementId)
            ->whereNotIn('status', ['draft', 'withdrawn'])
            ->with(['provider', 'items'])
            ->get()
            ->sortBy('total_ugx')
            ->values();
    }

    public function shortlistedOfferIds(): Collection
    {
        return Offer::where('requirement_id', $this->requirementId)->where('status', 'shortlisted')->pluck('id');
    }

    public function clarifications(): Collection
    {
        return Clarification::where('requirement_id', $this->requirementId)
            ->latest('created_at')
            ->get();
    }

    protected function ownedOffer(int $offerId): Offer
    {
        return Offer::where('id', $offerId)->where('requirement_id', $this->requirementId)->firstOrFail();
    }

    public function shortlist(int $offerId, ShortlistOffer $shortlistOffer): void
    {
        $shortlistOffer($this->ownedOffer($offerId));
    }

    public function reject(int $offerId, RejectOffer $rejectOffer): void
    {
        $rejectOffer($this->ownedOffer($offerId));
    }

    public function accept(int $offerId, AcceptOffer $acceptOffer): void
    {
        $booking = $acceptOffer($this->ownedOffer($offerId));

        $this->redirectRoute('events.dashboard', $booking->event);
    }

    public function answer(int $clarificationId, AnswerClarification $answerClarification): void
    {
        $clarification = Clarification::where('id', $clarificationId)
            ->where('requirement_id', $this->requirementId)
            ->firstOrFail();

        $answerText = trim($this->answers[$clarificationId] ?? '');

        if ($answerText === '') {
            return;
        }

        $answerClarification($clarification, auth()->user(), $answerText);

        unset($this->answers[$clarificationId]);
    }

    public function render()
    {
        return view('livewire.sourcing.offer-comparison', [
            'requirement' => $this->requirement(),
            'offers' => $this->offers(),
            'shortlistedOfferIds' => $this->shortlistedOfferIds(),
            'clarifications' => $this->clarifications(),
        ]);
    }
}
