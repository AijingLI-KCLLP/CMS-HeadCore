<?php

namespace App\Repositories;

use App\Entities\User;
use Core\Repositories\AbstractRepository;

class DashboardRepository extends AbstractRepository
{
    public function __construct(){
        parent::__construct(User::class);
    }

    public function countUsersByRole(): array{
        $sql = <<<SQL
            SELECT role, COUNT(*) AS total
            FROM users
            GROUP BY role
            ORDER BY role
        SQL;

        $stmt = $this->db->getConnexion()->query($sql);
        $rows  = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $counts = ['admin' => 0, 'editor' => 0, 'author' => 0, 'reader' => 0];
        $total  = 0;

        foreach ($rows as $row) {
            $role = $row['role'];
            $n    = (int) $row['total'];

            if (array_key_exists($role, $counts)) {
                $counts[$role] = $n;
            }
            $total += $n;
        }

        $counts['total'] = $total;

        return $counts;
    }

    public function countContentsByStatus(): array
    {
        $empty = ['draft' => 0, 'published' => 0, 'archived' => 0, 'total' => 0];

        try {
            $sql = <<<SQL
                SELECT status, COUNT(*) AS total
                FROM contents
                GROUP BY status
            SQL;

            $stmt = $this->db->getConnexion()->query($sql);
            $rows  = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $counts = $empty;

            foreach ($rows as $row) {
                $status = $row['status'];
                $n      = (int) $row['total'];

                if (array_key_exists($status, $counts) && $status !== 'total') {
                    $counts[$status] = $n;
                }
                $counts['total'] += $n;
            }

            return $counts;
        } catch (\PDOException) {
            return $empty;
        }
    }

    public function countMedia(): int
    {
        try {
            $stmt = $this->db->getConnexion()->query('SELECT COUNT(*) FROM media');
            return (int) $stmt->fetchColumn();
        } catch (\PDOException) {
            return 0;
        }
    }
}
