<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFirstLastNameToUsers extends Migration
{
    public function up()
    {
        // 1. Add the two new columns
        $fields = [
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'default'    => '',
                'after'      => 'password',
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'default'    => '',
                'after'      => 'last_name',
            ],
        ];
        $this->forge->addColumn('users', $fields);

        // 2. Migrate existing data: split full_name on the first space
        //    Everything before the first space  → first_name
        //    Everything after the first space   → last_name
        //    If no space exists, copy whole value → last_name
        $this->db->query("
            UPDATE users
            SET
                first_name = IF(
                    LOCATE(' ', full_name) > 0,
                    SUBSTRING_INDEX(full_name, ' ', 1),
                    ''
                ),
                last_name = IF(
                    LOCATE(' ', full_name) > 0,
                    TRIM(SUBSTRING(full_name, LOCATE(' ', full_name) + 1)),
                    full_name
                )
        ");

        // 3. Drop the old column
        $this->forge->dropColumn('users', 'full_name');
    }

    public function down()
    {
        // Reverse: add full_name back, populate it, drop new columns
        $fields = [
            'full_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'default'    => '',
                'after'      => 'password',
            ],
        ];
        $this->forge->addColumn('users', $fields);

        $this->db->query("
            UPDATE users
            SET full_name = TRIM(CONCAT(first_name, ' ', last_name))
        ");

        $this->forge->dropColumn('users', 'first_name');
        $this->forge->dropColumn('users', 'last_name');
    }
}
