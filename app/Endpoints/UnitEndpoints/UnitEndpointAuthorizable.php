<?php

use App\Entity\Authorization\User;
use App\Exceptions\InvalidArgumentException;
use App\Exceptions\InvalidAuthenticationException;

trait UnitEndpointAuthorizable
{

    /**
     * @return array additional collection filter,
     * key (is group id of users groups) => value (is prepared for dql filter)
     * @throws InvalidArgumentException if user with non-existing role
     * @throws InvalidAuthenticationException
     */
    public function validateList(): ?array
    {
        switch ($this->userPermissions['platform_wise']){
            case User::ADMIN:
            case User::POWER:
            case User::REGISTERED:
                return [];
            case User::TEMPORARY:
            case User::GUEST:
                throw new InvalidAuthenticationException("Temporary user and guest can't access this endpoint.",
                    "Sign up and confirm the registration.");
            default:
                throw new InvalidArgumentException('user_type', $this->userPermissions['platform_wise'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidAuthenticationException
     */
    public function validateDetail(): bool
    {
        switch ($this->userPermissions['platform_wise']){
            case User::ADMIN:
            case User::POWER:
            case User::REGISTERED:
                return true;
            case User::TEMPORARY:
            case User::GUEST:
                throw new InvalidAuthenticationException("Temporary user and guest can't access this endpoint.",
                    "Sign up and confirm the registration.");
            default:
                throw new InvalidArgumentException('user_type', $this->userPermissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidAuthenticationException
     */
    public function validateAdd(): bool
    {
        switch ($this->userPermissions['platform_wise']){
            case User::ADMIN:
            case User::POWER:
            case User::REGISTERED:
                return true;
            case User::TEMPORARY:
            case User::GUEST:
                throw new InvalidAuthenticationException("Temporary user and guest can't add units.",
                    "Sign up and confirm the registration.");
            default:
                throw new InvalidArgumentException('user_type', $this->userPermissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidAuthenticationException
     */
    public function validateEdit(): bool
    {
        switch ($this->userPermissions['platform_wise']){
            case User::ADMIN:
            case User::POWER:
                return true;
            case User::REGISTERED:
            case User::TEMPORARY:
            case User::GUEST:
                throw new InvalidAuthenticationException("Temporary user and guest can't manipulate units.",
                    "Sign up and confirm the registration.");
            default:
                throw new InvalidArgumentException('user_type', $this->userPermissions['user_type'],
                    'This user type does not exist on the platform');
        }
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     * @throws InvalidAuthenticationException
     */
    public function validateDelete(): bool
    {
        return $this->validateEdit();
    }

}