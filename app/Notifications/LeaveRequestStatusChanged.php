<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestStatusChanged extends Notification
{
    use Queueable;

    public $leaveRequest;
    public $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(LeaveRequest $leaveRequest, string $status)
    {
        $this->leaveRequest = $leaveRequest;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusText = ucfirst($this->status);
        $color = $this->status === 'approved' ? 'green' : 'red';

        return (new MailMessage)
            ->subject("Leave Request {$statusText}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your leave request has been {$this->status}.")
            ->line("Leave Type: {$this->leaveRequest->leave_type}")
            ->line("Dates: {$this->leaveRequest->start_date->format('M d, Y')} to {$this->leaveRequest->end_date->format('M d, Y')}")
            ->line("Reason: {$this->leaveRequest->reason}")
            ->action('View Details', route('leave-requests.show', $this->leaveRequest))
            ->line("Thank you for using our application!");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'status' => $this->status,
            'message' => "Your leave request has been {$this->status}.",
            'leave_type' => $this->leaveRequest->leave_type,
            'start_date' => $this->leaveRequest->start_date->format('M d, Y'),
            'end_date' => $this->leaveRequest->end_date->format('M d, Y'),
        ];
    }
}
