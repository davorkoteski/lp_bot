<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 3/19/2019
 * Time: 10:09 PM
 */

namespace App\Controller;

use App\Services\DatabaseConnection;
use App\Services\MainService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MainController
{
    /**
     * @Route("/api/add-users", methods={"POST"})
     */
    public function insertUser(Request $request, MainService $users, DatabaseConnection $connection)
    {
        $data = json_decode($request->getContent(), true);
        try {
            $connection->getConnection()->beginTransaction();
            try {
                $user_id = $users->insertUsers($data);
                $user = array('id' => $user_id);
            } catch (\Throwable $exception) {
                $users->insertLog(print_r($data, true), $exception->getMessage());
            }
            $connection->getConnection()->commit();
            $response = new JsonResponse($user, 200);
            return $response;
        } catch (\Throwable $exception) {
            $users->insertLog(print_r($data, true), $exception->getMessage());
            $response = new JsonResponse([$exception->getMessage()], 200);
            return $response;
        }
    }
    /**
     * @Route("/api/select-users/{app_id}", methods={"GET"})
     */
    public function selectUser(MainService $users, $app_id)
    {
        $user = $users->selectUsers($app_id);
        $users->insertLog(print_r($user, true), 'yes');
        return new JsonResponse($user);
    }
    /**
     * @Route("/api/add-plates", methods={"POST"})
     */
    public function insertPlate(Request $request, MainService $plates, DatabaseConnection $connection)
    {
        $data = json_decode($request->getContent(), true);
        try {
            $connection->getConnection()->beginTransaction();
            try {
                $plates->insertPlates($data);
            } catch (\Throwable $exception) {
                $plates->insertLog(print_r($data, true), $exception->getMessage());
            }
            $connection->getConnection()->commit();
            $response = new JsonResponse([], 200);
            return $response;
        } catch (\Throwable $exception) {
            $plates->insertLog(print_r($data, true), $exception->getMessage());
            $response = new JsonResponse([$exception->getMessage()], 200);
            return $response;
        }
    }
    /**
     * @Route("/api/select-plates/{number}", methods={"GET"})
     */
    public function selectPlate(MainService $plates, $number)
    {
        $plate = $plates->selectPlates($number);
        $plates->insertLog(print_r($plate, true), 'yes');
        return new JsonResponse($plate);
    }
    /**
     * @Route("/api/add-messages", methods={"POST"})
     */
    public function insertMessage(Request $request, MainService $messages, DatabaseConnection $connection)
    {
        $data = json_decode($request->getContent(), true);
        try {
            $connection->getConnection()->beginTransaction();
            try {
                $messages->insertMessages($data);
            } catch (\Throwable $exception) {
                $messages->insertLog(print_r($data, true), $exception->getMessage());
            }
            $connection->getConnection()->commit();
            $response = new JsonResponse([], 200);
            return $response;
        } catch (\Throwable $exception) {
            $messages->insertLog(print_r($data, true), $exception->getMessage());
            $response = new JsonResponse([$exception->getMessage()], 200);
            return $response;
        }
    }
    /**
     * @Route("/api/select-messages/{user_id}/{plate_id}", methods={"GET"})
     */
    public function selectMessage(MainService $messages, $user_id, $plate_id)
    {
        $message = $messages->selectMessages($user_id, $plate_id);
        $message->insertLog(print_r($message, true), 'yes');
        return new JsonResponse($message);
    }
}