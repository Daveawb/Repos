# Repositories for Laravel 5.1

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

### Criteria

### Terminators

### Credits
[A repository pattern for Laravel 5.1 by andersao](https://github.com/andersao/l5-repository)