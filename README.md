# Laravel Action class generator
This is a quick package I've created to help generate action classes for Laravel models. It'll generate a quick and simple action classes for creating, reading, updating and deleting models.

As well as this, it'll generate validation rules for the model based on the table of the model passed. These are used on the create/update action classes.  **Note:** It's worth pointing out that getting every single validation rule based on table column/name is impossible. Because of this, do check the validation rules it generates and edit them if needs be.

*The action classes are a starting point for your project. Do edit them if needs be.*

<a name="installation"></a>
## Installation
Requires [Laravel](https://laravel.com/ "Laravel").
Use composer to install it as described below:
```
composer require applejack21/laravel-actions
```

<a name="usage"></a>
## Usage
The package contains a command to run to start making the files ``laravel-actions:create-actions <model_name>`` (if you don't specify a model name it'll ask for one).
The command also has a few arguments you can pass to customise it:
- ``--table-name``: The table name for this model. If not entered, will default to plural of the model passed.
- ``--no-create`` Don't make a create action class.
- ``--no-read`` Don't make a read/get action class.
- ``--no-update`` Don't make an update action class.
- ``--no-delete`` Don't make a delete action class.
- ``--perma-delete`` Whether the delete action class should have a perma delete option.

The files are then put into the folder ``app\Actions\<model_name>``. If there are files already in this folder it'll prompt you to replace these with the ones generated instead.

<a name="examples"></a>
## Examples
See the ``examples`` folder for a list of action files that have been generated using this command based on the default User model from Laravel. I shall try my best to remember to update the examples alongside code changes.

<a name="suggestions"></a>
## Suggestions
You're free to fork this and modify the code as you wish to add in your own extra functionality. However, if you have any suggestions that should be added to this package, do create an issue with the suggestion or even a PR with your modified code!
