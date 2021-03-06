<?php

namespace Okipa\LaravelBaseRepository\Test\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Okipa\LaravelBaseRepository\Test\BaseRepositoryTestCase;
use Okipa\LaravelBaseRepository\Test\Fakers\CompaniesFaker;
use Okipa\LaravelBaseRepository\Test\Fakers\UsersFaker;
use Okipa\LaravelBaseRepository\Test\Models\Company;
use Okipa\LaravelBaseRepository\Test\Models\User;
use Okipa\LaravelBaseRepository\Test\Repositories\CompanyRepositoryWithCustomDefaultAttributesToExcept;
use Okipa\LaravelBaseRepository\Test\Repositories\CompanyRepositoryWithDisabledDefaultAttributesException;
use Okipa\LaravelBaseRepository\Test\Repositories\UserRepository;
use Okipa\LaravelBaseRepository\Test\Repositories\UserRepositoryWithNoModel;

class TableListColumnTest extends BaseRepositoryTestCase
{
    use UsersFaker;
    use CompaniesFaker;

    public function testCreateMultipleFromArray()
    {
        $data = [
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
        ];
        $users = $this->repository->createOrUpdateMultipleFromArray($data);
        $this->assertCount(count($data), $users);
        foreach ($data as $key => $user) {
            $this->assertEquals($data[$key]['name'], $users->get($key)->name);
            $this->assertEquals($data[$key]['email'], $users->get($key)->email);
            $this->assertEquals($data[$key]['password'], $users->get($key)->password);
        }
    }

    public function testUpdateMultipleFromArrayWithoutFormattingFromFillables()
    {
        $originalUsers = $this->createMultipleUsers(3);
        $usersUpdatedArray = $originalUsers->toArray();
        foreach ($usersUpdatedArray as $key => $user) {
            unset($usersUpdatedArray[$key]['name']);
            $usersUpdatedArray[$key]['email'] = $this->faker->email;
        }
        $updatedUsers = $this->repository->createOrUpdateMultipleFromArray($usersUpdatedArray, false);
        foreach ($updatedUsers as $key => $updatedUser) {
            $this->assertEquals($updatedUser->name, $originalUsers->get($key)->name);
            $this->assertNotEquals($updatedUser->email, $originalUsers->get($key)->email);
            $this->assertEquals($updatedUser->email, $usersUpdatedArray[$key]['email']);
            $this->assertEquals($updatedUser->password, $originalUsers->get($key)->password);
        }
    }

    public function testUpdateMultipleFromArrayWithFormattingFromFillables()
    {
        $originalUsers = $this->createMultipleUsers(3);
        $usersUpdatedArray = $originalUsers->toArray();
        foreach ($usersUpdatedArray as $key => $user) {
            unset($usersUpdatedArray[$key]['name']);
            $usersUpdatedArray[$key]['email'] = $this->faker->email;
        }
        $updatedUsers = $this->repository->createOrUpdateMultipleFromArray($usersUpdatedArray);
        foreach ($updatedUsers as $key => $updatedUser) {
            $this->assertNull($updatedUser->name);
            $this->assertNotEquals($updatedUser->email, $originalUsers->get($key)->email);
            $this->assertEquals($updatedUser->email, $usersUpdatedArray[$key]['email']);
            $this->assertEquals($updatedUser->password, $originalUsers->get($key)->password);
        }
    }

    public function testUpdateSingleFromArrayWithoutFormattingFromFillables()
    {
        $originalUser = $this->createUniqueUser();
        $userUpdatedArray = $originalUser->toArray();
        unset($userUpdatedArray['name']);
        $userUpdatedArray['email'] = $this->faker->email;
        $updatedUser = $this->repository->createOrUpdateFromArray($userUpdatedArray, false);
        $this->assertEquals($updatedUser->name, $originalUser->name);
        $this->assertNotEquals($updatedUser->email, $originalUser->email);
        $this->assertEquals($updatedUser->email, $userUpdatedArray['email']);
        $this->assertEquals($updatedUser->password, $originalUser->password);
    }

    public function testUpdateSingleFromArrayWithFormattingFromFillables()
    {
        $originalUser = $this->createUniqueUser();
        $userUpdatedArray = $originalUser->toArray();
        unset($userUpdatedArray['name']);
        $userUpdatedArray['email'] = $this->faker->email;
        $updatedUser = $this->repository->createOrUpdateFromArray($userUpdatedArray);
        $this->assertNull($updatedUser->name);
        $this->assertNotEquals($updatedUser->email, $originalUser->email);
        $this->assertEquals($updatedUser->email, $userUpdatedArray['email']);
        $this->assertEquals($updatedUser->password, $originalUser->password);
    }

    public function testCreateMultipleFromRequest()
    {
        $data = [
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
        ];
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $users = $this->repository->createOrUpdateMultipleFromRequest();
        $this->assertCount(count($data), $users);
        foreach ($data as $key => $user) {
            $this->assertEquals($data[$key]['name'], $users->get($key)->name);
            $this->assertEquals($data[$key]['email'], $users->get($key)->email);
            $this->assertEquals($data[$key]['password'], $users->get($key)->password);
        }
    }

    public function testUpdateMultipleFromRequestWithoutFormattingFromFillables()
    {
        $originalUsers = $this->createMultipleUsers(3);
        $usersUpdatedArray = $originalUsers->toArray();
        foreach ($usersUpdatedArray as $key => $user) {
            unset($usersUpdatedArray[$key]['name']);
            $usersUpdatedArray[$key]['email'] = $this->faker->email;
        }
        $request = Request::create('test', 'GET', $usersUpdatedArray);
        $this->repository->setRequest($request);
        $updatedUsers = $this->repository->createOrUpdateMultipleFromRequest([], [], false);
        foreach ($updatedUsers as $key => $updatedUser) {
            $this->assertEquals($updatedUser->name, $originalUsers->get($key)->name);
            $this->assertNotEquals($updatedUser->email, $originalUsers->get($key)->email);
            $this->assertEquals($updatedUser->email, $usersUpdatedArray[$key]['email']);
            $this->assertEquals($updatedUser->password, $originalUsers->get($key)->password);
        }
    }

    public function testUpdateMultipleFromRequestWithFormattingFromFillables()
    {
        $originalUsers = $this->createMultipleUsers(3);
        $usersUpdatedArray = $originalUsers->toArray();
        foreach ($usersUpdatedArray as $key => $user) {
            unset($usersUpdatedArray[$key]['name']);
            $usersUpdatedArray[$key]['email'] = $this->faker->email;
        }
        $request = Request::create('test', 'GET', $usersUpdatedArray);
        $this->repository->setRequest($request);
        $updatedUsers = $this->repository->createOrUpdateMultipleFromRequest();
        foreach ($updatedUsers as $key => $updatedUser) {
            $this->assertNull($updatedUser->name);
            $this->assertNotEquals($updatedUser->email, $originalUsers->get($key)->email);
            $this->assertEquals($updatedUser->email, $usersUpdatedArray[$key]['email']);
            $this->assertEquals($updatedUser->password, $originalUsers->get($key)->password);
        }
    }

    public function testCreateMultipleFromRequestWithAttributesExceptionAndAddition()
    {
        $data = [
            '_token'  => 'token',
            '_method' => 'update',
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
            $this->generateFakeUserData(),
        ];
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $users = $this->repository->createOrUpdateMultipleFromRequest([
            '0.name'           => 'Michel',
            '0.remember_token' => 'token',
        ], [
            '1',
            '2',
        ]);
        $this->assertCount(1, $users);
        $this->assertEquals('Michel', $users->get(0)->name);
        $this->assertEquals($data[0]['email'], $users->get(0)->email);
        $this->assertEquals($data[0]['password'], $users->get(0)->password);
        $this->assertEquals('token', $users->get(0)->remember_token);
    }

    public function testCreateSingleFromRequest()
    {
        $data = $this->generateFakeUserData();
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $user = $this->repository->createOrUpdateFromRequest();
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['password'], $user->password);
    }

    public function testUpdateSingleFromRequestWithoutFormattingFromFillables()
    {
        $user = $this->createUniqueUser();
        $user->name = 'Jean';
        $user->remember_token = 'token';
        $data = $user->toArray();
        unset($data['email']);
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $updatedUser = $this->repository->createOrUpdateFromRequest([], [], false);
        $this->assertEquals('Jean', $updatedUser->name);
        $this->assertEquals($user->email, $updatedUser->email);
        $this->assertEquals($user->password, $updatedUser->password);
        $this->assertEquals('token', $updatedUser->remember_token);
    }

    public function testUpdateSingleFromRequestWithFormattingFromFillables()
    {
        $user = $this->createUniqueUser();
        $user->name = 'Jean';
        $user->remember_token = 'token';
        $data = $user->toArray();
        unset($data['email']);
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $updatedUser = $this->repository->createOrUpdateFromRequest();
        $this->assertEquals('Jean', $updatedUser->name);
        $this->assertNull($updatedUser->email);
        $this->assertEquals($user->password, $updatedUser->password);
        $this->assertEquals('token', $updatedUser->remember_token);
    }

    public function testDeleteFromRequest()
    {
        $user = $this->createUniqueUser();
        $request = Request::create('test', 'GET', $user->toArray());
        $this->repository->setRequest($request);
        $user->remember_token = null;
        $this->assertEquals([$user->toArray()], app(User::class)->all()->toArray());
        $this->repository->deleteFromRequest();
        $this->assertEmpty(app(User::class)->all());
    }

    public function testUpdateByPrimaryWithoutFormattingFromFillables()
    {
        $user = $this->createUniqueUser();
        $updatedUser = $this->repository->updateByPrimary($user->id, [
            'name'           => 'Jean',
            'remember_token' => 'token',
        ], false);
        $this->assertEquals('Jean', $updatedUser->name);
        $this->assertEquals($user->email, $updatedUser->email);
        $this->assertEquals($user->password, $updatedUser->password);
        $this->assertEquals('token', $updatedUser->remember_token);
    }

    public function testUpdateByPrimaryWithFormattingFromFillables()
    {
        $user = $this->createUniqueUser();
        $updatedUser = $this->repository->updateByPrimary($user->id, [
            'name'           => 'Jean',
            'remember_token' => 'token',
        ]);
        $this->assertEquals('Jean', $updatedUser->name);
        $this->assertEquals(null, $updatedUser->email);
        $this->assertEquals(null, $updatedUser->password);
        $this->assertEquals('token', $updatedUser->remember_token);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     * @expectedExceptionMessage No query results for model [Okipa\LaravelBaseRepository\Test\Models\User] 1
     */
    public function testUpdateByPrimaryFail()
    {
        $this->repository->updateByPrimary(1, []);
    }

    public function testDeleteFromArray()
    {
        $user = $this->createUniqueUser();
        $user->remember_token = null;
        $this->assertEquals($user->toArray(), app(User::class)->find($user->id)->toArray());
        $status = $this->repository->deleteFromArray(['id' => $user->id]);
        $this->assertTrue($status);
        $this->assertEmpty(app(User::class)->find($user->id));
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     * @expectedExceptionMessage No query results for model [Okipa\LaravelBaseRepository\Test\Models\User] 1
     */
    public function testDeleteFromArrayFail()
    {
        $this->repository->deleteFromArray(['id' => 1]);
    }

    public function testDeleteByPrimary()
    {
        $user = $this->createUniqueUser();
        $user->remember_token = null;
        $this->assertEquals($user->toArray(), app(User::class)->find($user->id)->toArray());
        $status = $this->repository->deleteByPrimary($user->id);
        $this->assertTrue($status);
        $this->assertEmpty(app(User::class)->find($user->id));
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     * @expectedExceptionMessage No query results for model [Okipa\LaravelBaseRepository\Test\Models\User] 1
     */
    public function testDeleteByPrimaryFail()
    {
        $this->repository->deleteByPrimary(1);
    }

    public function testDeleteAnotherModelByPrimary()
    {
        $user = $this->createUniqueUser();
        $company = $this->createUniqueCompany();
        $user->remember_token = null;
        $company->_token = null;
        $company->_method = null;
        $this->assertEquals([$user->toArray()], app(User::class)->all()->toArray());
        $this->assertEquals([$company->toArray()], app(Company::class)->all()->toArray());
        $this->repository->setModel(Company::class);
        $this->repository->deleteByPrimary($company->id);
        $this->assertEquals([$user->toArray()], app(User::class)->all()->toArray());
        $this->assertEmpty(app(Company::class)->all());
    }

    public function testDeleteMultipleFromPrimaries()
    {
        $users = $this->createMultipleUsers(5);
        $this->assertCount(5, $users);
        $primaries = $users->pluck('id')->toArray();
        $this->repository->deleteMultipleFromPrimaries($primaries);
        $this->assertEmpty(app(User::class)->all());
    }

    public function testDisableExceptDefaultAttributes()
    {
        $this->repository = app(CompanyRepositoryWithDisabledDefaultAttributesException::class);
        $data = array_merge([
            '_token'  => 'token',
            '_method' => 'update',
        ], $this->generateFakeCompanyData());
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $company = $this->repository->createOrUpdateFromRequest();
        $this->assertEquals($data['name'], $company->name);
        $this->assertEquals($data['_token'], $company->_token);
        $this->assertEquals($data['_method'], $company->_method);
    }

    /**
     * @expectedException \PDOException
     * @expectedExceptionMessage Integrity constraint violation: 19 NOT NULL constraint failed: companies.name
     */
    public function testCustomizeDefaultAttributesToExcept()
    {
        $this->repository = app(CompanyRepositoryWithCustomDefaultAttributesToExcept::class);
        $data = array_merge([
            '_token'  => 'token',
            '_method' => 'update',
        ], $this->generateFakeCompanyData());
        $request = Request::create('test', 'GET', $data);
        $this->repository->setRequest($request);
        $this->repository->createOrUpdateFromRequest();
    }

    public function testPaginateArrayResults()
    {
        $users = $this->createMultipleUsers(35);
        $paginatedUsersPageOne = $this->repository->paginateArrayResults($users->toArray(), 20);
        $this->assertCount(20, $paginatedUsersPageOne);
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginatedUsersPageOne);
        $request = Request::create('test', 'GET', [
            'page' => 2,
        ]);
        $this->repository->setRequest($request);
        $paginatedUsersPageTwo = $this->repository->paginateArrayResults($users->toArray(), 20);
        $this->assertCount(15, $paginatedUsersPageTwo);
        $this->assertInstanceOf(LengthAwarePaginator::class, $paginatedUsersPageTwo);
        $this->assertCount(
            20,
            array_diff($paginatedUsersPageOne->pluck('id')->toArray(), $paginatedUsersPageTwo->pluck('id')->toArray())
        );
    }

    public function testFindOneByPrimary()
    {
        $this->createMultipleUsers(5);
        $user = app(User::class)->find(rand(1, 5));
        $foundUser = $this->repository->findOneByPrimary($user->id);
        $this->assertEquals($user, $foundUser);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     * @expectedExceptionMessage No query results for model [Okipa\LaravelBaseRepository\Test\Models\User] 1
     */
    public function testFindOneByPrimaryFail()
    {
        $this->repository->findOneByPrimary(1);
    }

    public function testFindOneByPrimaryNull()
    {
        $user = $this->repository->findOneByPrimary(1, false);
        $this->assertNull($user);
    }

    public function testFindOneFromArray()
    {
        $this->createMultipleUsers(5);
        $user = app(User::class)->find(rand(1, 5));
        $foundUser = $this->repository->findOneFromArray(['id' => $user->id]);
        $this->assertEquals($user, $foundUser);
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     * @expectedExceptionMessage No query results for model [Okipa\LaravelBaseRepository\Test\Models\User].
     */
    public function testFindOneFromArrayFail()
    {
        $this->repository->findOneFromArray(['id' => 1]);
    }

    public function testFindOneFromArrayNull()
    {
        $user = $this->repository->findOneFromArray(['id' => 1], false);
        $this->assertNull($user);
    }

    public function testFindMultipleFromArray()
    {
        $data = $this->generateFakeUserData();
        app(User::class)->create($data);
        $data['email'] = $this->faker->email;
        app(User::class)->create($data);
        $users = $this->repository->findMultipleFromArray([
            'name'           => $data['name'],
            'remember_token' => null,
        ]);
        $this->assertCount(2, $users);
    }

    public function testGetAll()
    {
        $users = $this->createMultipleUsers(15);
        $users = $users->sortByDesc('name')->pluck('name');
        $foundUsers = $this->repository->getAll(['name'], 'name', 'desc')->pluck('name');
        $this->assertEquals($users, $foundUsers);
    }

    public function testMake()
    {
        $data = $this->generateFakeUserData();
        $user = $this->repository->make($data);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertEquals($data['password'], $user->password);
        $otherUser = $this->repository->make($data);
        $this->assertNotEquals(spl_object_hash($user), spl_object_hash($otherUser));
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     * @expectedExceptionMessage You must declare your repository $model attribute with an
     *                           Illuminate\Database\Eloquent\Model namespace to use this feature.
     */
    public function testRepositoryInstantiationWithNoModel()
    {
        $repository = app(UserRepositoryWithNoModel::class);
        $data = $this->generateFakeUserData();
        $repository->make($data);
    }

    public function testModelUniqueInstanceWithStoredInstance()
    {
        $storedUser = $this->createUniqueUser();
        $storedUser->remember_token = null;
        $user = app(UserRepository::class)->modelUniqueInstance();
        $this->assertEquals($storedUser->toArray(), $user->toArray());
    }

    public function testModelUniqueInstanceWithNoStoredInstance()
    {
        $user = app(UserRepository::class)->modelUniqueInstance();
        $this->assertInstanceOf(Model::class, $user);
    }

    public function testfindMultipleFromPrimaries()
    {
        $users = $this->createMultipleUsers(5);
        $foundUsers = app(UserRepository::class)->findMultipleFromPrimaries($users->pluck('id')->toArray());
        $this->assertEquals($foundUsers->pluck('id'), $foundUsers->pluck('id'));
    }

    public function testSetMissingFillableAttributesToNull()
    {
        $raw = [
            'name'           => false,
            'password'       => 'test',
            'remember_token' => 123,
        ];
        $data = app(UserRepository::class)->setMissingFillableAttributesToNull($raw);
        $this->assertEquals([
            'name'           => false,
            'email'          => null,
            'password'       => 'test',
            'remember_token' => 123,
        ], $data);
    }
}
