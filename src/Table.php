<?php

declare(strict_types=1);

namespace Shrinker;

use Exception;
use RoyallTheFourth\SmoothPdo\DataObject;

class Table
{
    private $name;
    private $db;
    private $dataFree;
    private $dataLength;

    public function __construct(string $name, string $db, float $dataFree, float $dataLength)
    {
        $this->name = $name;
        $this->db = $db;
        $this->dataFree = $dataFree;
        $this->dataLength = $dataLength;
        return $this;
    }

    public function LogSkip(): string
    {
        return sprintf("%s Skipping table %s.%s of size %d with ratio %.2f\n",
            date(DATE_ISO8601),
            $this->db,
            $this->name,
            $this->dataLength,
            $this->dataFree / $this->dataLength);
    }

    public function LogStart(): string
    {
        return sprintf("%s Shrinking table %s.%s of size %d with ratio %.2f\n",
            date(DATE_ISO8601),
            $this->db,
            $this->name,
            $this->dataLength,
            $this->dataFree / $this->dataLength);
    }

    public function LogFinish(int $kbytes): string
    {
        return sprintf("%s Finished shrinking table %s.%s. Saved %d kilobytes\n",
            date(DATE_ISO8601),
            $this->db,
            $this->name,
            $kbytes);
    }

    public function ShouldShrink(float $ratio): bool
    {
        if ($this->dataFree == 0 || $this->dataLength == 0) {
            return false;
        }
        return $this->dataFree / $this->dataLength > $ratio;
    }

    /**
     * @param DataObject $spdo
     * @return int Number of bytes saved
     * @throws Exception
     */
    public function Shrink(DataObject $spdo): int
    {
        try {
            $spdo->prepare("ALTER TABLE {$this->db}.{$this->name} ENGINE=\"InnoDB\";")->execute();
        } catch (Exception $e) {
            throw new Exception("Failed to shrink table {$this->db}.{$this->name}", 0, $e);
        }

        try {
            $dataLength = $spdo->prepare(
                "SELECT DATA_LENGTH
FROM information_schema.tables
WHERE TABLE_SCHEMA LIKE ?
AND TABLE_NAME LIKE ?
AND ENGINE LIKE 'InnoDB'
AND DATA_FREE > 0;")->execute([$this->db, $this->name])->fetch(\PDO::FETCH_ASSOC)['DATA_LENGTH'];
        } catch (Exception $e) {
            return 0;
        }

        $oldSize = $this->dataLength;
        $this->dataLength = $dataLength;

        return $dataLength - (int)$oldSize;
    }
}
