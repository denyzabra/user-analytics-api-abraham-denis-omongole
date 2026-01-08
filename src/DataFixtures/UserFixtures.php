<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Users data with realistic names
        $usersData = [
            // 8 users created within last 7 days
            ['name' => 'John Smith', 'email' => 'john.smith@example.com', 'status' => 'active', 'daysAgo' => 1],
            ['name' => 'Emma Johnson', 'email' => 'emma.johnson@example.com', 'status' => 'active', 'daysAgo' => 2],
            ['name' => 'Michael Brown', 'email' => 'michael.brown@example.com', 'status' => 'inactive', 'daysAgo' => 3],
            ['name' => 'Sophia Davis', 'email' => 'sophia.davis@example.com', 'status' => 'active', 'daysAgo' => 4],
            ['name' => 'William Wilson', 'email' => 'william.wilson@example.com', 'status' => 'active', 'daysAgo' => 5],
            ['name' => 'Olivia Martinez', 'email' => 'olivia.martinez@example.com', 'status' => 'inactive', 'daysAgo' => 6],
            ['name' => 'James Anderson', 'email' => 'james.anderson@example.com', 'status' => 'active', 'daysAgo' => 7],
            ['name' => 'Isabella Taylor', 'email' => 'isabella.taylor@example.com', 'status' => 'active', 'daysAgo' => 7],

            // 5 users created between 8-15 days ago
            ['name' => 'Benjamin Thomas', 'email' => 'benjamin.thomas@example.com', 'status' => 'active', 'daysAgo' => 8],
            ['name' => 'Mia Hernandez', 'email' => 'mia.hernandez@example.com', 'status' => 'inactive', 'daysAgo' => 10],
            ['name' => 'Lucas Moore', 'email' => 'lucas.moore@example.com', 'status' => 'active', 'daysAgo' => 12],
            ['name' => 'Charlotte Martin', 'email' => 'charlotte.martin@example.com', 'status' => 'active', 'daysAgo' => 14],
            ['name' => 'Henry Jackson', 'email' => 'henry.jackson@example.com', 'status' => 'inactive', 'daysAgo' => 15],

            // 12 users created older than 15 days
            ['name' => 'Amelia Garcia', 'email' => 'amelia.garcia@example.com', 'status' => 'active', 'daysAgo' => 20],
            ['name' => 'Alexander Lee', 'email' => 'alexander.lee@example.com', 'status' => 'inactive', 'daysAgo' => 25],
            ['name' => 'Evelyn Harris', 'email' => 'evelyn.harris@example.com', 'status' => 'active', 'daysAgo' => 30],
            ['name' => 'Sebastian Clark', 'email' => 'sebastian.clark@example.com', 'status' => 'active', 'daysAgo' => 35],
            ['name' => 'Abigail Lewis', 'email' => 'abigail.lewis@example.com', 'status' => 'inactive', 'daysAgo' => 40],
            ['name' => 'Daniel Robinson', 'email' => 'daniel.robinson@example.com', 'status' => 'active', 'daysAgo' => 45],
            ['name' => 'Emily Walker', 'email' => 'emily.walker@example.com', 'status' => 'active', 'daysAgo' => 50],
            ['name' => 'Matthew Hall', 'email' => 'matthew.hall@example.com', 'status' => 'inactive', 'daysAgo' => 60],
            ['name' => 'Harper Allen', 'email' => 'harper.allen@example.com', 'status' => 'active', 'daysAgo' => 70],
            ['name' => 'David Young', 'email' => 'david.young@example.com', 'status' => 'active', 'daysAgo' => 80],
            ['name' => 'Ella King', 'email' => 'ella.king@example.com', 'status' => 'inactive', 'daysAgo' => 90],
            ['name' => 'Joseph Wright', 'email' => 'joseph.wright@example.com', 'status' => 'active', 'daysAgo' => 100],
        ];

        foreach ($usersData as $userData) {
            $user = new User();
            $user->setName($userData['name']);
            $user->setEmail($userData['email']);
            $user->setStatus($userData['status']);

            // Set created_at to specific days ago
            $createdAt = new \DateTimeImmutable("-{$userData['daysAgo']} days");
            $user->setCreatedAt($createdAt);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
