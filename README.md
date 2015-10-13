# Laravel Smart Repositories

A repository package to add easy to use repositories to any Laravel project utilising Eloquent as the primary DAL. Tested in Laravel 5.1.

### Installation
`composer require daveawb/repos`

### Usage
#### Create a repository class
````
namespace App\Repositories;

class Repository extends \Daveawb\Repos\Repository
{
    /**
     * Return the models class name
     *
     * @return string
     */
    public function model()
    {
        return App\User::class;
    }
}
````

#### Available API methods
| method | signature | return |
|--------|-----------|--------|
| findAll | array $columns = ['*'] | Illuminate\Database\Eloquent\Collection |
| findBy  | $field, $id, array $columns = ['*'] | Illuminate\Database\Eloquent\Model |
| findByMethod | $method | array $columns = ['*'] | \Illuminate\Database\Eloquent\Collection | \Illuminate\Database\Eloquent\Model | void |

### Criteria

### Terminators