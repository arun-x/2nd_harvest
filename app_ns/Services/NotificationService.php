<?php
namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    private Notification $model;

    public function __construct()
    {
        $this->model = new Notification();
    }

    public function notify(int $userId, string $message, string $type): void
    {
        $this->model->create($userId, $message, $type);
    }
}
