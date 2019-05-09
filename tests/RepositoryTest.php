<?php /** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection PhpParamsInspection */

use Classes\NameCriteria;
use Classes\NameTerminator;
use Classes\Repository;
use Classes\User;
use Daveawb\Repos\Contracts\RepositoryStandards;
use Daveawb\Repos\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Orchestra\Testbench\TestCase;

/**
 * Class RepositoryTest
 * @package Daveawb\Tests
 */
class RepositoryTest extends TestCase {

    /** @var User[] */
    protected $users = [];

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'tests');
        $app['config']->set('database.connections.tests', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        $this->users[] = Classes\User::create([
            "first_name" => "Anette",
            "last_name" => "Curtain",
            "username" => "anette",
            "email" => "anette.curtain@example.org",
            "password" => 'secret'
        ]);

        $this->users[] = Classes\User::create([
            "first_name" => "Wayne",
            "last_name" => "King",
            "username" => "wayne",
            "email" => "wayne.king@example.org",
            "password" => 'secret'
        ]);
    }

    public function testRepositoryIsCreated()
    {
        $this->assertInstanceOf(RepositoryStandards::class, $this->repoFactory());
    }

    public function testRepositoryReturnsAClassName()
    {
        $this->assertStringMatchesFormat('Classes\%s', $this->repoFactory()->model());
    }

    public function testRepositoryRetrievesAModelByID()
    {
        $model = $this->repoFactory()->findById(1);

        $this->assertInstanceOf(Model::class, $model);
    }

    public function testRepositoryRetrievesAModelByAColumnIdentifier()
    {
        $user = $this->users[0];

        $model = $this->repoFactory()->findBy('email', $user->email);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals($user->email, $model->email);
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

        $this->assertInstanceOf(EloquentCollection::class, $collection);
    }

    public function testRepositoryPersistsData()
    {
        $rawPassword = "testing123";

        $persisted = $this->repoFactory()->create([
            "first_name" => "Testy",
            "last_name" => "McTest",
            "username" => "testy",
            "email" => "testy.mctest@mctesters.com",
            "password" => $rawPassword
        ]);

        $model = $this->repoFactory()->findBy('email', $persisted->email);

        $this->assertInstanceOf(Model::class, $persisted);
        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals($persisted->first_name, $model->first_name);
        $this->assertEquals($persisted->last_name, $model->last_name);
        $this->assertEquals($persisted->email, $model->email);
        $this->assertTrue(Hash::check($rawPassword, $model->password));
    }

    public function testRepositoryPersistsDataUsingACallable()
    {
        $rawPassword = "testing123";

        $persisted = $this->repoFactory()->create(function(Model $model) use ($rawPassword) {
            $model->fill([
                "first_name" => "Testy",
                "last_name" => "McTest",
                "username" => "testy",
                "email" => "testy.mctest@mctesters.com",
                "password" => $rawPassword
            ]);
        });

        $model = $this->repoFactory()->findBy('email', "testy.mctest@mctesters.com");

        $this->assertInstanceOf(Model::class, $persisted);
        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals($persisted->first_name, $model->first_name);
        $this->assertEquals($persisted->last_name, $model->last_name);
        $this->assertEquals($persisted->email, $model->email);
        $this->assertTrue(Hash::check($rawPassword, $model->password));
    }

    public function testRepositoryUpdatesData()
    {
        $control = $this->users[0];
        $model = $this->repoFactory()->findBy('id', $control->id);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals($control->first_name, $model->first_name);
        $this->assertEquals($control->email, $model->email);
        $this->assertEquals($control->last_name, $model->last_name);

        $model = $this->repoFactory()->update([
            "first_name" => "Not Wayne"
        ], 'id', $control->id);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals("Not Wayne", $model->first_name);
        $this->assertEquals($control->email, $model->email);
    }

    public function testRepositoryUpdatesWithExistingModel()
    {
        $model = $this->repoFactory()->findBy('id', 1);

        $result = $this->repoFactory()->updateModel([
            "first_name" => "Not David Barker"
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

    public function testRepositoryDeletesData()
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Model does not exist.");

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
        }
    }

    public function testRepositoryFindByMethodRetrievesResults()
    {
        $repo = $this->repoFactory();

        $model = $repo->findByMethod('first');

        $this->assertInstanceOf(Model::class, $model);

        $collection = $repo->findByMethod('get');

        $this->assertInstanceOf(EloquentCollection::class, $collection);
    }

    public function testRepositoryThrowsExceptionWhenUserDoesNotExist()
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Model does not exist.");

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

    public function testRepositoryAcceptsCriteriaWhenGettingDataByField()
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Model does not exist.");

        $repo = $this->repoFactory();

        $repo->pushCriteria($this->usernameCriteria());

        $user = $this->users[0];

        $data = $repo->findBy('id', $user->id, ['id', 'username']);

        $this->assertNull($data);
    }

    public function testRepositoryAcceptsCriteriaAndCustomFindMethod()
    {
        $repo = $this->repoFactory();

        $data = $repo->getByCriteria($this->usernameCriteria())->findByMethod('first');

        $this->assertInstanceOf(Model::class, $data);
    }

    public function testRepositoryGetsByTerminableCriteria()
    {
        $repo = $this->repoFactory();

        $data = $repo->getByTerminator(NameTerminator::class);

        $this->assertInstanceOf(Model::class, $data);
        $this->assertEquals("Wayne", $data->first_name);
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

    private function repoFactory(): Repository
    {
        return new Repository($this->app, new Collection());
    }

    private function usernameCriteria()
    {
        return NameCriteria::class;
    }
}
