<?php

use ChurchCRM\Authentication\AuthenticationManager;
use ChurchCRM\Service\DiscipleMakerService;
use ChurchCRM\Slim\Middleware\Request\Auth\EditRecordsRoleAuthMiddleware;
use ChurchCRM\Slim\SlimUtils;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->group('/disciples', function (RouteCollectorProxy $group): void {
    $discipleMakerService = new DiscipleMakerService();

    /**
     * @OA\Get(
     *     path="/disciples/makers",
     *     summary="Get all potential disciple makers",
     *     description="Returns a list of all people who can be assigned as disciple makers",
     *     tags={"Disciples"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Response(response=200, description="List of potential disciple makers",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="fullName", type="string"),
     *             @OA\Property(property="disciplesCount", type="integer")
     *         ))
     *     )
     * )
     */
    $group->get('/makers', function (Request $request, Response $response) use ($discipleMakerService) {
        $makers = $discipleMakerService->getPotentialDiscipleMakers();
        return SlimUtils::renderJSON($response, $makers);
    });

    /**
     * @OA\Post(
     *     path="/disciples/person/{personId}/maker",
     *     summary="Set disciple maker for a person",
     *     description="Assigns or updates the disciple maker for a specific person",
     *     tags={"Disciples"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(name="personId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"discipleMakerId"},
     *             @OA\Property(property="discipleMakerId", type="integer", description="ID of the disciple maker (null to remove)")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Disciple maker assigned successfully"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     @OA\Response(response=404, description="Person not found")
     * )
     */
    $group->post('/person/{personId}/maker', function (Request $request, Response $response, array $args) use ($discipleMakerService) {
        $personId = (int) $args['personId'];
        $body = $request->getParsedBody();
        $discipleMakerId = isset($body['discipleMakerId']) ? (int) $body['discipleMakerId'] : null;

        // Si discipleMakerId est 0 ou vide, le mettre à null
        if ($discipleMakerId === 0 || $discipleMakerId === null) {
            $discipleMakerId = null;
        }

        $success = $discipleMakerService->setDiscipleMaker($personId, $discipleMakerId);

        if ($success) {
            return SlimUtils::renderSuccessJSON($response, ['message' => 'Disciple maker updated successfully']);
        } else {
            return SlimUtils::renderErrorJSON($response, 'Failed to update disciple maker', [], 400);
        }
    })->add(EditRecordsRoleAuthMiddleware::class);

    /**
     * @OA\Get(
     *     path="/disciples/person/{personId}/maker",
     *     summary="Get disciple maker for a person",
     *     description="Returns the disciple maker assigned to a specific person",
     *     tags={"Disciples"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(name="personId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Disciple maker information"),
     *     @OA\Response(response=404, description="No disciple maker assigned")
     * )
     */
    $group->get('/person/{personId}/maker', function (Request $request, Response $response, array $args) use ($discipleMakerService) {
        $personId = (int) $args['personId'];
        $discipleMaker = $discipleMakerService->getDiscipleMaker($personId);

        if ($discipleMaker === null) {
            return SlimUtils::renderJSON($response, ['discipleMaker' => null]);
        }

        return SlimUtils::renderJSON($response, ['discipleMaker' => $discipleMaker]);
    });

    /**
     * @OA\Get(
     *     path="/disciples/maker/{makerId}/disciples",
     *     summary="Get all disciples of a disciple maker",
     *     description="Returns all people assigned to a specific disciple maker",
     *     tags={"Disciples"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(name="makerId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List of disciples",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="fullName", type="string"),
     *             @OA\Property(property="email", type="string")
     *         ))
     *     )
     * )
     */
    $group->get('/maker/{makerId}/disciples', function (Request $request, Response $response, array $args) use ($discipleMakerService) {
        $makerId = (int) $args['makerId'];
        $disciples = $discipleMakerService->getDisciples($makerId);

        return SlimUtils::renderJSON($response, ['disciples' => $disciples]);
    });

    /**
     * @OA\Get(
     *     path="/disciples/maker/{makerId}/stats",
     *     summary="Get disciple maker statistics",
     *     description="Returns statistics about disciples for a specific disciple maker",
     *     tags={"Disciples"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(name="makerId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Disciple maker statistics")
     * )
     */
    $group->get('/maker/{makerId}/stats', function (Request $request, Response $response, array $args) use ($discipleMakerService) {
        $makerId = (int) $args['makerId'];
        $stats = $discipleMakerService->getDisciplesStats($makerId);

        return SlimUtils::renderJSON($response, $stats);
    });

    /**
     * @OA\Post(
     *     path="/disciples/transfer",
     *     summary="Transfer disciples from one maker to another",
     *     description="Transfers all disciples from one disciple maker to another",
     *     tags={"Disciples"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"fromDiscipleMakerId", "toDiscipleMakerId"},
     *             @OA\Property(property="fromDiscipleMakerId", type="integer"),
     *             @OA\Property(property="toDiscipleMakerId", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Disciples transferred successfully"),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    $group->post('/transfer', function (Request $request, Response $response) use ($discipleMakerService) {
        $body = $request->getParsedBody();
        $fromId = (int) $body['fromDiscipleMakerId'];
        $toId = (int) $body['toDiscipleMakerId'];

        $transferred = $discipleMakerService->transferDisciples($fromId, $toId);

        if ($transferred > 0) {
            return SlimUtils::renderSuccessJSON($response, [
                'message' => "$transferred disciple(s) transferred successfully",
                'transferredCount' => $transferred
            ]);
        } else {
            return SlimUtils::renderErrorJSON($response, 'Failed to transfer disciples', [], 400);
        }
    })->add(EditRecordsRoleAuthMiddleware::class);

    /**
     * @OA\Delete(
     *     path="/disciples/person/{personId}/maker",
     *     summary="Remove disciple maker from a person",
     *     description="Removes the disciple maker assignment from a specific person",
     *     tags={"Disciples"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(name="personId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Disciple maker removed successfully")
     * )
     */
    $group->delete('/person/{personId}/maker', function (Request $request, Response $response, array $args) use ($discipleMakerService) {
        $personId = (int) $args['personId'];
        $success = $discipleMakerService->removeDiscipleMaker($personId);

        if ($success) {
            return SlimUtils::renderSuccessJSON($response, ['message' => 'Disciple maker removed successfully']);
        } else {
            return SlimUtils::renderErrorJSON($response, 'Failed to remove disciple maker', [], 400);
        }
    })->add(EditRecordsRoleAuthMiddleware::class);
});
