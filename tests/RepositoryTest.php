<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Classes\Repository;
use Classes\NameCriteria;
use Classes\NameTerminator;
use Orchestra\Testbench\TestCase;

/**
 * Class RepositoryTest
 * @package Daveawb\Tests
 */
class RepositoryTest extends TestCase {

    use DatabaseMigrations;
    use DatabaseTransactions;

    protected function getEnvironmentSetUp($app)
    {
        $app->register(\Orchestra\Database\ConsoleServiceProvider::class);

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

        $this->loadLaravelMigrations([
            '--database' => 'tests',
            '--path' => 'migrations'
        ]);

        Classes\User::create([
            "name" => "David Barker",
            "email" => "daveawb@hotmail.com",
            "password" => 'secret'
        ]);

        Classes\User::create([
            "name" => "Simon Holloway",
            "email" => "simon@syhol.io",
            "password" => 'secret'
        ]);
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
        $model = $this->repoFactory()->findById(1);

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

        $model = $repo->findBy('id', 1, ['id']);

        $this->assertNull($model->name);
    }

    public function testRepositoryRetrievesAllModels()
    {
        $collection = $this->repoFactory()->findAll();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $collection);
    }

    public function testRepositoryPersistsData()
    {
        $persisted = $this->repoFactory()->create([
            "name" => "Testy McTest",
            "email" => "testy.mctest@mctesters.com",
            "password" => "testymctest"
        ]);

        $model = $this->repoFactory()->findBy('email', "testy.mctest@mctesters.com");

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $persisted);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $model);
        $this->assertEquals("Testy McTest", $model->name);
        $this->assertEquals("testy.mctest@mctesters.com", $model->email);
        $this->assertTrue(Hash::check("testymctest", $model->password));
    }

    public function testRepositoryPersistsDataUsingACallable()
    {
        $persisted = $this->repoFactory()->create(function($model) {
            $model->fill([
                "name" => "Testy McTest",
                "email" => "testy.mctest@mctesters.com",
                "password" => "testymctest"
            ]);
        });

        $model = $this->repoFactory()->findBy('email', "testy.mctest@mctesters.com");

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $persisted);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $model);
        $this->assertEquals("Testy McTest", $model->name);
        $this->assertEquals("testy.mctest@mctesters.com", $model->email);
        $this->assertTrue(Hash::check("testymctest", $model->password));
    }

    public function testRepositoryUpdatesData()
    {
        $model = $this->repoFactory()->findBy('id', 1);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $model);
        $this->assertEquals("David Barker", $model->name);
        $this->assertEquals("daveawb@hotmail.com", $model->email);
        $this->assertTrue(Hash::check($model->salt . "secret", $model->password));

        $model = $this->repoFactory()->update([
            "name" => "Not David Barker"
        ], 'id', 1);

//        $model = $this->repoFactory()->findBy('id', 1);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $model);
        $this->assertEquals("Not David Barker", $model->name);
        $this->assertEquals("daveawb@hotmail.com", $model->email);
        $this->assertTrue(Hash::check($model->salt . "secret", $model->password));
    }

    public function testRepositoryUpdatesWithExistingModel()
    {
        $model = $this->repoFactory()->findBy('id', 1);

        $result = $this->repoFactory()->updateModel([
            "name" => "Not David Barker"
        ], $model);

        $this->assertTrue($result);
    }

    public function testRepositoryDoesNotUpdateExistingModelWhenNotModified()
    {
        $model = $this->repoFactory()->findBy('id', 1);

        $result = $this->repoFactory()->updateModel([
            "name" => $model->name
        ], $model);

        $this->assertFalse($result);
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

        $data = $repo->paginate(1, ['name']);

        $this->assertInstanceOf('Illuminate\Pagination\AbstractPaginator', $data);

        foreach ($data as $model)
        {
            $this->assertNotNull($model->name);
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

        $data = $repo->getByTerminator(NameTerminator::class);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $data);
        $this->assertEquals('David Barker', $data->name);
    }

    public function testRepositoryFlushesModel()
    {
        $repo = $this->repoFactory();

        $model = $repo->getModel();

        $repo->flushModel();

        $this->assertNotSame($model, $repo->getModel());
    }

    public function testRepositoryNewInstance()
    {
        $repo = $this->repoFactory();

        $repo->pushCriteria($this->usernameCriteria());

        $newRepo = $repo->newInstance();

        $this->assertNotSame($repo, $newRepo);
    }

    public function testRepositoryGetsDifferentResultsBetweenInstances()
    {
        $repo = $this->repoFactory();

        $repo->pushCriteria($this->usernameCriteria());

        $newRepo = $repo->newInstance();

        $this->assertEmpty($newRepo->getCriteria());
        $this->assertNotEmpty($repo->getCriteria());

        $this->assertTrue($repo->findAll()->count() === 1);
        $this->assertTrue($newRepo->findAll()->count() > 1);
    }

    private function repoFactory()
    {
        return new Repository($this->app, new Collection());
    }

    private function usernameCriteria()
    {
        return NameCriteria::class;
    }
}