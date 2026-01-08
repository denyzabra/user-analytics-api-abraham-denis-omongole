<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/users', name: 'create_user', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse([
                'success' => false,
                'data' => null,
                'message' => 'Invalid JSON format.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // check if email already exists
        if (isset($data['email'])) {
            $existingUser = $this->userRepository->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return new JsonResponse([
                    'success' => false,
                    'data' => null,
                    'message' => 'Email already exists.'
                ], Response::HTTP_CONFLICT);
            }
        }

        $user = new User();

        if (isset($data['name'])) {
            $user->setName($data['name']);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['status'])) {
            $user->setStatus($data['status']);
        }

        // vaalidate the user entity
        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse([
                'success' => false,
                'data' => null,
                'message' => 'Validation failed.',
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'data' => $user->toArray(),
            'message' => 'User created successfully.'
        ], Response::HTTP_CREATED);
    }

    #[Route('/users', name: 'list_users', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $status = $request->query->get('status');

        // validate status parameter if provided
        if ($status !== null && !in_array($status, ['active', 'inactive'], true)) {
            return new JsonResponse([
                'success' => false,
                'data' => null,
                'message' => 'Invalid status parameter. Must be "active" or "inactive".'
            ], Response::HTTP_BAD_REQUEST);
        }

        $users = $this->userRepository->findAllSortedByCreatedAt($status);

        $usersArray = array_map(fn(User $user) => $user->toArray(), $users);

        return new JsonResponse([
            'success' => true,
            'data' => $usersArray,
            'message' => 'Users retrieved successfully.'
        ], Response::HTTP_OK);
    }

    #[Route('/users/analytics', name: 'user_analytics', methods: ['GET'])]
    public function analytics(): JsonResponse
    {
        $totalUsers = $this->userRepository->countAll();
        $usersLast15Days = $this->userRepository->countCreatedInLastDays(15);
        $averageUsersPerDayLast7Days = $this->userRepository->getAverageNewUsersPerDay(7);

        return new JsonResponse([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'users_last_15_days' => $usersLast15Days,
                'average_users_per_day_last_7_days' => $averageUsersPerDayLast7Days
            ],
            'message' => 'Analytics retrieved successfully.'
        ], Response::HTTP_OK);
    }
}
