<?php

namespace Applejack21\LaravelActions\Commands;

use Applejack21\LaravelActions\Actions\GetSchema;
use Applejack21\LaravelActions\Actions\MakeCreateFile;
use Applejack21\LaravelActions\Actions\MakeReadFile;
use Applejack21\LaravelActions\Actions\MakeUpdateFile;
use Applejack21\LaravelActions\Actions\MakeDeleteFile;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class CreateAction extends Command implements PromptsForMissingInput
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'laravel-actions:create-actions
								{model : The name of the model to generate classes for}
								{--table-name= : The table name for this model. If not entered, will default to plural of the model passed}
								{--perma-delete : Whether the delete action class should have a perma delete option}
								{--no-create : Don\'t make a create action class}
								{--no-read : Don\'t make a read/get action class}
								{--no-update : Don\'t make an update action class}
								{--no-delete : Don\'t make a delete action class}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create CRUD action class files for the entered model';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$model = $this->argument('model');
		$modelUp = Str::ucfirst($model);
		$tableName = $this->option('table-name');
		$permaDelete = $this->option('perma-delete');
		$noCreate = $this->option('no-create');
		$noRead = $this->option('no-read');
		$noUpdate = $this->option('no-update');
		$noDelete = $this->option('no-delete');

		// no table name passed, use plural of model name
		if (!$tableName) {
			$tableName = Str::plural($model);
		}

		$this->info('Model to use: ' . $modelUp);
		$this->info('Table to use: ' . $tableName);

		// prompt them to double check the inputs
		if ($this->confirm('Is the above info correct?', true)) {

			// returns collection/string if error
			$columns = (new GetSchema())->execute($tableName);

			// table exists?
			if (gettype($columns) == 'string') {
				$this->error($columns);
				return;
			}

			// get the validation rules for the table
			$validationRules = $this->generateValidationRules($columns, $tableName);

			// create the files
			if (!$noCreate) {
				$replace = false;

				// check if a file exists for the file name
				$actionName = "Create{$modelUp}.php";
				$fileName = app_path("Actions/{$modelUp}/{$actionName}");

				if (File::exists($fileName)) {
					if ($this->confirm("A file called $actionName already exists in app/Actions/$modelUp directory. Should this be replaced?", false)) {
						$replace = true;
						File::delete($fileName);
						$this->warn("File deleted.");
					} else {
						$this->info("File kept intact.");
					}
				} else {
					$replace = true;
				}

				if ($replace) {
					$createFile = (new MakeCreateFile())->execute($modelUp, $actionName, $validationRules);
					$this->info("$actionName created in app/Actions/$modelUp directory.");
				}

				$this->newLine();
			}

			if (!$noRead) {
				$replace = false;

				$actionName = "Get{$modelUp}s.php";
				$fileName = app_path("Actions/{$modelUp}/{$actionName}");

				if (File::exists($fileName)) {
					if ($this->confirm("A file called $actionName already exists in app/Actions/$modelUp directory. Should this be replaced?", false)) {
						$replace = true;
						File::delete($fileName);
						$this->warn("File deleted.");
					} else {
						$this->info("File kept intact.");
					}
				} else {
					$replace = true;
				}

				if ($replace) {
					$readFile = (new MakeReadFile())->execute($modelUp, $actionName);
					$this->info("$actionName file created in app/Actions/$modelUp directory.");
				}

				$this->newLine();
			}

			if (!$noUpdate) {
				$replace = false;

				$actionName = "Update{$modelUp}.php";
				$fileName = app_path("Actions/{$modelUp}/{$actionName}");

				if (File::exists($fileName)) {
					if ($this->confirm("A file called $actionName already exists in app/Actions/$modelUp directory. Should this be replaced?", false)) {
						$replace = true;
						File::delete($fileName);
						$this->warn("File deleted.");
					} else {
						$this->info("File kept intact.");
					}
				} else {
					$replace = true;
				}

				if ($replace) {
					$updateFile = (new MakeUpdateFile())->execute($modelUp, $actionName, $validationRules);
					$this->info("$actionName file created in app/Actions/$modelUp directory.");
				}

				$this->newLine();
			}

			if (!$noDelete) {
				$replace = false;

				$actionName = "Delete{$modelUp}.php";
				$fileName = app_path("Actions/{$modelUp}/{$actionName}");

				if (File::exists($fileName)) {
					if ($this->confirm("A file called $actionName already exists in app/Actions/$modelUp directory. Should this be replaced?", false)) {
						$replace = true;
						File::delete($fileName);
						$this->warn("File deleted.");
					} else {
						$this->info("File kept intact.");
					}
				} else {
					$replace = true;
				}

				if ($replace) {
					$deleteFile = (new MakeDeleteFile())->execute($modelUp, $actionName, $permaDelete);
					$this->info("$actionName file created in app/Actions/$modelUp directory.");
				}

				$this->newLine();
			}

			$this->info('File(s) created (if any)! Please check your app/Actions/' . $modelUp . ' folder for the action classes.');
			$this->warn("Please note: It's impossible to capture all column names, types, validation rules etc... So double check the files are correct.");
			$this->warn("And remember to adjust the files generated as your project requires. (Sorry for the spaces where '->' exist)");
			$this->warn("If you have any unique fields within the update action, I strongly recommend to add in the 'ignore' rule to update the model record as described here - https://laravel.com/docs/master/validation#rule-unique");
		} else {
			$this->info('See you soon.');
		}
	}

	/**
	 * Prompt for missing input arguments using the returned questions.
	 *
	 * @return array
	 */
	protected function promptForMissingArgumentsUsing()
	{
		return [
			'model' => 'What model are these action classes for?',
		];
	}

	/**
	 * Generate validation rules for the table, using common table names/type
	 */
	public function generateValidationRules(Collection $columns, string $tableName)
	{
		// validate array for action class
		$validationRules = [];

		// column names
		$colNames = $columns->pluck('name');

		foreach ($columns as $column) {
			$name = $column['name'];

			// skip timestamps and id
			if (in_array($name, ['created_at', 'updated_at', 'id', 'deleted_at'])) {
				continue;
			}

			// rules for this column
			$rules = [];

			// try and capture common occurrences/field names
			if (!$column['notnull']) {
				$rules[] = 'nullable';
			} else {
				$rules[] = 'required';
			}

			if ($column['is_unique']) {
				$rules[] = 'unique:' . $tableName . ',' . $name;
			}

			if ($column['type'] === 'string' && $column['length']) {
				$rules[] = 'max:' . $column['length'];
			}

			if ($name === 'email') {
				$rules[] = 'email';
			}

			if ($column['type'] === 'boolean') {
				$rules[] = 'boolean';
			}

			if ($name === 'url') {
				$rules[] = 'url';
			}

			if ($name === 'password') {
				$rules[] = 'confirmed';
			}

			if ($name === 'role') {
				$rules[] = 'in:super_admin,admin,customer';
			}

			if ($name === 'start_date') {
				$rules[] = 'date';

				// if we also have an end date
				if ($colNames->contains('end_date')) {
					$rules[] = 'before:end_date';
				}
			}

			if ($name === 'end_date') {
				$rules[] = 'date';

				// if we also have a start date
				if ($colNames->contains('start_date')) {
					$rules[] = 'after:start_date';
				}
			}

			// add exists for foreign keys
			if ($column['is_foreign_key']) {
				$rules[] = 'exists:' . $column['referenced_table'];
			}

			if ($name === 'terms') {
				$rules[] = 'accepted';
			}

			if ($column['type'] === 'integer') {
				$rules[] = 'integer';
			}

			if ($name === 'uuid') {
				$rules[] = 'uuid';
			}

			if ($name === 'ulid') {
				$rules[] = 'ulid';
			}

			// add rules to overall validation ruleset
			$validationRules[$name] = implode('|', $rules);
		}

		return $validationRules;
	}
}
