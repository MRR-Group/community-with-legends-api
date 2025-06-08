<?php

declare(strict_types=1);

namespace CommunityWithLegends\Events;

use CommunityWithLegends\Models\Report;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportCreated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Report $report,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("admin"),
        ];
    }

    public function broadcastAs(): string
    {
        return "report.created";
    }

    public function broadcastWith(): array
    {
        return [
            "report_id" => $this->report->id,
            "reporter_name" => $this->report->user->name,
            "reportable_type" => class_basename($this->report->reportable_type),
            "reportable_id" => $this->report->reportable->id,
            "created_at" => $this->report->created_at->toDateTimeString(),
        ];
    }
}
