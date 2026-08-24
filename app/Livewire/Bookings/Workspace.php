<?php

namespace App\Livewire\Bookings;

use App\Domain\Bookings\Actions\CancelBooking;
use App\Domain\Bookings\Actions\CompleteBooking;
use App\Domain\Bookings\Actions\RecordAmendment;
use App\Domain\Bookings\Actions\ToggleTask;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\Models\BookingFile;
use App\Domain\Bookings\Models\BookingTask;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Booking workspace')]
class Workspace extends Component
{
    use WithFileUploads;

    public int $bookingId;

    public string $viewerSide;

    public string $newTaskTitle = '';

    public string $newTaskOwnerSide = 'both';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $upload;

    public string $fileLabel = '';

    public string $newTotalUgx = '';

    public string $amendmentNote = '';

    public string $cancellationNote = '';

    public function mount(Booking $booking): void
    {
        $booking->load(['event', 'provider']);

        $side = $booking->viewerSide(auth()->user());
        abort_unless($side !== null, 403);

        $this->bookingId = $booking->id;
        $this->viewerSide = $side;
    }

    protected function booking(): Booking
    {
        return Booking::with(['event', 'provider', 'requirement'])->findOrFail($this->bookingId);
    }

    public function tasks(): Collection
    {
        return $this->booking()->tasks;
    }

    public function files(): Collection
    {
        return $this->booking()->files()->latest()->get();
    }

    public function amendments(): Collection
    {
        return $this->booking()->amendments()->latest()->get();
    }

    public function addTask(): void
    {
        $this->validate([
            'newTaskTitle' => ['required', 'string', 'max:200'],
            'newTaskOwnerSide' => ['required', 'in:organiser,provider,both'],
        ]);

        $booking = $this->booking();

        BookingTask::create([
            'booking_id' => $booking->id,
            'title' => $this->newTaskTitle,
            'owner_side' => $this->newTaskOwnerSide,
            'status' => 'open',
            'sort_order' => $booking->tasks()->max('sort_order') + 1,
        ]);

        $this->reset(['newTaskTitle', 'newTaskOwnerSide']);
    }

    public function toggleTask(int $taskId, ToggleTask $toggleTask): void
    {
        $task = $this->booking()->tasks()->findOrFail($taskId);

        $toggleTask($task, auth()->user());
    }

    public function uploadFile(): void
    {
        $this->validate([
            'upload' => ['required', 'file', 'max:10240'],
            'fileLabel' => ['required', 'string', 'max:150'],
        ]);

        $booking = $this->booking();
        $path = $this->upload->store("booking-files/{$booking->id}", config('filesystems.default'));

        BookingFile::create([
            'booking_id' => $booking->id,
            'uploaded_by_user_id' => auth()->id(),
            'label' => $this->fileLabel,
            'path' => $path,
            'mime' => $this->upload->getMimeType(),
            'size_bytes' => $this->upload->getSize(),
        ]);

        $this->reset(['upload', 'fileLabel']);
    }

    public function deleteFile(int $fileId): void
    {
        $file = $this->booking()->files()->where('id', $fileId)->first();

        if ($file) {
            Storage::disk(config('filesystems.default'))->delete($file->path);
            $file->delete();
        }
    }

    public function recordAmendment(RecordAmendment $recordAmendment): void
    {
        $this->validate([
            'newTotalUgx' => ['required', 'integer', 'min:0'],
            'amendmentNote' => ['required', 'string', 'max:500'],
        ]);

        $recordAmendment($this->booking(), auth()->user(), (int) $this->newTotalUgx, $this->amendmentNote);

        $this->reset(['newTotalUgx', 'amendmentNote']);
    }

    public function completeBooking(CompleteBooking $completeBooking): void
    {
        $completeBooking($this->booking(), auth()->user());
    }

    public function cancelBooking(CancelBooking $cancelBooking): void
    {
        $this->validate([
            'cancellationNote' => ['required', 'string', 'max:500'],
        ]);

        $cancelBooking($this->booking(), auth()->user(), $this->cancellationNote);

        $this->reset(['cancellationNote']);
    }

    public function render()
    {
        return view('livewire.bookings.workspace', [
            'booking' => $this->booking(),
            'tasks' => $this->tasks(),
            'files' => $this->files(),
            'amendments' => $this->amendments(),
        ]);
    }
}
