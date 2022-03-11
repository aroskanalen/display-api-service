<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\Tenant;
use App\Entity\User;
use App\Entity\UserRoleTenant;
use App\Utils\IriHelperUtils;
use Hautelook\AliceBundle\PhpUnit\BaseDatabaseTrait;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

abstract class AbstractBaseApiTestCase extends ApiTestCase
{
    use BaseDatabaseTrait;

    protected iriHelperUtils $iriHelperUtils;
    protected JWTTokenManagerInterface $JWTTokenManager;

    protected User $user;
    protected Tenant $tenant;

    public static function setUpBeforeClass(): void
    {
        static::bootKernel();
        static::ensureKernelTestCase();
        static::populateDatabase();
    }

    protected function setUp(): void
    {
        $this->iriHelperUtils = static::getContainer()->get(IriHelperUtils::class);
        $this->JWTTokenManager = static::getContainer()->get(JWTTokenManagerInterface::class);
    }

    protected function getJwtToken(User $user): string
    {
        return $this->JWTTokenManager->create($user);
    }

    /**
     * Get an authenticated client for a user scoped to the 'ABC' tenant loaded from fixtures.
     *
     * @param string $role
     *
     * @return Client
     */
    protected function getAuthenticatedClient(string $role = 'ROLE_EDITOR'): Client
    {
        $manager = self::$container->get('doctrine')->getManager();

        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $tenant = $manager->getRepository(Tenant::class)->findOneBy(['tenantKey' => 'ABC']);

        if (null === $user) {
            $user = new User();
            $user->setFullName('Test Test');
            $user->setEmail('test@example.com');
            $user->setProvider(self::class);
            $user->setPassword(
                self::$container->get('security.user_password_hasher')->hashPassword($user, '$3CR3T')
            );

            $userRoleTenant = new UserRoleTenant();
            $userRoleTenant->setTenant($tenant);
            $userRoleTenant->setRoles([$role]);

            $user->addUserRoleTenant($userRoleTenant);

            $manager->persist($user);
        }

        $userRoleTenants = $user->getUserRoleTenants();
        /** @var UserRoleTenant $userRoleTenant */
        $userRoleTenant = $userRoleTenants->first();
        $userRoleTenant->setRoles([$role]);
        $manager->flush();

        $user->setActiveTenant($tenant);

        $this->user = $user;
        $this->tenant = $user->getActiveTenant();

        $token = $this->getJwtToken($this->user);

        return static::createClient([], ['auth_bearer' => $token]);
    }
}
