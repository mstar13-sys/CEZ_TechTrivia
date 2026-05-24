<?php

class NotificationStore
{
    private $path;

    public function __construct($path = null)
    {
        $this->path = $path ?: __DIR__ . '/../backup_db/notifications.json';
    }

    public function add($type, $title, $message, array $payload = [])
    {
        $type = preg_replace('/[^a-z0-9_]/i', '', (string)$type);
        $title = $this->cleanText($title, 120);
        $message = $this->cleanText($message, 500);

        if ($type === '' || $title === '' || $message === '') {
            return false;
        }

        $data = $this->read();
        array_unshift($data['notifications'], [
            'id' => bin2hex(random_bytes(12)),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'payload' => $this->cleanPayload($payload),
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $data['notifications'] = array_slice($data['notifications'], 0, 250);
        return $this->write($data);
    }

    public function all()
    {
        return $this->read()['notifications'];
    }

    public function unreadCount()
    {
        return count(array_filter($this->all(), function ($notification) {
            return empty($notification['is_read']);
        }));
    }

    public function markRead($id)
    {
        $id = (string)$id;
        $data = $this->read();
        $changed = false;

        foreach ($data['notifications'] as &$notification) {
            if (($notification['id'] ?? '') === $id) {
                $notification['is_read'] = true;
                $changed = true;
                break;
            }
        }

        return $changed ? $this->write($data) : false;
    }

    private function cleanPayload(array $payload)
    {
        $clean = [];
        foreach ($payload as $key => $value) {
            $key = preg_replace('/[^a-z0-9_]/i', '', (string)$key);
            if ($key === '') {
                continue;
            }
            if (is_int($value) || is_float($value) || is_bool($value) || $value === null) {
                $clean[$key] = $value;
            } else {
                $clean[$key] = $this->cleanText($value, 500);
            }
        }
        return $clean;
    }

    private function cleanText($value, $maxLength)
    {
        $value = trim((string)$value);
        $value = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        return substr(trim($value), 0, $maxLength);
    }

    private function read()
    {
        $data = ['notifications' => []];

        if (!is_file($this->path)) {
            return $data;
        }

        $decoded = json_decode((string)file_get_contents($this->path), true);
        if (!is_array($decoded)) {
            return $data;
        }

        return [
            'notifications' => is_array($decoded['notifications'] ?? null) ? $decoded['notifications'] : [],
        ];
    }

    private function write(array $data)
    {
        $dir = dirname($this->path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            return false;
        }

        return file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX) !== false;
    }
}
