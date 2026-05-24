<?php

class SoftDeleteStore
{
    private $path;

    public function __construct($path = null)
    {
        $this->path = $path ?: __DIR__ . '/../backup_db/soft_deletes.json';
    }

    public function isDeleted($type, $id)
    {
        $data = $this->read();
        $id = (string)(int)$id;
        return isset($data[$type][$id]);
    }

    public function listDeleted($type)
    {
        $data = $this->read();
        return $data[$type] ?? [];
    }

    public function getDeletedIds($type)
    {
        return array_map('intval', array_keys($this->listDeleted($type)));
    }

    public function markDeleted($type, $id, $reason, ?array $deletedBy = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return false;
        }

        $reason = function_exists('normalize_delete_reason')
            ? normalize_delete_reason($reason)
            : trim((string)$reason);
        if ($reason === '') {
            return false;
        }
        if (strlen($reason) > 500) {
            return false;
        }

        $data = $this->read();
        $entry = [
            'id' => $id,
            'reason' => $reason,
            'deleted_at' => date('Y-m-d H:i:s'),
        ];

        if ($deletedBy !== null) {
            $entry['deleted_by_id'] = (int)($deletedBy['id'] ?? 0);
            $entry['deleted_by_username'] = (string)($deletedBy['username'] ?? 'Unknown');
        }

        $data[$type][(string)$id] = $entry;
        return $this->write($data);
    }

    public function restore($type, $id)
    {
        $id = (string)(int)$id;
        $data = $this->read();
        if (!isset($data[$type][$id])) {
            return false;
        }

        unset($data[$type][$id]);
        return $this->write($data);
    }

    public function filterRows($type, array $rows, $idColumn)
    {
        $deleted = array_flip($this->getDeletedIds($type));
        if (empty($deleted)) {
            return $rows;
        }

        return array_values(array_filter($rows, function ($row) use ($deleted, $idColumn) {
            return !isset($deleted[(int)($row[$idColumn] ?? 0)]);
        }));
    }

    private function read()
    {
        $data = [
            'players' => [],
            'questions' => [],
            'achievements' => [],
            'ranks' => [],
        ];

        if (!is_file($this->path)) {
            return $data;
        }

        $json = file_get_contents($this->path);
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return $data;
        }

        return array_merge($data, [
            'players' => is_array($decoded['players'] ?? null) ? $decoded['players'] : [],
            'questions' => is_array($decoded['questions'] ?? null) ? $decoded['questions'] : [],
            'achievements' => is_array($decoded['achievements'] ?? null) ? $decoded['achievements'] : [],
            'ranks' => is_array($decoded['ranks'] ?? null) ? $decoded['ranks'] : [],
        ]);
    }

    private function write(array $data)
    {
        $dir = dirname($this->path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            return false;
        }

        $json = json_encode($data, JSON_PRETTY_PRINT);
        return file_put_contents($this->path, $json, LOCK_EX) !== false;
    }
}
