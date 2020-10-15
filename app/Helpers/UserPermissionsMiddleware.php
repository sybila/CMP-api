<?php


use App\Controllers\AbstractController;
use App\Entity\Authorization\User;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserPermissionsControllerMiddleware
{

    /** @var EntityManager */
    private $orm;

    public function __construct(EntityManager $orm)
    {
        $this->orm = $orm;
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $id = $request->getAttribute('oauth_user_id');
        if (!is_null($id)) {
            $authUser = $this->orm->getRepository(User::class)->find($id);
            /** @var $usersGroupRoles
             * is an array, where key = GroupId and the value = groupRole in that group
             */
            $usersGroupRoles = [];
            foreach ($authUser->getGroups()->getIterator() as $groupLink){
                $usersGroupRoles[$groupLink->getuserGroupId()->getId()] = $groupLink->getRoleId();
            }
            $request = $request->withAttribute('user_permissions',
                ["group_wise" => $usersGroupRoles, "platform_wise" => $authUser->getType(), "user_id" => $id]);
        }
        else {
            $request = $request->withAttribute('user_permissions',
                ["group_wise" => [1 => 10], "platform_wise" => User::GUEST, "user_id" => null]);
        }
        // Pass the request and response onto the next responder in the chain
        return $next($request, $response);
    }
}