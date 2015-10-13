<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Classes\Repository;
use Classes\UsernameCriteria;
use Classes\UsernameTerminator;
use Orchestra\Testbench\TestCase;

/**
 * Class RepositoryTest
 * @package Daveawb\Tests
 */
class RepositoryTest extends TestCase {

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'tests');
        $app['config']->set('database.connections.tests', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--database' => 'tests',
            '--realpath' => realpath(__DIR__.'/migrations'),
        ]);

        Classes\User::create([
            "first_name" => "David",
            "last_name" => "Barker",
            "username" => "daveawb",
            "email" => "daveawb@hotmail.com",
            "password" => 'secret'
        ]);

        DB::beginTransaction();
    }

    public function tearDown()
    {
        DB::rollback();
    }

    public function testRepositoryIsCreated()
    {
        $this->assertInstanceOf('Daveawb\Repos\Contracts\RepositoryStandards', $this->repoFactory());
    }

    public function testRepositoryReturnsAClassName()
    {
        $this->assertStringMatchesFormat('Classes\%s', $this->repoFactory()->model());
    }

    public function testRepositoryRetrievesAModelByID()
    {
        $model = $this->repoFactory()->findBy('id', 1);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $model);
    }

    public function testRepositoryRetrievesAModelByAColumnIdentifier()
    {
        $model = $this->repoFactory()->findBy('email', 'daveawb@hotmail.com');

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $model);
        $this->assertEquals('daveawb@hotmail.com', $model->email);
    }

    public function testRepositoryRetrievesAModelWithSpecificColumns()
    {
        $repo = $this->repoFactory();

        $model = $repo->findBy('id', 1, ['id', 'username']);

        $this->assertNotNull($model->username);
        $this->assertNull($model->first_name);
    }

    public function testRepositoryRetrievesAllModels()
    {
        $collection = $this->repoFactory()->findAll();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $collection);
    }

    public function testRepositoryPersistsData()
    {
        $persisted = $this->repoFactory()->create([
            "first_name" => "Testy",
            "last_name" => "McTest",
            "username" => "mctester",
            "email" => "testy.mctest@mctesters.com",
            "password" => "testymctest"
        ]);

        $model = $this->repoFactory()->findBy('email', "testy.mctest@mctesters.com");

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $persisted);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $model);
        $this->assertEquals("Testy", $model->first_name);
        $this->assertEquals("mctester", $model->username);
        $this->assertEquals("testy.mctest@mctesters.com", $model->email);
        $this->assertTrue(Hash::check("testymctest", $model->password));
    }

    public function testRepositoryUpdatesData()
    {
        $model = $this->repoFactory()->findBy('id', 1);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $model);
        $this->assertEquals("David", $model->first_name);
        $this->assertEquals("Barker", $model->last_name);
        $this->assertEquals("daveawb@hotmail.com", $model->email);
        $this->assertTrue(Hash::check($model->salt . "secret", $model->password));

        $this->repoFactory()->update([
            "first_name" => "Not David Barker"
        ], 'id', 1);

        $model = $this->repoFactory()->findBy('id', 1);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $model);
        $this->assertEquals("Not David Barker", $model->first_name);
        $this->assertEquals("daveawb@hotmail.com", $model->email);
        $this->assertTrue(Hash::check($model->salt . "secret", $model->password));
    }

    /**
     * @expectedException \Daveawb\Repos\Exceptions\RepositoryException
     * @expectedExceptionMessage Model does not exist.
     */
    public function testRepositoryDeletesData()
    {
        $repo = $this->repoFactory();

        $repo->delete('id', 1);

        $repo->findBy('id', 1);
    }

    public function testRepositoryPaginatesData()
    {
        $repo = $this->repoFactory();

        $data = $repo->paginate(1);

        $this->assertInstanceOf('Illuminate\Pagination\AbstractPaginator', $data);
        $this->assertEquals($data->currentPage(), 1);
        $this->assertEquals($data->perPage(), 1);
    }

    public function testRepositoryPaginatesDataWithSpecifiedColumns()
    {
        $repo = $this->repoFactory();

        $data = $repo->paginate(1, ['first_name']);

        $this->assertInstanceOf('Illuminate\Pagination\AbstractPaginator', $data);

        foreach ($data as $model)
        {
            $this->assertNotNull($model->first_name);
            $this->assertNull($model->username);
        }
    }

    public function testRepositoryFindByMethodRetrievesResults()
    {
        $repo = $this->repoFactory();

        $model = $repo->findByMethod('first');

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $model);

        $collection = $repo->findByMethod('get');

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $collection);
    }

    /**
     * @expectedException \Daveawb\Repos\Exceptions\RepositoryException
     * @expectedExceptionMessage Model does not exist.
     */
    public function testRepositoryThrowsExceptionWhenUserDoesNotExist()
    {
        $repo = $this->repoFactory();

        $repo->findBy('id', 100000);
    }

    public function testRepositoryAcceptsCriteriaWhenGettingAllData()
    {
        $repo = $this->repoFactory();

        $repo->pushCriteria($this->usernameCriteria());

        $data = $repo->findAll(['id', 'username']);

        $this->assertCount(1, $data);
    }

    /**
     * @expectedException \Daveawb\Repos\Exceptions\RepositoryException
     * @expectedExceptionMessage Model does not exist.
     */
    public function testRepositoryAcceptsCriteriaWhenGettingDataByField()
    {
        $repo = $this->repoFactory();

        $repo->pushCriteria($this->usernameCriteria());

        $data = $repo->findBy('id', 2, ['id', 'username']);

        $this->assertNull($data);
    }


    public function testRepositoryAcceptsCriteriaWhenGettingDataByFieldTest2()
    {
        $repo = $this->repoFactory();

        $repo->pushCriteria($this->usernameCriteria());

        $data = $repo->findBy('id', 1, ['id', 'username']);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $data);
    }

    public function testRepositoryAcceptsCriteriaAndCustomFindMethod()
    {
        $repo = $this->repoFactory();

        $data = $repo->getByCriteria($this->usernameCriteria())->findByMethod('first');

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $data);
    }

    public function testRepositoryGetsByTerminableCriteria()
    {
        $repo = $this->repoFactory();

        $data = $repo->getByTerminator(UsernameTerminator::class);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $data);
        $this->assertEquals('daveawb', $data->username);
    }

    private function repoFactory()
    {
        return new Repository($this->app, new Collection());
    }

    private function usernameCriteria()
    {
        return UsernameCriteria::class;
    }
}